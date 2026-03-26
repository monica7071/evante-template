<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE `pdf_templates` MODIFY `contract_type` ENUM('reservation','purchase','quotation') NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE `pdf_templates` MODIFY `contract_type` ENUM('reservation','purchase') NOT NULL");
        }
    }
};
