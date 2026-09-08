<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

/**
 * OrderCreated — Domain Event baraye V1.1 Event-Driven Architecture.
 *
 * Ghanoon: `ShouldDispatchAfterCommit` yani event ghabl az commit-e transaction-e
 * OrderService::create dispatch nemishavad — Listener ha Order ra gharar nist ghabl az DB commit bebinand.
 * Bayad ba `event(new OrderCreated($order))` seda zade shavad (bad az `DB::transaction` + `reserveStock`).
 */
class OrderCreated implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Order $order,
    ) {}
}
