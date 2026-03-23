<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_images', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['floor_plan', 'room_layout']);
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('floor')->nullable();
            $table->string('unit_type', 10)->nullable();
            $table->string('image_path');
            $table->timestamps();

            // floor_plan: unique per project+floor
            $table->unique(['project_id', 'floor', 'type']);
            // room_layout: unique per unit_type
            $table->unique(['unit_type', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_images');
    }
};
