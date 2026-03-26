<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class ProfileField extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'field_name', 'field_label', 'field_label_th', 'field_type',
        'is_required', 'is_active', 'sort_order', 'options', 'field_group',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'options' => 'array',
    ];
}
