<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bank extends Model
{
    use SoftDeletes;

    protected $fillable = ['title', 'icon', 'status', 'admin_test', 'bank_type', 'config'];

    protected $casts = [
        'config' => 'json',
        'admin_test' => 'boolean',
    ];

    protected $appends = ['item_image'];

    public function getItemImageAttribute(): ?string
    {
        $icon = $this->attributes['icon'] ?? null;
        return $icon ? asset('assets/site/bank/' . $icon) : null;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
