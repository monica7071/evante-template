<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables that need organization_id added.
     */
    private array $tables = [
        'users',
        'projects',
        'listings',
        'sales',
        'reservations',
        'contracts',
        'sale_purchase_agreements',
        'sale_purchase_agreement_installments',
        'employees',
        'teams',
        'positions',
        'profile_fields',
        'locations',
        'listing_images',
        'project_images',
        'status_histories',
        'finance_snapshots',
        'pdf_templates',
        'template_mappings',
    ];

    public function up(): void
    {
        // Phase 1: Add nullable organization_id to all tables
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'organization_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('organization_id')->nullable()->after('id');
                    $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
                    $table->index('organization_id');
                });
            }
        }

        // Phase 2: Ensure default organization exists, then backfill
        $orgExists = DB::table('organizations')->where('id', 1)->exists();
        if (!$orgExists) {
            // Pull data from companies table if available
            $company = DB::table('companies')->first();
            DB::table('organizations')->insert([
                'id' => 1,
                'name' => $company->name ?? 'Default Organization',
                'name_th' => $company->name_th ?? null,
                'slug' => 'default',
                'logo' => $company->logo ?? null,
                'primary_color' => '#2A8B92',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Phase 3: Backfill all existing rows with organization_id = 1
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                DB::table($tableName)->whereNull('organization_id')->update(['organization_id' => 1]);
            }
        }

        // Phase 4: Make organization_id NOT NULL (except users — handled in next migration)
        foreach ($this->tables as $tableName) {
            if ($tableName === 'users') {
                continue; // users.organization_id stays nullable for super_admin
            }
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'organization_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('organization_id')->nullable(false)->change();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->tables) as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'organization_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropForeign(['organization_id']);
                    $table->dropIndex(['organization_id']);
                    $table->dropColumn('organization_id');
                });
            }
        }
    }
};
