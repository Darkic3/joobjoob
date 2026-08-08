<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'path', 'alt_text', 'is_primary', 'sort_order'];

    protected $casts = [
        'alt_text' => 'json',
        'is_primary' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ==========================================
    // Accessorهای URL — هماهنگ با ImageUploader/ImageResizer
    // فایل‌ها در public/uploads/products ذخیره می‌شوند
    // ==========================================

    public function getUrlAttribute(): string
    {
        return asset(config('paths.upload', 'uploads') . '/products/' . $this->path);
    }

    // نسخه رسیز شده کوچک (اگر وجود داشته باشد)
    public function getThumbUrlAttribute(): ?string
    {
        return $this->resizedUrl('thumb');
    }

    // نسخه رسیز شده متوسط
    public function getMediumUrlAttribute(): ?string
    {
        return $this->resizedUrl('medium');
    }

    // نسخه رسیز شده بزرگ
    public function getLargeUrlAttribute(): ?string
    {
        return $this->resizedUrl('large');
    }

    /**
     * ساخت URL برای نسخه رسیز شده در زیرپوشه مشخص
     */
    private function resizedUrl(string $sizeKey): ?string
    {
        $path = config('paths.upload', 'uploads') . '/products/' . $sizeKey . '/' . $this->path;

        // اگر فایل رسیز شده وجود نداشته باشد به تصویر اصلی برمی‌گردیم
        if (is_file(public_path($path))) {
            return asset($path);
        }

        return null;
    }

    public function getAltAttribute(): ?string
    {
        $alt = $this->alt_text;
        if (is_array($alt)) {
            return $alt[app()->getLocale()] ?? $alt['fa'] ?? null;
        }
        return $alt;
    }
}
