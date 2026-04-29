<?php

namespace Tests\Feature;

use App\Models\Camera;
use App\Models\CameraStatusLog;
use App\Models\HikvisionEvent;
use App\Models\MaintenanceTask;
use App\Models\MaintenanceTaskType;
use App\Models\Organisation;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_guests_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_dashboard_and_camera_pages(): void
    {
        $user = User::factory()->create();
        $camera = Camera::query()->create([
            'name' => 'Test Camera',
            'site_name' => 'Test Site',
            'location_name' => 'Test Entrance',
            'ip_address' => '10.0.0.10',
            'web_ui_url' => 'http://10.0.0.10',
            'latitude' => 51.5,
            'longitude' => -0.12,
            'is_online' => true,
            'last_seen_at' => now(),
            'description' => 'Feature test camera',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Monitoring dashboard')
            ->assertSee($camera->name);

        $this->actingAs($user)
            ->get(route('cameras.index'))
            ->assertOk()
            ->assertSee($camera->ip_address);

        $this->actingAs($user)
            ->get(route('cameras.show', $camera))
            ->assertOk()
            ->assertSee('Open web UI');

        $this->actingAs($user)
            ->get(route('cameras.events'))
            ->assertForbidden();
    }

    public function test_admin_can_manage_users_and_view_user_admin_pages(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('User administration');

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Operations Viewer',
                'email' => 'viewer@example.com',
                'role' => User::ROLE_VIEWER,
                'phone' => '0117 555 0202',
                'job_title' => 'Monitoring Analyst',
                'department' => 'Security',
                'notes' => 'Receives read-only access.',
                'password' => 'securepass',
                'is_active' => '1',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'viewer@example.com',
            'role' => User::ROLE_VIEWER,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_manage_organisations_and_sites(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $organisation = Organisation::query()->create([
            'name' => 'Existing Client',
            'type' => 'client',
        ]);

        $this->actingAs($admin)
            ->get(route('organisations.index'))
            ->assertOk()
            ->assertSee('Organisations');

        $this->actingAs($admin)
            ->post(route('organisations.store'), [
                'name' => 'West Borough Council',
                'type' => 'council',
                'contact_name' => 'Network Desk',
                'contact_email' => 'network@westborough.local',
                'contact_phone' => '01234 555000',
                'notes' => 'Primary borough CCTV owner.',
            ])
            ->assertRedirect(route('organisations.index'));

        $newOrganisation = Organisation::query()->where('name', 'West Borough Council')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('sites.store'), [
                'organisation_id' => $newOrganisation->id,
                'name' => 'Town Hall',
                'address_line_1' => '1 Civic Square',
                'town' => 'Bristol',
                'postcode' => 'BS1 2AA',
                'latitude' => 51.45,
                'longitude' => -2.58,
                'what3words' => 'filled.count.soap',
                'permit_to_dig_number' => 'PTD-100',
                'notes' => 'Main council building.',
            ])
            ->assertRedirect(route('sites.index'));

        $this->assertDatabaseHas('sites', [
            'name' => 'Town Hall',
            'organisation_id' => $newOrganisation->id,
        ]);

        $site = Site::query()->where('name', 'Town Hall')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('sites.edit', $site))
            ->assertOk()
            ->assertSee('Town Hall');
    }

    public function test_auditor_has_read_only_access_without_admin_actions(): void
    {
        $auditor = User::factory()->create([
            'role' => User::ROLE_VIEWER,
            'is_active' => true,
        ]);
        $organisation = Organisation::query()->create([
            'name' => 'Read Only Council',
            'type' => 'council',
        ]);
        $site = Site::query()->create([
            'organisation_id' => $organisation->id,
            'name' => 'Read Only Site',
        ]);

        $this->actingAs($auditor)
            ->get(route('users.index'))
            ->assertForbidden();

        $this->actingAs($auditor)
            ->get(route('cameras.events'))
            ->assertForbidden();

        $this->actingAs($auditor)
            ->get(route('organisations.index'))
            ->assertOk()
            ->assertSee('Read Only Council')
            ->assertDontSee('Add organisation');

        $this->actingAs($auditor)
            ->get(route('sites.index'))
            ->assertOk()
            ->assertSee('Read Only Site')
            ->assertDontSee('Add site');

        $this->actingAs($auditor)
            ->get(route('sites.edit', $site))
            ->assertForbidden();
    }

    public function test_client_user_only_sees_their_organisation_cameras_and_api_data(): void
    {
        $clientOrganisation = Organisation::query()->create([
            'name' => 'Client Scoped Ltd',
            'type' => 'client',
        ]);
        $otherOrganisation = Organisation::query()->create([
            'name' => 'Other Estate Ltd',
            'type' => 'client',
        ]);
        $clientSite = Site::query()->create([
            'organisation_id' => $clientOrganisation->id,
            'name' => 'Client Scoped Site',
        ]);
        $otherSite = Site::query()->create([
            'organisation_id' => $otherOrganisation->id,
            'name' => 'Other Scoped Site',
        ]);
        $clientCamera = Camera::query()->create([
            'name' => 'Client Visible Camera',
            'site_id' => $clientSite->id,
            'site_name' => $clientSite->name,
            'location_name' => 'Client Gate',
            'ip_address' => '10.70.0.10',
            'web_ui_url' => 'http://10.70.0.10',
            'status' => 'online',
            'is_online' => true,
            'ownership_type' => 'client',
        ]);
        $otherCamera = Camera::query()->create([
            'name' => 'Other Hidden Camera',
            'site_id' => $otherSite->id,
            'site_name' => $otherSite->name,
            'location_name' => 'Other Gate',
            'ip_address' => '10.80.0.10',
            'web_ui_url' => 'http://10.80.0.10',
            'status' => 'online',
            'is_online' => true,
            'ownership_type' => 'client',
        ]);
        $clientUser = User::factory()->create([
            'role' => User::ROLE_CLIENT,
            'organisation_id' => $clientOrganisation->id,
            'is_active' => true,
        ]);

        $this->actingAs($clientUser)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Client Visible Camera')
            ->assertDontSee('Other Hidden Camera');

        $this->actingAs($clientUser)
            ->get(route('cameras.show', $clientCamera))
            ->assertOk()
            ->assertSee('Client Visible Camera');

        $this->actingAs($clientUser)
            ->get(route('cameras.show', $otherCamera))
            ->assertForbidden();

        $response = $this->actingAs($clientUser)->getJson(route('api.cameras.index'));

        $response
            ->assertOk()
            ->assertJsonPath('cameras.0.name', 'Client Visible Camera');

        $this->assertCount(1, $response->json('cameras'));

        $this->actingAs($clientUser)
            ->getJson(route('api.cameras.show', $otherCamera))
            ->assertForbidden();

        $this->actingAs($clientUser)
            ->get(route('reports.uptime'))
            ->assertOk()
            ->assertSee('Client Visible Camera')
            ->assertDontSee('Other Hidden Camera');
    }

    public function test_engineer_can_edit_cameras_but_not_manage_users_or_create_cameras(): void
    {
        $engineer = User::factory()->create([
            'role' => User::ROLE_ENGINEER,
            'is_active' => true,
        ]);
        $organisation = Organisation::query()->create([
            'name' => 'Engineering Council',
            'type' => 'council',
        ]);
        $site = Site::query()->create([
            'organisation_id' => $organisation->id,
            'name' => 'Engineering Site',
        ]);
        $camera = Camera::query()->create([
            'name' => 'Editable Camera',
            'site_id' => $site->id,
            'site_name' => $site->name,
            'location_name' => 'Plant Room',
            'ip_address' => '10.90.0.10',
            'web_ui_url' => 'http://10.90.0.10',
            'status' => 'online',
            'is_online' => true,
        ]);

        $this->actingAs($engineer)
            ->get(route('cameras.edit', $camera))
            ->assertOk()
            ->assertSee('Editable Camera');

        $this->actingAs($engineer)
            ->get(route('users.index'))
            ->assertForbidden();

        $this->actingAs($engineer)
            ->get(route('cameras.create'))
            ->assertForbidden();
    }

    public function test_hikvision_setup_info_page_is_available_to_alarm_admin_users(): void
    {
        $engineer = User::factory()->create([
            'role' => User::ROLE_ENGINEER,
            'is_active' => true,
        ]);
        $auditor = User::factory()->create([
            'role' => User::ROLE_AUDITOR,
            'is_active' => true,
        ]);

        $this->actingAs($engineer)
            ->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Camera setup guide');

        $this->actingAs($engineer)
            ->get(route('settings.hikvision-setup'))
            ->assertOk()
            ->assertSee('Hikvision camera setup')
            ->assertSee('/api/hikvision/events')
            ->assertSee('X-Hikvision-Token')
            ->assertSee('MAC address')
            ->assertSee('PowerShell test command')
            ->assertSee('Camera MAC address')
            ->assertSee('Alarm token')
            ->assertSee('Invoke-RestMethod');

        $this->actingAs($auditor)
            ->get(route('settings.hikvision-setup'))
            ->assertForbidden();
    }

    public function test_live_status_endpoint_returns_all_cameras_with_latest_event_data(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_VIEWER,
            'is_active' => true,
        ]);

        $camera = Camera::query()->create([
            'name' => 'Lobby Camera',
            'site_name' => 'HQ',
            'location_name' => 'Lobby',
            'ip_address' => '10.0.0.40',
            'mac_address' => '44:19:B6:AA:BB:CC',
            'web_ui_url' => 'http://10.0.0.40',
            'status' => 'online',
            'is_online' => true,
            'last_seen_at' => now(),
            'last_event_at' => now(),
            'latitude' => 51.5,
            'longitude' => -0.1,
        ]);

        HikvisionEvent::query()->create([
            'camera_id' => $camera->id,
            'source_ip' => '10.0.0.40',
            'event_type' => 'VMD',
            'event_state' => 'active',
            'event_description' => 'Motion detected in lobby',
            'event_time' => now(),
            'mac_address' => $camera->mac_address,
            'ip_address' => $camera->ip_address,
            'raw_payload' => '{"eventType":"VMD"}',
            'parsed_payload' => ['eventType' => 'VMD'],
        ]);

        $response = $this->actingAs($user)->getJson(route('api.cameras.live-status'));

        $response
            ->assertOk()
            ->assertJsonPath('cameras.0.id', $camera->id)
            ->assertJsonPath('cameras.0.latest_event_type', 'VMD')
            ->assertJsonPath('cameras.0.latest_event_state', 'active')
            ->assertJsonPath('cameras.0.latest_event_description', 'Motion detected in lobby');
    }

    public function test_camera_index_api_returns_connectivity_fields(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_VIEWER,
            'is_active' => true,
        ]);

        $camera = Camera::query()->create([
            'name' => 'Connectivity Camera',
            'site_name' => 'HQ',
            'location_name' => 'Comms Room',
            'ip_address' => '10.0.0.42',
            'mac_address' => '44:19:B6:11:22:33',
            'web_ui_url' => 'http://10.0.0.42',
            'status' => 'online',
            'is_online' => true,
            'connectivity_type' => 'sim',
            'connectivity_provider' => 'Vodafone',
            'sim_number' => '447700900123',
            'sim_iccid' => '89441122334455667788',
            'sim_static_ip' => '100.64.10.20',
            'apn_name' => 'private.apn',
            'router_model' => 'Teltonika RUT241',
            'router_serial' => 'RUT241-12345',
            'router_ip_address' => '192.168.8.1',
            'wan_ip_address' => '81.2.69.142',
            'private_apn' => true,
            'connectivity_notes' => 'Primary uplink via managed SIM.',
        ]);

        $response = $this->actingAs($user)->getJson(route('api.cameras.index'));

        $response
            ->assertOk()
            ->assertJsonPath('cameras.0.id', $camera->id)
            ->assertJsonPath('cameras.0.connectivity_type', 'sim')
            ->assertJsonPath('cameras.0.connectivity_provider', 'Vodafone')
            ->assertJsonPath('cameras.0.sim_iccid', '89441122334455667788')
            ->assertJsonPath('cameras.0.private_apn', true);
    }

    public function test_camera_api_includes_organisation_site_and_site_status(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_VIEWER,
            'is_active' => true,
        ]);

        $organisation = Organisation::query()->create([
            'name' => 'Example Council',
            'type' => 'council',
        ]);

        $site = Site::query()->create([
            'organisation_id' => $organisation->id,
            'name' => 'Town Centre',
            'town' => 'Bristol',
            'postcode' => 'BS1 1AA',
        ]);

        $camera = Camera::query()->create([
            'name' => 'Square Camera',
            'site_id' => $site->id,
            'site_name' => $site->name,
            'location_name' => 'Market Square',
            'ip_address' => '10.0.0.55',
            'web_ui_url' => 'http://10.0.0.55',
            'status' => 'online',
            'is_online' => true,
            'ownership_type' => 'council',
            'managed_by_council' => true,
        ]);

        Camera::query()->create([
            'name' => 'Arcade Camera',
            'site_id' => $site->id,
            'site_name' => $site->name,
            'location_name' => 'Arcade',
            'ip_address' => '10.0.0.56',
            'web_ui_url' => 'http://10.0.0.56',
            'status' => 'offline',
            'is_online' => false,
            'ownership_type' => 'council',
            'managed_by_council' => true,
        ]);

        $response = $this->actingAs($user)->getJson(route('api.cameras.show', $camera));

        $response
            ->assertOk()
            ->assertJsonPath('organisation.name', 'Example Council')
            ->assertJsonPath('site.name', 'Town Centre')
            ->assertJsonPath('site.status', 'degraded')
            ->assertJsonPath('ownership_type', 'council')
            ->assertJsonPath('managed_by_council', true);
    }

    public function test_single_camera_live_status_endpoint_returns_debug_event_payload(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_VIEWER,
            'is_active' => true,
        ]);

        $camera = Camera::query()->create([
            'name' => 'Rear Gate Camera',
            'site_name' => 'HQ',
            'location_name' => 'Rear Gate',
            'ip_address' => '10.0.0.41',
            'mac_address' => '44:19:B6:DD:EE:FF',
            'web_ui_url' => 'http://10.0.0.41',
            'status' => 'offline',
            'is_online' => false,
            'last_seen_at' => now()->subMinutes(6),
            'last_event_at' => now()->subMinutes(6),
        ]);

        HikvisionEvent::query()->create([
            'camera_id' => $camera->id,
            'source_ip' => '10.0.0.41',
            'event_type' => 'lineCrossing',
            'event_state' => 'inactive',
            'event_description' => 'Line crossing cleared',
            'event_time' => now()->subMinutes(6),
            'mac_address' => $camera->mac_address,
            'ip_address' => $camera->ip_address,
            'raw_payload' => '<EventNotificationAlert />',
            'parsed_payload' => ['eventType' => 'lineCrossing'],
        ]);

        $response = $this->actingAs($user)->getJson(route('api.cameras.live-status.show', $camera));

        $response
            ->assertOk()
            ->assertJsonPath('id', $camera->id)
            ->assertJsonPath('latest_event_type', 'lineCrossing')
            ->assertJsonPath('latest_event_state', 'inactive')
            ->assertJsonPath('latest_event_description', 'Line crossing cleared')
            ->assertJsonPath('latest_event_raw_payload', '<EventNotificationAlert />');
    }

    public function test_single_camera_api_returns_connectivity_details(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_VIEWER,
            'is_active' => true,
        ]);

        $camera = Camera::query()->create([
            'name' => 'WAN Camera',
            'site_name' => 'HQ',
            'location_name' => 'Roof',
            'ip_address' => '10.0.0.43',
            'web_ui_url' => 'http://10.0.0.43',
            'status' => 'unknown',
            'is_online' => false,
            'connectivity_type' => 'leased_line',
            'connectivity_provider' => 'BT',
            'router_model' => 'Cisco ISR',
            'router_serial' => 'ISR-98765',
            'router_ip_address' => '172.16.0.1',
            'wan_ip_address' => '203.0.113.50',
            'private_apn' => false,
            'connectivity_notes' => 'Terminated in building comms cabinet.',
        ]);

        $response = $this->actingAs($user)->getJson(route('api.cameras.show', $camera));

        $response
            ->assertOk()
            ->assertJsonPath('id', $camera->id)
            ->assertJsonPath('connectivity_type', 'leased_line')
            ->assertJsonPath('connectivity_provider', 'BT')
            ->assertJsonPath('router_model', 'Cisco ISR')
            ->assertJsonPath('wan_ip_address', '203.0.113.50')
            ->assertJsonPath('connectivity_notes', 'Terminated in building comms cabinet.');
    }

    public function test_hikvision_event_matches_camera_by_mac_and_updates_status(): void
    {
        $camera = Camera::query()->create([
            'name' => 'Receiving Camera',
            'site_name' => 'HQ',
            'location_name' => 'Door',
            'ip_address' => '10.0.0.20',
            'mac_address' => '44:19:B6:12:34:56',
            'mac_address_normalized' => '4419B6123456',
            'web_ui_url' => 'http://10.0.0.20',
            'status' => 'offline',
            'is_online' => false,
        ]);

        $response = $this->postJson('/api/hikvision/events', [
            'ipAddress' => '10.0.0.99',
            'macAddress' => '44-19-b6-12-34-56',
            'eventType' => 'VMD',
            'eventState' => 'active',
            'eventDescription' => 'Motion detected',
            'dateTime' => now()->toIso8601String(),
            'channelID' => '1',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Event received',
            ]);

        $camera->refresh();

        $this->assertSame('online', $camera->status);
        $this->assertTrue($camera->is_online);
        $this->assertSame('10.0.0.99', $camera->ip_address);
        $this->assertDatabaseHas('hikvision_events', [
            'camera_id' => $camera->id,
            'event_type' => 'VMD',
            'event_state' => 'active',
        ]);
        $this->assertDatabaseHas('camera_status_logs', [
            'camera_id' => $camera->id,
            'old_status' => 'offline',
            'new_status' => 'online',
        ]);
    }

    public function test_hikvision_endpoint_rejects_invalid_token_when_configured(): void
    {
        config()->set('hikvision.alarm_token', 'secret-token');

        $response = $this->postJson('/api/hikvision/events', [
            'eventType' => 'VMD',
        ], [
            'X-Hikvision-Token' => 'wrong-token',
        ]);

        $response->assertForbidden();
        $this->assertSame(0, HikvisionEvent::query()->count());
    }

    public function test_hikvision_endpoint_accepts_query_token_for_camera_alarm_server(): void
    {
        config()->set('hikvision.alarm_token', 'secret-token');

        $camera = Camera::query()->create([
            'name' => 'Query Token Camera',
            'site_name' => 'HQ',
            'location_name' => 'Gate',
            'ip_address' => '10.0.0.25',
            'mac_address' => '44:19:B6:AA:25:25',
            'mac_address_normalized' => '4419B6AA2525',
            'web_ui_url' => 'http://10.0.0.25',
            'status' => 'offline',
            'is_online' => false,
        ]);

        $response = $this->post('/api/hikvision/events?token=secret-token', [
            'macAddress' => '44:19:B6:AA:25:25',
            'ipAddress' => '10.0.0.25',
            'eventType' => 'VMD',
            'eventState' => 'active',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('hikvision_events', [
            'camera_id' => $camera->id,
            'event_type' => 'VMD',
        ]);

        $this->assertSame('online', $camera->refresh()->status);
    }

    public function test_camera_status_command_marks_stale_cameras_offline_and_never_seen_unknown(): void
    {
        $offlineCamera = Camera::query()->create([
            'name' => 'Stale Camera',
            'site_name' => 'HQ',
            'location_name' => 'Yard',
            'ip_address' => '10.0.0.30',
            'web_ui_url' => 'http://10.0.0.30',
            'status' => 'online',
            'is_online' => true,
            'last_seen_at' => now()->subMinutes(10),
        ]);

        $unknownCamera = Camera::query()->create([
            'name' => 'Never Seen Camera',
            'site_name' => 'HQ',
            'location_name' => 'Vault',
            'ip_address' => '10.0.0.31',
            'web_ui_url' => 'http://10.0.0.31',
            'status' => 'online',
            'is_online' => true,
            'last_seen_at' => null,
        ]);

        Artisan::call('cameras:check-status');

        $offlineCamera->refresh();
        $unknownCamera->refresh();

        $this->assertSame('offline', $offlineCamera->status);
        $this->assertFalse($offlineCamera->is_online);
        $this->assertSame('unknown', $unknownCamera->status);
        $this->assertFalse($unknownCamera->is_online);
        $this->assertSame(2, CameraStatusLog::query()->count());
    }

    public function test_uptime_report_filters_by_organisation_and_exports_csv(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_VIEWER,
            'is_active' => true,
        ]);
        $organisation = Organisation::query()->create([
            'name' => 'Report Council',
            'type' => 'council',
        ]);
        $otherOrganisation = Organisation::query()->create([
            'name' => 'Other Client',
            'type' => 'client',
        ]);
        $site = Site::query()->create([
            'organisation_id' => $organisation->id,
            'name' => 'Civic Centre',
        ]);
        $otherSite = Site::query()->create([
            'organisation_id' => $otherOrganisation->id,
            'name' => 'Other Site',
        ]);
        $camera = Camera::query()->create([
            'name' => 'Report Camera',
            'site_id' => $site->id,
            'site_name' => $site->name,
            'location_name' => 'Front Door',
            'ip_address' => '10.10.0.10',
            'web_ui_url' => 'http://10.10.0.10',
            'status' => 'online',
            'is_online' => true,
            'ownership_type' => 'council',
            'connectivity_type' => 'sim',
        ]);
        $otherCamera = Camera::query()->create([
            'name' => 'Hidden Report Camera',
            'site_id' => $otherSite->id,
            'site_name' => $otherSite->name,
            'location_name' => 'Back Door',
            'ip_address' => '10.10.0.11',
            'web_ui_url' => 'http://10.10.0.11',
            'status' => 'online',
            'is_online' => true,
            'ownership_type' => 'client',
            'connectivity_type' => 'fibre',
        ]);
        $from = now()->subDay()->toDateString();
        $to = now()->toDateString();

        CameraStatusLog::query()->create([
            'camera_id' => $camera->id,
            'old_status' => 'unknown',
            'new_status' => 'online',
            'reason' => 'test baseline',
            'created_at' => now()->subDays(2),
        ]);
        CameraStatusLog::query()->create([
            'camera_id' => $camera->id,
            'old_status' => 'online',
            'new_status' => 'offline',
            'reason' => 'test outage',
            'created_at' => now()->subHours(12),
        ]);
        CameraStatusLog::query()->create([
            'camera_id' => $camera->id,
            'old_status' => 'offline',
            'new_status' => 'online',
            'reason' => 'test recovery',
            'created_at' => now()->subHours(6),
        ]);
        CameraStatusLog::query()->create([
            'camera_id' => $otherCamera->id,
            'old_status' => 'unknown',
            'new_status' => 'online',
            'reason' => 'other baseline',
            'created_at' => now()->subDays(2),
        ]);

        $query = [
            'date_from' => $from,
            'date_to' => $to,
            'organisation' => $organisation->id,
        ];

        $this->actingAs($user)
            ->get(route('reports.uptime', $query))
            ->assertOk()
            ->assertSee('Uptime report')
            ->assertSee('Report Camera')
            ->assertDontSee('Hidden Report Camera')
            ->assertSee('Calculated from status logs');

        $response = $this->actingAs($user)->get(route('reports.uptime.export', [...$query, 'format' => 'csv']));
        $content = $response->streamedContent();

        $response->assertOk();
        $this->assertStringContainsString('Report Camera', $content);
        $this->assertStringNotContainsString('Hidden Report Camera', $content);
    }

    public function test_event_report_filters_by_event_type_and_exports_pdf(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_VIEWER,
            'is_active' => true,
        ]);
        $organisation = Organisation::query()->create([
            'name' => 'Events Council',
            'type' => 'council',
        ]);
        $site = Site::query()->create([
            'organisation_id' => $organisation->id,
            'name' => 'Market Square',
        ]);
        $camera = Camera::query()->create([
            'name' => 'Events Camera',
            'site_id' => $site->id,
            'site_name' => $site->name,
            'location_name' => 'Square',
            'ip_address' => '10.20.0.10',
            'web_ui_url' => 'http://10.20.0.10',
            'status' => 'online',
            'is_online' => true,
        ]);
        $from = now()->subDay()->toDateString();
        $to = now()->toDateString();

        HikvisionEvent::query()->create([
            'camera_id' => $camera->id,
            'source_ip' => $camera->ip_address,
            'event_type' => 'VMD',
            'event_state' => 'active',
            'event_description' => 'Motion event for report',
            'event_time' => now()->subHours(2),
            'raw_payload' => '{"eventType":"VMD"}',
            'parsed_payload' => ['eventType' => 'VMD'],
        ]);
        HikvisionEvent::query()->create([
            'camera_id' => $camera->id,
            'source_ip' => $camera->ip_address,
            'event_type' => 'lineCrossing',
            'event_state' => 'active',
            'event_description' => 'Line crossing event should be filtered',
            'event_time' => now()->subHour(),
            'raw_payload' => '{"eventType":"lineCrossing"}',
            'parsed_payload' => ['eventType' => 'lineCrossing'],
        ]);

        $query = [
            'date_from' => $from,
            'date_to' => $to,
            'event_type' => 'VMD',
        ];

        $this->actingAs($user)
            ->get(route('reports.events', $query))
            ->assertOk()
            ->assertSee('Motion event for report')
            ->assertDontSee('Line crossing event should be filtered');

        $this->actingAs($user)
            ->get(route('reports.events.export', [...$query, 'format' => 'pdf']))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_council_operator_can_create_maintenance_task(): void
    {
        $operator = User::factory()->create([
            'role' => User::ROLE_COUNCIL_OPERATOR,
            'is_active' => true,
        ]);
        [$organisation, $site, $camera] = $this->maintenanceEstate();

        $this->actingAs($operator)
            ->post(route('maintenance.store'), [
                'organisation_id' => $organisation->id,
                'site_id' => $site->id,
                'camera_id' => $camera->id,
                'assigned_user_id' => $operator->id,
                'task_type' => MaintenanceTask::TYPE_LENS_CLEANING,
                'title' => 'Clean north lens',
                'description' => 'Scheduled clean after bad weather.',
                'status' => MaintenanceTask::STATUS_SCHEDULED,
                'priority' => MaintenanceTask::PRIORITY_HIGH,
                'scheduled_for' => now()->addDay()->toDateString(),
                'due_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
                'recurrence_type' => MaintenanceTask::RECURRENCE_MONTHLY,
                'recurrence_interval' => 1,
                'notes' => 'Use lift access.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('maintenance_tasks', [
            'title' => 'Clean north lens',
            'camera_id' => $camera->id,
            'task_type' => MaintenanceTask::TYPE_LENS_CLEANING,
            'priority' => MaintenanceTask::PRIORITY_HIGH,
        ]);
    }

    public function test_council_operator_can_manage_maintenance_task_types(): void
    {
        $operator = User::factory()->create([
            'role' => User::ROLE_COUNCIL_OPERATOR,
            'is_active' => true,
        ]);

        $this->actingAs($operator)
            ->get(route('settings.maintenance-task-types.index'))
            ->assertOk()
            ->assertSee('Maintenance task types')
            ->assertSee('Lens cleaning');

        $this->actingAs($operator)
            ->post(route('settings.maintenance-task-types.store'), [
                'name' => 'Pole bracket inspection',
                'slug' => 'pole_bracket_inspection',
                'description' => 'Check pole brackets, fixings, and corrosion.',
                'sort_order' => 20,
                'is_active' => '1',
            ])
            ->assertRedirect(route('settings.maintenance-task-types.index'));

        $taskType = MaintenanceTaskType::query()->where('slug', 'pole_bracket_inspection')->firstOrFail();

        $this->actingAs($operator)
            ->put(route('settings.maintenance-task-types.update', $taskType), [
                'name' => 'Pole and bracket inspection',
                'slug' => 'pole_bracket_inspection',
                'description' => 'Updated inspection wording.',
                'sort_order' => 21,
                'is_active' => '1',
            ])
            ->assertRedirect(route('settings.maintenance-task-types.index'));

        $this->actingAs($operator)
            ->get(route('maintenance.create'))
            ->assertOk()
            ->assertSee('Pole and bracket inspection');
    }

    public function test_engineer_can_mark_maintenance_task_complete(): void
    {
        $engineer = User::factory()->create([
            'role' => User::ROLE_ENGINEER,
            'is_active' => true,
        ]);
        [$organisation, $site, $camera] = $this->maintenanceEstate();
        $task = MaintenanceTask::query()->create([
            'organisation_id' => $organisation->id,
            'site_id' => $site->id,
            'camera_id' => $camera->id,
            'assigned_user_id' => $engineer->id,
            'task_type' => MaintenanceTask::TYPE_ROUTER_SIM_CHECK,
            'title' => 'SIM check',
            'status' => MaintenanceTask::STATUS_IN_PROGRESS,
            'priority' => MaintenanceTask::PRIORITY_NORMAL,
            'due_at' => now()->addHour(),
        ]);

        $this->actingAs($engineer)
            ->post(route('maintenance.complete', $task), [
                'completion_notes' => 'SIM signal checked and stable.',
                'engineer_recommendations' => 'Replace antenna next visit.',
            ])
            ->assertRedirect();

        $task->refresh();

        $this->assertSame(MaintenanceTask::STATUS_COMPLETED, $task->status);
        $this->assertNotNull($task->completed_at);
        $this->assertSame('SIM signal checked and stable.', $task->completion_notes);
        $this->assertSame('Replace antenna next visit.', $task->engineer_recommendations);
    }

    public function test_maintenance_status_command_marks_overdue_and_generates_recurring_task(): void
    {
        [$organisation, $site, $camera] = $this->maintenanceEstate();
        $overdueTask = MaintenanceTask::query()->create([
            'organisation_id' => $organisation->id,
            'site_id' => $site->id,
            'camera_id' => $camera->id,
            'task_type' => MaintenanceTask::TYPE_INSPECTION,
            'title' => 'Past due inspection',
            'status' => MaintenanceTask::STATUS_SCHEDULED,
            'priority' => MaintenanceTask::PRIORITY_NORMAL,
            'due_at' => now()->subHour(),
        ]);
        $recurringTask = MaintenanceTask::query()->create([
            'organisation_id' => $organisation->id,
            'site_id' => $site->id,
            'camera_id' => $camera->id,
            'task_type' => MaintenanceTask::TYPE_ANNUAL_SERVICE_REPORT,
            'title' => 'Annual report',
            'status' => MaintenanceTask::STATUS_COMPLETED,
            'priority' => MaintenanceTask::PRIORITY_HIGH,
            'due_at' => now()->subDay(),
            'completed_at' => now()->subHour(),
            'recurrence_type' => MaintenanceTask::RECURRENCE_ANNUALLY,
            'recurrence_interval' => 1,
            'next_due_at' => now()->addYear(),
        ]);

        Artisan::call('maintenance:update-status');

        $overdueTask->refresh();
        $recurringTask->refresh();

        $this->assertSame(MaintenanceTask::STATUS_OVERDUE, $overdueTask->status);
        $this->assertNotNull($recurringTask->recurrence_generated_at);
        $this->assertDatabaseHas('maintenance_tasks', [
            'recurring_source_id' => $recurringTask->id,
            'status' => MaintenanceTask::STATUS_SCHEDULED,
            'task_type' => MaintenanceTask::TYPE_ANNUAL_SERVICE_REPORT,
        ]);

        Artisan::call('maintenance:update-status');

        $this->assertSame(1, MaintenanceTask::query()->where('recurring_source_id', $recurringTask->id)->count());
    }

    public function test_maintenance_role_restrictions_and_client_scope(): void
    {
        [$organisation, $site, $camera] = $this->maintenanceEstate();
        [$otherOrganisation, $otherSite, $otherCamera] = $this->maintenanceEstate('Other Maintenance Client', 'Other Maintenance Site', 'Other Maintenance Camera');
        $client = User::factory()->create([
            'role' => User::ROLE_CLIENT,
            'organisation_id' => $organisation->id,
            'is_active' => true,
        ]);
        $auditor = User::factory()->create([
            'role' => User::ROLE_AUDITOR,
            'is_active' => true,
        ]);
        $visibleTask = MaintenanceTask::query()->create([
            'organisation_id' => $organisation->id,
            'site_id' => $site->id,
            'camera_id' => $camera->id,
            'task_type' => MaintenanceTask::TYPE_INSPECTION,
            'title' => 'Client visible maintenance',
            'status' => MaintenanceTask::STATUS_SCHEDULED,
            'priority' => MaintenanceTask::PRIORITY_NORMAL,
            'due_at' => now()->addDay(),
        ]);
        $hiddenTask = MaintenanceTask::query()->create([
            'organisation_id' => $otherOrganisation->id,
            'site_id' => $otherSite->id,
            'camera_id' => $otherCamera->id,
            'task_type' => MaintenanceTask::TYPE_INSPECTION,
            'title' => 'Hidden maintenance',
            'status' => MaintenanceTask::STATUS_SCHEDULED,
            'priority' => MaintenanceTask::PRIORITY_NORMAL,
            'due_at' => now()->addDay(),
        ]);

        $this->actingAs($client)
            ->get(route('maintenance.index'))
            ->assertOk()
            ->assertSee('Client visible maintenance')
            ->assertDontSee('Hidden maintenance');

        $this->actingAs($client)
            ->get(route('maintenance.show', $visibleTask))
            ->assertOk();

        $this->actingAs($client)
            ->get(route('maintenance.show', $hiddenTask))
            ->assertForbidden();

        $this->actingAs($client)
            ->get(route('maintenance.create'))
            ->assertForbidden();

        $this->actingAs($auditor)
            ->get(route('maintenance.edit', $visibleTask))
            ->assertForbidden();
    }

    public function test_annual_service_report_pdf_route(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_AUDITOR,
            'is_active' => true,
        ]);
        [$organisation, $site, $camera] = $this->maintenanceEstate();
        $task = MaintenanceTask::query()->create([
            'organisation_id' => $organisation->id,
            'site_id' => $site->id,
            'camera_id' => $camera->id,
            'task_type' => MaintenanceTask::TYPE_ANNUAL_SERVICE_REPORT,
            'title' => 'Annual Service 2026',
            'status' => MaintenanceTask::STATUS_COMPLETED,
            'priority' => MaintenanceTask::PRIORITY_HIGH,
            'completed_at' => now(),
            'completion_notes' => 'All checks completed.',
            'engineer_recommendations' => 'Schedule lens replacement.',
        ]);

        $this->actingAs($user)
            ->get(route('maintenance.service-report.pdf', $task))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_engineer_can_upload_one_and_multiple_maintenance_images(): void
    {
        Storage::fake('public');
        $engineer = User::factory()->create([
            'role' => User::ROLE_ENGINEER,
            'is_active' => true,
        ]);
        [$organisation, $site, $camera] = $this->maintenanceEstate();
        $task = MaintenanceTask::query()->create([
            'organisation_id' => $organisation->id,
            'site_id' => $site->id,
            'camera_id' => $camera->id,
            'assigned_user_id' => $engineer->id,
            'task_type' => MaintenanceTask::TYPE_LENS_CLEANING,
            'title' => 'Upload evidence',
            'status' => MaintenanceTask::STATUS_IN_PROGRESS,
            'priority' => MaintenanceTask::PRIORITY_NORMAL,
        ]);

        $this->actingAs($engineer)
            ->post(route('api.maintenance.attachments.store', $task), [
                'attachments' => [UploadedFile::fake()->image('single.jpg')],
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->actingAs($engineer)
            ->post(route('api.maintenance.attachments.store', $task), [
                'attachments' => [
                    UploadedFile::fake()->image('first.png'),
                    UploadedFile::fake()->image('second.webp'),
                ],
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame(3, $task->attachments()->count());

        foreach ($task->attachments as $attachment) {
            Storage::disk('public')->assertExists($attachment->path);
        }
    }

    public function test_invalid_maintenance_uploads_are_rejected(): void
    {
        Storage::fake('public');
        config()->set('maintenance.max_upload_kb', 1);
        $engineer = User::factory()->create([
            'role' => User::ROLE_ENGINEER,
            'is_active' => true,
        ]);
        [$organisation, $site, $camera] = $this->maintenanceEstate();
        $task = MaintenanceTask::query()->create([
            'organisation_id' => $organisation->id,
            'site_id' => $site->id,
            'camera_id' => $camera->id,
            'task_type' => MaintenanceTask::TYPE_LENS_CLEANING,
            'title' => 'Upload validation',
            'status' => MaintenanceTask::STATUS_IN_PROGRESS,
            'priority' => MaintenanceTask::PRIORITY_NORMAL,
        ]);

        $this->actingAs($engineer)
            ->post(route('api.maintenance.attachments.store', $task), [
                'attachments' => [UploadedFile::fake()->create('bad.txt', 1, 'text/plain')],
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable();

        $this->actingAs($engineer)
            ->post(route('api.maintenance.attachments.store', $task), [
                'attachments' => [UploadedFile::fake()->image('too-large.jpg')->size(2048)],
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable();

        $this->assertSame(0, $task->attachments()->count());
    }

    public function test_client_can_view_but_not_upload_maintenance_images(): void
    {
        Storage::fake('public');
        $engineer = User::factory()->create([
            'role' => User::ROLE_ENGINEER,
            'is_active' => true,
        ]);
        [$organisation, $site, $camera] = $this->maintenanceEstate();
        $client = User::factory()->create([
            'role' => User::ROLE_CLIENT,
            'organisation_id' => $organisation->id,
            'is_active' => true,
        ]);
        $task = MaintenanceTask::query()->create([
            'organisation_id' => $organisation->id,
            'site_id' => $site->id,
            'camera_id' => $camera->id,
            'task_type' => MaintenanceTask::TYPE_LENS_CLEANING,
            'title' => 'Client image task',
            'status' => MaintenanceTask::STATUS_COMPLETED,
            'priority' => MaintenanceTask::PRIORITY_NORMAL,
        ]);

        $this->actingAs($engineer)
            ->post(route('api.maintenance.attachments.store', $task), [
                'attachments' => [UploadedFile::fake()->image('client-visible.jpg')],
            ], ['Accept' => 'application/json'])
            ->assertOk();

        $this->actingAs($client)
            ->get(route('maintenance.show', $task))
            ->assertOk()
            ->assertSee('client-visible.jpg');

        $this->actingAs($client)
            ->post(route('api.maintenance.attachments.store', $task), [
                'attachments' => [UploadedFile::fake()->image('blocked.jpg')],
            ], ['Accept' => 'application/json'])
            ->assertForbidden();
    }

    private function maintenanceEstate(
        string $organisationName = 'Maintenance Council',
        string $siteName = 'Maintenance Site',
        string $cameraName = 'Maintenance Camera',
    ): array {
        static $counter = 1;

        $organisation = Organisation::query()->create([
            'name' => $organisationName,
            'type' => 'client',
        ]);
        $site = Site::query()->create([
            'organisation_id' => $organisation->id,
            'name' => $siteName,
        ]);
        $camera = Camera::query()->create([
            'name' => $cameraName,
            'site_id' => $site->id,
            'site_name' => $site->name,
            'location_name' => 'North gate',
            'ip_address' => '10.200.0.'.$counter,
            'web_ui_url' => 'http://10.200.0.'.$counter,
            'status' => 'online',
            'is_online' => true,
            'ownership_type' => 'client',
            'connectivity_type' => 'sim',
            'connectivity_provider' => 'Vodafone',
            'router_model' => 'Teltonika RUT241',
            'wan_ip_address' => '203.0.113.'.$counter,
        ]);
        $counter++;

        return [$organisation, $site, $camera];
    }
}
