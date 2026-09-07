# Inventory — API Contract (Verified Audit)

> ⚠️ The old doc listed a phantom `/admin/inventory` CRUD resource — **no such controller or routes exist**. Inventory has no admin HTTP surface; it is a Service+Model module.

## HTTP surface (verified: none for admin)

Stock visibility rides on other modules' endpoints:

| Method | URI | Name | Consumes | Notes |
|--------|-----|------|----------|-------|
| POST | `/checkout/validate-stock` | `checkout.validate-stock` | `StockService::validateCartItems` (auth) | JSON `{success, unavailable[], insufficient[]}` |
| GET | `/cart` | `cart.index` | `CartService::dbContent` → `enrichCartItemsWithStockStatus` | per-row `is_in_stock` / `current_stock` |
| — | product listings | — | `Product::stock`, `ProductVariant::scopeInStock` | `stock > 0` filter |

## Ops surface

```
php artisan orders:cancel-expired {--minutes=15 : انتظار} {--dry-run : بدون اجرا}
```
Cancels `paying` orders older than the cutoff and restores stock (P0-06: dry-run + per-order
try/catch). ⚠️ See gotchas #1 (double-restore race) and Notifications (cron never notifies the user).

## No CRUD exists for

- `inventory_transactions` ledger — write-only, no reader UI (see gotchas #6).
- Manual stock adjustments — done indirectly via `ProductService` (admin product edit) and
  `aggregateVariantPrices` (variant-sum re-sync), which share the drift risk in gotchas #3.
