<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

/**
 * OrderPaid — Vaghti `order_status` be `paid` / `deposit_paid` mire.
 *
 * Caller: OrderService::markPaid() ya Webhook-e bank bad az `verifyPayment`.
 * Listener ha: Notify admins + user, Audit status change.
 */
class OrderPaid implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Order $order,
        public readonly ?string $previousStatus = null,
    ) {}
}
