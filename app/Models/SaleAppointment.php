<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleAppointment extends Model
{
    protected $fillable = [
        'sale_id',
        'appointment_date',
        'appointment_time',
        'remark',
    ];

    protected $casts = [
        'appointment_date' => 'date',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}
