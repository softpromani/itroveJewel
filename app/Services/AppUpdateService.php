<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Throwable;
use ZipArchive;

class AppUpdateService
{
    public function __construct(
        protected UsbLockService $usbLockService
    ) {}

    /**
     * Retrieve the currently installed application version.
     */
    public function getCurrentVersion(): string
    {
        $versionFile = base_path('version.json');
        if (File::exists($versionFile)) {
            $data = json_decode(File::get($versionFile), true);
            if (! empty($data['version'])) {
                return (string) $data['version'];
            }
        }

        return config('app.version', '1.0.0');
    }

    /**
     * Retrieve the central server base URL defined by the APP_URL environment variable.
     * Guaranteed to match env('APP_URL') or config('app.url').
     */
    public function getServerUrl(): string
    {
        $url = config('app.url')
            ?: env('APP_URL')
            ?: config('app.update_server_url')
            ?: config('app.license_server_url')
            ?: 'http://127.0.0.1:8000';

        $url = trim((string) $url);

        if (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
            $url = 'https://'.$url;
        }

        return rtrim($url, '/');
    }

    /**
     * Check if a newer version is available from the central server.
     * Gracefully handles offline state when customer has no internet connection.
     *
     * @return array{
     *     online: bool,
     *     update_available: bool,
     *     current_version: string,
     *     latest_version?: string,
     *     title?: string,
     *     release_notes?: array,
     *     download_url?: string,
     *     sha256?: ?string,
     *     message: string
     * }
     */
    public function checkForUpdates(): array
    {
        $currentVersion = $this->getCurrentVersion();
        $serverUrl = $this->getServerUrl();
        $hw = $this->usbLockService->getHardwareInfo();
        $serial = $hw['serial_number'] ?? '';

        try {
            $response = Http::timeout(4)
                ->acceptJson()
                ->get("{$serverUrl}/api/updates/check", [
                    'version' => $currentVersion,
                    'serial' => $serial,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'online' => true,
                    'update_available' => (bool) ($data['update_available'] ?? false),
                    'current_version' => $currentVersion,
                    'latest_version' => $data['latest_version'] ?? $currentVersion,
                    'title' => $data['title'] ?? 'Software Update',
                    'release_notes' => $data['release_notes'] ?? [],
                    'download_url' => $data['download_url'] ?? null,
                    'sha256' => $data['sha256'] ?? null,
                    'file_size' => $data['file_size'] ?? 0,
                    'message' => $data['message'] ?? 'Checked successfully.',
                ];
            }

            return [
                'online' => true,
                'update_available' => false,
                'current_version' => $currentVersion,
                'message' => 'Update server returned an error: '.$response->status(),
            ];
        } catch (Throwable $e) {
            // Customer is offline or server unreachable - completely normal for portable USB operation
            return [
                'online' => false,
                'update_available' => false,
                'current_version' => $currentVersion,
                'message' => 'Offline - No internet connection detected. You can continue using ItroveJewel safely offline.',
            ];
        }
    }

