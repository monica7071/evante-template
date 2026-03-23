<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_purchase_agreements', function (Blueprint $table) {
            $table->unsignedInteger('total_term')->nullable()->after('remaining_text');
        });
    }

    public function down(): void
    {
        Schema::table('sale_purchase_agreements', function (Blueprint $table) {
            $table->dropColumn('total_term');
        });
    }
};
