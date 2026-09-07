# Inventory — Dependencies (Verified Audit)

## Call graph

```
OrderService::create
  └─→ StockService::reserveStock(order)        [own DB::transaction; fail → order 'cancelled' + rethrow]
        └─→ decreaseItemStock → decreaseProductStock / decreaseVariantStock
              ├─→ Product/ProductVariant::lockForUpdate → decrement
              ├─→ InventoryTransaction::create (type 'subtract')
              └─→ AuditService::productStockChanged (try/catch)

OrderService::handleFailedPayment / cancelOrder
  └─→ StockService::restoreStock(order)        [caller-guarded — see gotchas #4]
        └─→ increaseItemStock → increaseProductStock / increaseVariantStock
              ├─→ increment (+ parent sum re-sync for variants, UNLOCKED parent — gotcha #3)
              ├─→ InventoryTransaction::create (type 'add')
              └─→ AuditService::productStockChanged

CancelExpiredOrders (cron)
  └─→ Order (status='paying' fetch) → update('cancelled') → StockService::restoreStock
       ⚠️ no re-check at processing time (gotcha #1); no AuditService; no NotificationService

CartService::dbContent        → StockService::enrichCartItemsWithStockStatus (N+1, in-memory model mutation)
CheckoutController::validateStock → StockService::validateCartItems (N+1)
ProductService (admin edits)  → writes stock directly + aggregateVariantPrices sum re-sync (drift family, gotcha #3)
```

## Consumers matrix

| Caller | Method | Guard |
|--------|--------|-------|
| `OrderService::create` | `reserveStock` | try/catch → cancelled + rethrow (P0-09) |
| `OrderService::handleFailedPayment` | `restoreStock` | `order_status !== 'unpaid'` early return |
| `OrderService::cancelOrder` | `restoreStock` | previous status ∈ paid/deposit_paid/pending_confirmation/wait_for_verification |
| `CancelExpiredOrders` | `restoreStock` | query filter `status='paying'` (stale — gotcha #1) |
| `CartService::dbContent` | `enrichCartItemsWithStockStatus` | display only |
| `CheckoutController::validateStock` | `validateCartItems` | pre-create check (advisory — race window remains) |

## Model relations

- `InventoryTransaction` belongsTo `Product`, `ProductVariant`, `Order` (morph-free, FK `nullOnDelete`).
- `ProductVariant` belongsTo `Product`; `scopeInStock`; casts `stock` int, `final_price` float.
- `Product::default_variant` accessor — in-stock variant ?? first (same semantics as CartService repair).

## Cross-module invariants

- **P0-02:** OrderObserver contains no stock logic (verified).
- **P0-09:** order+items and stock reservation are two separate atomic transactions.
- **Caller-guard rule:** `restoreStock` never self-guards (#4).
- Ledger is write-only (#6) — audit before/after values live in `AuditService`.

## Evidence

- `app/Services/StockService.php` (full), `app/Observers/OrderObserver.php`,
  `app/Console/Commands/CancelExpiredOrders.php`, `app/Services/OrderService.php`,
  `app/Models/InventoryTransaction.php`, `app/Models/Product.php:236-244`,
  `app/Models/ProductVariant.php:16-58`, `database/migrations/2026_08_26_000011_*`.
