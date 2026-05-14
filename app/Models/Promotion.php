<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Promotion extends Model
{
    protected $fillable = [
        'title', 'description', 'discount_type', 'discount_value',
        'start_date', 'end_date', 'conditions', 'is_active', 'organization_id',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'discount_value' => 'float',
        'start_date'     => 'date',
        'end_date'       => 'date',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()->toDateString()));
    }
}
