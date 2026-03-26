<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add default payment type to projects
        Schema::table('projects', function (Blueprint $table) {
            $table->enum('default_transfer_payment_type', ['bank_loan', 'cash'])->default('bank_loan')->after('total_units');
        });

        // Add new bank-loan detail fields to sale_transfers
        Schema::table('sale_transfers', function (Blueprint $table) {
            $table->decimal('actual_loan_amount', 14, 2)->nullable()->after('loan_amount');
            $table->decimal('customer_extra_payment', 14, 2)->nullable()->after('actual_loan_amount');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('default_transfer_payment_type');
        });

        Schema::table('sale_transfers', function (Blueprint $table) {
            $table->dropColumn(['actual_loan_amount', 'customer_extra_payment']);
        });
    }
};
