<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultRolesSeeder extends Seeder
{
    public function run(): void
    {
        $orgIds = DB::table('organizations')->pluck('id');
        foreach ($orgIds as $orgId) {
            static::seedForOrganization($orgId);
        }
    }

    public static function seedForOrganization(int $orgId): void
    {
        $now = now();
        $allPermissionIds = DB::table('permissions')->pluck('id')->toArray();

        $defaults = [
            [
                'name' => 'admin',
                'display_name' => 'Admin',
                'description' => 'Full access to all features',
                'is_default' => true,
                'permissions' => $allPermissionIds,
            ],
            [
                'name' => 'leader',
                'display_name' => 'Leader',
                'description' => 'Team leader with extended access',
                'is_default' => true,
                'permissions' => DB::table('permissions')
                    ->whereIn('module', [
                        'dashboard', 'buy_sale', 'listing_locations', 'listing_projects',
                        'listing_units', 'floor_plan', 'report', 'finance', 'activity',
                        'contracts', 'employee_list', 'employee_teams',
                    ])
                    ->pluck('id')->toArray(),
            ],
            [
                'name' => 'agent',
                'display_name' => 'Agent',
                'description' => 'Sales agent with basic access',
                'is_default' => true,
                'permissions' => DB::table('permissions')
                    ->whereIn('name', [
                        'dashboard.view', 'buy_sale.view', 'buy_sale.create', 'buy_sale.edit',
                        'buy_sale.remarks', 'listing_locations.view', 'listing_projects.view',
                        'listing_units.view', 'floor_plan.view', 'activity.view',
                        'contracts.view', 'contracts.download',
                    ])
                    ->pluck('id')->toArray(),
            ],
            [
                'name' => 'sales_manager',
                'display_name' => 'Sales Manager',
                'description' => 'Sales manager with deal slip approval',
                'is_default' => true,
                'permissions' => DB::table('permissions')
                    ->whereIn('module', [
                        'dashboard', 'buy_sale', 'listing_locations', 'listing_projects',
                        'listing_units', 'floor_plan', 'report', 'activity', 'contracts',
                    ])
                    ->pluck('id')->toArray(),
            ],
        ];

        foreach ($defaults as $role) {
            $existing = DB::table('roles')
                ->where('organization_id', $orgId)
                ->where('name', $role['name'])
                ->first();

            if ($existing) {
                continue;
            }

            $roleId = DB::table('roles')->insertGetId([
                'organization_id' => $orgId,
                'name' => $role['name'],
                'display_name' => $role['display_name'],
                'description' => $role['description'],
                'is_default' => $role['is_default'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $pivotData = array_map(fn($pid) => [
                'role_id' => $roleId,
                'permission_id' => $pid,
            ], $role['permissions']);

            if (!empty($pivotData)) {
                DB::table('role_permission')->insert($pivotData);
            }
        }
    }
}
