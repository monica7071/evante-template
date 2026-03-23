<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // {"1": "project-assets/1/floor/1.jpg", "2": "project-assets/1/floor/2.jpg"}
            $table->json('floor_plan_images')->nullable()->after('total_units');
            // {"C": "project-assets/1/layout/C.jpg", "H": "project-assets/1/layout/H.jpg"}
            $table->json('room_layout_images')->nullable()->after('floor_plan_images');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['floor_plan_images', 'room_layout_images']);
        });
    }
};
