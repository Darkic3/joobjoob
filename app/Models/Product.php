<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_category_id', 'name', 'slug', 'main_image', 'short_description', 'description',
        'sku', 'brand', 'price', 'discount_price', 'stock', 'weight',
        'is_active', 'is_featured', 'is_best_seller', 'colors', 'sizes', 'deal_ends_at',
    ];

    protected $casts = [
        'name' => 'array',
        'short_description' => 'array',
        'description' => 'array',
        'colors' => 'array',
        'sizes' => 'array',
        'price' => 'integer',
        'discount_price' => 'integer',
        'stock' => 'integer',
        'weight' => 'integer',
        'is_featured' => 'boolean',
        'is_best_seller' => 'boolean',
        'is_active' => 'boolean',
        'deal_ends_at' => 'datetime',
    ];

    // ==========================================
    // روابط (Relationships)
    // ==========================================

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * تصویر اصلی محصول — یا از ستون main_image یا اولین تصویر با is_primary
     */
    public function getMainImageAttribute(?string $value): ?string
    {
        if (!empty($value)) {
            return $value;
        }

        // اگر ستون خالی بود از روی رابطه استنتاج می‌کنیم (سازگاری با داده قبلی)
        $primary = $this->relationLoaded('images')
            ? $this->images->firstWhere('is_primary', true)
            : $this->images()->where('is_primary', true)->first();

        return $primary?->path;
    }

    /**
     * URL کامل تصویر اصلی (در صورت نبودن، از اولین تصویر گالری)
     */
    public function getMainImageUrlAttribute(): ?string
    {
        if ($this->main_image) {
            return asset(config('paths.upload', 'uploads') . '/products/' . $this->main_image);
        }

        // اگر رابطه لود شده باشد از حافظه استفاده کن (بدون کوئری اضافه)
        if ($this->relationLoaded('images')) {
            $primary = $this->images->firstWhere('is_primary', true) ?? $this->images->first();
            return $primary?->url;
        }

        return $this->images()->orderBy('sort_order')->first()?->url;
    }

    // ⚠️ توجه: فقط یک متد reviews وجود دارد که شامل اسکوپ approved است
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->approved();
    }

    // ==========================================
    // اسکوپ‌های کوئری (Query Scopes)
    // ==========================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // ==========================================
    // اکسسورها (Accessors)
    // ==========================================

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

    // دریافت میانگین امتیاز (بهینه‌شده برای جلوگیری از N+1)
    public function getAverageRatingAttribute(): float
    {
        if ($this->relationLoaded('reviews')) {
            return round($this->reviews->avg('rating') ?? 0, 1);
        }
        return round($this->reviews()->avg('rating') ?? 0, 1);
    }

    // دریافت تعداد نظرات (بهینه‌شده)
    public function getReviewsCountAttribute(): int
    {
        if ($this->relationLoaded('reviews')) {
            return $this->reviews->count();
        }
        return $this->reviews()->count();
    }

    // ==========================================
    // متدهای کمکی (Helpers)
    // ==========================================

    // کمک‌کننده برای دریافت ترجمه فیلدهای JSON
    public function trans(string $field): ?string
    {
        $value = $this->$field;
        if (is_array($value) || is_object($value)) {
            return $value[app()->getLocale()] ?? $value['fa'] ?? null;
        }
        return $value;
    }
}
