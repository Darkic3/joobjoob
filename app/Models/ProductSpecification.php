<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * جدول میانی product_specification
 * - یک ردیف = یک مشخصه انتخابی (value=NULL و parent_id اشاره به ردیف والد) یا
 * - یک مقدار نوشتاری (value=متن)
 */
class ProductSpecification extends Model
{
    use SoftDeletes;

    protected $table = 'product_specification';

    protected $fillable = [
        'product_id', 'specification_id', 'value', 'parent_id',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function specification(): BelongsTo
    {
        return $this->belongsTo(Specification::class);
    }

    public function parent(): HasOne
    {
        return $this->hasOne(ProductSpecification::class, 'id', 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ProductSpecification::class, 'parent_id')->with('children');
    }

    public function getParentSpecificationAttribute()
    {
        return $this->parent?->specification;
    }
}
