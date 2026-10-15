<?php

namespace Tests\Feature;

use App\Models\AppRelease;
use App\Services\UsbLockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AdminReleaseManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        UsbLockService::resetFake();
        parent::tearDown();
    }

    /**
     * Test admin can access the releases management and publishing page.
     */
    public function test_admin_can_view_releases_page(): void
    {
        $response = $this->get('/admin/releases');

        $response->assertStatus(200);
        $response->assertSee('Client Software Updates &amp; Releases', false);
        $response->assertSee('Publish New Client Software Update', false);
        $response->assertSee('Publish Release to All Clients', false);
    }

    /**
     * Test admin release routes are strictly blocked in desktop build environments.
     */
    public function test_admin_release_routes_are_blocked_in_desktop_build_environment(): void
    {
        UsbLockService::$fakeBuildEnvironment = true;

        $response = $this->get('/admin/releases');
        $response->assertStatus(404);

        $postResponse = $this->post('/admin/releases', [
            'version' => '1.5.0',
            'title' => 'Test Release',
        ]);
        $postResponse->assertStatus(404);
    }

    /**
     * Test admin can publish a new release from the dashboard form.
     */
    public function test_admin_can_publish_new_release_from_dashboard(): void
    {
        $version = '9.8.7';

        $response = $this->post('/admin/releases', [
            'version' => $version,
            'title' => 'ItroveJewel v9.8.7 Luxury Diamond Release',
            'release_notes' => "Added Diamond Valuation Calculator\nOptimized Cloud Sync Engine\nFixed Barcode scanning latency",
            'min_supported_version' => '1.0.0',
            'is_active' => '1',
        ]);

        $response->assertRedirect('/admin/releases');
        $response->assertSessionHas('success');

        // Verify database entry
        $this->assertDatabaseHas('app_releases', [
            'version' => $version,
            'title' => 'ItroveJewel v9.8.7 Luxury Diamond Release',
            'is_active' => true,
        ], 'admin');

        $release = AppRelease::where('version', $version)->first();
        $this->assertNotNull($release);
        $this->assertCount(3, $release->release_notes);
        $this->assertEquals('Added Diamond Valuation Calculator', $release->release_notes[0]);
        $this->assertNotEmpty($release->file_hash);
        $this->assertGreaterThan(0, $release->file_size);
        $this->assertFileExists($release->file_path);

        // Clean up generated zip
        if (File::exists($release->file_path)) {
            File::delete($release->file_path);
        }
    }

    /**
     * Test admin can download published release update zip and increment download counter.
     */
    public function test_admin_can_download_published_release_zip(): void
    {
        $version = '9.8.8';

        $this->post('/admin/releases', [
            'version' => $version,
            'title' => 'Download Test v9.8.8',
            'release_notes' => 'Test notes',
            'is_active' => '1',
        ]);

        $release = AppRelease::where('version', $version)->firstOrFail();
        $this->assertEquals(0, $release->download_count);

        $downloadResponse = $this->get("/admin/releases/{$release->id}/download");
        $downloadResponse->assertStatus(200);
        $downloadResponse->assertHeader('content-type', 'application/zip');

        $release->refresh();
        $this->assertEquals(1, $release->download_count);

        // Clean up
        if (File::exists($release->file_path)) {
            File::delete($release->file_path);
        }
    }

    /**
     * Test admin can toggle active/paused status of a release.
     */
    public function test_admin_can_toggle_release_active_status(): void
    {
        $release = AppRelease::create([
            'version' => '9.8.9',
            'title' => 'Status Toggle Test',
            'is_active' => true,
        ]);

        $response = $this->post("/admin/releases/{$release->id}/toggle");
        $response->assertSessionHas('success');

        $release->refresh();
        $this->assertFalse($release->is_active);

        // Toggle back to active
        $this->post("/admin/releases/{$release->id}/toggle");
        $release->refresh();
        $this->assertTrue($release->is_active);
    }

    /**
     * Test admin can delete release and clean up package zip file.
     */
    public function test_admin_can_delete_release(): void
    {
        $version = '9.8.10';

        $this->post('/admin/releases', [
            'version' => $version,
            'title' => 'Delete Test v9.8.10',
            'release_notes' => 'Delete notes',
        ]);

        $release = AppRelease::where('version', $version)->firstOrFail();
        $zipPath = $release->file_path;
        $this->assertFileExists($zipPath);

        $deleteResponse = $this->delete("/admin/releases/{$release->id}");
        $deleteResponse->assertRedirect('/admin/releases');
        $deleteResponse->assertSessionHas('success');

        $this->assertDatabaseMissing('app_releases', [
            'version' => $version,
        ], 'admin');

        $this->assertFileDoesNotExist($zipPath);
    }
}
