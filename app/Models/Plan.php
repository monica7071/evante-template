<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'storage_limit',
        'price',
        'description',
        'is_active',
    ];

    protected $casts = [
        'storage_limit' => 'integer',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function organizations(): HasMany
    {
        return $this->hasMany(Organization::class);
    }

    public function storageLimitInGB(): float
    {
        return round($this->storage_limit / 1024, 2);
    }
}
