<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\PendriveRegistration;
use App\Services\UsbLockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class UsbHardwareLockTest extends TestCase
{
    use RefreshDatabase;

    protected UsbLockService $usbLockService;

    protected function setUp(): void
    {
        parent::setUp();
        UsbLockService::fake();
        UsbLockService::$fakeBuildEnvironment = true;
        $this->usbLockService = app(UsbLockService::class);
        $this->usbLockService->resetLock();
    }

    protected function tearDown(): void
    {
        $this->usbLockService->resetLock();
        UsbLockService::resetFake();
        parent::tearDown();
    }

    /**
     * Test that an uninstalled application renders the first-time installation wizard.
     */
    public function test_uninstalled_application_shows_first_time_installation_wizard(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('USB Hardware Serial Lock');
        $response->assertSee('First-Time Installation Required');
        $response->assertSee('Physical Hardware Serial Number');
    }

    /**
     * Test that an unregistered USB pendrive cannot be installed and returns 403.
     */
    public function test_unregistered_usb_serial_is_rejected_on_installation(): void
    {
        $response = $this->postJson('/api/usb/install', [
            'admin_name' => 'Intruder',
            'confirm_lock' => true,
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'status' => 'NOT_REGISTERED',
        ]);
        $this->assertFalse($this->usbLockService->isInstalled());
    }

    /**
     * Test USB Hardware Serial Lock installation endpoint when registered on server.
     */
    public function test_can_install_and_bind_to_usb_hardware_serial(): void
    {
        $client = Client::create([
            'client_name' => 'Test Jeweller',
            'shop_name' => 'Royal Gems & Co.',
            'mobile_number' => '9876543210',
            'email' => 'jewel@example.com',
            'status' => 'active',
        ]);

        PendriveRegistration::create([
            'client_id' => $client->id,
            'serial_number' => 'SANDISK_HW_12345678',
            'pendrive_model' => 'SanDisk Ultra USB 3.0',
            'status' => 'active',
            'registered_at' => now(),
        ]);

        $response = $this->postJson('/api/usb/install', [
            'admin_name' => 'Test Jeweller',
            'confirm_lock' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'client' => [
                'shop_name' => 'Royal Gems & Co.',
                'client_name' => 'Test Jeweller',
            ],
        ]);

        $this->assertTrue($this->usbLockService->isInstalled());

        $lockData = $this->usbLockService->getLockData();
        $this->assertNotEmpty($lockData['locked_serial']);
        $this->assertEquals('Royal Gems & Co.', $lockData['shop_name']);
        $this->assertNotEmpty($lockData['signature']);
        $this->assertEquals('Test Jeweller', $lockData['installed_by']);

        // Now visiting '/' should render the active secured workspace
        $webResponse = $this->get('/');
        $webResponse->assertStatus(200);
        $webResponse->assertSee('ItroveJewel Secured Workspace');
        $webResponse->assertSee('USB SERIAL LOCK: ACTIVE');
    }

    /**
     * Test copy-protection lockout when software is copied to an unauthorized drive serial.
     */
    public function test_unauthorized_hardware_serial_triggers_lockout(): void
    {
        // 1. Lock to authorized fake serial
        $this->usbLockService->installAndLock(['admin_name' => 'Original Admin']);

        // 2. Simulate copying to another pendrive or C: drive with a different serial
        UsbLockService::fake([
            'found' => true,
            'drive_letter' => 'F',
            'model' => 'Unauthorized Cloned USB Drive',
            'serial_number' => 'PIRATED_COPIED_SERIAL_99999',
            'bus_type' => 'USB',
            'is_usb' => true,
            'unique_id' => 'USB\\VID_0000&PID_0000\\PIRATED_COPIED_SERIAL_99999',
            'size' => 32000000000,
            'size_formatted' => '32 GB',
            'error' => null,
        ]);

        // 3. Verify that accessing the application triggers 403 Lockout
        $response = $this->get('/');

        $response->assertStatus(403);
        $response->assertSee('Unauthorized Hardware Lockout');
        $response->assertSee('PIRATED_COPIED_SERIAL_99999');
        $response->assertSee('ACCESS DENIED');
    }

    /**
     * Test tampering detection if an attacker manually modifies the lock file serial.
     */
    public function test_tampered_lock_file_fails_signature_and_blocks_access(): void
    {
        // 1. Install lock
        $this->usbLockService->installAndLock();

        // 2. Tamper with the lock file (attacker attempts to change the locked serial to their new drive)
        $paths = $this->usbLockService->getLockFilePaths();
        foreach ($paths as $path) {
            if (File::exists($path)) {
                $data = json_decode(File::get($path), true);
                $data['locked_serial'] = 'ATTACKER_INJECTED_SERIAL';
                // (Note: attacker does not have secret salt/key so signature remains old)
                File::put($path, json_encode($data));
            }
        }

        // 3. Verification must fail with TAMPERED and block access
        $verification = $this->usbLockService->verifyLock();
        $this->assertEquals('TAMPERED', $verification['status']);
        $this->assertFalse($verification['is_authorized']);

        $response = $this->get('/');
        $response->assertStatus(403);
        $response->assertSee('Unauthorized Hardware Lockout');
    }

    /**
     * Test resetting the hardware lock.
     */
    public function test_can_reset_hardware_lock(): void
    {
        $this->usbLockService->installAndLock();
        $this->assertTrue($this->usbLockService->isInstalled());

        $response = $this->postJson('/api/usb/reset');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertFalse($this->usbLockService->isInstalled());
    }

    /**
     * Test that developer systems bypass the hardware lock so developers on different machines are never locked out.
     */
    public function test_developer_environment_bypasses_hardware_lock(): void
    {
        UsbLockService::$fakeBuildEnvironment = false;

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Developer Workspace (Unlocked)');
        $response->assertSee('DEV MODE: HARDWARE LOCK BYPASSED');
        $response->assertDontSee('Unauthorized Hardware Lockout');

        $verification = $this->usbLockService->verifyLock();
        $this->assertEquals('DEV_BYPASS', $verification['status']);
        $this->assertTrue($verification['is_authorized']);
        $this->assertFalse($verification['is_build']);
    }

    /**
     * Test that the Windows desktop app screens include the Dark and Light theme toggle.
     */
    public function test_desktop_app_screens_render_dark_and_light_theme_toggle(): void
    {
        // 1. Uninstalled / Setup screen
        $installResponse = $this->get('/');
        $installResponse->assertStatus(200);
        $installResponse->assertSee('data-theme-toggle', false);
        $installResponse->assertSee('theme-btn-light', false);
        $installResponse->assertSee('theme-btn-dark', false);

        // 2. Developer Workspace / Home screen
        UsbLockService::$fakeBuildEnvironment = false;
        $homeResponse = $this->get('/');
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('data-theme-toggle', false);
        $homeResponse->assertSee('theme-btn-light', false);
        $homeResponse->assertSee('theme-btn-dark', false);
    }
}
