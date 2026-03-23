<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['reservation', 'purchase']);
            $table->string('buyer_name');
            $table->string('id_number');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('unit_number');
            $table->decimal('price', 15, 2);
            $table->decimal('deposit', 15, 2)->nullable();
            $table->date('contract_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
