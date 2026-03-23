<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_fields', function (Blueprint $table) {
            $table->id();
            $table->string('field_name');
            $table->string('field_label');
            $table->string('field_label_th')->nullable();
            $table->enum('field_type', ['text', 'number', 'date', 'select', 'file', 'textarea'])->default('text');
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('options')->nullable();
            $table->enum('field_group', ['personal', 'contact', 'document', 'bank', 'other'])->default('personal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_fields');
    }
};
