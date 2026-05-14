<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First alter enum to include all values (old + new)
        if (DB::getDriverName() !== 'sqlite') { DB::statement("ALTER TABLE sales MODIFY COLUMN status ENUM('vacant','available','reserved','contract','installment','transfer','transferred') DEFAULT 'available'"); }

        // Update data
        DB::table('sales')->where('status', 'vacant')->update(['status' => 'available']);
        DB::table('sales')->where('status', 'transfer')->update(['status' => 'transferred']);

        // Remove old enum values
        if (DB::getDriverName() !== 'sqlite') { DB::statement("ALTER TABLE sales MODIFY COLUMN status ENUM('available','reserved','contract','installment','transferred') DEFAULT 'available'"); }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') { DB::statement("ALTER TABLE sales MODIFY COLUMN status ENUM('vacant','available','reserved','contract','installment','transfer','transferred') DEFAULT 'vacant'"); }

        DB::table('sales')->where('status', 'available')->update(['status' => 'vacant']);
        DB::table('sales')->where('status', 'transferred')->update(['status' => 'transfer']);

        if (DB::getDriverName() !== 'sqlite') { DB::statement("ALTER TABLE sales MODIFY COLUMN status ENUM('vacant','reserved','contract','installment','transfer') DEFAULT 'vacant'"); }
    }
};
