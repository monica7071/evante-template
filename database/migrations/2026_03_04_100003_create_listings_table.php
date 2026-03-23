<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->integer('floor')->nullable();
            $table->string('room_number');
            $table->string('unit_code')->nullable();
            $table->integer('bedrooms')->nullable();
            $table->decimal('area', 8, 2)->nullable();
            $table->decimal('price_per_room', 12, 2)->nullable();
            $table->decimal('price_per_sqm', 12, 2)->nullable();
            $table->string('unit_type')->nullable();
            $table->enum('status', ['vacant', 'reserved', 'contract', 'installment', 'transfer'])->default('vacant');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
