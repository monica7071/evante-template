<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'appointment_date',
                'appointment_time',
                'appointment_name',
                'appointment_phone',
                'remark_appointment',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->date('appointment_date')->nullable();
            $table->time('appointment_time')->nullable();
            $table->string('appointment_name')->nullable();
            $table->string('appointment_phone')->nullable();
            $table->text('remark_appointment')->nullable();
        });
    }
};
