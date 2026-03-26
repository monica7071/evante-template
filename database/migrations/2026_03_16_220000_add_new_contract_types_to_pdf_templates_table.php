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
        'overdue_installment_reminder_1',
        'overdue_installment_reminder_2',
        'property_ownership_transfer_appointment',
        'contract_termination_and_forfeiture',
    ];

    public function up(): void
    {
        $enumList = "'" . implode("','", $this->types) . "'";
        if (DB::getDriverName() !== 'sqlite') { DB::statement("ALTER TABLE `pdf_templates` MODIFY `contract_type` ENUM({$enumList}) NOT NULL"); }
    }

    public function down(): void
    {
        $previous = [
            'quotation',
            'reservation_agreement',
            'addendum_to_agreement',
            'agreement_to_sell_and_purchase',
            'contract_amendment',
        ];
        $enumList = "'" . implode("','", $previous) . "'";
        if (DB::getDriverName() !== 'sqlite') { DB::statement("ALTER TABLE `pdf_templates` MODIFY `contract_type` ENUM({$enumList}) NOT NULL"); }
    }
};
