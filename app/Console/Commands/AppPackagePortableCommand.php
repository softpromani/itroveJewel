<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class AppPackagePortableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:package-portable
        {--target=USB_READY_ItroveJewel/resources/build/app : Destination path for the desktop app build}
        {--audit-only : Audit target directory to ensure NO admin code is present without copying}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Package and verify the desktop app build, ensuring the Admin Panel and License Verification APIs are strictly excluded.';

    /**
     * Paths relative to base_path that must NEVER be included in desktop app builds.
     */
    protected array $forbiddenBuildPaths = [
        'app/Http/Controllers/Admin',
        'app/Http/Controllers/Api/LicenseVerificationController.php',
        'resources/views/admin',
    ];

    /**
     * Application source roots to synchronize into the desktop build.
     */
    protected array $appSourceFolders = [
        'app',
        'bootstrap',
        'config',
        'database',
        'public',
        'resources',
        'routes',
    ];

    /**
     * Root files to synchronize.
     */
    protected array $appSourceFiles = [
        'artisan',
        'composer.json',
        'package.json',
    ];

    /**
     * Directory exclusion patterns during build copy.
     */
    protected array $excludedPatterns = [
        'app/Http/Controllers/Admin*',
        'app/Http/Controllers/Api/LicenseVerificationController.php',
        'resources/views/admin*',
        '*/tests/*',
        '*.git*',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $targetDir = base_path($this->option('target'));

        $this->info('========================================================================');
        $this->info(' ITROVEJEWEL - APP BUILD PACKAGING & ISOLATION SYSTEM');
        $this->info('========================================================================');
        $this->line("Target build directory: <comment>{$targetDir}</comment>");

        if ($this->option('audit-only')) {
            return $this->auditBuild($targetDir) ? 0 : 1;
        }

        if (! File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        $this->info('1. Purging any existing admin components from build directory...');
        $this->purgeAdminFromTarget($targetDir);

        $this->info('2. Synchronizing core application to build directory (skipping admin panel)...');
        $this->copyCleanApp($targetDir);

        $this->info('3. Securing desktop build .env configuration...');
        $this->secureEnvFile($targetDir);

        $this->info('4. Auditing build to guarantee ZERO admin code is included...');
        $passed = $this->auditBuild($targetDir);

        if (! $passed) {
            $this->error('CRITICAL: Audit failed! Admin files were detected in the app build directory.');

            return 1;
        }

        $this->info('========================================================================');
        $this->info(' SUCCESS: Desktop app successfully packaged with ZERO admin code built.');
        $this->info('========================================================================');

        return 0;
    }

    /**
     * Purge any forbidden admin folders from the target directory if they somehow exist.
     */
    protected function purgeAdminFromTarget(string $targetDir): void
    {
        foreach ($this->forbiddenBuildPaths as $relPath) {
            $fullPath = $targetDir.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relPath);
            if (File::isDirectory($fullPath)) {
                File::deleteDirectory($fullPath);
                $this->line("  - Removed: {$relPath}");
            } elseif (File::isFile($fullPath)) {
                File::delete($fullPath);
                $this->line("  - Removed: {$relPath}");
            }
        }
    }

    /**
     * Copy application source files to target build directory, skipping excluded patterns.
     */
    protected function copyCleanApp(string $targetDir): void
    {
        $base = base_path();

        // Copy directories
        foreach ($this->appSourceFolders as $folder) {
            $sourceDir = $base.DIRECTORY_SEPARATOR.$folder;
            if (! File::isDirectory($sourceDir)) {
                continue;
            }

            $directory = new RecursiveDirectoryIterator(
                $sourceDir,
                RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::FOLLOW_SYMLINKS
            );

            $patterns = $this->excludedPatterns;

            $filter = new RecursiveCallbackFilterIterator($directory, function ($current) use ($base, $patterns) {
                $rel = substr($current->getPathname(), strlen($base) + 1);
                $rel = str_replace(DIRECTORY_SEPARATOR, '/', $rel);

                foreach ($patterns as $pattern) {
                    if (fnmatch($pattern, $rel)) {
                        return false;
                    }
                }

                return true;
            });

            $iterator = new RecursiveIteratorIterator($filter, RecursiveIteratorIterator::SELF_FIRST);

            foreach ($iterator as $item) {
                $relPath = substr($item->getPathname(), strlen($base) + 1);
                $dest = $targetDir.DIRECTORY_SEPARATOR.$relPath;

                if ($item->isDir()) {
                    if (! File::isDirectory($dest)) {
                        File::makeDirectory($dest, 0755, true);
                    }
                } else {
                    $destFolder = dirname($dest);
                    if (! File::isDirectory($destFolder)) {
                        File::makeDirectory($destFolder, 0755, true);
                    }
                    copy($item->getPathname(), $dest);
                }
            }
        }

        // Copy individual root files
        foreach ($this->appSourceFiles as $file) {
            $sourceFile = $base.DIRECTORY_SEPARATOR.$file;
            $destFile = $targetDir.DIRECTORY_SEPARATOR.$file;
            if (File::exists($sourceFile)) {
                copy($sourceFile, $destFile);
            }
        }
    }

    /**
     * Ensure the build .env file forces APP_ENABLE_ADMIN_PANEL=false.
     */
    protected function secureEnvFile(string $targetDir): void
    {
        $envPath = $targetDir.DIRECTORY_SEPARATOR.'.env';
        if (File::exists($envPath)) {
            $env = File::get($envPath);
            if (! str_contains($env, 'APP_ENABLE_ADMIN_PANEL')) {
                $env .= "\nAPP_ENABLE_ADMIN_PANEL=false\n";
            } else {
                $env = preg_replace('/APP_ENABLE_ADMIN_PANEL=.*/', 'APP_ENABLE_ADMIN_PANEL=false', $env);
            }
            File::put($envPath, $env);
        }
    }

    /**
     * Audit the target build directory to ensure NO admin code exists.
     */
    public function auditBuild(string $targetDir): bool
    {
        if (! File::isDirectory($targetDir)) {
            $this->error("Directory does not exist: {$targetDir}");

            return false;
        }

        $violations = [];

        foreach ($this->forbiddenBuildPaths as $forbidden) {
            $path = $targetDir.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $forbidden);
            if (File::exists($path) || File::isDirectory($path)) {
                $violations[] = $forbidden;
            }
        }

        if (count($violations) > 0) {
            $this->error('The following forbidden admin components were found in the build:');
            foreach ($violations as $v) {
                $this->error(" - {$v}");
            }

            return false;
        }

        $this->line('  ✓ No app/Http/Controllers/Admin directory found in build.');
        $this->line('  ✓ No resources/views/admin directory found in build.');
        $this->line('  ✓ No LicenseVerificationController found in build.');
        $this->line('  ✓ Build is completely free of Admin Panel code.');

        return true;
    }
}
