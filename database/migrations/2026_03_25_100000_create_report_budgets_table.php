<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_budgets', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');                          // 1-12
            $table->unsignedTinyInteger('week');                           // 1-4
            $table->decimal('budget_marketing_online', 14, 2)->default(0);
            $table->decimal('budget_marketing_offline', 14, 2)->default(0);
            $table->timestamps();

            $table->unique(['year', 'month', 'week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_budgets');
    }
};
