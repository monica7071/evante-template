<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('template_mappings', function (Blueprint $table) {
            $table->decimal('img_width', 8, 2)->default(50)->after('page_number');
        });
    }

    public function down(): void
    {
        Schema::table('template_mappings', function (Blueprint $table) {
            $table->dropColumn('img_width');
        });
    }
};
