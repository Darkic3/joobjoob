<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Services\CouponService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * RecordCouponUsageListener — V1.1 Event-Driven (Ready-to-Merge).
 *
 * Masool: Bad az OrderCreated, `discount_usages` ra sabt konad.
 * Ghanoon: `CouponService::recordUsage` already `DB::transaction` + `lockForUpdate` darad (FIXED).
 * In listener faghat seda zanande — logic dar Service mimand.
 * Agar `discount_id` null bashad skip (sefarsh bedoone coupon).
 */
class RecordCouponUsageListener implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    public int $tries = 3;

    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        if (! $order->discount_id) {
            return;
        }

        try {
            /** @var CouponService $coupon */
            $coupon = app(CouponService::class);
            $coupon->recordUsage(
                (int) $order->discount_id,
                $order->user_id ? (int) $order->user_id : null,
                (int) $order->id,
                (float) ($order->discount_price ?? 0)
            );
        } catch (\Throwable $e) {
            Log::error('RecordCouponUsageListener failed', [
                'order_id' => $order->id,
                'discount_id' => $order->discount_id,
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(OrderCreated $event, \Throwable $exception): void
    {
        Log::error('RecordCouponUsageListener permanently failed', [
            'order_id' => $event->order->id,
            'message' => $exception->getMessage(),
        ]);
    }
}
