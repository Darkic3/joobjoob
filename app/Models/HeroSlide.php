<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class HeroSlide extends Model
{
    protected $fillable = [
        'badge', 'title', 'desc', 'btn_text',
        'price', 'old_price', 'currency', 'rating', 'viewers',
        'linkable_type', 'linkable_id', 'image', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'badge' => 'array',
        'title' => 'array',
        'desc' => 'array',
        'btn_text' => 'array',
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
}
