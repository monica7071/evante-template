<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('reservations', 'buyer_first_name')) {
                $table->string('buyer_first_name')->nullable()->after('listing_id');
            }
            if (!Schema::hasColumn('reservations', 'buyer_last_name')) {
                $table->string('buyer_last_name')->nullable()->after('buyer_first_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (Schema::hasColumn('reservations', 'buyer_first_name')) {
                $table->dropColumn('buyer_first_name');
            }
            if (Schema::hasColumn('reservations', 'buyer_last_name')) {
                $table->dropColumn('buyer_last_name');
            }
        });
    }
};
