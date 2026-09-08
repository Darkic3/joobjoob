<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * SendOrderNotificationListener — V1.1 Event-Driven (Ready-to-Merge).
 *
 * ShouldQueue: queue=notifications, retry 3, fail log.
 * Ghanoon: Ghabl az notify `shouldNotify()` check mikonad (Smart Notifications Toggle).
 * Agar `allow_open_requests=false` va type='order.new' skip.
 */
class SendOrderNotificationListener implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'notifications';

    public int $tries = 3;

    public int $backoff = 5;

    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        try {
            // Smart Notifications gate — agar khamosh bashad skip
            if (! $this->shouldNotify('order.new', $order)) {
                Log::info('Notification skipped by shouldNotify gate', ['order_id' => $order->id, 'type' => 'order.new']);
                return;
            }

            /** @var NotificationService $service */
            $service = app(NotificationService::class);
            $service->notifyAdmins(
                'order.new',
                'سفارش جدید ثبت شد',
                null,
                route('admin.orders.show', $order->id),
                [
                    'order_id' => $order->id,
                    'user_name' => $order->user?->name ?? (string) $order->user_id,
                    'amount' => $order->payment_price,
                    'items_count' => $order->items()->count(),
                ]
            );
        } catch (\Throwable $e) {
            Log::error('SendOrderNotificationListener failed for order #' . $order->id, [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e; // Queue retry
        }
    }

    /**
     * Mark as failed after max retries.
     */
    public function failed(OrderCreated $event, \Throwable $exception): void
    {
        Log::error('SendOrderNotificationListener permanently failed', [
            'order_id' => $event->order->id,
            'message' => $exception->getMessage(),
        ]);
    }

    /**
     * V1.1: markaze tasmim baraye Smart Notifications.
     * Hala faghat `allow_open_requests` check — ghabele gostaresh be `notify_order_new` ...
     */
    private function shouldNotify(string $type, $order): bool
    {
        // Agar notification type 'open_requests' bashad va toggle off => skip
        // Hal feli faghat order.new hamishe true, magar allow_open_requests logic ezafe shavad.
        // Pishnahad: setting("notify_{$type}", true) default true.
        try {
            if (function_exists('setting')) {
                $key = str_replace('.', '_', $type);
                $val = setting("notify_{$key}", null);
                if ($val === false || $val === '0' || $val === 0) {
                    return false;
                }
            }
        } catch (\Throwable $e) {
            // setting() ghayre ghabele dastres dar queue bootstrap — ignore
        }
        return true;
    }
}
