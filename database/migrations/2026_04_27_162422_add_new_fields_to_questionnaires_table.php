<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questionnaires', function (Blueprint $table) {
            // Replace first_name + last_name with full_name
            $table->string('full_name')->nullable()->after('agent_id');

            // Gender
            $table->string('gender')->nullable()->after('email');
            $table->string('gender_other')->nullable()->after('gender');

            // Address fields
            $table->string('address_house_no')->nullable()->after('gender_other');
            $table->string('address_street')->nullable()->after('address_house_no');
            $table->string('address_subdistrict')->nullable()->after('address_street');
            $table->string('address_district')->nullable()->after('address_subdistrict');
            $table->string('address_province')->nullable()->after('address_district');
            $table->string('address_postal_code', 10)->nullable()->after('address_province');
            $table->string('address_country')->nullable()->after('address_postal_code');

            // Age range (radio) replaces integer age
            $table->string('age_range')->nullable()->after('address_country');

            // Marital status
            $table->string('marital_status')->nullable()->after('age_range');
            $table->unsignedTinyInteger('children_count')->nullable()->after('marital_status');

            // Household income
            $table->string('household_income')->nullable()->after('children_count');

            // Visit reasons (multiple choice - stored as JSON)
            $table->json('visit_reasons')->nullable()->after('source_other');
            $table->string('visit_reasons_other')->nullable()->after('visit_reasons');

            // Promotions (multiple choice - stored as JSON)
            $table->json('promotions')->nullable()->after('visit_reasons_other');
            $table->string('promotions_other')->nullable()->after('promotions');

            // Budget
            $table->string('budget')->nullable()->after('promotions_other');

            // Purchase purpose
            $table->string('purchase_purpose')->nullable()->after('budget');
            $table->string('purchase_purpose_other')->nullable()->after('purchase_purpose');

            // Finance plan
            $table->string('finance_plan')->nullable()->after('purchase_purpose_other');
            $table->string('finance_plan_other')->nullable()->after('finance_plan');
        });
    }

    public function down(): void
    {
        Schema::table('questionnaires', function (Blueprint $table) {
            $table->dropColumn([
                'full_name',
                'gender',
                'gender_other',
                'address_house_no',
                'address_street',
                'address_subdistrict',
                'address_district',
                'address_province',
                'address_postal_code',
                'address_country',
                'age_range',
                'marital_status',
                'children_count',
                'household_income',
                'visit_reasons',
                'visit_reasons_other',
                'promotions',
                'promotions_other',
                'budget',
                'purchase_purpose',
                'purchase_purpose_other',
                'finance_plan',
                'finance_plan_other',
            ]);
        });
    }
};
