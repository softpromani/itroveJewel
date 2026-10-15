<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\PendriveRegistration;
use App\Services\UsbLockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminClientManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        UsbLockService::resetFake();
        parent::tearDown();
    }

    /**
     * Test that the Admin Panel is fully accessible on the web server.
     */
    public function test_admin_portal_is_accessible_on_web_server(): void
    {
        UsbLockService::$fakeBuildEnvironment = false;

        $response = $this->get('/admin/clients');

        $response->assertStatus(200);
        $response->assertSee('Clients & USB Licenses');
        $response->assertSee('Add New Client');
        $response->assertSee('SERVER ONLINE');
    }

    /**
     * Test that the Admin Panel is strictly BLOCKED inside desktop/portable app builds.
     */
    public function test_admin_portal_is_strictly_blocked_in_packaged_app_build(): void
    {
        // Simulate running inside the packaged desktop app build
        UsbLockService::$fakeBuildEnvironment = true;

        $response = $this->get('/admin/clients');
        $response->assertStatus(404);

        $jsonResponse = $this->getJson('/admin/clients');
        $jsonResponse->assertStatus(403);
        $jsonResponse->assertJson([
            'error' => 'ADMIN_PANEL_DISABLED_IN_DESKTOP_BUILD',
        ]);
    }

    /**
     * Test that the Admin Panel can be completely disabled via configuration.
     */
    public function test_admin_portal_is_blocked_when_disabled_via_config(): void
    {
        config(['app.enable_admin_panel' => false]);
        UsbLockService::$fakeBuildEnvironment = false;

        $response = $this->get('/admin/clients');
        $response->assertStatus(404);
    }

    /**
     * Test onboarding a new client with mobile number, email, shop name, and client name.
     */
    public function test_can_create_client_with_mobile_email_shopname_clientname(): void
    {
        UsbLockService::$fakeBuildEnvironment = false;

        $clientData = [
            'client_name' => 'Rajesh Sharma',
            'shop_name' => 'Maharaja Jewellers',
            'mobile_number' => '+91 9876543210',
            'email' => 'maharaja.jewels@example.com',
            'notes' => 'Flagship showroom counter installation',
        ];

        $response = $this->post('/admin/clients', $clientData);

        $client = Client::where('email', 'maharaja.jewels@example.com')->first();
        $this->assertNotNull($client);
        $this->assertEquals('Rajesh Sharma', $client->client_name);
        $this->assertEquals('Maharaja Jewellers', $client->shop_name);
        $this->assertEquals('+91 9876543210', $client->mobile_number);
        $this->assertEquals('active', $client->status);

        $response->assertRedirect(route('admin.clients.show', $client));
        $response->assertSessionHas('success');
    }

    /**
     * Test registering a USB pendrive serial number for a client.
     */
    public function test_can_register_pendrive_serial_to_client(): void
    {
        UsbLockService::$fakeBuildEnvironment = false;

        $client = Client::factory()->create([
            'shop_name' => 'Kalyan Gems',
            'client_name' => 'Kalyan Raman',
        ]);

        $pendriveData = [
            'serial_number' => 'SANDISK_FLAIR_99887766',
            'pendrive_model' => 'SanDisk Ultra Flair 64GB',
            'label' => 'Main Billing Counter #1',
        ];

        $response = $this->post(route('admin.clients.pendrives.store', $client), $pendriveData);

        $response->assertRedirect(route('admin.clients.show', $client));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pendrive_registrations', [
            'client_id' => $client->id,
            'serial_number' => 'SANDISK_FLAIR_99887766',
            'status' => 'active',
        ], 'admin');
    }

    /**
     * Test that a pendrive serial number cannot be assigned to another active client.
     */
    public function test_cannot_assign_same_serial_to_two_different_active_clients(): void
    {
        UsbLockService::$fakeBuildEnvironment = false;

        $clientA = Client::factory()->create(['shop_name' => 'Jewel Store A']);
        $clientB = Client::factory()->create(['shop_name' => 'Jewel Store B']);

        PendriveRegistration::create([
            'client_id' => $clientA->id,
            'serial_number' => 'SHARED_SERIAL_123',
            'status' => 'active',
        ]);

        // Attempt to register the exact same serial number for Client B
        $response = $this->post(route('admin.clients.pendrives.store', $clientB), [
            'serial_number' => 'SHARED_SERIAL_123',
            'pendrive_model' => 'Duplicate Drive',
        ]);

        $response->assertSessionHasErrors('serial_number');

        $this->assertDatabaseMissing('pendrive_registrations', [
            'client_id' => $clientB->id,
            'serial_number' => 'SHARED_SERIAL_123',
        ], 'admin');
    }

    /**
     * Test toggling pendrive status between active and revoked.
     */
    public function test_can_toggle_pendrive_status(): void
    {
        UsbLockService::$fakeBuildEnvironment = false;

        $client = Client::factory()->create();
        $pendrive = PendriveRegistration::factory()->create([
            'client_id' => $client->id,
            'serial_number' => 'SERIAL_TO_REVOKE',
            'status' => 'active',
        ]);

        // 1. Toggle to revoked
        $response = $this->post(route('admin.pendrives.toggle', $pendrive));
        $response->assertSessionHas('success');
        $this->assertEquals('revoked', $pendrive->fresh()->status);

        // 2. Toggle back to active
        $response = $this->post(route('admin.pendrives.toggle', $pendrive));
        $response->assertSessionHas('success');
        $this->assertEquals('active', $pendrive->fresh()->status);
    }

    /**
     * Test server verification API endpoint returns 200 and client details for active pendrive.
     */
    public function test_server_verification_api_returns_success_for_active_pendrive(): void
    {
        $client = Client::factory()->create([
            'client_name' => 'Anil Kapoor',
            'shop_name' => 'Kapoor Jewellers',
            'mobile_number' => '9820012345',
            'email' => 'anil@kapoorjewels.com',
            'status' => 'active',
        ]);

        $pendrive = PendriveRegistration::factory()->create([
            'client_id' => $client->id,
            'serial_number' => 'VERIFIED_USB_SERIAL_777',
            'pendrive_model' => 'Kingston DTX 32GB',
            'status' => 'active',
            'verification_count' => 0,
        ]);

        $response = $this->postJson('/api/license/verify-usb', [
            'serial_number' => 'VERIFIED_USB_SERIAL_777',
            'model' => 'Kingston DTX 32GB',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'authorized' => true,
            'status' => 'ACTIVE',
            'client' => [
                'id' => $client->id,
                'client_name' => 'Anil Kapoor',
                'shop_name' => 'Kapoor Jewellers',
                'mobile_number' => '9820012345',
                'email' => 'anil@kapoorjewels.com',
            ],
            'pendrive' => [
                'serial_number' => 'VERIFIED_USB_SERIAL_777',
            ],
        ]);

        $this->assertNotEmpty($response->json('server_token'));
        $this->assertEquals(1, $pendrive->fresh()->verification_count);
        $this->assertNotNull($pendrive->fresh()->last_verified_at);
    }

    /**
     * Test server verification API rejects unregistered serial numbers.
     */
    public function test_server_verification_api_rejects_unregistered_serial(): void
    {
        $response = $this->postJson('/api/license/verify-usb', [
            'serial_number' => 'UNKNOWN_PIRATED_SERIAL_000',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'authorized' => false,
            'status' => 'NOT_REGISTERED',
        ]);
    }

    /**
     * Test server verification API rejects revoked pendrives.
     */
    public function test_server_verification_api_rejects_revoked_pendrive(): void
    {
        $client = Client::factory()->create(['status' => 'active']);
        PendriveRegistration::factory()->create([
            'client_id' => $client->id,
            'serial_number' => 'REVOKED_USB_SERIAL_555',
            'status' => 'revoked',
        ]);

        $response = $this->postJson('/api/license/verify-usb', [
            'serial_number' => 'REVOKED_USB_SERIAL_555',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'authorized' => false,
            'status' => 'REVOKED',
        ]);
    }

    /**
     * Test server verification API rejects pendrives belonging to suspended clients.
     */
    public function test_server_verification_api_rejects_suspended_client(): void
    {
        $client = Client::factory()->create(['status' => 'suspended']);
        PendriveRegistration::factory()->create([
            'client_id' => $client->id,
            'serial_number' => 'SUSPENDED_CLIENT_USB_444',
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/license/verify-usb', [
            'serial_number' => 'SUSPENDED_CLIENT_USB_444',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'authorized' => false,
            'status' => 'CLIENT_INACTIVE',
        ]);
    }

    /**
     * Test that the Admin Portal includes the Dark and Light theme toggle.
     */
    public function test_admin_portal_renders_dark_and_light_theme_toggle(): void
    {
        $response = $this->get('/admin/clients');

        $response->assertStatus(200);
        $response->assertSee('data-theme-toggle', false);
        $response->assertSee('theme-btn-light', false);
        $response->assertSee('theme-btn-dark', false);
        $response->assertSee('setAppTheme', false);
    }

    /**
     * Test that the Admin Portal includes a sidebar with navigation icons, expandable submenus, and minimize options.
     */
    public function test_admin_portal_renders_sidebar_with_icons_and_minimize_options(): void
    {
        $response = $this->get('/admin/clients');

        $response->assertStatus(200);
        // Sidebar container and elements
        $response->assertSee('id="admin-sidebar"', false);
        $response->assertSee('toggleSidebarCollapse()', false);
        $response->assertSee('toggleMobileSidebar()', false);
        $response->assertSee('toggleSubmenu', false);
        $response->assertSee('Minimize Sidebar');
        $response->assertSee('Clients & Stores', false);
        $response->assertSee('USB Hardware Locks');
        $response->assertSee('License Verify API');
    }
}
