<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('buyer_id_type', 20)->default('id_card')->after('buyer_full_name');
            $table->string('buyer_nationality')->nullable()->after('buyer_id_number');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['buyer_id_type', 'buyer_nationality']);
        });
    }
};
