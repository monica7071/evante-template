<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Listing extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'location_id',
        'project_id',
        'building',
        'project_name',
        'floor',
        'room_number',
        'unit_code',
        'bedrooms',
        'area',
        'price_per_room',
        'price_per_sqm',
        'unit_type',
        'status',
        'reservation_deposit',
        'contract_payment',
        'installment_15_terms',
        'installment_15_terms_en',
        'installment_12_terms',
        'special_installment_3_terms',
        'transfer_amount',
        'transfer_amount_en',
        'transfer_fee',
        'annual_common_fee',
        'sinking_fund',
        'utility_fee',
        'total_misc_fee',
        'floor_plan_image',
        'room_layout_image',
    ];

    protected $casts = [
        'area' => 'decimal:2',
        'price_per_room' => 'decimal:2',
        'price_per_sqm' => 'decimal:2',
        'reservation_deposit' => 'decimal:2',
        'contract_payment' => 'decimal:2',
        'installment_15_terms' => 'decimal:2',
        'installment_15_terms_en' => 'decimal:2',
        'installment_12_terms' => 'decimal:2',
        'special_installment_3_terms' => 'decimal:2',
        'transfer_amount' => 'decimal:2',
        'transfer_amount_en' => 'decimal:2',
        'transfer_fee' => 'decimal:2',
        'annual_common_fee' => 'decimal:2',
        'sinking_fund' => 'decimal:2',
        'utility_fee' => 'decimal:2',
        'total_misc_fee' => 'decimal:2',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function listingImages(): HasMany
    {
        return $this->hasMany(ListingImage::class)->orderBy('sort_order');
    }

    public function latestStatusHistory(): HasOneThrough
    {
        return $this->hasOneThrough(
            StatusHistory::class,
            Sale::class,
            'listing_id',
            'sale_id'
        )->latestOfMany();
    }
}
