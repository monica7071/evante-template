<?php

namespace App\Models;

use App\Scopes\OrganizationScope;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
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
        'type',
        'buyer_name',
        'id_number',
        'phone',
        'email',
        'unit_number',
        'price',
        'deposit',
        'contract_date',
        'image_path',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'deposit' => 'decimal:2',
        'contract_date' => 'date',
    ];
}
