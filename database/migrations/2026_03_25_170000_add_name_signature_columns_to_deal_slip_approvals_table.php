<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('deal_slip_approvals', 'prepared_name')) {
            return;
        }

        Schema::table('deal_slip_approvals', function (Blueprint $table) {
            $table->string('prepared_name')->nullable()->after('prepared_by');
            $table->text('prepared_signature')->nullable()->after('prepared_name');
            $table->string('checked_name')->nullable()->after('checked_by');
            $table->text('checked_signature')->nullable()->after('checked_name');
            $table->string('approved_name')->nullable()->after('approved_by');
            $table->text('approved_signature')->nullable()->after('approved_name');
            if (Schema::hasColumn('deal_slip_approvals', 'signature_data')) {
                $table->dropColumn('signature_data');
            }
        });
    }

    public function down(): void
    {
        Schema::table('deal_slip_approvals', function (Blueprint $table) {
            $table->text('signature_data')->nullable()->after('approved_at');
            $table->dropColumn(['prepared_name', 'prepared_signature', 'checked_name', 'checked_signature', 'approved_name', 'approved_signature']);
        });
    }
};
