<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PromoBanner extends Model
{
    /**
     * آیکون‌های از پیش‌طراحی‌شده برای بنرها (منبع واحد حقیقت — برای فرم و اعتبارسنجی)
     */
    public const ICONS = [
        'megaphone', 'truck', 'ticket', 'gift', 'percent',
        'star', 'fire', 'lightning', 'heart', 'sparkles',
    ];

    /**
     * رنگ‌های مجاز هر بنر (کلاس .promo-banner--{tone} متغیرهای رنگ را تعریف می‌کند)
     */
    public const TONES = [
        'accent', 'gold', 'emerald', 'rose', 'blue', 'orange', 'violet',
    ];

    protected $fillable = [
        'icon',
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
