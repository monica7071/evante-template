<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        // Step 1: Create the default organization (idempotent)
        $company = DB::table('companies')->first();

        DB::table('organizations')->updateOrInsert(
            ['id' => 1],
            [
                'name' => $company->name ?? 'Default Organization',
                'name_th' => $company->name_th ?? null,
                'slug' => 'default',
                'logo' => $company->logo ?? null,
                'primary_color' => '#2A8B92',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Step 2: Assign all users without an organization to org 1
        // (skip any super_admin users that should have NULL organization_id)
        DB::table('users')
            ->whereNull('organization_id')
            ->where('role', '!=', 'super_admin')
            ->update(['organization_id' => 1]);

        // Step 3: Backfill organization_id for all other tenant tables
        $tables = [
            'projects', 'listings', 'sales', 'reservations', 'contracts',
            'sale_purchase_agreements', 'sale_purchase_agreement_installments',
            'employees', 'teams', 'positions', 'profile_fields', 'locations',
            'listing_images', 'project_images', 'status_histories',
            'finance_snapshots', 'pdf_templates', 'template_mappings',
        ];

        foreach ($tables as $table) {
            if (\Illuminate\Support\Facades\Schema::hasTable($table)
                && \Illuminate\Support\Facades\Schema::hasColumn($table, 'organization_id')) {
                DB::table($table)
                    ->whereNull('organization_id')
                    ->update(['organization_id' => 1]);
            }
        }
    }
}
