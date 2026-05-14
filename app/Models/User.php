<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'role_id',
        'avatar',
        'signature',
        'phone',
        'is_active',
        'last_login_at',
        'organization_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function dynamicRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        if ($this->role_id && $this->dynamicRole) {
            return $this->dynamicRole->name === 'admin';
        }

        return $this->role === 'admin';
    }

    public function isAgent(): bool
    {
        if ($this->role_id && $this->dynamicRole) {
            return $this->dynamicRole->name === 'agent';
        }

        return $this->role === 'agent';
    }

    public function isLeader(): bool
    {
        if ($this->role_id && $this->dynamicRole) {
            return $this->dynamicRole->name === 'leader';
        }

        return $this->role === 'leader';
    }

    public function isSalesManager(): bool
    {
        if ($this->role_id && $this->dynamicRole) {
            return $this->dynamicRole->name === 'sales_manager';
        }

        return $this->role === 'sales_manager';
    }

    public function hasRole(string $role): bool
    {
        if ($this->role_id && $this->dynamicRole) {
            return $this->dynamicRole->name === $role;
        }

        return $this->role === $role;
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->role_id && $this->dynamicRole) {
            return $this->dynamicRole->hasPermission($permission);
        }

        // Fallback: admin gets all permissions
        if ($this->role === 'admin') {
            return true;
        }

        return false;
    }

    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->name);
        $initials = '';
        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= mb_strtoupper(mb_substr($word, 0, 1));
        }
        return $initials;
    }

    public function getRoleDisplayNameAttribute(): string
    {
        if ($this->isSuperAdmin()) {
            return 'Super Admin';
        }

        if ($this->role_id && $this->dynamicRole) {
            return $this->dynamicRole->display_name;
        }

        return ucfirst(str_replace('_', ' ', $this->role));
    }
}
