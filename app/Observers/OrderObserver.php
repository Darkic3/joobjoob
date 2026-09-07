<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderHistory;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     *
     * In Laravel, `created` fires AFTER the model is saved, so isDirty()
     * always returns false. We unconditionally create the initial history.
     */
    public function created(Order $order): void
    {
        OrderHistory::create([
            'order_id' => $order->id,
            'order_status' => $order->order_status,
            'shipping_status_id' => $order->shipping_status_id,
        ]);
    }

    /**
     * Handle the Order "updated" event.
     *
     * Use wasChanged() (not isDirty) — isDirty is true mid-save but
     * wasChanged correctly reflects the persisted state change.
     *
     * Stock restoration is handled exclusively by OrderService::cancelOrder
     * and CancelExpiredOrders command — NOT here, to avoid double restore.
     */
    public function updated(Order $order): void
    {
        if ($order->wasChanged('order_status') || $order->wasChanged('shipping_status_id')) {
            OrderHistory::create([
                'order_id' => $order->id,
                'order_status' => $order->order_status,
                'shipping_status_id' => $order->shipping_status_id,
            ]);
        }
    }
}
