# Inventory Reserve / Restore Workflow

> **Source:** `app/Services/StockService.php`, `OrderService.php:90-95`, `OrderObserver.php`

## Overview
Stock is reserved at order creation (paying) and restored on cancel/fail.

## Reserve
`OrderService::create -> try stockService->reserveStock(order) catch RuntimeException -> forceDelete order + throw`
`StockService::reserveStock(Order)`:
  `DB::transaction foreach items = order.items()->get() -> decreaseItemStock(product_id, variant_id, qty, orderId)`
`decreaseProductStock`:
  `Product::where(id)->lockForUpdate()->first` -> if stock < qty throw -> decrement stock -> InventoryTransaction create type=subtract -> Audit productStockChanged
`decreaseVariantStock`:
  `ProductVariant::where(id)->lockForUpdate()->first` -> if stock < qty throw -> decrement variant stock -> recalc parent `product.stock = variants()->sum(stock)` -> InventoryTransaction with variant_id

## Restore
`StockService::restoreStock` similar `DB::transaction foreach -> increaseItemStock`
`increaseProductStock`: lockForUpdate -> increment -> type=add
`increaseVariantStock`: increment variant + recalc parent sum

Callers of restore: `OrderService::handleFailedPayment`, `OrderService::cancelOrder`, `OrderObserver::updated wasChanged cancelled`, `CancelExpiredOrders` — **double restore risk** if both Observer and Service called.

## N+1 / Race
- `getAvailableStock` uses `withTrashed()->find` per item — N+1 but inside transaction with lock.
- Race mitigated by lockForUpdate but CartService has no atomic validation before create.

## InventoryTransaction
`inventory_transactions: product_id, product_variant_id nullable, quantity, type add/subtract, order_id, description, softDeletes` — log only.

## AI gotcha
- Product and Variant stock handled differently; variant needs parent recalc.
- Use lockForUpdate; don't invent repository.
- Restore only if previous status in [paid,deposit_paid,pending_confirmation,wait_for_verification] (Observer guard).