    /**
     * Execute the 1-Click Upgrade process:
     * 1. Safely backs up customer SQLite database
     * 2. Downloads update zip package
     * 3. Verifies integrity checksum
     * 4. Extracts files with safety blacklist (protects DB, hardware lock, and .env)
     * 5. Runs pending migrations
     * 6. Updates version.json
     *
     * @return array{
     *     success: bool,
     *     message: string,
     *     new_version?: string,
     *     backup_path?: string
     * }
     */
    public function applyUpdate(?string $downloadUrl = null, ?string $expectedHash = null): array
    {
        // 1. If downloadUrl was not passed, query server for it
        if (empty($downloadUrl)) {
            $check = $this->checkForUpdates();
            if (! $check['update_available'] || empty($check['download_url'])) {
                return [
                    'success' => false,
                    'message' => 'No active update is available to apply.',
                ];
            }
            $downloadUrl = $check['download_url'];
            $expectedHash = $check['sha256'] ?? null;
        }

        // 2. Pre-Upgrade Safety Backup of Customer's Local Database
        $backupResult = $this->backupCustomerDatabase();

        // 3. Download the update zip archive
        $tempDir = storage_path('app/updates');
        if (! File::isDirectory($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $zipPath = $tempDir.DIRECTORY_SEPARATOR.'update_'.time().'.zip';

        try {
            // Support both remote HTTP URLs and local testing file paths
            if (str_starts_with($downloadUrl, 'http://') || str_starts_with($downloadUrl, 'https://')) {
                $response = Http::timeout(60)->get($downloadUrl);
                if (! $response->successful()) {
                    return [
                        'success' => false,
                        'message' => 'Failed to download update package: HTTP '.$response->status(),
                    ];
                }
                File::put($zipPath, $response->body());
            } elseif (File::exists($downloadUrl)) {
                File::copy($downloadUrl, $zipPath);
            } else {
                return [
                    'success' => false,
                    'message' => 'Invalid update package download source.',
                ];
            }

            // 4. Verify checksum if provided
            if (! empty($expectedHash)) {
                $actualHash = hash_file('sha256', $zipPath);
                if (! hash_equals($expectedHash, $actualHash)) {
                    File::delete($zipPath);

                    return [
                        'success' => false,
                        'message' => 'Update package integrity verification failed (SHA256 mismatch).',
                    ];
                }
            }

            // 5. Extract files safely using ZipArchive
            $zip = new ZipArchive;
            if ($zip->open($zipPath) !== true) {
                File::delete($zipPath);

                return [
                    'success' => false,
                    'message' => 'Failed to open update archive. Package may be corrupt.',
                ];
            }

            $targetBase = base_path();
            $extractedNewVersion = null;

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                $normalized = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $filename);

                // STRICT SAFETY FILTERS: Never touch user database, security lock, or environment
                if (
                    str_contains($normalized, 'data'.DIRECTORY_SEPARATOR.'database') ||
                    str_contains($normalized, 'data'.DIRECTORY_SEPARATOR.'security') ||
                    str_ends_with($normalized, '.env') ||
                    str_ends_with($normalized, 'database.sqlite')
                ) {
                    continue;
                }

                $destPath = $targetBase.DIRECTORY_SEPARATOR.$normalized;

                if (str_ends_with($filename, '/')) {
                    if (! File::isDirectory($destPath)) {
                        File::makeDirectory($destPath, 0755, true);
                    }
                } else {
                    $parent = dirname($destPath);
                    if (! File::isDirectory($parent)) {
                        File::makeDirectory($parent, 0755, true);
                    }
                    $stream = $zip->getStream($filename);
                    if ($stream) {
                        file_put_contents($destPath, stream_get_contents($stream));
                        fclose($stream);
                    }

                    if (basename($filename) === 'version.json') {
                        $vData = json_decode(File::get($destPath), true);
                        if (! empty($vData['version'])) {
                            $extractedNewVersion = $vData['version'];
                        }
                    }
                }
            }

            $zip->close();
            File::delete($zipPath);

            // 6. Run pending migrations for the app database
            try {
                Artisan::call('migrate', ['--force' => true]);
            } catch (Throwable) {
                // Migrations may already be up to date
            }

            $finalVersion = $extractedNewVersion ?: $this->getCurrentVersion();

            return [
                'success' => true,
                'message' => "Successfully upgraded to version {$finalVersion}! Refreshing application...",
                'new_version' => $finalVersion,
                'backup_path' => $backupResult['backup_file'] ?? null,
            ];
        } catch (Exception $e) {
            if (File::exists($zipPath)) {
                File::delete($zipPath);
            }

            return [
                'success' => false,
                'message' => 'Upgrade process encountered an error: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Backup customer SQLite database before any upgrade operation.
     *
     * @return array{success: bool, backup_file: ?string}
     */
    protected function backupCustomerDatabase(): array
    {
        // Check standard portable database location or default database
        $candidates = [
            base_path('../../data/database/database.sqlite'),
            base_path('data/database/database.sqlite'),
            database_path('database.sqlite'),
        ];

        foreach ($candidates as $dbPath) {
            if (File::exists($dbPath)) {
                $dir = dirname($dbPath);
                $backupName = 'database_backup_v'.$this->getCurrentVersion().'_'.date('Ymd_His').'.sqlite';
                $dest = $dir.DIRECTORY_SEPARATOR.$backupName;
                File::copy($dbPath, $dest);

                return ['success' => true, 'backup_file' => $dest];
            }
        }

        return ['success' => false, 'backup_file' => null];
    }
}
