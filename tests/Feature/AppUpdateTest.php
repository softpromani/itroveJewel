<?php

namespace Tests\Feature;

use App\Models\AppRelease;
use App\Services\AppUpdateService;
use App\Services\UsbLockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use ZipArchive;

class AppUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected string $testTempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testTempDir = storage_path('framework/testing/app_update_test_'.uniqid());
        if (! File::isDirectory($this->testTempDir)) {
            File::makeDirectory($this->testTempDir, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        UsbLockService::resetFake();
        if (File::isDirectory($this->testTempDir)) {
            File::deleteDirectory($this->testTempDir);
        }
        parent::tearDown();
    }

    /**
     * Test server returns update available when client has an older version.
     */
    public function test_server_returns_update_available_when_client_version_is_older(): void
    {
        // Create an active release in admin DB
        AppRelease::query()->delete();
        $release = AppRelease::create([
            'version' => '1.2.0',
            'title' => 'ItroveJewel v1.2.0 Luxury Edition',
            'release_notes' => ['Added Dark Mode', 'Optimized USB Hardware Check'],
            'download_url' => 'http://127.0.0.1:8000/api/updates/download/1.2.0',
            'file_hash' => hash('sha256', 'mock_content'),
            'file_size' => 1024,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/updates/check?version=1.0.0');

        $response->assertStatus(200);
        $response->assertJson([
            'update_available' => true,
            'current_version' => '1.0.0',
            'latest_version' => '1.2.0',
            'title' => 'ItroveJewel v1.2.0 Luxury Edition',
            'sha256' => $release->file_hash,
        ]);
        $response->assertJsonFragment(['release_notes' => ['Added Dark Mode', 'Optimized USB Hardware Check']]);
    }

    /**
     * Test server reports no update when customer is already on latest version.
     */
    public function test_server_reports_no_update_when_client_is_already_on_latest(): void
    {
        AppRelease::query()->delete();
        AppRelease::create([
            'version' => '1.2.0',
            'title' => 'ItroveJewel v1.2.0',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/updates/check?version=1.2.0');

        $response->assertStatus(200);
        $response->assertJson([
            'update_available' => false,
            'current_version' => '1.2.0',
            'latest_version' => '1.2.0',
        ]);
    }

    /**
     * Test server download endpoint returns 404 for unknown version.
     */
    public function test_server_download_returns_404_for_unknown_version(): void
    {
        $response = $this->get('/api/updates/download/99.99.99');
        $response->assertStatus(404);
    }

    /**
     * Test server download streams valid binary zip archive when release exists.
     */
    public function test_server_download_streams_zip_file_when_release_exists(): void
    {
        // Create a dummy zip file
        $mockZip = $this->testTempDir.DIRECTORY_SEPARATOR.'release-1.5.0.zip';
        $zip = new ZipArchive;
        $zip->open($mockZip, ZipArchive::CREATE);
        $zip->addFromString('version.json', json_encode(['version' => '1.5.0']));
        $zip->close();

        AppRelease::query()->delete();
        AppRelease::create([
            'version' => '1.5.0',
            'title' => 'ItroveJewel v1.5.0',
            'file_path' => $mockZip,
            'file_hash' => hash_file('sha256', $mockZip),
            'file_size' => filesize($mockZip),
            'is_active' => true,
        ]);

        $response = $this->get('/api/updates/download/1.5.0');
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/zip');
    }

    /**
     * Test client update service handles offline gracefully with zero crashes.
     */
    public function test_client_update_check_handles_offline_gracefully(): void
    {
        // Fake HTTP timeout / network down
        Http::fake([
            '*/api/updates/check*' => Http::response(null, 500),
        ]);

        $service = app(AppUpdateService::class);
        $status = $service->checkForUpdates();

        $this->assertFalse($status['update_available']);
        $this->assertNotEmpty($status['message']);
    }

    /**
     * Test client 1-click upgrade applies package safely, verifies hash, and backs up database.
     */
    public function test_client_1_click_upgrade_safely_applies_update_with_checksum_verification(): void
    {
        // 1. Create a mock update zip archive
        $mockZip = $this->testTempDir.DIRECTORY_SEPARATOR.'mock_update_pkg.zip';
        $zip = new ZipArchive;
        $zip->open($mockZip, ZipArchive::CREATE);
        $zip->addFromString('version.json', json_encode(['version' => '1.9.0', 'released_at' => date('Y-m-d')]));
        $zip->addFromString('test_update_file.txt', 'Updated successfully via single click!');
        // Ensure user database file attempt inside zip is safely ignored
        $zip->addFromString('data/database/database.sqlite', 'HACK_ATTEMPT');
        $zip->close();

        $validHash = hash_file('sha256', $mockZip);

        $service = app(AppUpdateService::class);

        // 2. Test apply with corrupted hash (must fail and rollback)
        $failResult = $service->applyUpdate($mockZip, 'INVALID_CORRUPTED_SHA256');
        $this->assertFalse($failResult['success']);
        $this->assertStringContainsString('integrity verification failed', $failResult['message']);

        // 3. Test apply with valid checksum
        $successResult = $service->applyUpdate($mockZip, $validHash);
        $this->assertTrue($successResult['success']);
        $this->assertEquals('1.9.0', $successResult['new_version']);

        // Assert updated version was registered
        $this->assertEquals('1.9.0', $service->getCurrentVersion());

        // Assert blacklisted database was NOT replaced
        $this->assertFileDoesNotExist(base_path('data/database/database.sqlite'));

        // Assert extracted test file exists
        $extractedFile = base_path('test_update_file.txt');
        $this->assertFileExists($extractedFile);
        $this->assertEquals('Updated successfully via single click!', File::get($extractedFile));

        // Clean up test file and reset version
        File::delete($extractedFile);
        File::put(base_path('version.json'), json_encode(['version' => '1.0.0', 'updated_at' => date('Y-m-d H:i:s')]));
    }

    /**
     * Test that any API call and build configuration strictly matches the defined APP_URL.
     */
    public function test_app_uses_env_app_url_as_base_domain_for_all_api_calls(): void
    {
        // 1. Configure APP_URL
        config(['app.url' => 'https://api.itrovejewel.com']);
        putenv('APP_URL=https://api.itrovejewel.com');

        $service = app(AppUpdateService::class);
        $this->assertEquals('https://api.itrovejewel.com', $service->getServerUrl());

        // 2. Verify download_url in UpdateServerController matches APP_URL
        AppRelease::query()->delete();
        AppRelease::create([
            'version' => '2.0.0',
            'title' => 'ItroveJewel v2.0.0',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/updates/check?version=1.0.0');
        $response->assertStatus(200);
        $response->assertJson([
            'download_url' => 'https://api.itrovejewel.com/api/updates/download/2.0.0',
        ]);

        // 3. Verify app:package-portable sets APP_URL in build .env
        $customTarget = $this->testTempDir.DIRECTORY_SEPARATOR.'test_build_env';
        $this->artisan('app:package-portable', [
            '--target' => $customTarget,
            '--app-url' => 'https://custom-license-server.com',
        ])->assertSuccessful();

        $envContent = File::get($customTarget.DIRECTORY_SEPARATOR.'.env');
        $this->assertStringContainsString('APP_URL="https://custom-license-server.com"', $envContent);
        $this->assertStringContainsString('APP_ENABLE_ADMIN_PANEL=false', $envContent);
    }
}
