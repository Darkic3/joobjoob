<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Discount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'amount', 'type', 'count', 'basket_minimum_price',
        'first_purchase', 'with_discount', 'user_id', 'max_usage_per_user', 'pay_type',
    ];

    protected $casts = [
        'count' => 'integer',
        'first_purchase' => 'integer',
        'with_discount' => 'integer',
        'max_usage_per_user' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'discount_id');
    }

    public function discountable(): MorphToMany
    {
        return $this->morphedByMany(ProductCategory::class, 'discountable', 'discountable');
    }

    public function productCategories(): MorphToMany
    {
        return $this->morphedByMany(ProductCategory::class, 'discountable', 'discountable');
    }

    public function brands(): MorphToMany
    {
        return $this->morphedByMany(Brand::class, 'discountable', 'discountable');
    }

    public function getAmountNameAttribute(): string
    {
        return $this->attributes['type'] === 'cash'
            ? number_format($this->attributes['amount']) . ' تومان'
            : $this->attributes['amount'] . ' درصد';
    }
}
