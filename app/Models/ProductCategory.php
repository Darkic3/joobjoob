<?php

namespace App\Models;

use App\Traits\HasSeoMeta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCategory extends Model
{
    use HasFactory, SoftDeletes, HasSeoMeta;

    protected $fillable = [
        'parent_id', 'name', 'slug', 'icon', 'image',
        'description', 'meta_title', 'meta_description',
        'sort_order', 'is_active',
    ];

    protected $casts = [
        'name' => 'json',
        'description' => 'json',
        'meta_title' => 'json',
        'meta_description' => 'json',
        'is_active' => 'boolean',
        'parent_id' => 'integer',
    ];

    // روابط
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ProductCategory::class, 'parent_id')->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    // کمک‌کننده برای دریافت ترجمه (مشابه کدی که در Blade داشتید)
    public function trans(string $field): ?string
    {
        $value = $this->$field;
        if (is_array($value) || is_object($value)) {
            return $value[app()->getLocale()] ?? $value['fa'] ?? null;
        }

        return $value;
    }

    // URL تصویر — تصاویر توسط ImageUploader در public/uploads/product-categories ذخیره می‌شوند
    public function getImageUrlAttribute(): ?string
    {
        return $this->image
            ? asset(config('paths.upload', 'uploads').'/product-categories/'.$this->image)
            : null;
    }
}
