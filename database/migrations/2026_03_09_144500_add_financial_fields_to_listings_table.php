<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->decimal('reservation_deposit', 12, 2)->nullable()->after('price_per_sqm');
            $table->decimal('contract_payment', 12, 2)->nullable()->after('reservation_deposit');
            $table->decimal('installment_15_terms', 12, 2)->nullable()->after('contract_payment');
            $table->decimal('installment_12_terms', 12, 2)->nullable()->after('installment_15_terms');
            $table->decimal('special_installment_3_terms', 12, 2)->nullable()->after('installment_12_terms');
            $table->decimal('transfer_amount', 12, 2)->nullable()->after('special_installment_3_terms');
            $table->decimal('transfer_fee', 12, 2)->nullable()->after('transfer_amount');
            $table->decimal('annual_common_fee', 12, 2)->nullable()->after('transfer_fee');
            $table->decimal('sinking_fund', 12, 2)->nullable()->after('annual_common_fee');
            $table->decimal('utility_fee', 12, 2)->nullable()->after('sinking_fund');
            $table->decimal('total_misc_fee', 12, 2)->nullable()->after('utility_fee');
            $table->string('floor_plan_image')->nullable()->after('total_misc_fee');
            $table->string('room_layout_image')->nullable()->after('floor_plan_image');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn([
                'reservation_deposit',
                'contract_payment',
                'installment_15_terms',
                'installment_12_terms',
                'special_installment_3_terms',
                'transfer_amount',
                'transfer_fee',
                'annual_common_fee',
                'sinking_fund',
                'utility_fee',
                'total_misc_fee',
                'floor_plan_image',
                'room_layout_image',
            ]);
        });
    }
};
