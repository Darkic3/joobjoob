<?php

namespace App\Models;

use App\Models\Traits\HasAdminPermissions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasAdminPermissions, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'mobile', 'email', 'password', 'avatar',
        'is_active', 'locale', 'theme', 'is_admin',
        'confirm_code', 'confirm_code_expires_at',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'confirm_code_expires_at' => 'datetime',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    // ==================== Relationships ====================

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function paidOrders(): HasMany
    {
        return $this->hasMany(Order::class)->where('order_status', 'paid');
    }

    public function ordersWithDeposit(): HasMany
    {
        return $this->hasMany(Order::class)
            ->whereIn('order_status', ['paid', 'deposit_paid', 'wait_for_verification'])
            ->orderBy('id', 'DESC');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    // ==================== Methods ====================

    public function isAdmin(): bool
    {
        return $this->is_admin ?? false;
    }
}
