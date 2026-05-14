<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['group' => 'Dashboard', 'module' => 'dashboard', 'action' => 'view'],

            ['group' => 'Buy/Sale', 'module' => 'buy_sale', 'action' => 'view'],
            ['group' => 'Buy/Sale', 'module' => 'buy_sale', 'action' => 'create'],
            ['group' => 'Buy/Sale', 'module' => 'buy_sale', 'action' => 'edit'],
            ['group' => 'Buy/Sale', 'module' => 'buy_sale', 'action' => 'advance'],
            ['group' => 'Buy/Sale', 'module' => 'buy_sale', 'action' => 'cancel'],
            ['group' => 'Buy/Sale', 'module' => 'buy_sale', 'action' => 'deal_slip'],
            ['group' => 'Buy/Sale', 'module' => 'buy_sale', 'action' => 'remarks'],

            ['group' => 'Listing Setting', 'module' => 'listing_locations', 'action' => 'view'],
            ['group' => 'Listing Setting', 'module' => 'listing_locations', 'action' => 'create'],
            ['group' => 'Listing Setting', 'module' => 'listing_locations', 'action' => 'edit'],
            ['group' => 'Listing Setting', 'module' => 'listing_locations', 'action' => 'delete'],

            ['group' => 'Listing Setting', 'module' => 'listing_projects', 'action' => 'view'],
            ['group' => 'Listing Setting', 'module' => 'listing_projects', 'action' => 'create'],
            ['group' => 'Listing Setting', 'module' => 'listing_projects', 'action' => 'edit'],
            ['group' => 'Listing Setting', 'module' => 'listing_projects', 'action' => 'delete'],

            ['group' => 'Listing Setting', 'module' => 'listing_units', 'action' => 'view'],
            ['group' => 'Listing Setting', 'module' => 'listing_units', 'action' => 'create'],
            ['group' => 'Listing Setting', 'module' => 'listing_units', 'action' => 'edit'],
            ['group' => 'Listing Setting', 'module' => 'listing_units', 'action' => 'delete'],
            ['group' => 'Listing Setting', 'module' => 'listing_units', 'action' => 'import'],

            ['group' => 'Floor Plan', 'module' => 'floor_plan', 'action' => 'view'],

            ['group' => 'Report', 'module' => 'report', 'action' => 'view'],
            ['group' => 'Report', 'module' => 'report', 'action' => 'export'],
            ['group' => 'Report', 'module' => 'report', 'action' => 'manage_budget'],

            ['group' => 'Finance', 'module' => 'finance', 'action' => 'view'],
            ['group' => 'Finance', 'module' => 'finance', 'action' => 'transfer'],

            ['group' => 'Activity', 'module' => 'activity', 'action' => 'view'],

            ['group' => 'Templates', 'module' => 'templates', 'action' => 'view'],
            ['group' => 'Templates', 'module' => 'templates', 'action' => 'upload'],
            ['group' => 'Templates', 'module' => 'templates', 'action' => 'manage_mappings'],
            ['group' => 'Templates', 'module' => 'templates', 'action' => 'delete'],

            ['group' => 'Employee', 'module' => 'employee_company', 'action' => 'view'],
            ['group' => 'Employee', 'module' => 'employee_company', 'action' => 'edit'],

            ['group' => 'Employee', 'module' => 'employee_profile_fields', 'action' => 'view'],
            ['group' => 'Employee', 'module' => 'employee_profile_fields', 'action' => 'manage'],

            ['group' => 'Employee', 'module' => 'employee_list', 'action' => 'view'],
            ['group' => 'Employee', 'module' => 'employee_list', 'action' => 'create'],
            ['group' => 'Employee', 'module' => 'employee_list', 'action' => 'edit'],
            ['group' => 'Employee', 'module' => 'employee_list', 'action' => 'delete'],

            ['group' => 'Employee', 'module' => 'employee_positions', 'action' => 'view'],
            ['group' => 'Employee', 'module' => 'employee_positions', 'action' => 'manage'],

            ['group' => 'Employee', 'module' => 'employee_teams', 'action' => 'view'],
            ['group' => 'Employee', 'module' => 'employee_teams', 'action' => 'manage'],

            ['group' => 'Contracts', 'module' => 'contracts', 'action' => 'view'],
            ['group' => 'Contracts', 'module' => 'contracts', 'action' => 'create'],
            ['group' => 'Contracts', 'module' => 'contracts', 'action' => 'download'],

            ['group' => 'Admin Chat', 'module' => 'admin_chat', 'action' => 'view'],
            ['group' => 'Admin Chat', 'module' => 'admin_chat', 'action' => 'manage'],

            ['group' => 'Settings', 'module' => 'roles', 'action' => 'view'],
            ['group' => 'Settings', 'module' => 'roles', 'action' => 'manage'],
        ];

        $now = now();
        foreach ($permissions as $p) {
            $name = $p['module'] . '.' . $p['action'];
            $displayName = ucfirst(str_replace('_', ' ', $p['module'])) . ' - ' . ucfirst(str_replace('_', ' ', $p['action']));

            DB::table('permissions')->updateOrInsert(
                ['name' => $name],
                [
                    'module' => $p['module'],
                    'action' => $p['action'],
                    'display_name' => $displayName,
                    'group' => $p['group'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
