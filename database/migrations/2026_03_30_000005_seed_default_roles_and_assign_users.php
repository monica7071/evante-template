<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Database\Seeders\DefaultRolesSeeder;

return new class extends Migration
{
    public function up(): void
    {
        // Seed permissions first
        $this->seedPermissions();

        // Create default roles for each existing organization
        $orgIds = DB::table('organizations')->pluck('id');
        foreach ($orgIds as $orgId) {
            DefaultRolesSeeder::seedForOrganization($orgId);
        }

        // Assign role_id to existing users based on their ENUM role
        $users = DB::table('users')
            ->whereNotNull('organization_id')
            ->where('role', '!=', 'super_admin')
            ->get(['id', 'organization_id', 'role']);

        foreach ($users as $user) {
            $roleId = DB::table('roles')
                ->where('organization_id', $user->organization_id)
                ->where('name', $user->role)
                ->value('id');

            if ($roleId) {
                DB::table('users')->where('id', $user->id)->update(['role_id' => $roleId]);
            }
        }
    }

    public function down(): void
    {
        DB::table('users')->update(['role_id' => null]);
    }

    private function seedPermissions(): void
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

            DB::table('permissions')->insertOrIgnore([
                'module' => $p['module'],
                'action' => $p['action'],
                'name' => $name,
                'display_name' => $displayName,
                'group' => $p['group'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
};
