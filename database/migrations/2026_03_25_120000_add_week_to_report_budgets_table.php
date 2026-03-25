<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_budgets', function (Blueprint $table) {
            $table->unsignedTinyInteger('week')->default(1)->after('month');

            // Drop old unique and add new one with week
            $table->dropUnique(['year', 'month']);
            $table->unique(['year', 'month', 'week']);
        });
    }

    public function down(): void
    {
        Schema::table('report_budgets', function (Blueprint $table) {
            $table->dropUnique(['year', 'month', 'week']);
            $table->unique(['year', 'month']);
            $table->dropColumn('week');
        });
    }
};
