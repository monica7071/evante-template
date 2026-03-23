<?php

namespace App\Models;

use App\Scopes\OrganizationScope;
use Illuminate\Database\Eloquent\Model;

class FinanceSnapshot extends Model
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
