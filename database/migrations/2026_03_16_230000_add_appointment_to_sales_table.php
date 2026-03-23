<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->date('appointment_date')->nullable()->after('contract_data');
            $table->time('appointment_time')->nullable()->after('appointment_date');
            $table->text('remark_appointment')->nullable()->after('remark_available');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['appointment_date', 'appointment_time', 'remark_appointment']);
        });
    }
};
