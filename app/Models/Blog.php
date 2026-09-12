<?php

namespace App\Models;

use App\Traits\HasSeoMeta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blog extends Model
{
    use SoftDeletes, HasSeoMeta;

    protected $fillable = [
        'blog_category_id', 'user_id', 'title', 'slug', 'excerpt',
        'content', 'image', 'views', 'is_active', 'show_in_first_page',
    ];

    protected function casts(): array
    {
        return [
            'views' => 'integer',
            'is_active' => 'boolean',
            'show_in_first_page' => 'boolean',
        ];
    }

    // Relations
    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFirstPage($query)
    {
        return $query->where('show_in_first_page', true);
    }

    // Increase view method
    public function incrementViews(): void
    {
        $this->increment('views');
    }

    /**
     * زمان تقریبی مطالعه (دقیقه) — بر اساس تعداد واژهٔ متن (بدون تگ‌ها) و سرعت ۲۰۰ واژه/دقیقه.
     * برای متن‌های فارسی هم همین هیوریستیک ساده کافی است.
     */
    public function readingMinutes(): int
    {
        $text = strip_tags((string) $this->content);
        $words = count(preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY));

        return max(1, (int) ceil($words / 200));
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image
            ? asset(config('paths.upload', 'uploads').'/blogs/'.$this->image)
            : null;
    }

    /**
     * تاریخ انتشار محلی‌شده بر اساس زبان فعال صفحه — نقطهٔ واحد نمایش تاریخ بلاگ.
     *
     * همهٔ ویوها (کارت صفحهٔ اصلی، ایندکس بلاگ و صفحهٔ جزئیات) از همین متد استفاده
     * می‌کنند تا تاریخ همیشه با زبان/تقویمِ تنظیمیِ زبانِ فعال هماهنگ باشد:
     *   - fa → تاریخ جلالی با ارقام فارسی (مرداد ۱۴۰۵)
     *   - ar → تاریخ قمری با ارقام عربی (صفر ١٤٤٨)
     *   - بقیهٔ زبان‌ها → میلادی با نام ماه به زبان همان لوکال (en → August 2026, tr → Ağustos 2026, …)
     *
     * تصمیم‌گیری داخل show_date() دیتا-درایو است (LocaleService::isJalali)؛
     * اگر زبان جدیدی اضافه شد، بدون تغییر این متد خودکار عمل می‌کند.
     *
     * فرمت پیش‌فرض 'd F Y' نام کامل ماه را در هر دو تقویم می‌دهد
     * (F در jdate → «مرداد» و در Carbon → «August»).
     */
    public function localizedDate(string $format = 'd F Y'): string
    {
        return show_date($this->created_at, $format);
    }
}
