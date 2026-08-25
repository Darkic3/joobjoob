<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ShippingMethod extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'price', 'price_ceiling', 'status', 'description',
        'freight_balance', 'type', 'chapar_type', 'config',
        'sender_name', 'sender_company', 'sender_city_id', 'sender_phone',
        'sender_mobile', 'sender_email', 'sender_address', 'sender_postal_code',
    ];

    protected $casts = [
        'price' => 'string',
        'price_ceiling' => 'double',
        'config' => 'json',
        'freight_balance' => 'integer',
    ];

    public function cities(): BelongsToMany
    {
        return $this->belongsToMany(City::class, 'shipping_method_city');
    }

    public function senderCity(): HasOne
    {
        return $this->hasOne(City::class, 'id', 'sender_city_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function getTypeNameAttribute(): array
    {
        return match ($this->attributes['type'] ?? null) {
            'chapar' => ['title' => 'چاپار', 'badge' => 'success'],
            'cod24' => ['title' => 'Cod24', 'badge' => 'primary'],
            'with_weight' => ['title' => 'بر اساس وزن', 'badge' => 'info'],
            default => ['title' => 'قیمت ثابت', 'badge' => 'info'],
        };
    }

    public function getFreightBalanceNameAttribute(): array
    {
        return $this->attributes['freight_balance'] == 1
            ? ['title' => 'پس کرایه', 'badge' => 'success']
            : ['title' => 'پیش کرایه', 'badge' => 'info'];
    }
}
