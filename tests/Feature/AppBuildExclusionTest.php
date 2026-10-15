<?php

namespace Tests\Feature;

use App\Services\UsbLockService;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AppBuildExclusionTest extends TestCase
{
    protected function tearDown(): void
    {
        UsbLockService::resetFake();
        parent::tearDown();
    }

    /**
     * Test that NativePHP configuration explicitly excludes admin controllers and views from production builds.
     */
    public function test_nativephp_config_excludes_admin_files_from_build(): void
    {
        $excludeFiles = config('nativephp.cleanup_exclude_files', []);

        $this->assertContains('app/Http/Controllers/Admin', $excludeFiles);
        $this->assertContains('app/Http/Controllers/Admin/*', $excludeFiles);
        $this->assertContains('app/Http/Controllers/Api/LicenseVerificationController.php', $excludeFiles);
        $this->assertContains('app/Http/Controllers/Api/UpdateServerController.php', $excludeFiles);
        $this->assertContains('app/Console/Commands/AdminMigrateCommand.php', $excludeFiles);
        $this->assertContains('app/Console/Commands/AppPublishReleaseCommand.php', $excludeFiles);
        $this->assertContains('resources/views/admin', $excludeFiles);
        $this->assertContains('resources/views/admin/*', $excludeFiles);
        $this->assertContains('routes/admin.php', $excludeFiles);
        $this->assertContains('database/admin.sqlite*', $excludeFiles);
        $this->assertContains('database/migrations/admin/*', $excludeFiles);
    }

    /**
     * Test that NativePHP configuration scrubs admin environment keys from bundled builds.
     */
    public function test_nativephp_config_cleans_up_admin_env_keys(): void
    {
        $cleanupKeys = config('nativephp.cleanup_env_keys', []);

        $this->assertContains('APP_ENABLE_ADMIN_PANEL', $cleanupKeys);
        $this->assertContains('ADMIN_*', $cleanupKeys);
    }

    /**
     * Test that electron-builder packaging configuration filters out all admin controllers and views.
     */
    public function test_electron_builder_filters_out_admin_files(): void
    {
        $electronConfigPath = base_path('nativephp/electron/electron-builder.mjs');
        $this->assertFileExists($electronConfigPath);

        $content = File::get($electronConfigPath);

        $this->assertStringContainsString('!app/Http/Controllers/Admin/**', $content);
        $this->assertStringContainsString('!app/Http/Controllers/Api/LicenseVerificationController.php', $content);
        $this->assertStringContainsString('!app/Http/Controllers/Api/UpdateServerController.php', $content);
        $this->assertStringContainsString('!app/Console/Commands/AdminMigrateCommand.php', $content);
        $this->assertStringContainsString('!app/Console/Commands/AppPublishReleaseCommand.php', $content);
        $this->assertStringContainsString('!resources/views/admin/**', $content);
        $this->assertStringContainsString('!routes/admin.php', $content);
        $this->assertStringContainsString('!database/admin.sqlite*', $content);
        $this->assertStringContainsString('!database/migrations/admin/**', $content);
    }

    /**
     * Test that when in a packaged desktop app build, admin routes are completely blocked or non-existent.
     */
    public function test_admin_routes_are_blocked_in_build_environment(): void
    {
        UsbLockService::$fakeBuildEnvironment = true;

        $response = $this->get('/admin/clients');
        $this->assertEquals(404, $response->getStatusCode());

        $jsonResponse = $this->getJson('/admin/clients');
        $this->assertEquals(403, $jsonResponse->getStatusCode());
        $jsonResponse->assertJson(['error' => 'ADMIN_PANEL_DISABLED_IN_DESKTOP_BUILD']);
    }

    /**
     * Test that the packaged desktop application directory contains ZERO admin code or views.
     */
    public function test_packaged_build_directory_contains_zero_admin_files(): void
    {
        $buildAppDir = base_path('USB_READY_ItroveJewel/resources/build/app');

        // If the packaged directory exists, audit it strictly
        if (File::isDirectory($buildAppDir)) {
            $this->assertDirectoryDoesNotExist($buildAppDir.'/app/Http/Controllers/Admin');
            $this->assertFileDoesNotExist($buildAppDir.'/app/Http/Controllers/Api/LicenseVerificationController.php');
            $this->assertFileDoesNotExist($buildAppDir.'/app/Http/Controllers/Api/UpdateServerController.php');
            $this->assertFileDoesNotExist($buildAppDir.'/app/Console/Commands/AdminMigrateCommand.php');
            $this->assertFileDoesNotExist($buildAppDir.'/app/Console/Commands/AppPublishReleaseCommand.php');
            $this->assertDirectoryDoesNotExist($buildAppDir.'/resources/views/admin');
            $this->assertFileDoesNotExist($buildAppDir.'/routes/admin.php');
            $this->assertFileDoesNotExist($buildAppDir.'/database/admin.sqlite');
            $this->assertDirectoryDoesNotExist($buildAppDir.'/database/migrations/admin');

            // Ensure build .env explicitly disables admin panel
            $envPath = $buildAppDir.'/.env';
            if (File::exists($envPath)) {
                $this->assertStringContainsString('APP_ENABLE_ADMIN_PANEL=false', File::get($envPath));
            }
        }
    }

    /**
     * Test that the app:package-portable Artisan command successfully audits the build directory.
     */
    public function test_artisan_package_portable_audits_build_successfully(): void
    {
        $this->artisan('app:package-portable', ['--audit-only' => true])
            ->expectsOutputToContain('No app/Http/Controllers/Admin directory found in build')
            ->expectsOutputToContain('No resources/views/admin directory found in build')
            ->expectsOutputToContain('No LicenseVerificationController found in build')
            ->expectsOutputToContain('No UpdateServerController found in build')
            ->expectsOutputToContain('No AdminMigrateCommand found in build')
            ->expectsOutputToContain('No AppPublishReleaseCommand found in build')
            ->expectsOutputToContain('No routes/admin.php found in build')
            ->expectsOutputToContain('No database/admin.sqlite found in build')
            ->expectsOutputToContain('No database/migrations/admin found in build')
            ->expectsOutputToContain('Build is completely free of Admin Panel code and database')
            ->assertSuccessful();
    }
}
