<?php

namespace Tests\Feature;

use App\Models\FreshInstallerPackage;
use App\Services\UsbLockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use ZipArchive;

class AdminPackageManagementTest extends TestCase
{
    use RefreshDatabase;

    protected string $testStorageDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testStorageDir = storage_path('app/fresh_installers');
        if (! File::isDirectory($this->testStorageDir)) {
            File::makeDirectory($this->testStorageDir, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        UsbLockService::resetFake();
        parent::tearDown();
    }

    /**
     * Test admin can view fresh packages management page.
     */
    public function test_admin_can_view_packages_page(): void
    {
        UsbLockService::$fakeBuildEnvironment = false;

        $response = $this->get('/admin/packages');

        $response->assertStatus(200);
        $response->assertSee('Fresh USB Installers', false);
        $response->assertSee('Create New Fresh Installation Software');
        $response->assertSee('Generate Fresh Installation ZIP');
    }

    /**
     * Test packages route is strictly blocked inside desktop builds.
     */
    public function test_packages_page_is_blocked_in_packaged_desktop_build(): void
    {
        UsbLockService::$fakeBuildEnvironment = true;

        $response = $this->get('/admin/packages');
        $response->assertStatus(404);
    }

    /**
     * Test admin can create a fresh installer package with version name.
     */
    public function test_admin_can_generate_fresh_installation_package(): void
    {
        UsbLockService::$fakeBuildEnvironment = false;

        $response = $this->post('/admin/packages/create-fresh', [
            'version' => '1.5.0',
            'title' => 'ItroveJewel v1.5.0 Fresh Customer USB',
            'package_type' => 'app_core',
            'notes' => 'Tested clean image for new jewel clients.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('fresh_installer_packages', [
            'version' => '1.5.0',
            'title' => 'ItroveJewel v1.5.0 Fresh Customer USB',
            'package_type' => 'app_core',
        ], 'admin');

        $package = FreshInstallerPackage::where('version', '1.5.0')->first();
        $this->assertNotNull($package);
        $this->assertFileExists($package->file_path);
        $this->assertGreaterThan(0, $package->file_size);
        $this->assertNotEmpty($package->sha256);

        // Clean up generated file
        if (File::exists($package->file_path)) {
            File::delete($package->file_path);
        }
    }

    /**
     * Test admin can download already created fresh installation zip on click.
     */
    public function test_admin_can_download_existing_fresh_package_in_single_click(): void
    {
        UsbLockService::$fakeBuildEnvironment = false;

        // Create dummy package zip
        $dummyZip = $this->testStorageDir.DIRECTORY_SEPARATOR.'test-installer-v1.6.0.zip';
        $zip = new ZipArchive;
        $zip->open($dummyZip, ZipArchive::CREATE);
        $zip->addFromString('version.json', json_encode(['version' => '1.6.0']));
        $zip->close();

        $package = FreshInstallerPackage::create([
            'version' => '1.6.0',
            'title' => 'ItroveJewel v1.6.0',
            'package_type' => 'full_usb',
            'file_name' => 'test-installer-v1.6.0.zip',
            'file_path' => $dummyZip,
            'file_size' => filesize($dummyZip),
            'sha256' => hash_file('sha256', $dummyZip),
            'download_count' => 0,
        ]);

        $response = $this->get("/admin/packages/{$package->id}/download");

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/zip');

        // Assert download count incremented
        $this->assertEquals(1, $package->fresh()->download_count);

        if (File::exists($dummyZip)) {
            File::delete($dummyZip);
        }
    }

    /**
     * Test admin can delete old package from server storage and database.
     */
    public function test_admin_can_delete_package(): void
    {
        UsbLockService::$fakeBuildEnvironment = false;

        $dummyZip = $this->testStorageDir.DIRECTORY_SEPARATOR.'test-installer-to-delete.zip';
        File::put($dummyZip, 'MOCK_ZIP_CONTENT');

        $package = FreshInstallerPackage::create([
            'version' => '1.7.0',
            'title' => 'Delete Me',
            'package_type' => 'app_core',
            'file_name' => 'test-installer-to-delete.zip',
            'file_path' => $dummyZip,
            'file_size' => 17,
        ]);

        $response = $this->delete("/admin/packages/{$package->id}");
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('fresh_installer_packages', [
            'id' => $package->id,
        ], 'admin');

        $this->assertFileDoesNotExist($dummyZip);
    }
}
