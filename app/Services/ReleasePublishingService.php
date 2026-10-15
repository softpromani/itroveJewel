<?php

namespace App\Services;

use App\Models\AppRelease;
use Exception;
use Illuminate\Support\Facades\File;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class ReleasePublishingService
{
    /**
     * Files and directory patterns strictly excluded from client updates.
     * Prevents admin routes, central license databases, customer data, and tests from being bundled.
     *
     * @var list<string>
     */
    protected array $excludedPatterns = [
        'app/Http/Controllers/Admin*',
        'app/Http/Controllers/Api/LicenseVerificationController.php',
        'app/Http/Controllers/Api/UpdateServerController.php',
        'app/Console/Commands/Admin*',
        'app/Console/Commands/AppPublishReleaseCommand.php',
        'resources/views/admin*',
        'routes/admin.php',
        'database/admin.sqlite*',
        'database/test_admin.sqlite*',
        'database/migrations/admin*',
        'storage/app/fresh_installers*',
        'storage/app/releases*',
        'bootstrap/cache/*.php',
        'data/database*',
        'data/security*',
        '.usb_hardware_lock.json',
        '*/tests/*',
        '*.git*',
        'phpunit.xml*',
    ];

    /**
     * Package and publish an application release to the central server.
     *
     * @param  list<string>  $releaseNotes
     *
     * @throws Exception
     */
    public function publishRelease(
        string $version,
        string $title,
        array $releaseNotes = [],
        string $minSupportedVersion = '1.0.0',
        bool $isActive = true,
        ?string $customDownloadUrl = null
    ): AppRelease {
        @set_time_limit(300);

        $version = trim($version);
        if (empty($version)) {
            throw new Exception('A valid version string is required.');
        }

        $releasesDir = storage_path('app/releases');
        if (! File::isDirectory($releasesDir)) {
            File::makeDirectory($releasesDir, 0755, true);
        }

        $zipPath = $releasesDir.DIRECTORY_SEPARATOR."itrovejewel-update-{$version}.zip";
        if (File::exists($zipPath)) {
            File::delete($zipPath);
        }

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception("Failed to create zip archive at {$zipPath}");
        }

        $base = base_path();
        $sourceFolders = ['app', 'bootstrap', 'config', 'database', 'public', 'resources', 'routes'];

        foreach ($sourceFolders as $folder) {
            $sourceDir = $base.DIRECTORY_SEPARATOR.$folder;
            if (! File::isDirectory($sourceDir)) {
                continue;
            }

            $directory = new RecursiveDirectoryIterator(
                $sourceDir,
                RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::FOLLOW_SYMLINKS
            );

            $filter = new RecursiveCallbackFilterIterator($directory, function ($current) use ($base) {
                $rel = substr($current->getPathname(), strlen($base) + 1);
                $rel = str_replace(DIRECTORY_SEPARATOR, '/', $rel);

                foreach ($this->excludedPatterns as $pattern) {
                    if (fnmatch($pattern, $rel)) {
                        return false;
                    }
                }

                return true;
            });

            $iterator = new RecursiveIteratorIterator($filter, RecursiveIteratorIterator::SELF_FIRST);

            foreach ($iterator as $item) {
                $relPath = substr($item->getPathname(), strlen($base) + 1);
                $zipRelPath = str_replace(DIRECTORY_SEPARATOR, '/', $relPath);

                if ($item->isDir()) {
                    $zip->addEmptyDir($zipRelPath);
                } else {
                    $zip->addFile($item->getPathname(), $zipRelPath);
                }
            }
        }

        // Embed the updated version.json inside the package
        $versionPayload = [
            'version' => $version,
            'channel' => 'stable',
            'build_name' => 'ItroveJewel Portable Edition',
            'released_at' => date('Y-m-d H:i:s'),
        ];
        $zip->addFromString('version.json', json_encode($versionPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $zip->close();

        $sha256 = hash_file('sha256', $zipPath);
        $fileSize = (int) File::size($zipPath);

        // Update server root version.json to stay synchronized
        File::put(base_path('version.json'), json_encode([
            'version' => $version,
            'updated_at' => date('Y-m-d H:i:s'),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $downloadUrl = $customDownloadUrl ?: route('updates.download', ['version' => $version], false);

        if (empty($releaseNotes)) {
            $releaseNotes = [
                'Performance improvements and bug fixes',
                'Enhanced USB hardware security',
                'Optimized database engine',
            ];
        }

        return AppRelease::updateOrCreate(
            ['version' => $version],
            [
                'title' => $title,
                'release_notes' => $releaseNotes,
                'download_url' => $downloadUrl,
                'file_path' => $zipPath,
                'file_hash' => $sha256,
                'file_size' => $fileSize,
                'min_supported_version' => $minSupportedVersion ?: '1.0.0',
                'is_active' => $isActive,
            ]
        );
    }

    /**
     * Delete an existing release and its corresponding zip package file.
     */
    public function deleteRelease(AppRelease $release): bool
    {
        if ($release->file_path && File::exists($release->file_path)) {
            File::delete($release->file_path);
        }

        return (bool) $release->delete();
    }

    /**
     * Toggle active status of a release.
     */
    public function toggleStatus(AppRelease $release): bool
    {
        $release->is_active = ! $release->is_active;

        return $release->save();
    }

    /**
     * Get the current codebase version recorded on this server.
     */
    public function getCurrentServerVersion(): string
    {
        $versionFile = base_path('version.json');
        if (File::exists($versionFile)) {
            $data = json_decode(File::get($versionFile), true);
            if (! empty($data['version'])) {
                return (string) $data['version'];
            }
        }

        return '1.0.0';
    }

    /**
     * Suggest the next version number based on recent releases.
     */
    public function suggestNextVersion(): string
    {
        $latest = AppRelease::latest('id')->first();
        $current = $latest ? $latest->version : $this->getCurrentServerVersion();

        $parts = explode('.', $current);
        if (count($parts) >= 2) {
            $parts[1] = (int) $parts[1] + 1;
            if (isset($parts[2])) {
                $parts[2] = 0;
            }

            return implode('.', $parts);
        }

        return '1.1.0';
    }
}
