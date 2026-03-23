<?php

namespace App\Models;

use App\Scopes\OrganizationScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalePurchaseAgreement extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope(new OrganizationScope());

        static::creating(function ($model) {
            if (auth()->check() && !auth()->user()->isSuperAdmin() && empty($model->organization_id)) {
                $model->organization_id = auth()->user()->organization_id;
            }
        });
    }

    protected $fillable = [
        'sale_id',
        'listing_id',
        'contract_number',
        'contract_date',
        'buyer_full_name',
        'buyer_phone',
        'house_no',
        'village_no',
        'street',
        'province',
        'district',
        'subdistrict',
        'postal_code',
        'project_name',
        'floor',
        'room_number',
        'unit_type',
        'quantity',
        'price_per_sqm_number',
        'area_sqm',
        'total_price_number',
        'total_price_text',
        'adjustment_number',
        'adjustment_text',
        'deposit_number',
        'deposit_text',
        'deposit_date',
        'contract_payment_number',
        'contract_payment_text',
        'contract_payment_date',
        'installment_total_number',
        'installment_total_text',
        'remaining_number',
        'remaining_text',
        'total_term',
        'is_bank_loan',
        'is_cash_transfer',
        'seller_name',
        'buyer_signature_name',
        'witness_one_name',
        'witness_two_name',
    ];

    protected $casts = [
        'contract_date' => 'date',
        'deposit_date' => 'date',
        'contract_payment_date' => 'date',
        'price_per_sqm_number' => 'decimal:2',
        'area_sqm' => 'decimal:2',
        'total_price_number' => 'decimal:2',
        'adjustment_number' => 'decimal:2',
        'deposit_number' => 'decimal:2',
        'contract_payment_number' => 'decimal:2',
        'installment_total_number' => 'decimal:2',
        'remaining_number' => 'decimal:2',
        'is_bank_loan' => 'boolean',
        'is_cash_transfer' => 'boolean',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(SalePurchaseAgreementInstallment::class);
    }
}
