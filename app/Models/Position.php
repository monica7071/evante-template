<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'name', 'name_th', 'department', 'level', 'description', 'is_active', 'role_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
