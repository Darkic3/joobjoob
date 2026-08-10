<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class TrustBadge extends Model
{
    protected $fillable = [
        'icon',
        'title',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
        'is_active' => 'boolean',
    ];

    // اسکوپ برای دریافت آیتم‌های فعال و مرتب‌شده
    public function scopeActiveOrdered($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
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
        static::saved(fn () => Cache::forget('trust_badges_active'));
        static::deleted(fn () => Cache::forget('trust_badges_active'));
    }
}
