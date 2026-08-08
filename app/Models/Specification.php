<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Specification extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'type', 'parent_id', 'active', 'is_filter', 'is_color', 'color_code',
    ];

    protected $casts = [
        'title' => 'json',
        'active' => 'boolean',
        'is_filter' => 'boolean',
        'is_color' => 'boolean',
    ];

    // ==========================================
    // روابط
    // ==========================================

    /**
     * والد: برای مشخصه‌های select، والد = گروه (مثل «رنگ») و فرزندان = مقادیر (مثل «قرمز»)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Specification::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Specification::class, 'parent_id')
            ->orderBy('id')->with('children');
    }

    public function products(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_specification')
            ->withPivot('value', 'parent_id')
            ->wherePivotNull('deleted_at');
    }

    public function productValues(): HasMany
    {
        return $this->hasMany(ProductSpecification::class, 'specification_id');
    }

    public function variants(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(ProductVariant::class, 'product_variant_specification',
            'specification_id', 'product_variant_id');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    // ==========================================
    // اکسسورها و کمکی‌ها
    // ==========================================

    public function getTypeNameAttribute(): string
    {
        return $this->type === 'text' ? 'نوشتاری' : 'انتخابی';
    }

    public function trans(string $field): ?string
    {
        $value = $this->$field;
        if (is_array($value) || is_object($value)) {
            return $value[app()->getLocale()] ?? $value['fa'] ?? null;
        }
        return $value;
    }
}
