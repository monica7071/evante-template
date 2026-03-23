<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create a default plan for backfilling existing organizations
        $defaultPlanId = DB::table('plans')->insertGetId([
            'name'          => 'Default',
            'slug'          => 'default',
            'storage_limit' => 10240,
            'price'         => 0,
            'description'   => 'Default plan for existing organizations',
            'is_active'     => true,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        Schema::table('organizations', function (Blueprint $table) {
            $table->unsignedBigInteger('plan_id')->nullable()->after('id');
            $table->foreign('plan_id')->references('id')->on('plans')->nullOnDelete();
        });

        // Backfill existing organizations with the default plan
        DB::table('organizations')->whereNull('plan_id')->update(['plan_id' => $defaultPlanId]);
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumn('plan_id');
        });

        DB::table('plans')->where('slug', 'default')->delete();
    }
};
