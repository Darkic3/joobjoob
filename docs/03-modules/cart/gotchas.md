# Cart — Gotchas & Known Bugs (Enriched Phase 2.1)

## Critical Gotchas for AI
- **Dual storage**: Guest `session cart` (md5 rowId) vs Auth `carts` table (id PK). `isAuth()` check har request — agar be yek service `auth()->check` mock nadai test fail mishe.
- **mergeGuestCart UNKNOWN**: Method `CartService::mergeGuestCart(array guestItems)` vojoud darad vali `grep -rn mergeGuestCart app` faghate tarif, hich caller nist. Natije: vaghti user login mikonad (OTP verify -> Auth::login) guest cart dar session mimunad va merge nemishe — momkene gom she ya duplicate bemunad. Creative: hanuz `UNKNOWN / NEEDS VERIFICATION` behtar ast, na hads.
- **N+1 Product::find per item**: `dbContent()` + `sessionContent()` + `enrichCartItemsWithStockStatus()` har item `Product::withTrashed()->find(product_id)` + `variants()->find` — baraye 20 item 40 query. Mitune `whereIn` she vali feli N+1 hast. Evidence `CartService.php:30-60`.
- **Price snapshot**: `price = variant?final_price:product->final_price` + `original_price = variant?price:product->price` dar zaman add — taghir badie catalog price rooye cart asar nadarad. Agent nabayad `Product::final_price` ro live bekhune, bayad cart price ro bekhune.
- **Toman unit**: Hameye price ha toman int — ZarinPal/Chapar `.'0'` rial tabdil dar payment/shipping, na dar cart.
- **is_in_stock enrich**: `stockService->enrichCartItemsWithStockStatus` faghate namayeshi, reserve dar OrderService ba `lockForUpdate` — cart khode concurrency handle nadarad (double add oversell ta reserve mahar nemishe).
- **Session keys**: `cart`, `coupon`, `checkout_address_id`, `checkout_shipping_method_id`, `checkout_shipping_price` — in 5 key bayad ba ham bashand. `checkout.create` agar `!addressId || !shippingMethodId` -> `back error اطلاعات تکمیل نیست` (CheckoutController.php: ~170).
- **CartService singleton**: `AppServiceProvider register singleton(CartService)` — state dar request scope, na global. Test bayad app()->make fresh.

## Known Bugs (doc only, no fix)
1. **N+1** — bala, performance na correctness.
2. **mergeGuestCart lost** — guest cart bad az login gom.
3. **original_price backfill** per item har request — save loop if `getOriginal null`.
4. **linkable_id string** (content) not cart vali filter dar hero.

## Legacy
- `Cart` model `softDeletes` nadarad — hard delete dar remove/clear.
- Guest cart structure az Laravel shoppingcart package copy shode (md5) — legacy.

## AI Checklist before touching Cart
- [ ] Checked `global-context.md` dual cart + session keys
- [ ] Verified price snapshot vs live product
- [ ] Tested guest + auth flow both
- [ ] Checked stock enrich N+1 before optimize
- [ ] Did not invent `mergeGuestCart` call — kept UNKNOWN

## Evidence
- `app/Services/CartService.php:12 sessionKey, 16 isAuth, 26 dbContent N+1, 80 dbAdd md5, 260 mergeGuestCart no caller`
- `app/Http/Controllers/Site/CartController.php:14 index guest guard, 46 add, 112 update clamp, 147 remove, 167 applyCoupon auth`
- `app/Models/Cart.php:8 fillable, no SoftDeletes`
- `routes/site.php:344 cart.* (guest allowed) + 348 cart.coupon auth + checkout group auth`
