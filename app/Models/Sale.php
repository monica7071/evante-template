<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Sale extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'listing_id',
        'user_id',
        'sale_number',
        'status',
        'previous_status',
        'reservation_data',
        'contract_data',
        'remark_available',
        'remark_reserved',
        'remark_contract',
        'remark_installment',
        'remark_transferred',
        'avail_name',
        'avail_tel',
    ];

    protected $casts = [
        'reservation_data' => 'array',
        'contract_data' => 'array',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function purchaseAgreement(): HasOne
    {
        return $this->hasOne(SalePurchaseAgreement::class);
    }

    public function purchaseAgreementInstallments(): HasManyThrough
    {
        return $this->hasManyThrough(
            SalePurchaseAgreementInstallment::class,
            SalePurchaseAgreement::class,
            'sale_id',
            'sale_purchase_agreement_id'
        );
    }

    public function appointment(): HasOne
    {
        return $this->hasOne(SaleAppointment::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(StatusHistory::class);
    }

    public function dealSlipApproval(): HasOne
    {
        return $this->hasOne(DealSlipApproval::class);
    }

    protected static function booted(): void
    {
        static::creating(function ($sale) {
            // Auto-generate sale number
            $today = now()->format('Ymd');
            $count = static::whereDate('created_at', today())->count() + 1;
            $sale->sale_number = 'SL-' . $today . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
        });
    }
}
