<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Move existing JSON data into project_images rows
        $projects = DB::table('projects')
            ->whereNotNull('floor_plan_images')
            ->orWhereNotNull('room_layout_images')
            ->get(['id', 'floor_plan_images', 'room_layout_images']);

        foreach ($projects as $project) {
            // floor_plan_images: {floor: path}
            if ($project->floor_plan_images) {
                $floorPlans = json_decode($project->floor_plan_images, true) ?? [];
                foreach ($floorPlans as $floor => $path) {
                    DB::table('project_images')->updateOrInsert(
                        ['type' => 'floor_plan', 'project_id' => $project->id, 'floor' => (int) $floor],
                        ['image_path' => $path, 'created_at' => now(), 'updated_at' => now()]
                    );
                }
            }

            // room_layout_images: {unit_type: path} — skip if already inserted (shared)
            if ($project->room_layout_images) {
                $roomLayouts = json_decode($project->room_layout_images, true) ?? [];
                foreach ($roomLayouts as $unitType => $path) {
                    DB::table('project_images')->updateOrInsert(
                        ['type' => 'room_layout', 'unit_type' => strtoupper($unitType)],
                        ['project_id' => null, 'floor' => null, 'image_path' => $path, 'created_at' => now(), 'updated_at' => now()]
                    );
                }
            }
        }

        // Drop the old JSON columns
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['floor_plan_images', 'room_layout_images']);
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->json('floor_plan_images')->nullable()->after('total_units');
            $table->json('room_layout_images')->nullable()->after('floor_plan_images');
        });
    }
};
