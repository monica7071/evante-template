<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use BelongsToOrganization;

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
