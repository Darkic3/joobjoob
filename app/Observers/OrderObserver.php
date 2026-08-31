<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderHistory;
use App\Services\StockService;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

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
        }

        // بازگردانی موجودی هنگام لغو سفارش
        if ($order->wasChanged('order_status') && $order->order_status === 'cancelled') {
            $previousStatus = $order->getOriginal('order_status');
            if (in_array($previousStatus, ['paid', 'deposit_paid', 'pending_confirmation', 'wait_for_verification'])) {
                try {
                    $this->stockService->restoreStock($order);
                } catch (\Exception $e) {
                    Log::error('OrderObserver: Stock restoration failed for cancelled order #' . $order->id, [
                        'message' => $e->getMessage(),
                    ]);
                }
            }
        }
    }
}
