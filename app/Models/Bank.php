<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bank extends Model
{
    use SoftDeletes;

    protected $fillable = ['title', 'icon', 'status', 'admin_test', 'bank_type', 'config', 'card_number', 'card_holder_name', 'card_description'];

    protected $casts = [
        'config' => 'json',
        'admin_test' => 'boolean',
    ];

    protected $appends = ['item_image'];

    /**
     * حذف فرمت از شماره کارت هنگام ذخیره در دیتابیس
     */
    public function setCardNumberAttribute(?string $value): void
    {
        $this->attributes['card_number'] = clean_card_number($value);
    }

    public function getItemImageAttribute(): ?string
    {
        $icon = $this->attributes['icon'] ?? null;
        if (! $icon) {
            return null;
        }
        // Check new uploads path first, then legacy path
        $uploadPath = config('paths.upload', 'uploads');
        if (file_exists(public_path("{$uploadPath}/bank/{$icon}"))) {
            return asset("{$uploadPath}/bank/{$icon}");
        }
        return asset('assets/site/bank/' . $icon);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
