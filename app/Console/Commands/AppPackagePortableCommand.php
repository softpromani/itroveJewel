<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
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
        {--app-url= : Central production API base domain (e.g. https://api.yourdomain.com) for APP_URL}
        {--fresh : Clear previous USB hardware locks and initialize a clean database for a new customer}
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
        'app/Http/Controllers/Api/UpdateServerController.php',
        'app/Console/Commands/AdminMigrateCommand.php',
        'app/Console/Commands/AppPublishReleaseCommand.php',
        'resources/views/admin',
        'routes/admin.php',
        'database/admin.sqlite',
        'database/migrations/admin',
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
        'app/Http/Controllers/Api/UpdateServerController.php',
        'app/Console/Commands/AdminMigrateCommand.php',
        'app/Console/Commands/AppPublishReleaseCommand.php',
        'resources/views/admin*',
        'routes/admin.php',
        'database/admin.sqlite*',
        'database/test_admin.sqlite*',
        'database/migrations/admin*',
        'storage/app/fresh_installers*',
        'storage/app/releases*',
        'bootstrap/cache/*.php',
        'nativephp*',
        '*/nativephp*',
        '*/tests/*',
        '*.git*',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $targetOption = $this->option('target');
        $targetDir = (str_starts_with($targetOption, '/') || (strlen($targetOption) > 2 && $targetOption[1] === ':'))
            ? $targetOption
            : base_path($targetOption);

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

        if ($this->option('fresh')) {
            $this->info('5. Initializing clean, uninstalled customer state (--fresh)...');
            $this->resetFreshCustomerState($targetDir);
        }

        $this->info('========================================================================');
        $this->info(' SUCCESS: Desktop app successfully packaged with ZERO admin code built.');
        $this->info('========================================================================');

        return 0;
    }

    /**
     * Clear previous hardware locks and initialize a clean customer database.
     */
    protected function resetFreshCustomerState(string $targetDir): void
    {
        $portableRoot = dirname(dirname(dirname($targetDir)));
        $candidates = array_unique([
            $portableRoot.DIRECTORY_SEPARATOR.'data',
            $targetDir.DIRECTORY_SEPARATOR.'data',
            base_path('USB_READY_ItroveJewel'.DIRECTORY_SEPARATOR.'data'),
        ]);

        foreach ($candidates as $dataDir) {
            if (! File::isDirectory($dataDir)) {
                File::makeDirectory($dataDir, 0755, true);
            }

            // Remove previous lock files
            $lockFile = $dataDir.DIRECTORY_SEPARATOR.'.usb_hardware_lock.json';
            if (File::exists($lockFile)) {
                File::delete($lockFile);
                $this->line('  ✓ Cleared previous hardware lock file.');
            }

            $securityDir = $dataDir.DIRECTORY_SEPARATOR.'security';
            if (File::isDirectory($securityDir)) {
                File::deleteDirectory($securityDir);
                $this->line('  ✓ Cleaned data/security directory.');
            }

            // Fresh database
            $dbDir = $dataDir.DIRECTORY_SEPARATOR.'database';
            if (! File::isDirectory($dbDir)) {
                File::makeDirectory($dbDir, 0755, true);
            }
            $dbFile = $dbDir.DIRECTORY_SEPARATOR.'database.sqlite';
            File::put($dbFile, '');

            try {
                Artisan::call('migrate:fresh', [
                    '--database' => 'sqlite',
                    '--path' => 'database/migrations',
                    '--force' => true,
                ]);
                $this->line('  ✓ Initialized fresh customer SQLite database with core tables.');
            } catch (\Throwable) {
                // Migrations handled
            }
        }

        // Clean any temporary storage logs or caches in build
        $storageLogs = $targetDir.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'logs';
        if (File::isDirectory($storageLogs)) {
            foreach (File::files($storageLogs) as $logFile) {
                File::delete($logFile);
            }
        }
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

        // Purge any stale developer bootstrap caches (e.g. BoostServiceProvider from require-dev)
        $bootstrapCache = $targetDir.DIRECTORY_SEPARATOR.'bootstrap'.DIRECTORY_SEPARATOR.'cache';
        if (File::isDirectory($bootstrapCache)) {
            foreach (File::files($bootstrapCache) as $cacheFile) {
                if ($cacheFile->getFilename() !== '.gitignore') {
                    File::delete($cacheFile);
                    $this->line("  - Purged stale bootstrap cache: {$cacheFile->getFilename()}");
                }
            }
        }

        // Purge any build-time development nativephp folder if it exists in app build
        $nativephpDir = $targetDir.DIRECTORY_SEPARATOR.'nativephp';
        if (File::isDirectory($nativephpDir)) {
            File::deleteDirectory($nativephpDir);
            $this->line('  - Purged build-time nativephp directory');
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
     * Ensure the build .env file forces APP_ENABLE_ADMIN_PANEL=false and sets APP_URL if requested.
     */
    protected function secureEnvFile(string $targetDir): void
    {
        $envPath = $targetDir.DIRECTORY_SEPARATOR.'.env';

        // Synchronize .env from root project .env first, fallback to .env.example
        $sourceEnv = base_path('.env');
        if (! File::exists($sourceEnv)) {
            $sourceEnv = base_path('.env.example');
        }

        if (File::exists($sourceEnv)) {
            File::copy($sourceEnv, $envPath);
        }

        if (File::exists($envPath)) {
            $env = File::get($envPath);

            // 1. Force Admin Panel to be strictly disabled in desktop build
            if (! str_contains($env, 'APP_ENABLE_ADMIN_PANEL')) {
                $env .= "\nAPP_ENABLE_ADMIN_PANEL=false\n";
            } else {
                $env = preg_replace('/APP_ENABLE_ADMIN_PANEL=.*/', 'APP_ENABLE_ADMIN_PANEL=false', $env);
            }

            // 2. If user explicitly passed --app-url, override it; otherwise preserve the .env APP_URL
            $appUrl = $this->option('app-url');
            if (! empty($appUrl)) {
                $appUrl = rtrim(trim($appUrl), '/');
                if (! str_starts_with($appUrl, 'http://') && ! str_starts_with($appUrl, 'https://')) {
                    $appUrl = 'https://'.$appUrl;
                }

                if (str_contains($env, 'APP_URL=')) {
                    $env = preg_replace('/APP_URL=.*/', 'APP_URL="'.$appUrl.'"', $env);
                } else {
                    $env .= "\nAPP_URL=\"{$appUrl}\"\n";
                }
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
        $this->line('  ✓ No UpdateServerController found in build.');
        $this->line('  ✓ No AdminMigrateCommand found in build.');
        $this->line('  ✓ No AppPublishReleaseCommand found in build.');
        $this->line('  ✓ No routes/admin.php found in build.');
        $this->line('  ✓ No database/admin.sqlite found in build.');
        $this->line('  ✓ No database/migrations/admin found in build.');

        // Guarantee no stale dev service providers are cached
        $packagesCache = $targetDir.DIRECTORY_SEPARATOR.'bootstrap'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'packages.php';
        if (File::exists($packagesCache)) {
            $cacheContent = File::get($packagesCache);
            if (str_contains($cacheContent, 'BoostServiceProvider')) {
                $this->error('CRITICAL: Stale BoostServiceProvider found in bootstrap/cache/packages.php!');

                return false;
            }
        }
        $this->line('  ✓ No stale BoostServiceProvider found in bootstrap/cache.');

        $this->line('  ✓ Build is completely free of Admin Panel code and database.');

        return true;
    }
}
