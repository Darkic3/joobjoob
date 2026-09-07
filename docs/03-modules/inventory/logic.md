# Inventory — Logic (Verified Audit)

> **Service:** `StockService` (218 lines) | **Model:** `InventoryTransaction` (30 lines)
> **Related:** `Product` / `ProductVariant` (stock parts), `OrderObserver`, `CancelExpiredOrders`, `OrderService` call sites
> **Migration:** `2026_08_26_000011_create_inventory_transactions_table`
> **HTTP surface:** NONE for admin (see api-contract) — pure Service+Model module.

---

## P0-02 ✅ VERIFIED FIXED

`app/Observers/OrderObserver.php` contains **NO stock logic**:
- `created()` unconditionally writes the initial `OrderHistory` (P0-01 fix, explained in code comment).
- `updated()` uses `wasChanged('order_status' | 'shipping_status_id')` for history only.
- Explicit code comment: *"Stock restoration is handled exclusively by OrderService::cancelOrder and CancelExpiredOrders command — NOT here, to avoid double restore."*

## P0-09 ✅ VERIFIED FIXED (with nuance)

`OrderService::create` wraps `Order::create` + the `OrderItem::create` loop in ONE `DB::transaction`.
`reserveStock` runs **after** it, inside its **own internal** `DB::transaction` (separate atomic unit).
On reservation failure (`RuntimeException`): `Log::warning` → order marked `'cancelled'` (never forceDelete) → rethrow.
**Nuance:** a cancelled order WITH items may remain as the audit trail of a failed reservation — intentional P0-09 behavior; the two transactions are deliberately not one giant unit.

## StockService API

- **`reserveStock(order)`** — all items in ONE `DB::transaction` → `decreaseItemStock` per item (all-or-nothing across items).
- **`restoreStock(order)`** — same shape with `increaseItemStock`. **NOT self-guarded** — idempotency lives in callers:
  - `OrderService::handleFailedPayment` — guard `order_status === 'unpaid'` return.
  - `OrderService::cancelOrder` — restores only if previous status ∈ `[paid, deposit_paid, pending_confirmation, wait_for_verification]`.
  - `CancelExpiredOrders` — query filters `status = 'paying'` upfront.
  - **Invariant for AI coders: every new caller must re-add its own guard. No ledger dedup exists.**
- **`getAvailableStock(productId, variantId?)`** — `ProductVariant`/`Product::withTrashed()->find` → `max(0, stock)`. ⚠️ `withTrashed` HERE vs global scope (no trashed) in the mutators — mismatch feeds gotcha #2.
- **`enrichCartItemsWithStockStatus(items)`** — map + `getAvailableStock` per item → sets `is_in_stock`/`current_stock`. N+1 (1 query per row). Called from `CartService::dbContent` (NOT dead code — corrects an older Products-module claim). Mutates Eloquent models in-memory.
- **`validateCartItems(items)`** — per-item `getAvailableStock` → `{unavailable[], insufficient[]}`. Consumed by `POST /checkout/validate-stock`. Same N+1.

## Mutator internals

- **`decreaseProductStock` / `increaseProductStock`** — `Product::lockForUpdate` → sufficient-stock check (decrease only; throws RuntimeException with Persian diagnostics) → `decrement`/`increment` → `InventoryTransaction` row → `AuditService::productStockChanged` (try/catch logged).
- **`decreaseVariantStock` / `increaseVariantStock`** — locks **only the variant row** → decrement/increment → re-fetch parent `Product` (**NO lock**) → `products.stock = variants()->sum('stock')` → ledger + audit.
  - INSUFFICIENT stock → throws (rolls back the whole reserve transaction).
  - MISSING product/variant row → `Log::warning` + **SILENT RETURN** (no throw) — feeds gotcha #2.

## Ledger — `inventory_transactions` (migration 000011)

Columns: `product_id?` / `product_variant_id?` / `order_id?` (FK `nullOnDelete`), `quantity` int nullable, `type` string `add|subtract`, `description` text, softDeletes, indexes `product_id` + `order_id`.
Convention: **quantity is POSITIVE; direction lives in `type`**.
**WRITE-ONLY today** — no admin UI, no report, no reader anywhere (grep: only StockService + the model itself). Before/after values live in `AuditService::productStockChanged`, not the ledger. No `user_id` (actor), no idempotency key.

## Cron interaction — `CancelExpiredOrders` (P0-06 ✅ `--dry-run` + per-order try/catch)

Fetches `status='paying'` older than `--minutes` (default 15) → per order: `update('cancelled')` → `restoreStock` → `Log::info`.
**Diverges from `OrderService::cancelOrder`:** NO `AuditService` call, NO user notification (history arrives via OrderObserver). See gotchas #1 (double-restore race) and the Notifications module (cron never notifies).

## Model casts (stock-related)

- `ProductVariant`: `final_price => float`, `stock => integer`; `scopeInStock` = `stock > 0`; discount-percent accessor.
- `Product`: `stock => integer`; `default_variant` = in-stock variant (sortByDesc stock) ?? first — matches CartService's repair logic exactly.

## Evidence

- `app/Services/StockService.php` — full audit (all 6 private mutators + 5 public methods).
- `app/Observers/OrderObserver.php` — P0-01/P0-02 verification.
- `app/Console/Commands/CancelExpiredOrders.php` — dry-run, per-order try/catch, no re-check (gotcha #1).
- `app/Services/OrderService.php` — create/handleFailedPayment/cancelOrder call sites.
- `app/Models/InventoryTransaction.php`, `database/migrations/2026_08_26_000011_*`.
