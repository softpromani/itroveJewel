<?php

namespace App\Console\Commands;

use App\Models\AppRelease;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class AppPublishReleaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:publish-release
        {version : Release version string, e.g. 1.1.0}
        {--title= : Human-readable title for this release}
        {--notes= : Comma-separated list of release notes/features}
        {--url= : Optional direct external download URL}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Package and publish an application release for customer 1-click upgrades';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $version = trim((string) $this->argument('version'));
        $title = $this->option('title') ?: "ItroveJewel v{$version} Update";

        $rawNotes = $this->option('notes') ?: 'Performance improvements and bug fixes,Enhanced USB hardware security,Optimized database engine';
        $releaseNotes = is_array($rawNotes) ? $rawNotes : array_map('trim', explode(',', (string) $rawNotes));

        $this->info("Packaging release v{$version}...");

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
            $this->error("Failed to create zip archive at {$zipPath}");

            return 1;
        }

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
            '*/tests/*',
            '*.git*',
        ];

        foreach ($sourceFolders as $folder) {
            $sourceDir = $base.DIRECTORY_SEPARATOR.$folder;
            if (! File::isDirectory($sourceDir)) {
                continue;
            }

            $directory = new RecursiveDirectoryIterator(
                $sourceDir,
                RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::FOLLOW_SYMLINKS
            );

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
                $zipRelPath = str_replace(DIRECTORY_SEPARATOR, '/', $relPath);

                if ($item->isDir()) {
                    $zip->addEmptyDir($zipRelPath);
                } else {
                    $zip->addFile($item->getPathname(), $zipRelPath);
                }
            }
        }

        // Add version.json into zip with the new version
        $newVersionJson = json_encode([
            'version' => $version,
            'channel' => 'stable',
            'build_name' => 'ItroveJewel Portable Edition',
            'released_at' => date('Y-m-d'),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $zip->addFromString('version.json', $newVersionJson);
        $zip->close();

        $sha256 = hash_file('sha256', $zipPath);
        $fileSize = File::size($zipPath);

        $this->line("  ✓ Generated archive: {$zipPath}");
        $this->line("  ✓ SHA256 Checksum: {$sha256}");
        $this->line('  ✓ Package Size: '.round($fileSize / 1024 / 1024, 2).' MB');

        // Record in admin database
        $downloadUrl = $this->option('url') ?: route('updates.download', ['version' => $version], false);

        AppRelease::updateOrCreate(
            ['version' => $version],
            [
                'title' => $title,
                'release_notes' => $releaseNotes,
                'download_url' => $downloadUrl,
                'file_path' => $zipPath,
                'file_hash' => $sha256,
                'file_size' => $fileSize,
                'min_supported_version' => '1.0.0',
                'is_active' => true,
            ]
        );

        $this->info("SUCCESS: Release v{$version} is now LIVE on the central server!");
        $this->line('Customers connected to the internet can now upgrade to this version with 1-click.');

        return 0;
    }
}
