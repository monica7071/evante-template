<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') { DB::statement("ALTER TABLE sales MODIFY COLUMN status ENUM('available','appointment','reserved','contract','installment','transferred') DEFAULT 'available'"); }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') { DB::statement("ALTER TABLE sales MODIFY COLUMN status ENUM('available','reserved','contract','installment','transferred') DEFAULT 'available'"); }
    }
};
