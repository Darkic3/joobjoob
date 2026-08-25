<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderHistory extends Model
{
    use SoftDeletes;

    protected $fillable = ['order_id', 'shipping_status_id', 'order_status'];

    public function shippingStatus()
    {
        return $this->belongsTo(OrderShippingStatus::class, 'shipping_status_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function getStatusNameAttribute(): string
    {
        return match ($this->attributes['order_status'] ?? null) {
            'paying' => 'در حال پرداخت',
            'paid' => 'پرداخت شده',
            'deposit_paid' => 'بیعانه پرداخت شده',
            'wait_for_verification' => 'در انتظار تایید فیش',
            'unpaid' => 'پرداخت نشده',
            'cancelled' => 'لغو شده',
            default => '',
        };
    }
}
