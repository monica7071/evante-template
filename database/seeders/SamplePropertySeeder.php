<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SamplePropertySeeder extends Seeder
{
    public function run(): void
    {
        // Reuse existing org or create one
        $org = DB::table('organizations')->first();
        if (! $org) {
            $orgId = DB::table('organizations')->insertGetId([
                'name'          => 'Evante Property',
                'name_th'       => 'อีวานเต้ พร็อพเพอร์ตี้',
                'slug'          => 'evante',
                'primary_color' => '#2A8B92',
                'is_active'     => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        } else {
            $orgId = $org->id;
        }

        // Locations
        $loc1 = DB::table('locations')->insertGetId([
            'project_name'    => 'Evante Condo Thonglor',
            'province'        => 'กรุงเทพมหานคร',
            'district'        => 'วัฒนา',
            'subdistrict'     => 'คลองเตยเหนือ',
            'address'         => 'สุขุมวิท 55 (ทองหล่อ) กรุงเทพฯ',
            'organization_id' => $orgId,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        $loc2 = DB::table('locations')->insertGetId([
            'project_name'    => 'Evante Villa Sukhumvit',
            'province'        => 'กรุงเทพมหานคร',
            'district'        => 'วัฒนา',
            'subdistrict'     => 'คลองตัน',
            'address'         => 'สุขุมวิท 71 (พร้อมพงษ์) กรุงเทพฯ',
            'organization_id' => $orgId,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // Projects
        $proj1 = DB::table('projects')->insertGetId([
            'location_id'     => $loc1,
            'name'            => 'Evante Condo Thonglor',
            'total_floors'    => 32,
            'total_units'     => 280,
            'organization_id' => $orgId,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        $proj2 = DB::table('projects')->insertGetId([
            'location_id'     => $loc2,
            'name'            => 'Evante Villa Sukhumvit',
            'total_floors'    => 8,
            'total_units'     => 120,
            'organization_id' => $orgId,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // Listings (rooms)
        $listings = [
            [
                'location_id'     => $loc1,
                'project_id'      => $proj1,
                'project_name'    => 'Evante Condo Thonglor',
                'building'        => 'A',
                'unit_code'       => 'ECT-1001',
                'room_number'     => '1001',
                'floor'           => 10,
                'unit_type'       => 'Studio',
                'bedrooms'        => 0,
                'area'            => 28.50,
                'price_per_room'  => 2850000,
                'price_per_sqm'   => 100000,
                'status'          => 'available',
                'floor_plan_image'  => 'https://picsum.photos/seed/ect1001fp/600/400',
                'room_layout_image' => 'https://picsum.photos/seed/ect1001rl/600/400',
            ],
            [
                'location_id'     => $loc1,
                'project_id'      => $proj1,
                'project_name'    => 'Evante Condo Thonglor',
                'building'        => 'A',
                'unit_code'       => 'ECT-2005',
                'room_number'     => '2005',
                'floor'           => 20,
                'unit_type'       => '1 Bedroom',
                'bedrooms'        => 1,
                'area'            => 42.00,
                'price_per_room'  => 5460000,
                'price_per_sqm'   => 130000,
                'status'          => 'available',
                'floor_plan_image'  => 'https://picsum.photos/seed/ect2005fp/600/400',
                'room_layout_image' => 'https://picsum.photos/seed/ect2005rl/600/400',
            ],
            [
                'location_id'     => $loc1,
                'project_id'      => $proj1,
                'project_name'    => 'Evante Condo Thonglor',
                'building'        => 'B',
                'unit_code'       => 'ECT-3012',
                'room_number'     => '3012',
                'floor'           => 30,
                'unit_type'       => '2 Bedroom',
                'bedrooms'        => 2,
                'area'            => 68.00,
                'price_per_room'  => 10200000,
                'price_per_sqm'   => 150000,
                'status'          => 'available',
                'floor_plan_image'  => 'https://picsum.photos/seed/ect3012fp/600/400',
                'room_layout_image' => 'https://picsum.photos/seed/ect3012rl/600/400',
            ],
            [
                'location_id'     => $loc1,
                'project_id'      => $proj1,
                'project_name'    => 'Evante Condo Thonglor',
                'building'        => 'A',
                'unit_code'       => 'ECT-1508',
                'room_number'     => '1508',
                'floor'           => 15,
                'unit_type'       => '1 Bedroom',
                'bedrooms'        => 1,
                'area'            => 38.50,
                'price_per_room'  => 4620000,
                'price_per_sqm'   => 120000,
                'status'          => 'reserved',
                'floor_plan_image'  => 'https://picsum.photos/seed/ect1508fp/600/400',
                'room_layout_image' => 'https://picsum.photos/seed/ect1508rl/600/400',
            ],
            [
                'location_id'     => $loc2,
                'project_id'      => $proj2,
                'project_name'    => 'Evante Villa Sukhumvit',
                'building'        => 'Main',
                'unit_code'       => 'EVS-201',
                'room_number'     => '201',
                'floor'           => 2,
                'unit_type'       => '2 Bedroom',
                'bedrooms'        => 2,
                'area'            => 75.00,
                'price_per_room'  => 9750000,
                'price_per_sqm'   => 130000,
                'status'          => 'available',
                'floor_plan_image'  => 'https://picsum.photos/seed/evs201fp/600/400',
                'room_layout_image' => 'https://picsum.photos/seed/evs201rl/600/400',
            ],
            [
                'location_id'     => $loc2,
                'project_id'      => $proj2,
                'project_name'    => 'Evante Villa Sukhumvit',
                'building'        => 'Main',
                'unit_code'       => 'EVS-305',
                'room_number'     => '305',
                'floor'           => 3,
                'unit_type'       => '3 Bedroom',
                'bedrooms'        => 3,
                'area'            => 110.00,
                'price_per_room'  => 16500000,
                'price_per_sqm'   => 150000,
                'status'          => 'available',
                'floor_plan_image'  => 'https://picsum.photos/seed/evs305fp/600/400',
                'room_layout_image' => 'https://picsum.photos/seed/evs305rl/600/400',
            ],
            [
                'location_id'     => $loc1,
                'project_id'      => $proj1,
                'project_name'    => 'Evante Condo Thonglor',
                'building'        => 'B',
                'unit_code'       => 'ECT-0408',
                'room_number'     => '0408',
                'floor'           => 4,
                'unit_type'       => 'Studio',
                'bedrooms'        => 0,
                'area'            => 26.00,
                'price_per_room'  => 2340000,
                'price_per_sqm'   => 90000,
                'status'          => 'transferred',
                'floor_plan_image'  => 'https://picsum.photos/seed/ect0408fp/600/400',
                'room_layout_image' => 'https://picsum.photos/seed/ect0408rl/600/400',
            ],
        ];

        foreach ($listings as $listing) {
            DB::table('listings')->insert(array_merge($listing, [
                'organization_id' => $orgId,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]));
        }

        $this->command->info('Sample property data seeded:');
        $this->command->info('  - 2 projects (Evante Condo Thonglor, Evante Villa Sukhumvit)');
        $this->command->info('  - ' . count($listings) . ' listings (5 available, 1 reserved, 1 transferred)');
    }
}
