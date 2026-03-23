<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_purchase_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->string('contract_number')->nullable();
            $table->date('contract_date')->nullable();
            $table->string('buyer_full_name');
            $table->string('buyer_phone', 50);
            $table->string('house_no');
            $table->string('village_no')->nullable();
            $table->string('street')->nullable();
            $table->string('province');
            $table->string('district');
            $table->string('subdistrict');
            $table->string('project_name');
            $table->string('floor')->nullable();
            $table->string('room_number');
            $table->string('unit_type');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('price_per_sqm_number', 15, 2)->nullable();
            $table->decimal('area_sqm', 10, 2)->nullable();
            $table->decimal('total_price_number', 15, 2);
            $table->string('total_price_text');
            $table->decimal('adjustment_number', 15, 2)->default(0);
            $table->string('adjustment_text');
            $table->decimal('deposit_number', 15, 2);
            $table->string('deposit_text');
            $table->date('deposit_date')->nullable();
            $table->decimal('contract_payment_number', 15, 2);
            $table->string('contract_payment_text');
            $table->date('contract_payment_date')->nullable();
            $table->decimal('installment_total_number', 15, 2)->nullable();
            $table->string('installment_total_text')->nullable();
            $table->decimal('remaining_number', 15, 2)->nullable();
            $table->string('remaining_text')->nullable();
            $table->json('installments')->nullable();
            $table->string('seller_name');
            $table->string('buyer_signature_name');
            $table->string('witness_one_name');
            $table->string('witness_two_name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_purchase_agreements');
    }
};
