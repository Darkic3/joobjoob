# Order Lifecycle

> **Source:** `app/Models/Order.php:114`, `OrderService.php`, `OrderObserver.php`, `CancelExpiredOrders.php`, `Admin/OrderController.php`

## Statuses (7, no enum, no state machine)
`paying` -> initial
`paid` / `deposit_paid` -> after successful verify (deposit if deposit_price !=0)
`pending_confirmation` -> card-to-card
`wait_for_verification` -> legacy? used in newOrdersCount
`unpaid` -> after failed verify
`cancelled` -> via admin, observer, or auto-cancel

Evidence: `Order.php getStatusAttribute match` + `OrderService 68,146,156,195,230` + `Admin OrderController newOrdersCount wait_for_verification/pending_confirmation` + migration comment.

## Transitions (no guard, any->any via admin)
- paying -> pending_confirmation (card_to_card)
- paying -> paid/deposit_paid (zarinpal success, free order)
- paying -> unpaid (verify failed)
- paying -> cancelled (auto-cancel after 15min, cron every 5min)
- paid/deposit_paid/pending_confirmation/wait_for_verification -> cancelled (OrderObserver + cancelOrder) -> restoreStock
- Any -> any via `Admin/OrderController::changeOrderStatus` (no validation)

## Side effects table
| Event | Effect |
|-------|--------|
| Order::create | reserveStock (lockForUpdate, transaction), Audit orderCreated, notifyUser+notifyAdmins, OrderHistory intended but bugged (isDirty false) |
| updated order_status/shipping_status_id | OrderHistory create |
| wasChanged cancelled && prev in [paid,deposit_paid,pending_confirmation,wait_for_verification] | StockService::restoreStock (Observer AND cancelOrder — double risk) |
| handleFailedPayment | update unpaid + restoreStock |
| CancelExpiredOrders (every 5min, --minutes=15) | where paying && created_at < cutoff -> update cancelled + restoreStock |

## Cron
`Schedule::command('orders:cancel-expired')->everyFiveMinutes()` — `app/Console/Kernel? routes/console.php`

## AI gotcha
- No state machine — must not invent guard.
- Observer created bug: isDirty always false -> no history for new order.
- Double restore possible.
- Hard delete on token failure (not cancelled).
