<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

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

    // Accessor برای دریافت URL کامل به صورت خودکار
    public function getUrlAttribute(): string
    {
        // فرض بر این است که از دیسک 'public' استفاده می‌کنید
        return Storage::disk('public')->url($this->path);
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
