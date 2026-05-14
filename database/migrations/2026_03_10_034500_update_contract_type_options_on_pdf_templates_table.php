<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $types = [
        'quotation',
        'reservation_agreement',
        'addendum_to_agreement',
        'agreement_to_sell_and_purchase',
        'contract_amendment',
    ];

    public function up(): void
    {
        $enumList = "'" . implode("','", $this->types) . "'";
        if (DB::getDriverName() !== 'sqlite') { DB::statement("ALTER TABLE `pdf_templates` MODIFY `contract_type` ENUM({$enumList}) NOT NULL"); }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') { DB::statement("ALTER TABLE `pdf_templates` MODIFY `contract_type` ENUM('reservation','purchase','quotation') NOT NULL"); }
    }
};
