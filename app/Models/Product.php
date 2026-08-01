<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_category_id', 'name', 'slug', 'short_description', 'description',
        'sku', 'brand', 'price', 'discount_price', 'stock', 'weight',
        'status', 'is_featured', 'is_best_seller', 'colors', 'sizes', 'deal_ends_at',
    ];

    protected $casts = [
        'name' => 'json',
        'short_description' => 'json',
        'description' => 'json',
        'colors' => 'array',
        'sizes' => 'array',
        'price' => 'integer',
        'discount_price' => 'integer',
        'stock' => 'integer',
        'weight' => 'integer',
        'is_featured' => 'boolean',
        'is_best_seller' => 'boolean',
        'deal_ends_at' => 'datetime',
    ];

    // روابط
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    // اسکوپ‌های کاربردی (Advanced Tip برای تمیزی کد)
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // Accessorها
    public function getFinalPriceAttribute(): int
    {
        return $this->discount_price ?? $this->price;
    }

    public function getHasDiscountAttribute(): bool
    {
        return $this->discount_price && $this->discount_price < $this->price;
    }

    public function getDiscountPercentageAttribute(): ?int
    {
        if ($this->has_discount) {
            return round((($this->price - $this->discount_price) / $this->price) * 100);
        }
        return null;
    }

    // کمک‌کننده ترجمه
    public function trans(string $field): ?string
    {
        $value = $this->$field;
        if (is_array($value) || is_object($value)) {
            return $value[app()->getLocale()] ?? $value['fa'] ?? null;
        }
        return $value;
    }
}
