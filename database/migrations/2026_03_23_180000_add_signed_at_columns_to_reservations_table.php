<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->timestamp('buyer_signed_at')->nullable()->after('buyer_signature_path');
            $table->timestamp('witness_one_signed_at')->nullable()->after('witness_one_signature_path');
            $table->timestamp('witness_two_signed_at')->nullable()->after('witness_two_signature_path');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['buyer_signed_at', 'witness_one_signed_at', 'witness_two_signed_at']);
        });
    }
};
