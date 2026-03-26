<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'location_id',
        'name',
        'total_floors',
        'total_units',
        'default_transfer_payment_type',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProjectImage::class);
    }

    public function floorPlanImages(): HasMany
    {
        return $this->hasMany(ProjectImage::class)->where('type', 'floor_plan')->orderBy('floor');
    }
}
