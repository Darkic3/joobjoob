# Cart — Gotchas & Known Bugs (Enriched Phase 2.1)

## Critical Gotchas for AI
- **Dual storage**: Guest `session cart` (md5 rowId) vs Auth `carts` table (id PK). `isAuth()` check har request — agar be yek service `auth()->check` mock nadai test fail mishe.
- **mergeGuestCart — FIXED/VERIFIED (P1-05 ✅)**: Caller = `OtpController::verify:146`. Flow: capture `session('cart')` pre-login → OTP verify (login) → `session()->regenerate()` → `mergeGuestCart($guestCart)` → `sessionClear()` inside. Existing rows capped at `min(existing+qty, stock)`; new rows via `dbAdd`. Guest md5 rowIds translate to DB-id rowIds — frontend must treat rowId as opaque. The old "no caller / guest cart lost" claim is WRONG — remove it wherever it still appears.
- **N+1 Product::find per item**: `dbContent()` + `sessionContent()` + `enrichCartItemsWithStockStatus()` har item `Product::withTrashed()->find(product_id)` + `variants()->find` — baraye 20 item 40 query. Mitune `whereIn` she vali feli N+1 hast. Evidence `CartService.php:30-60`.
- **Price «روز» re-sync, not snapshot** (UPDATED): `refreshDbPrices` re-syncs price/original_price/stock on every authenticated read (write-on-read!) and repairs variant-less rows. Snapshot lives only between read and order creation. Cart reads must still use cart prices (not live `Product::final_price`) within a request.
- **Toman unit**: Hameye price ha toman int — ZarinPal/Chapar `.'0'` rial tabdil dar payment/shipping, na dar cart.
- **is_in_stock enrich**: `stockService->enrichCartItemsWithStockStatus` faghate namayeshi, reserve dar OrderService ba `lockForUpdate` — cart khode concurrency handle nadarad (double add oversell ta reserve mahar nemishe).
- **Session keys**: `cart`, `coupon`, `checkout_address_id`, `checkout_shipping_method_id`, `checkout_shipping_price` — in 5 key bayad ba ham bashand. `checkout.create` agar `!addressId || !shippingMethodId` -> `back error اطلاعات تکمیل نیست` (CheckoutController.php: ~170).
- **CartService singleton**: `AppServiceProvider register singleton(CartService)` — state dar request scope, na global. Test bayad app()->make fresh.

## Known Bugs (doc only, no fix)
1. **N+1 in enrich** — `getAvailableStock` = one `withTrashed()->find` per cart row (products batched). Performance, na correctness.
2. **mergeGuestCart qty=0 edge** — `min(existing+qty, stock)` with `stock=0` persists a **0-qty row** (bypasses `dbUpdate`'s `max(1,qty)` clamp) → flows into `toArray()` → `OrderService::create` → OrderItem quantity 0 → reserveStock decrements 0 + InventoryTransaction noise. Fix: skip or clamp when stock>0.
3. **`getSessionItems()` is dead code** — `CartService:386`, zero callers. Delete or wire in.
4. **`CartController::update()` missing rowId guard** — `content()->get($rowId)` null for invalid rowId → `$item['product_id']` on null (PHP 8 warning) → `getAvailableStock(0)`=0 → qty 0 → `dbUpdate(max(1,0)=1)` matches 0 rows. Silent confusing no-op. Should 404/redirect.
5. **dbAdd dedupe race — no unique constraint** — `carts` has NO unique index on (user_id, product_id, variant_id, color, size); SELECT-then-INSERT → concurrent adds duplicate rows. Fix: composite unique + upsert.
6. **Write-on-read cost** — `dbContent→refreshDbPrices` runs on EVERY auth cart read (incl. header `count()`) and may issue UPDATEs; `sessionContent` rewrites session when drifted. Intentional «قیمت روز» design — document per-request write cost.
7. **FK cascade side-effect** — `carts.product_id` cascadeOnDelete: deleting a product wipes the row from ALL users' carts; `user_id` nullOnDelete + nullable: hard-deleted user leaves orphan rows (invisible, accumulate).

## Legacy
- `Cart` model `softDeletes` nadarad — hard delete dar remove/clear.
- Guest cart structure az Laravel shoppingcart package copy shode (md5) — legacy.

## AI Checklist before touching Cart
- [ ] Checked `global-context.md` dual cart + session keys
- [ ] Verified price snapshot vs live product
- [ ] Tested guest + auth flow both
- [ ] Checked stock enrich N+1 before optimize
- [ ] mergeGuestCart IS called from OtpController::verify:146 (P1-05) — do not add another call site without a guard

## Evidence
- `app/Services/CartService.php:12 sessionKey, 16 isAuth, dbContent+refreshDbPrices, dbAdd md5(rowId unused local), 348 mergeGuestCart (caller verified), 386 getSessionItems DEAD`
- `app/Http/Controllers/Site/CartController.php:14 index guest guard, 46 add, 112 update clamp, 147 remove, 167 applyCoupon auth`
- `app/Models/Cart.php:8 fillable, no SoftDeletes`
- `routes/site.php:344 cart.* (guest allowed) + 348 cart.coupon auth + checkout group auth`
