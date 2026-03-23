<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('pdf_templates')->cascadeOnDelete();
            $table->string('db_field');
            $table->enum('field_type', ['text', 'number', 'date', 'image', 'checkbox']);
            $table->integer('x_position');
            $table->integer('y_position');
            $table->integer('page_number')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_mappings');
    }
};
