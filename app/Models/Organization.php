<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    protected $fillable = [
        'name',
        'name_th',
        'slug',
        'logo',
        'primary_color',
        'domain',
        'storage_limit',
        'storage_used',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'storage_limit' => 'integer',
        'storage_used' => 'integer',
    ];

    // ── Relationships ──

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function financeSnapshots(): HasMany
    {
        return $this->hasMany(FinanceSnapshot::class);
    }

    public function pdfTemplates(): HasMany
    {
        return $this->hasMany(PdfTemplate::class);
    }

    // ── Storage helpers ──

    public function isStorageFull(): bool
    {
        return $this->storage_used >= $this->storage_limit;
    }

    public function storageUsedInGB(): float
    {
        return round($this->storage_used / 1024, 2);
    }

    public function storageLimitInGB(): float
    {
        return round($this->storage_limit / 1024, 2);
    }

    public function getStorageUsagePercentAttribute(): float
    {
        if ($this->storage_limit === 0) {
            return 100;
        }

        return round(($this->storage_used / $this->storage_limit) * 100, 1);
    }
}
