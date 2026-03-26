<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->decimal('installment_15_terms_en', 15, 2)->nullable()->after('installment_15_terms');
            $table->decimal('transfer_amount_en', 15, 2)->nullable()->after('transfer_amount');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn(['installment_15_terms_en', 'transfer_amount_en']);
        });
    }
};
