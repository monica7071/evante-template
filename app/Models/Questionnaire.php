<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Questionnaire extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'agent_id',
        'full_name',
        'phone',
        'email',
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
        'source',
        'source_other',
        'source_billboard_detail',
        'source_online_media_detail',
        'visit_reasons',
        'visit_reasons_other',
        'promotions',
        'promotions_other',
        'budget',
        'purchase_purpose',
        'purchase_purpose_other',
        'finance_plan',
    ];

    protected $casts = [
        'children_count' => 'integer',
        'visit_reasons' => 'array',
        'promotions' => 'array',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
