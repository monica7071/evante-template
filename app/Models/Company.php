<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name', 'name_th', 'tax_id', 'address', 'address_th',
        'phone', 'email', 'website', 'logo', 'established_date',
        'description', 'social_security_number',
    ];

    protected $casts = [
        'established_date' => 'date',
    ];
}
