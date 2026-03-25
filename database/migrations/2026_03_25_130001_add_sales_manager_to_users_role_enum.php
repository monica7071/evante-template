<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin','leader','agent','sales_manager') NOT NULL DEFAULT 'agent'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin','leader','agent') NOT NULL DEFAULT 'agent'");
    }
};
