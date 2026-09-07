# Inventory — Gotchas & Known Bugs (Verified Audit)

## FIXED — remove from any "Known Bugs" list
- **OrderObserver created isDirty bug** → FIXED (P0-01: unconditional history).
- **Double restoreStock (Observer + cancelOrder)** → FIXED (P0-02: observer has no stock logic).
- ~~payment_receipt dead assignment~~ → belongs to Orders module, already fixed (P0-09 era).
- ~~Chapar authToken BROKEN~~ → belongs to Shipping module (dead code, documented there).

## 1) Cron double-restore race (NEW — most important)
`CancelExpiredOrders` re-checks nothing at processing time: an admin cancels + restores order X
between the command's fetch and its loop iteration → the cron overwrites the status (no-op) and
calls `restoreStock` AGAIN → **double increment**.
**Fix:** conditional update + restore only on success:
```php
$affected = Order::whereKey($order->id)->where('order_status', 'paying')
    ->update(['order_status' => 'cancelled']);
if ($affected === 1) { $stockService->restoreStock($order); }
```

## 2) Silent skip on missing product/variant during reserve → ghost stock
`decrease*Stock`: row not found → `Log::warning` + **return (NO throw)**. Combined with
`getAvailableStock` using `withTrashed()`:
soft-deleted product → passes cart display + `validate-stock` → order created → reserve
SILENTLY skips → customer buys an unavailable product with **zero stock movement** and only a
warning log.
**Fix:** throw on missing rows during reserve (treat like insufficient stock), or exclude
trashed from availability checks.

## 3) Parent `products.stock` lost-update drift
Variant mutators lock only the variant row; the sum re-sync writes `products.stock` **without
locking it**. Concurrent reserves on DIFFERENT variants of the same product → interleaved sums,
last-write-wins → aggregate drifts (variant rows stay correct; the cache lies).
Same family: `ProductService::aggregateVariantPrices` (admin edits).
**Fix:** `lockForUpdate` the parent product before summing, or drop the denormalized aggregate.

## 4) `restoreStock` is caller-guarded, not self-guarded (hard invariant)
No status check, no ledger dedup, no idempotency key inside the method. Guards live in
`handleFailedPayment` (unpaid check), `cancelOrder` (previous-status list), the cron (paying
filter). **Every new caller must re-implement the guard.**

## 5) Null-unsafe audit call in `decreaseVariantStock`
`$product` re-fetch is guarded with `if ($product)` for the sum update, but the audit line
outside the `if` reads `$product->stock` unguarded → property-on-null warning + junk audit
values when the parent is soft-deleted. (The throw-path uses `$product?->trans` — inconsistent.)

## 6) Ledger has no readers and no before/after snapshots
Write-only table; `stock_before/after` live in `AuditService` instead; no `user_id` (actor);
no unique idempotency key. Document before anyone builds reports on `quantity` alone.
Cheap future win: an admin ledger report would immediately surface drift (#3) and
double-restores (#1).

## 7) Cancel-path restore failure = stock leak
Both `cancelOrder` and the cron update the status FIRST and restore SECOND inside try/catch —
if restore throws, the order is already cancelled and the stock is leaked (log-only).
Ordering protects status consistency, not stock.

## STILL VALID (carried cross-module)
- N+1: `getAvailableStock` per item in `enrichCartItemsWithStockStatus` + `validateCartItems`.
- `getAvailableStock` uses `withTrashed` while mutators respect the global scope (feeds #2).

## AI Checklist before coding Inventory
- [ ] reserve throws on insufficient stock — keep it that way; never downgrade to warnings
- [ ] new restoreStock callers MUST add their own idempotency guard (#4)
- [ ] conditional status update before restore in any bulk/cron path (#1)
- [ ] parent product lock if touching the sum re-sync (#3)
- [ ] trashed products must not pass availability (#2)

## Evidence
- `app/Services/StockService.php` — silent returns (decrease*Stock), unguarded audit line, sum re-sync.
- `app/Console/Commands/CancelExpiredOrders.php` — fetch-then-loop without re-check.
- `app/Observers/OrderObserver.php` — P0-01/P0-02 fixed state.
- `app/Services/OrderService.php:cancelOrder` / `handleFailedPayment` — caller-side guards.
- `database/migrations/2026_08_26_000011_create_inventory_transactions_table.php` — ledger schema.
