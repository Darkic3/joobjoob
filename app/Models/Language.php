<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = [
        'code', 'name', 'native_name', 'dir', 'is_active', 'is_default', 'sort_order',
    ];

    protected $casts = [
        'name' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->ordered();
    }

    /**
     * نام زبان به زبانِ فعال سایت (fallback: فارسی → native_name).
     */
    public function localizedName(): string
    {
        $locale = app()->getLocale();

        return $this->name[$locale] ?? $this->name['fa'] ?? $this->native_name;
    }

    public function isRtl(): bool
    {
        return $this->dir === 'rtl';
    }

    /**
     * خانواده‌ی تقویم از کاتالوگ: jalali | gregorian
     */
    public function calendar(): string
    {
        return config("languages.{$this->code}.calendar", config('languages._default.calendar'));
    }

    /**
     * خانواده‌ی ارقام از کاتالوگ: persian | arabic | latin
     */
    public function digitFamily(): string
    {
        return config("languages.{$this->code}.digits", config('languages._default.digits'));
    }
}
