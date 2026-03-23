<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->text('remark_available')->nullable()->after('status');
            $table->text('remark_reserved')->nullable()->after('remark_available');
            $table->text('remark_contract')->nullable()->after('remark_reserved');
            $table->text('remark_installment')->nullable()->after('remark_contract');
            $table->text('remark_transferred')->nullable()->after('remark_installment');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'remark_available',
                'remark_reserved',
                'remark_contract',
                'remark_installment',
                'remark_transferred',
            ]);
        });
    }
};
