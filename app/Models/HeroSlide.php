<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;


class HeroSlide extends Model
{
    protected $fillable = [
        'badge', 'title', 'desc', 'image', 'price', 'old_price',
        'btn_text', 'btn_link', 'is_active', 'sort_order'
    ];

    protected $casts = [
        'badge' => 'array',
        'title' => 'array',
        'desc' => 'array',
        'btn_text' => 'array',
        'price' => 'array',
        'old_price' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Scope
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order', 'asc');
    }


    public function trans(string $field): string
    {
        $value = $this->{$field};
        if (!is_array($value) || empty($value)) {
            return '';
        }

        $currentLocale = app()->getLocale();

        return $value[$currentLocale]
            ?? $value['fa']
            ?? reset($value);
    }
}
