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
        'title', 'code', 'amount', 'type', 'count', 'basket_minimum_price',
        'first_purchase', 'with_discount', 'user_id', 'max_usage_per_user',
        'pay_type', 'is_active', 'starts_at', 'expires_at',
    ];

    protected $casts = [
        'count' => 'integer',
        'first_purchase' => 'integer',
        'with_discount' => 'integer',
        'max_usage_per_user' => 'integer',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'basket_minimum_price' => 'integer',
    ];

    // ── Relationships ──

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'discount_id');
    }

    public function usageRecords(): HasMany
    {
        return $this->hasMany(DiscountUsage::class);
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

    // ── Accessors ──

    public function getAmountNameAttribute(): string
    {
        return $this->attributes['type'] === 'cash'
            ? number_format($this->attributes['amount']) . ' تومان'
            : $this->attributes['amount'] . ' درصد';
    }

    // ── Scopes ──

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValid($query)
    {
        $now = now();

        return $query->active()
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', $now);
            });
    }

    // ── Helper Methods ──

    public function isCurrentlyValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $this->starts_at->isAfter($now)) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isBefore($now)) {
            return false;
        }

        return true;
    }

    public function hasReachedLimit(?int $userId = null): bool
    {
        if ($this->count <= 0) {
            return true;
        }

        $totalUsage = $this->usageRecords()->count();

        if ($totalUsage >= $this->count) {
            return true;
        }

        if ($userId && $this->max_usage_per_user > 0) {
            $userUsage = $this->usageRecords()->where('user_id', $userId)->count();

            if ($userUsage >= $this->max_usage_per_user) {
                return true;
            }
        }

        return false;
    }

    /**
     * محاسبه مبلغ تخفیف بر اساس مبلغ سبد خرید
     */
    public function calculateDiscount(float $subtotal): float
    {
        if ($this->type === 'percent') {
            $discount = $subtotal * ((float) $this->amount / 100);
        } else {
            $discount = (float) $this->amount;
        }

        // تخفیف نباید از مبلغ سبد بیشتر شود
        return min($discount, $subtotal);
    }
}
