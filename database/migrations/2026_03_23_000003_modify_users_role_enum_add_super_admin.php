<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MariaDB/MySQL: alter the enum to include super_admin
        if (DB::getDriverName() !== 'sqlite') { DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin','leader','agent') NOT NULL DEFAULT 'agent'"); }
    }

    public function down(): void
    {
        // Revert: remove super_admin from enum (any super_admin rows would need to be updated first)
        if (DB::getDriverName() !== 'sqlite') { DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','leader','agent') NOT NULL DEFAULT 'agent'"); }
    }
};
