<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PromoBanner extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'url',
        'style',
        'tone',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'title' => 'array',
        'subtitle' => 'array',
        'is_active' => 'boolean',
    ];

    // اسکوپ برای دریافت بنرهای فعال و مرتب‌شده (نمایش در سایت)
    public function scopeActiveOrdered($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * دریافت مقدار ترجمه‌شدهٔ یک فیلد بر اساس زبان جاری.
     * اگر مقدار زبان جاری خالی باشد، به fa و سپس به اولین مقدار غیرخالی fallback می‌شود.
     */
    public function trans(string $field): string
    {
        $value = $this->{$field};

        if (is_array($value)) {
            foreach ([app()->getLocale(), 'fa'] as $locale) {
                $candidate = trim((string) ($value[$locale] ?? ''));
                if ($candidate !== '') {
                    return $candidate;
                }
            }

            foreach ($value as $candidate) {
                $candidate = trim((string) $candidate);
                if ($candidate !== '') {
                    return $candidate;
                }
            }

            return '';
        }

        return (string) ($value ?? '');
    }

    // پاک کردن خودکار کش در صورت تغییر در دیتابیس
    protected static function booted()
    {
        static::saved(fn () => Cache::forget('promo_banners_active'));
        static::deleted(fn () => Cache::forget('promo_banners_active'));
    }
}
