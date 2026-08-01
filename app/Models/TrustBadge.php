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
        'is_active' => 'boolean',
    ];

    // اسکوپ برای دریافت آیتم‌های فعال و مرتب‌شده
    public function scopeActiveOrdered($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    // پاک کردن خودکار کش در صورت تغییر در دیتابیس
    protected static function booted()
    {
        static::saved(fn () => Cache::forget('trust_badges_active'));
        static::deleted(fn () => Cache::forget('trust_badges_active'));
    }
}
