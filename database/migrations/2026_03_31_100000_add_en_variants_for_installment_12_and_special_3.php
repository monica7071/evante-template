<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->decimal('installment_12_terms_en', 15, 2)->nullable()->after('installment_12_terms');
            $table->decimal('special_installment_3_terms_en', 15, 2)->nullable()->after('special_installment_3_terms');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn(['installment_12_terms_en', 'special_installment_3_terms_en']);
        });
    }
};
