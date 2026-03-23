<?php

namespace App\Models;

use App\Scopes\OrganizationScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
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
        'listing_id',
        'buyer_first_name',
        'buyer_last_name',
        'buyer_full_name',
        'buyer_id_type',
        'buyer_id_number',
        'buyer_nationality',
        'buyer_address',
        'buyer_phone',
        'buyer_email',
        'reservation_date',
        'reservation_amount',
        'amount_paid_number',
        'amount_paid_text',
        'contract_start_date',
        'buyer_signature_name',
        'buyer_signature_path',
        'seller_name',
        'seller_signature_path',
        'witness_one_name',
        'witness_one_signature_path',
        'witness_two_name',
        'witness_two_signature_path',
        'buyer_signed_at',
        'witness_one_signed_at',
        'witness_two_signed_at',
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'contract_start_date' => 'date',
        'reservation_amount' => 'decimal:2',
        'amount_paid_number' => 'decimal:2',
        'buyer_signed_at' => 'datetime',
        'witness_one_signed_at' => 'datetime',
        'witness_two_signed_at' => 'datetime',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }
}
