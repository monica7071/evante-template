<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'project_name',
        'province',
        'district',
        'subdistrict',
        'postal_code',
        'address',
    ];

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }
}
