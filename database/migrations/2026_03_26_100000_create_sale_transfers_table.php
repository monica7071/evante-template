<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->unique()->constrained()->cascadeOnDelete();
            $table->enum('transfer_payment_type', ['bank_loan', 'cash'])->default('cash');
            $table->enum('transfer_readiness', ['on_process', 'approved', 'not_ready'])->default('not_ready');
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->decimal('loan_amount', 14, 2)->nullable();
            $table->timestamp('bank_approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_transfers');
    }
};
