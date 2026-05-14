<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('listings', 'project_name')) {
            Schema::table('listings', function (Blueprint $table) {
                $table->string('project_name')->nullable()->after('building');
            });
        }

        if (DB::getDriverName() === 'sqlite') {
            $listings = DB::table('listings')->join('locations', 'listings.location_id', '=', 'locations.id')
                ->select('listings.id', 'locations.project_name')->get();
            foreach ($listings as $listing) {
                DB::table('listings')->where('id', $listing->id)->update(['project_name' => $listing->project_name]);
            }
        } else {
            DB::table('listings')
                ->join('locations', 'listings.location_id', '=', 'locations.id')
                ->update(['listings.project_name' => DB::raw('locations.project_name')]);
        }
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn('project_name');
        });
    }
};
