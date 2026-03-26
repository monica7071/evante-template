<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes, BelongsToOrganization;

    protected $fillable = [
        'employee_code', 'user_id', 'prefix', 'first_name', 'last_name',
        'first_name_th', 'last_name_th', 'nickname', 'gender', 'date_of_birth',
        'national_id', 'phone', 'email', 'line_id', 'address',
        'position_id', 'team_id', 'hire_date', 'end_date',
        'employment_type', 'status', 'salary',
        'bank_name', 'bank_branch', 'account_number', 'account_name', 'avatar',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'end_date' => 'date',
        'salary' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getFullNameThAttribute(): string
    {
        return ($this->first_name_th ?? '') . ' ' . ($this->last_name_th ?? '');
    }

    protected static function booted(): void
    {
        static::creating(function ($employee) {
            // Auto-generate employee code
            if (empty($employee->employee_code)) {
                $count = static::withTrashed()->count() + 1;
                $employee->employee_code = 'EMP-' . str_pad($count, 5, '0', STR_PAD_LEFT);
            }
        });
    }
}
