<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderHistory;

class OrderObserver
{
    public function created(Order $order): void
    {
        if ($order->isDirty('order_status') || $order->isDirty('shipping_status_id')) {
            OrderHistory::create([
                'order_id' => $order->id,
                'order_status' => $order->order_status,
                'shipping_status_id' => $order->shipping_status_id,
            ]);
        }
    }

    public function updated(Order $order): void
    {
        if ($order->isDirty('order_status') || $order->isDirty('shipping_status_id')) {
            OrderHistory::create([
                'order_id' => $order->id,
                'order_status' => $order->order_status,
                'shipping_status_id' => $order->shipping_status_id,
            ]);

            // حذف سبد خرید پس از پرداخت موفق
            if ($order->order_status === 'paid' && $order->basket_id) {
                // Basket model: اگر بعداً ساخته شد این خط فعال شود
                // Basket::find($order->basket_id)?->delete();
            }
        }
    }
}
