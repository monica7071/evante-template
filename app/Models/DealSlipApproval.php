<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealSlipApproval extends Model
{
    protected $fillable = [
        'sale_id',
        'status',
        'prepared_by',
        'prepared_name',
        'prepared_signature',
        'prepared_at',
        'checked_by',
        'checked_name',
        'checked_signature',
        'checked_at',
        'approved_by',
        'approved_name',
        'approved_signature',
        'approved_at',
    ];

    protected $casts = [
        'prepared_at' => 'datetime',
        'checked_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
