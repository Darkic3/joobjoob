<?php

namespace App\Models;

use App\Traits\HasSeoMeta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes, HasSeoMeta;

    protected $fillable = [
        'product_category_id', 'brand_id', 'name', 'slug', 'main_image', 'short_description', 'description',
        'sku', 'price', 'discount_price', 'final_price', 'stock', 'weight',
        'is_active', 'is_featured', 'is_best_seller', 'deal_ends_at',
        'timer_active', 'start_timer', 'end_timer',
    ];

    protected $casts = [
        'name' => 'array',
        'short_description' => 'array',
        'description' => 'array',
        'price' => 'integer',
        'discount_price' => 'integer',
        'final_price' => 'integer',
        'stock' => 'integer',
        'weight' => 'integer',
        'is_featured' => 'boolean',
        'is_best_seller' => 'boolean',
        'is_active' => 'boolean',
        'timer_active' => 'boolean',
        'deal_ends_at' => 'datetime',
        'start_timer' => 'datetime',
        'end_timer' => 'datetime',
    ];

    // ==========================================
    // روابط (Relationships)
    // ==========================================

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * گروه‌های مشخصات انتخابی محصول (select) — فقط ردیف‌های والد
     * (value=NULL و parent_id=NULL — مثل «رنگ» و «سایز»)
     */
    public function specifications(): BelongsToMany
    {
        return $this->belongsToMany(Specification::class, 'product_specification')
            ->withPivot('value', 'parent_id')
            ->wherePivotNull('value')
            ->wherePivotNull('parent_id')
            ->wherePivotNull('deleted_at');
    }

    /**
     * مقادیر نوشتاری مشخصات (text) — ردیف‌هایی که value دارند
     */
    public function specificationValues(): HasMany
    {
        return $this->hasMany(ProductSpecification::class)
            ->whereNotNull('value')
            ->whereNull('deleted_at')
            ->with('specification');
    }

    /**
     * تمام ردیف‌های مشخصه (برای مدیریت در پنل)
     */
    public function productSpecifications(): HasMany
    {
        return $this->hasMany(ProductSpecification::class)->with('children', 'specification');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->with('specifications');
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class)->orderBy('sort_order');
    }

    // ==========================================
    // محصولات مرتبط و مکمل (product_associations)
    // ==========================================

    public function related(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_associations',
            'product_id', 'related_product_id')
            ->withPivot('type')
            ->wherePivot('type', 'related');
    }

    public function complement(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_associations',
            'product_id', 'related_product_id')
            ->withPivot('type')
            ->wherePivot('type', 'complement');
    }

    public function associations(): HasMany
    {
        return $this->hasMany(ProductAssociation::class, 'product_id');
    }

    /**
     * تصویر اصلی محصول — یا از ستون main_image یا اولین تصویر با is_primary
     */
    public function getMainImageAttribute(?string $value): ?string
    {
        if (! empty($value)) {
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
            return asset(config('paths.upload', 'uploads').'/products/'.$this->main_image);
        }

        // اگر رابطه لود شده باشد از حافظه استفاده کن (بدون کوئری اضافه)
        if ($this->relationLoaded('images')) {
            $primary = $this->images->firstWhere('is_primary', true) ?? $this->images->first();

            return $primary?->url;
        }

        return $this->images()->orderBy('sort_order')->first()?->url;
    }

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

    public function scopeBestSeller($query)
    {
        return $query->where('is_best_seller', true);
    }

    // ==========================================
    // اکسسورها (Accessors)
    // ==========================================

    /**
     * مدل سه‌قیمته: final_price = اگر تخفیف دارد discounted_price وگرنه price
     * اولویت با ستون دیتابیس (تجمیع واریانت) و سپس محاسبه لحظه‌ای
     */
    public function getFinalPriceAttribute(): int
    {
        $value = $this->attributes['final_price'] ?? null;

        if ($value !== null && (int) $value > 0) {
            return (int) $value;
        }

        return $this->discount_price > 0 ? $this->discount_price : $this->price;
    }

    public function getHasDiscountAttribute(): bool
    {
        return $this->final_price > 0 && $this->discount_price > 0 && $this->discount_price < $this->price;
    }

    public function getDiscountPercentageAttribute(): ?int
    {
        if ($this->has_discount && $this->price > 0) {
            return round((($this->price - $this->final_price) / $this->price) * 100);
        }

        return null;
    }

    /**
     * آیا محصول واریانت‌دار است؟
     */
    public function getHasVariantsAttribute(): bool
    {
        return $this->relationLoaded('variants')
            ? $this->variants->isNotEmpty()
            : $this->variants()->exists();
    }

    /**
     * واریانت پیش‌فرض محصول (برای افزودن به سبد از صفحات فهرست بدون انتخاب مشخصه).
     * اولویت: بیشترین موجودیِ غیرصفر → اولین واریانت موجود → اولین واریانت.
     */
    public function getDefaultVariantAttribute(): ?ProductVariant
    {
        if (! $this->has_variants) {
            return null;
        }

        $variants = $this->relationLoaded('variants')
            ? $this->variants
            : $this->variants()->get();

        return $variants->sortByDesc('stock')->firstWhere(fn ($v) => (int) $v->stock > 0)
            ?? $variants->first();
    }

    public function getAverageRatingAttribute(): float
    {
        if ($this->relationLoaded('reviews')) {
            return round($this->reviews->avg('rating') ?? 0, 1);
        }

        return round($this->reviews()->avg('rating') ?? 0, 1);
    }

    public function getReviewsCountAttribute(): int
    {
        if ($this->relationLoaded('reviews')) {
            return $this->reviews->count();
        }

        return $this->reviews()->count();
    }

    /**
     * شناسهٔ کلکسیونی نمایشی («برچسب انبار» تم Eco-Collectible) — مثل #ECO-0042.
     * صرفاً نمایشی و پایدار است؛ جایگزین SKU نیست.
     */
    public function getCollectibleIdAttribute(): string
    {
        return '#ECO-'.str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    /**
     * تگ متریال نمایشی از روی مشخصات لودشده (بدون کوئری جدید — اگر رابطه
     * لود نشده باشد null برمی‌گردد تا در فهرست‌ها N+1 نسازد).
     */
    public function getMaterialTagAttribute(): ?string
    {
        $keywords = ['جنس', 'متریال', 'material', 'fabric', 'پارچه', 'چرم', 'چوب', 'فلز', 'نخ', 'الیاف', 'پنبه', 'کتان', 'پلاستیک', 'cotton', 'leather', 'wood', 'metal', 'polyester'];

        // ۱) مقادیر نوشتاری (text) — مثل «جنس: چرم طبیعی»
        if ($this->relationLoaded('specificationValues')) {
            foreach ($this->specificationValues as $row) {
                $title = $row->specification?->trans('title') ?? '';
                foreach ($keywords as $keyword) {
                    if (mb_stripos($title, $keyword) !== false && filled($row->value)) {
                        return mb_substr(trim((string) $row->value), 0, 40);
                    }
                }
            }
        }

        // ۲) گروه‌های انتخابی (select) — مثل گروه «جنس» با مقادیر فرزند
        if ($this->relationLoaded('productSpecifications')) {
            foreach ($this->productSpecifications as $ps) {
                if ($ps->value !== null || $ps->parent_id !== null || ! $ps->specification) {
                    continue;
                }
                $title = $ps->specification->trans('title') ?? '';
                foreach ($keywords as $keyword) {
                    if (mb_stripos($title, $keyword) !== false) {
                        $values = $ps->children
                            ->map(fn ($c) => $c->specification?->trans('title'))
                            ->filter()->implode('، ');
                        if (filled($values)) {
                            return mb_substr($values, 0, 40);
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * تگ وضعیت نمایشی کارت (اولویت: ناموجود → محدود → جدید → تخفیف).
     *
     * @return array{label: string, tone: string}|null
     */
    public function getStatusTagAttribute(): ?array
    {
        if ($this->stock === 0) {
            return ['label' => __('messages.out_of_stock'), 'tone' => 'danger'];
        }

        if ($this->stock > 0 && $this->stock <= 5) {
            return ['label' => __('messages.only_left', ['count' => $this->stock]), 'tone' => 'sand'];
        }

        if ($this->created_at && $this->created_at->diffInDays(now()) <= 14) {
            return ['label' => __('messages.new_badge'), 'tone' => 'sage'];
        }

        if ($this->has_discount && $this->discount_percentage) {
            return ['label' => $this->discount_percentage.'٪-', 'tone' => 'clay'];
        }

        return null;
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
