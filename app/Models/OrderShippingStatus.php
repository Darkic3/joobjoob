<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderShippingStatus extends Model
{
    use SoftDeletes;

    protected $table = 'order_shipping_statuses';

    protected $fillable = ['title', 'color', 'default', 'for_send', 'sending_sms'];

    protected $casts = [
        'default' => 'integer',
        'for_send' => 'integer',
        'sending_sms' => 'integer',
    ];

    public function scopeDefault($query)
    {
        return $query->where('default', 1);
    }

    public function getSendNameAttribute(): array
    {
        return $this->attributes['for_send'] == 1
            ? ['title' => 'آماده ارسال', 'badge' => 'success']
            : ['title' => 'عادی', 'badge' => 'warning'];
    }
}
