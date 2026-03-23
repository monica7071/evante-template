<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_purchase_agreements', function (Blueprint $table) {
            $table->boolean('is_bank_loan')->default(0)->after('total_term');
            $table->boolean('is_cash_transfer')->default(0)->after('is_bank_loan');
        });
    }

    public function down(): void
    {
        Schema::table('sale_purchase_agreements', function (Blueprint $table) {
            $table->dropColumn(['is_bank_loan', 'is_cash_transfer']);
        });
    }
};
