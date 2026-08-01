<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'parent_id', 'name', 'slug', 'icon', 'image',
        'description', 'meta_title', 'meta_description',
        'sort_order', 'is_active'
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
    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
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
}
