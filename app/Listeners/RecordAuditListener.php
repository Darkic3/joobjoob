<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Events\OrderPaid;
use App\Services\AuditService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * RecordAuditListener — V1.1 Event-Driven (Ready-to-Merge).
 *
 * Ghanoon: Audit nabayad synchronous dar OrderService bashad (testability + SRP).
 * In Listener `AuditService::orderCreated` / `orderStatusChanged` ra queue mikonad.
 * Dar V1.1 `ShouldQueue` amma ba queue low-priority — fail ham order ra az beyn nemibarad.
 */
class RecordAuditListener implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'audit';

    public int $tries = 3;

    public function handle(OrderCreated|OrderPaid $event): void
    {
        try {
            if ($event instanceof OrderCreated) {
                AuditService::orderCreated($event->order);
            } elseif ($event instanceof OrderPaid) {
                AuditService::orderStatusChanged(
                    $event->order,
                    $event->previousStatus ?? 'paying',
                    $event->order->order_status
                );
            }
        } catch (\Throwable $e) {
            Log::error('RecordAuditListener failed', [
                'order_id' => $event->order->id,
                'event' => get_class($event),
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(OrderCreated|OrderPaid $event, \Throwable $exception): void
    {
        Log::error('RecordAuditListener permanently failed', [
            'order_id' => $event->order->id,
            'message' => $exception->getMessage(),
        ]);
    }
}
