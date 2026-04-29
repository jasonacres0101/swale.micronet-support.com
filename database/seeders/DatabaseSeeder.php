<?php

namespace Database\Seeders;

use App\Models\Camera;
use App\Models\Organisation;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@micronet.local'],
            [
                'name' => 'Micronet Admin',
                'role' => User::ROLE_ADMIN,
                'job_title' => 'Security Systems Administrator',
                'department' => 'Operations',
                'phone' => '0117 555 0101',
                'is_active' => true,
                'password' => Hash::make('password'),
            ],
        );

        $organisations = [
            'bristol-council' => Organisation::query()->updateOrCreate(
                ['name' => 'Bristol City Council'],
                [
                    'type' => 'council',
                    'contact_name' => 'Estate Monitoring Desk',
                    'contact_email' => 'monitoring@bristolcouncil.local',
                    'contact_phone' => '0117 555 3000',
                    'notes' => 'Primary council-owned estate monitored by Micronet.',
                ],
            ),
            'harbour-client' => Organisation::query()->updateOrCreate(
                ['name' => 'Harbour Estates Ltd'],
                [
                    'type' => 'client',
                    'contact_name' => 'Mia Turner',
                    'contact_email' => 'mia.turner@harbourestates.local',
                    'contact_phone' => '020 7000 4000',
                    'notes' => 'Client-owned CCTV estate with mixed managed services.',
                ],
            ),
            'northern-contractor' => Organisation::query()->updateOrCreate(
                ['name' => 'Northern Facilities Group'],
                [
                    'type' => 'contractor',
                    'contact_name' => 'Service Desk',
                    'contact_email' => 'servicedesk@northernfacilities.local',
                    'contact_phone' => '0161 555 2200',
                    'notes' => 'Contract maintenance partner for DR and service locations.',
                ],
            ),
        ];

        $sites = [
            'Bristol HQ' => Site::query()->updateOrCreate(
                ['name' => 'Bristol HQ'],
                [
                    'organisation_id' => $organisations['bristol-council']->id,
                    'address_line_1' => '1 Temple Way',
                    'town' => 'Bristol',
                    'postcode' => 'BS1 6HG',
                    'latitude' => 51.4545130,
                    'longitude' => -2.5879100,
                    'what3words' => 'towers.engine.ships',
                    'permit_to_dig_number' => 'PTD-BRS-10021',
                    'notes' => 'Council headquarters campus with perimeter and entrance coverage.',
                ],
            ),
            'Harbour Retail Park' => Site::query()->updateOrCreate(
                ['name' => 'Harbour Retail Park'],
                [
                    'organisation_id' => $organisations['harbour-client']->id,
                    'address_line_1' => '22 Dockside Road',
                    'town' => 'London',
                    'postcode' => 'E16 2QT',
                    'latitude' => 51.5072200,
                    'longitude' => -0.1275000,
                    'what3words' => 'planet.care.shelter',
                    'permit_to_dig_number' => 'PTD-LDN-88910',
                    'notes' => 'Client retail site with public-facing coverage and ANPR at the gate.',
                ],
            ),
            'Manchester DR' => Site::query()->updateOrCreate(
                ['name' => 'Manchester DR'],
                [
                    'organisation_id' => $organisations['northern-contractor']->id,
                    'address_line_1' => '7 Atlas Street',
                    'town' => 'Manchester',
                    'postcode' => 'M1 4AZ',
                    'latitude' => 53.4808000,
                    'longitude' => -2.2426000,
                    'what3words' => 'signal.locked.stages',
                    'permit_to_dig_number' => 'PTD-MAN-44118',
                    'notes' => 'Disaster recovery suite with restricted access and service alley coverage.',
                ],
            ),
        ];

        $cameras = [
            [
                'name' => 'Front Entrance',
                'site_id' => $sites['Bristol HQ']->id,
                'site_name' => 'Bristol HQ',
                'location_name' => 'Main entrance canopy',
                'ip_address' => '10.40.0.11',
                'web_ui_url' => 'http://10.40.0.11',
                'latitude' => 51.4545130,
                'longitude' => -2.5879100,
                'ownership_type' => 'council',
                'managed_by_council' => true,
                'status' => 'online',
                'is_online' => true,
                'last_seen_at' => now()->subMinutes(2),
                'description' => 'Primary entry camera covering the front doors and visitor parking.',
            ],
            [
                'name' => 'Warehouse South',
                'site_id' => $sites['Bristol HQ']->id,
                'site_name' => 'Bristol HQ',
                'location_name' => 'South loading bay',
                'ip_address' => '10.40.0.21',
                'web_ui_url' => 'http://10.40.0.21',
                'latitude' => 51.4540200,
                'longitude' => -2.5891000,
                'connectivity_type' => 'fibre',
                'ownership_type' => 'council',
                'managed_by_council' => true,
                'status' => 'offline',
                'is_online' => false,
                'last_seen_at' => now()->subHours(3),
                'description' => 'Loading area overview. Currently flagged offline for investigation.',
            ],
            [
                'name' => 'Reception Dome',
                'site_id' => $sites['Harbour Retail Park']->id,
                'site_name' => 'Harbour Retail Park',
                'location_name' => 'Reception desk ceiling mount',
                'ip_address' => '10.52.1.15',
                'web_ui_url' => 'http://10.52.1.15',
                'latitude' => 51.5072200,
                'longitude' => -0.1275000,
                'connectivity_type' => 'broadband',
                'ownership_type' => 'client',
                'managed_by_council' => false,
                'status' => 'online',
                'is_online' => true,
                'last_seen_at' => now()->subMinutes(6),
                'description' => 'Wide-angle internal camera covering reception and the visitor waiting area.',
            ],
            [
                'name' => 'Car Park PTZ',
                'site_id' => $sites['Harbour Retail Park']->id,
                'site_name' => 'Harbour Retail Park',
                'location_name' => 'North car park mast',
                'ip_address' => '10.52.1.44',
                'web_ui_url' => 'http://10.52.1.44',
                'latitude' => 51.5078900,
                'longitude' => -0.1281000,
                'connectivity_type' => 'leased_line',
                'ownership_type' => 'client',
                'managed_by_council' => false,
                'status' => 'online',
                'is_online' => true,
                'last_seen_at' => now()->subMinute(),
                'description' => 'PTZ camera with vehicle plate visibility at the gate.',
            ],
            [
                'name' => 'Server Room Door',
                'site_name' => 'Manchester DR',
                'site_id' => $sites['Manchester DR']->id,
                'location_name' => 'Server room vestibule',
                'ip_address' => '10.61.7.9',
                'web_ui_url' => 'http://10.61.7.9',
                'latitude' => 53.4808000,
                'longitude' => -2.2426000,
                'connectivity_type' => 'lan',
                'ownership_type' => 'council',
                'managed_by_council' => true,
                'status' => 'online',
                'is_online' => true,
                'last_seen_at' => now()->subMinutes(9),
                'description' => 'Internal security view for the disaster recovery suite entrance.',
            ],
            [
                'name' => 'Rear Alley',
                'site_name' => 'Manchester DR',
                'site_id' => $sites['Manchester DR']->id,
                'location_name' => 'Rear service alley',
                'ip_address' => '10.61.7.17',
                'web_ui_url' => 'http://10.61.7.17',
                'latitude' => 53.4813000,
                'longitude' => -2.2431000,
                'connectivity_type' => 'sim',
                'ownership_type' => 'council',
                'managed_by_council' => true,
                'status' => 'offline',
                'is_online' => false,
                'last_seen_at' => now()->subDay(),
                'description' => 'External alley coverage for deliveries and after-hours access.',
            ],
        ];

        foreach ($cameras as $camera) {
            Camera::query()->updateOrCreate(
                ['ip_address' => $camera['ip_address']],
                $camera,
            );
        }
    }
}
