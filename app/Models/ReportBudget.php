<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportBudget extends Model
{
    protected $fillable = [
        'year',
        'month',
        'week',
        'budget_marketing_online',
        'budget_marketing_offline',
    ];

    protected $casts = [
        'budget_marketing_online' => 'decimal:2',
        'budget_marketing_offline' => 'decimal:2',
    ];
}
