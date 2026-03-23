<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('sale_purchase_agreement_installments');

        Schema::create('sale_purchase_agreement_installments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_purchase_agreement_id');
            $table->foreign('sale_purchase_agreement_id', 'spa_installments_agreement_fk')
                ->references('id')
                ->on('sale_purchase_agreements')
                ->cascadeOnDelete();
            $table->unsignedInteger('sequence')->default(1);
            $table->decimal('amount_number', 15, 2)->nullable();
            $table->string('amount_text')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_purchase_agreement_installments');
    }
};
