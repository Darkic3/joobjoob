<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id', 'price', 'discounted_price', 'final_price', 'stock', 'weight',
    ];

    protected $casts = [
        'price' => 'float',
        'discounted_price' => 'float',
        'final_price' => 'float',
        'stock' => 'integer',
        'weight' => 'float',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * مقادیر انتخابی واریانت (مشخصه‌های select — فرزندان والدها)
     */
    public function specifications(): BelongsToMany
    {
        return $this->belongsToMany(Specification::class, 'product_variant_specification',
            'product_variant_id', 'specification_id')
            ->with('parent');
    }

    public function scopeAvailable($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function getHasDiscountAttribute(): bool
    {
        return $this->discounted_price > 0 && $this->discounted_price < $this->price;
    }

    public function getPercentAttribute(): ?int
    {
        if ($this->price <= 0 || $this->final_price <= 0) {
            return null;
        }

        return round((($this->price - $this->final_price) / $this->price) * 100);
    }

    /**
     * برچسب ترکیب مشخصه‌های واریانت (مثل «قرمز / L»)
     */
    public function getLabelAttribute(): string
    {
        return $this->specifications
            ->sortBy(fn ($s) => $s->parent_id ?? 0)
            ->map(fn ($s) => $s->trans('title'))
            ->implode(' / ');
    }
}
