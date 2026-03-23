<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->string('buyer_first_name');
            $table->string('buyer_last_name');
            $table->string('buyer_id_number');
            $table->text('buyer_address')->nullable();
            $table->string('buyer_phone')->nullable();
            $table->string('buyer_email')->nullable();
            $table->date('reservation_date')->nullable();
            $table->decimal('reservation_amount', 15, 2)->nullable();
            $table->decimal('amount_paid_number', 15, 2)->nullable();
            $table->string('amount_paid_text')->nullable();
            $table->date('contract_start_date')->nullable();
            $table->string('buyer_signature_name')->nullable();
            $table->string('buyer_signature_path')->nullable();
            $table->string('seller_name')->nullable();
            $table->string('seller_signature_path')->nullable();
            $table->string('witness_one_name')->nullable();
            $table->string('witness_one_signature_path')->nullable();
            $table->string('witness_two_name')->nullable();
            $table->string('witness_two_signature_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
