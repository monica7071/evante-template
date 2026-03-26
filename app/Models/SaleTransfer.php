<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleTransfer extends Model
{
    protected $fillable = [
        'sale_id',
        'transfer_payment_type',
        'transfer_readiness',
        'bank_name',
        'bank_account_number',
        'loan_amount',
        'actual_loan_amount',
        'customer_extra_payment',
        'bank_approved_at',
    ];

    protected $casts = [
        'loan_amount' => 'decimal:2',
        'actual_loan_amount' => 'decimal:2',
        'customer_extra_payment' => 'decimal:2',
        'bank_approved_at' => 'datetime',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}
