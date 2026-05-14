<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') { DB::statement("ALTER TABLE listings MODIFY bedrooms VARCHAR(255) NULL"); }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') { DB::statement("ALTER TABLE listings MODIFY bedrooms INT NULL"); }
    }
};
