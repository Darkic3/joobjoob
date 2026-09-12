<?php

namespace App\Models;

use App\Traits\HasSeoMeta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use HasFactory, SoftDeletes, HasSeoMeta;

    protected $fillable = [
        'title', 'url', 'description', 'image', 'active', 'show_in_first_page', 'sort_order',
    ];

    protected $casts = [
        'title' => 'json',
        'description' => 'json',
        'active' => 'boolean',
        'show_in_first_page' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function getItemImageAttribute(): ?string
    {
        return $this->image
            ? asset(config('paths.upload', 'uploads').'/brands/'.$this->image)
            : null;
    }

    // کمک‌کننده برای دریافت ترجمه فیلدهای JSON
    public function trans(string $field): ?string
    {
        $value = $this->$field;
        if (is_array($value) || is_object($value)) {
            return $value[app()->getLocale()] ?? $value['fa'] ?? null;
        }

        return $value;
    }
}
