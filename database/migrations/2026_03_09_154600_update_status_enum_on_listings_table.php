<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `listings` MODIFY `status` ENUM('vacant','available','reserved','contract','installment','transfer','transferred') NOT NULL DEFAULT 'vacant'");

        DB::table('listings')->where('status', 'vacant')->update(['status' => 'available']);
        DB::table('listings')->where('status', 'transfer')->update(['status' => 'transferred']);

        DB::statement("ALTER TABLE `listings` MODIFY `status` ENUM('available','reserved','contract','installment','transferred') NOT NULL DEFAULT 'available'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `listings` MODIFY `status` ENUM('vacant','available','reserved','contract','installment','transfer','transferred') NOT NULL DEFAULT 'available'");

        DB::table('listings')->where('status', 'available')->update(['status' => 'vacant']);
        DB::table('listings')->where('status', 'transferred')->update(['status' => 'transfer']);

        DB::statement("ALTER TABLE `listings` MODIFY `status` ENUM('vacant','reserved','contract','installment','transfer') NOT NULL DEFAULT 'vacant'");
    }
};
