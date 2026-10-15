<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FreshInstallerPackage;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class AdminPackageController extends Controller
{
    /**
     * Display listing of fresh installer packages with generator form.
     */
    public function index(): View
    {
        $packages = FreshInstallerPackage::latest()->paginate(15);

        $storageDir = storage_path('app/fresh_installers');
        $totalSize = 0;
        if (File::isDirectory($storageDir)) {
            foreach (File::files($storageDir) as $f) {
                $totalSize += $f->getSize();
            }
        }

        $metrics = [
            'total_packages' => FreshInstallerPackage::count(),
            'total_downloads' => (int) FreshInstallerPackage::sum('download_count'),
            'storage_used' => $totalSize,
            'latest_version' => FreshInstallerPackage::latest('id')->value('version') ?? 'None',
        ];

        return view('admin.packages.index', [
            'packages' => $packages,
            'metrics' => $metrics,
        ]);
    }

    /**
     * Generate a new fresh installation software package.
     */
    public function createFresh(Request $request): RedirectResponse
    {
        @ini_set('max_execution_time', '0');
        @set_time_limit(0);
        @ignore_user_abort(true);

        $validated = $request->validate([
            'version' => ['required', 'string', 'max:30', 'regex:/^[a-zA-Z0-9.\-_]+$/', 'unique:admin.fresh_installer_packages,version'],
            'title' => ['nullable', 'string', 'max:255'],
            'package_type' => ['required', 'in:full_usb,app_core'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $version = trim($validated['version']);
        $packageType = $validated['package_type'];
        $title = ! empty($validated['title']) ? trim($validated['title']) : "ItroveJewel v{$version} Fresh Installer";

        $storageDir = storage_path('app/fresh_installers');
        if (! File::isDirectory($storageDir)) {
            File::makeDirectory($storageDir, 0755, true);
        }

        $zipFileName = "ItroveJewel-v{$version}-".($packageType === 'full_usb' ? 'Full-USB' : 'App-Core').'.zip';
        $zipFilePath = $storageDir.DIRECTORY_SEPARATOR.$zipFileName;

        if (File::exists($zipFilePath)) {
            File::delete($zipFilePath);
        }

        try {
            $zip = new ZipArchive;
            if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                return back()->with('error', 'Failed to create zip archive on server.');
            }

            if ($packageType === 'full_usb') {
                // Ensure portable build is up-to-date and 100% clean
                Artisan::call('app:package-portable', ['--fresh' => true]);

                $portableSource = base_path('USB_READY_ItroveJewel');
                if (! File::isDirectory($portableSource)) {
                    $zip->close();
                    if (File::exists($zipFilePath)) {
                        File::delete($zipFilePath);
                    }

                    return back()->with('error', 'USB_READY_ItroveJewel directory not found to package.');
                }

                $this->zipDirectory($zip, $portableSource, 'USB_READY_ItroveJewel');
            } else {
                // Application Core package
                $this->zipAppCore($zip, $version);
            }

            $zip->close();

            $fileSize = File::size($zipFilePath);
            $sha256 = hash_file('sha256', $zipFilePath);

            FreshInstallerPackage::create([
                'version' => $version,
                'title' => $title,
                'package_type' => $packageType,
                'file_name' => $zipFileName,
                'file_path' => $zipFilePath,
                'file_size' => $fileSize,
                'sha256' => $sha256,
                'notes' => $validated['notes'] ?? null,
            ]);

            return back()->with('success', "Fresh Installation Package v{$version} generated successfully! Click 'Download ZIP' below to download.");
        } catch (Exception $e) {
            if (File::exists($zipFilePath)) {
                File::delete($zipFilePath);
            }

            return back()->with('error', 'Packaging failed: '.$e->getMessage());
        }
    }

    /**
     * Download the specified fresh installation zip package.
     */
    public function download(FreshInstallerPackage $package): BinaryFileResponse|RedirectResponse
    {
        if (! File::exists($package->file_path)) {
            return back()->with('error', 'The requested installation package file was not found on this server.');
        }

        $package->increment('download_count');

        return response()->download($package->file_path, $package->file_name, [
            'Content-Type' => 'application/zip',
        ]);
    }

    /**
     * Delete an installer package from disk and database.
     */
    public function destroy(FreshInstallerPackage $package): RedirectResponse
    {
        if (File::exists($package->file_path)) {
            File::delete($package->file_path);
        }

        $package->delete();

        return back()->with('success', "Package v{$package->version} has been removed from server storage.");
    }

    /**
     * Recursively zip a directory with a given root prefix inside the zip.
     */
    protected function zipDirectory(ZipArchive $zip, string $sourceDir, string $zipPrefix = ''): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $baseLen = strlen($sourceDir);
        $binaryExtensions = ['exe', 'dll', 'pak', 'asar', 'dat', 'bin', 'png', 'jpg', 'jpeg', 'zip', 'gz', 'mp3', 'mp4', 'ico', 'pdf', 'woff', 'woff2', 'sqlite'];

        foreach ($iterator as $file) {
            $subPath = substr($file->getPathname(), $baseLen + 1);
            $normalized = str_replace(DIRECTORY_SEPARATOR, '/', $subPath);

            // Skip any potential sensitive, unwanted, or heavy dev files
            if (
                str_contains($normalized, '.usb_hardware_lock.json') ||
                str_contains($normalized, 'admin.sqlite') ||
                str_contains($normalized, '.git') ||
                str_contains($normalized, 'tests/') ||
                str_contains($normalized, 'nativephp/electron') ||
                str_contains($normalized, 'bootstrap/cache/')
            ) {
                continue;
            }

            $zipEntryPath = $zipPrefix !== '' ? ($zipPrefix.'/'.$normalized) : $normalized;

            if ($file->isDir()) {
                $zip->addEmptyDir($zipEntryPath);
            } else {
                $zip->addFile($file->getPathname(), $zipEntryPath);

                $ext = strtolower($file->getExtension());
                if (in_array($ext, $binaryExtensions, true)) {
                    $zip->setCompressionName($zipEntryPath, ZipArchive::CM_STORE);
                }
            }
        }
    }

    /**
     * Zip clean application core files.
     */
    protected function zipAppCore(ZipArchive $zip, string $version): void
    {
        $base = base_path();
        $sourceFolders = ['app', 'bootstrap', 'config', 'database', 'public', 'resources', 'routes'];
        $excludedPatterns = [
            'app/Http/Controllers/Admin*',
            'app/Http/Controllers/Api/LicenseVerificationController.php',
            'app/Http/Controllers/Api/UpdateServerController.php',
            'app/Console/Commands/Admin*',
            'app/Console/Commands/AppPublishReleaseCommand.php',
            'resources/views/admin*',
            'routes/admin.php',
            'database/admin.sqlite*',
            'database/migrations/admin*',
            'data/database*',
            'data/security*',
            'bootstrap/cache/*.php',
            '*/tests/*',
            '*.git*',
        ];

        foreach ($sourceFolders as $folder) {
            $sourceDir = $base.DIRECTORY_SEPARATOR.$folder;
            if (! File::isDirectory($sourceDir)) {
                continue;
            }

            $directory = new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS);
            $filter = new RecursiveCallbackFilterIterator($directory, function ($current) use ($base, $excludedPatterns) {
                $rel = substr($current->getPathname(), strlen($base) + 1);
                $rel = str_replace(DIRECTORY_SEPARATOR, '/', $rel);
                foreach ($excludedPatterns as $pattern) {
                    if (fnmatch($pattern, $rel)) {
                        return false;
                    }
                }

                return true;
            });

            $iterator = new RecursiveIteratorIterator($filter, RecursiveIteratorIterator::SELF_FIRST);

            foreach ($iterator as $item) {
                $relPath = substr($item->getPathname(), strlen($base) + 1);
                $zipRelPath = 'ItroveJewel/'.str_replace(DIRECTORY_SEPARATOR, '/', $relPath);

                if ($item->isDir()) {
                    $zip->addEmptyDir($zipRelPath);
                } else {
                    $zip->addFile($item->getPathname(), $zipRelPath);
                }
            }
        }

        // Add clean customer .env and version.json
        $envTemplate = base_path('.env.example');
        if (File::exists($envTemplate)) {
            $envContent = File::get($envTemplate);
            if (! str_contains($envContent, 'APP_ENABLE_ADMIN_PANEL')) {
                $envContent .= "\nAPP_ENABLE_ADMIN_PANEL=false\n";
            }
            $zip->addFromString('ItroveJewel/.env', $envContent);
        }

        $versionData = json_encode([
            'version' => $version,
            'channel' => 'stable',
            'build_name' => 'ItroveJewel Fresh USB Edition',
            'released_at' => date('Y-m-d'),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $zip->addFromString('ItroveJewel/version.json', $versionData);
    }
}
