<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class HeroSlide extends Model
{
    protected $fillable = [
        'badge', 'title', 'desc', 'btn_text',
        'price', 'old_price', 'currency', 'rating', 'viewers', 'trusts',
        'linkable_type', 'linkable_id', 'image', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'badge' => 'array',
        'title' => 'array',
        'desc' => 'array',
        'btn_text' => 'array',
        'trusts' => 'array',
        'price' => 'integer',
        'old_price' => 'integer',
        'viewers' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order', 'asc');
    }

    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }

    public function trans(string $field): string
    {
        $value = $this->{$field};
        if (! is_array($value) || empty($value)) {
            return '';
        }
        $currentLocale = app()->getLocale();

        // مقادیر null داخل آرایه (مثلاً title = ['fa' => null]) نباید null برگردانند
        return (string) ($value[$currentLocale] ?? $value['fa'] ?? reset($value) ?? '');
    }

    // اکسسور برای دریافت لینک نهایی اسلاید
    public function getUrlAttribute(): string
    {
        // اگر لینک دستی (URL) وارد شده باشد
        if ($this->linkable_type === 'url') {
            return $this->linkable_id ?? '#';
        }

        // اگر صفحه استاتیک باشد
        if ($this->linkable_type === 'page') {
            return route('site.page', $this->linkable_id);
        }

        // اگر به یک مدل (محصول، دسته، مقاله) لینک شده باشد
        if ($this->linkable_type && $this->linkable_id) {
            // نکته: در آینده وقتی روت‌های فرانت‌اند را ساختی، این قسمت را آپدیت کن.
            // مثال: if ($this->linkable_type === \App\Models\Blog::class) return route('blog.show', $this->linkable_id);

            // فعلاً برای جلوگیری از ارور، یک لینک پیش‌فرض برمی‌گردانیم
            return '#';
        }

        return '#';
    }

    // بررسی آیا دکمه فعال است
    public function hasButton(): bool
    {
        return ! empty($this->btn_text['fa']) || ! empty($this->linkable_type);
    }

    // بررسی آیا امتیاز فعال است
    public function hasRating(): bool
    {
        return ! empty($this->rating);
    }

    // بررسی آیا بازدیدکنندگان زنده فعال است
    public function hasViewers(): bool
    {
        return ! empty($this->viewers) && $this->viewers > 0;
    }

    /**
     * نوع مقصد لینک در فرم ادمین (برای پیش‌انتخاب دراپ‌داون).
     */
    public function linkType(): string
    {
        if (! $this->linkable_type) {
            return 'custom';
        }

        return match (true) {
            $this->linkable_type === 'url' => 'custom',
            str_contains($this->linkable_type, 'Product') && ! str_contains($this->linkable_type, 'Category') => 'product',
            str_contains($this->linkable_type, 'ProductCategory') => 'product_category',
            str_contains($this->linkable_type, 'Blog') && ! str_contains($this->linkable_type, 'Category') => 'blog',
            str_contains($this->linkable_type, 'BlogCategory') => 'blog_category',
            str_contains($this->linkable_type, 'Page') => 'page',
            default => 'custom',
        };
    }

    /**
     * آیتم‌های اعتماد به فرمت فرم ادمین (icon/fa/en) — از هر دو ساختار ذخیره‌شده پشتیبانی می‌کند.
     *
     * @return array<int, array{icon: string, fa: string, en: string}>
     */
    public function trustFormSeed(): array
    {
        $seed = [];
        foreach ((array) $this->trusts as $trust) {
            if (! is_array($trust)) {
                continue;
            }

            $trustText = is_array($trust['text'] ?? null) ? $trust['text'] : [];
            $seed[] = [
                'icon' => $trust['icon'] ?? '✓',
                'fa' => $trust['fa'] ?? $trustText['fa'] ?? '',
                'en' => $trust['en'] ?? $trustText['en'] ?? '',
            ];
        }

        return $seed;
    }

    /**
     * درصد پر شدن هر ۵ ستارهٔ امتیاز (۰ تا ۱۰۰) — برای نمایش ستارهٔ نیمه‌پر.
     * مثال: امتیاز ۴.۶ → [100, 100, 100, 100, 60]
     *
     * @return array<int, int>
     */
    public function ratingStars(): array
    {
        $rating = (float) $this->rating;
        $stars = [];

        for ($s = 1; $s <= 5; $s++) {
            $stars[] = (int) min(100, max(0, ($rating - ($s - 1)) * 100));
        }

        return $stars;
    }

    /**
     * برچسب واحد پول اسلاید بر اساس زبان جاری.
     */
    public function currencyLabel(): string
    {
        if ($this->currency === 'IRT') {
            return app()->getLocale() === 'fa' ? 'تومان' : 'IRT';
        }

        return (string) ($this->currency ?: 'IRT');
    }

    /**
     * درصد تخفیف (قیمت قدیمی → جدید).
     */
    public function discountPercent(): int
    {
        if ($this->old_price <= $this->price || $this->old_price <= 0) {
            return 0;
        }

        return (int) round((($this->old_price - $this->price) / $this->old_price) * 100);
    }

    /**
     * آدرس تصویر اسلاید — لینک خارجی یا مسیر آپلود محلی.
     */
    public function imageUrl(): string
    {
        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }

        return asset(config('paths.upload', 'uploads').'/hero-slides/'.$this->image);
    }

    /**
     * آیتم‌های اعتماد آمادهٔ رندر (با ترجمهٔ زبان جاری).
     *
     * فقط آیتم‌هایی که ادمین برای این اسلاید ذخیره کرده است برمی‌گردد —
     * اگر آیتمی پیکربندی نشده باشد (NULL یا []) آرایهٔ خالی برمی‌گردد و چیزی نمایش داده نمی‌شود.
     *
     * @return array<int, array{icon: string, text: string}>
     */
    public function trustItems(): array
    {
        $items = [];
        foreach ((array) $this->trusts as $item) {
            if (! is_array($item)) {
                continue;
            }

            $text = is_array($item['text'] ?? null)
                ? ($item['text'][app()->getLocale()] ?? $item['text']['fa'] ?? $item['text']['en'] ?? '')
                : (string) ($item['text'] ?? '');
            $text = trim((string) $text);
            if ($text === '') {
                continue;
            }

            $icon = trim((string) ($item['icon'] ?? '✓'));
            $items[] = [
                'icon' => $icon !== '' ? $icon : '✓',
                'text' => $text,
            ];
        }

        return $items;
    }
}
