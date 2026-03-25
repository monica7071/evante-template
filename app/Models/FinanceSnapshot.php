<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class FinanceSnapshot extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'year',
        'month',
        'reservation_total',
        'contract_payment_total',
        'transfer_amount_total',
        'fees_total',
        'transferred_value',
        'transferred_count',
        'active_deals',
        'per_person',
        'per_status',
        'snapshot_at',
    ];

    protected $casts = [
        'per_person' => 'array',
        'per_status' => 'array',
        'snapshot_at' => 'datetime',
        'reservation_total' => 'decimal:2',
        'contract_payment_total' => 'decimal:2',
        'transfer_amount_total' => 'decimal:2',
        'fees_total' => 'decimal:2',
        'transferred_value' => 'decimal:2',
    ];
}
