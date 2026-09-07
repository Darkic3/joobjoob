# Orders — Gotchas & Known Bugs

> **Source:** `app/Models/Order.php:114`, `database/migrations/2026_08_26_000008_create_orders_table.php:18`, `app/Observers/OrderObserver.php:14`, `app/Services/OrderService.php:83,122`, `app/Services/BankPaymentService.php:35`, `app/Services/StockService.php`, `app/Payments/ZarinPal.php:20`, `app/Services/CartService.php:290`, `routes/console.php`
> **Desc:** 7 status dar code vali 6 ta dar migration؛ toman string price؛ rial x10؛ va ...
> **UPDATE (verified audit):** P0-01 (Observer isDirty), P0-02 (double restoreStock), P0-03 (delete on token fail), P0-09 (forceDelete) — HAME FIx SHODEAND. Partials belahar.

## CRITICAL — Bayad Avval Khund

### 1) Migration comment `pending_confirmation` nadarad — 7 vs 6 divergence

- **Code:** `Order.php:getStatusAttribute` 7 value (`paying, paid, deposit_paid, wait_for_verification, pending_confirmation, unpaid, cancelled`) + `OrderHistory:getStatusNameAttribute` ham 7.
- **Migration:** `2026_08_26_000008_create_orders_table.php:18 comment 'paying|paid|deposit_paid|unpaid|cancelled|wait_for_verification'` — **`pending_confirmation` ja oftade.**
- **Risk:** AI ya DBA agar comment ra source truth begirad, `pending_confirmation` ra invalid mifahmad. هچ check constraint ham nadarad (string nullable) pas DB har string ra mipasirad, vali doc gumrah mikonad.
- **Evidence:** `Order.php:114 match pending_confirmation`, `migration:18 comment missing`, `OrderSeeder: pending_confirmation estefade shode`.

### 2) Double `restoreStock` — FIXED ✅ (P0-02)

**Previously:** Observer + cancelOrder (+ cron) both ran `restoreStock` → stock 2x increment.

**Now (verified in code):** `OrderObserver::updated` only writes `OrderHistory` when
`wasChanged('order_status') || wasChanged('shipping_status_id')` — **NO restoreStock anywhere
in the observer**. Code comment states it explicitly: stock restoration is handled exclusively
by `OrderService::cancelOrder` and `CancelExpiredOrders`.
- `cancelOrder`: update cancelled → audit → notify → restoreStock (conditional on previous status).
- `handleFailedPayment`: guard `unpaid` early-return → restoreStock (no double).
- Cron: filter `paying` upfront → restoreStock once.
⚠️ Residual (Inventory module gotcha #1): the cron re-checks nothing at PROCESSING time —
a concurrent admin cancel between fetch and loop still double-restores. Conditional update needed.

### 3) `OrderObserver::created` — FIXED ✅ (P0-01)

**Previously:** `isDirty()` in `created` was always false (model synced after INSERT) → no
initial history for new orders.

**Now (verified in code):** `created()` creates `OrderHistory` **unconditionally**:
```php
public function created(Order $order): void {
  OrderHistory::create([
    'order_id' => $order->id,
    'order_status' => $order->order_status,
    'shipping_status_id' => $order->shipping_status_id,
  ]);
}
```
`updated()` uses `wasChanged()` (correct post-save check) — explanatory comment in code.

### 4) Hard delete vs Soft delete inconsistency — FIXED ✅ (P0-09 + P0-03)

**Previously:** reserve-fail path used `forceDelete()`; token-fail used `delete()`.

**Now (verified in code):**
| Path | Old | New |
|------|-----|-----|
| Stock insufficient (`OrderService::create`) | `forceDelete()` | order marked `cancelled` + rethrown (audit trail preserved, P0-09) |
| Bank token failed (`BankPaymentService::handlePayment`) | `delete()` | `unpaid` + restoreStock + audit (P0-03) |

**No delete of any kind remains in order failure paths.** Order + items creation is wrapped in
`DB::transaction` (P0-09); reservation runs in its own transaction afterwards.
⚠️ Reminder: `Order::withTrashed` still needed to see any historically soft-deleted orders.

## DATA GOTCHAS

### 5) Price ha string hastand — toman as string

- **Schema:** `shipping_price, total_price, discount_price, payment_price, deposit_price, remaining_price` hame `string` (migration string nullable, comment 'toman').
- **Code:** `OrderService::checkout` => `intval(str_replace(',','', $order->deposit_price))` ya `payment_price` — agar string ba comma ya khali bashad, parse manual ast. `number_format(payment_price)` dar notification ham string ra format mikonad.
- **Risk:** `payment_price=''` ya `' , '` => `intval('')=0` => free order branch ghalat trigger. `deposit_price` ham hamintor.
- **Evidence:** `migration string`, `OrderService:124-128 price parse`, `ZarinPal:20 rial x10`.

### 6) Toman -> Rial `intval($amount.'0')` x10

- `ZarinPal.php:20 getBankToken` + `verifyTransaction` => `$amountRial = intval($amount.'0')` — toman *10 na *100. Agar toman=rial eshtebah shavad, 10x overcharge/undercharge.
- Chapar shipment ham hamin pattern darad (cross-module).
- **Evidence:** `Payments/ZarinPal.php:20,55`.

### 7) `OrderItem.price == discounted_price == cart.price`

- `OrderService:68-77` => `price = item['price']`, `discounted_price = item['price']` — har do yeki. `item['price']` az `CartService::dbAdd` = `variant.final_price` ya `product.final_price` (ba'd az discount). Takhfif radifi zakhire nemishavad, faghat `Order.discount_price` aggregate.
- **Risk:** Report radifi `price - discounted_price` hamishe 0. Agar AI bekhahad radifi discount hesab konad, bayad `original_price` az Cart begirad (vali zakhire nemishavad).
- **Evidence:** `OrderService:74-75`, `CartService dbAdd final_price`.

### 8) `payment_receipt` dead write — FIXED ✅

**Previously:** `OrderService:64` wrote `payment_receipt` against a dropped column.
**Now (verified):** `OrderService::create` writes `receipt_tracking_code` / `receipt_payer_card`
(matching the `2026_08_31` migration). No dead assignment remains.

### 9) `address` JSON snapshot + FK hamzaman

- `Order` ham `address_id FK` darad ham `address JSON` (state,city,address,receiptor_mobile,postal_code) snapshot. Agar Address bad az sefaresh edit shavad, JSON snapshot ghadimi mimanad (dorost) vali `location()` relation address jadid ra neshun midahad — divergence.
- **Evidence:** `Order fillable address array cast`, `CheckoutController:205-215 build address snapshot`.

## LOGIC TRAPS

### 10) No state machine — any->any via admin

- `Admin::changeOrderStatus` hich validate enum ya transition guard nadarad — `order_status` har string ra `update` mikonad. Mitavan `cancelled -> paid` kard bedun pardakht.
- Bayad AI guard nasazad, vali dar test bayad invalid transition ra expect konad ke alan pass mishavad.
- **Evidence:** `Admin OrderController:78 changeOrderStatus no rules`.

### 11) `mergeGuestCart` — VERIFIED (P1-05 ✅) — OTP is the ONLY login flow

- `CartService::mergeGuestCart` IS called from `OtpController::verify:146` — flow verified
  end-to-end (capture pre-login → verify → regenerate → merge → sessionClear).
- **Correction:** the old doc claimed a "normal password login" that skips merge — **password
  login DOES NOT EXIST in this codebase**. OTP via Kavenegar is the only authentication path,
  so ALL logins merge the guest cart.
- Full merge internals: see Cart module docs (incl. qty=0 edge-case gotcha).

### 12) `CartService` session vs DB dual — `weight` az DB copy

- `CartService::dbAdd` weight ra az `variant.weight ?? product.weight ?? 0` dar `carts` copy mikonad. `OrderService::create` weightSum ra az `cartItems[].weight` jama mikonad. Agar product weight bad az add be cart avaz shavad, order weight ghalat ast.
- `roundedWeight ceil(weight/100)*100` baraye `ShippingCalculator` estefade mishavad — 0~100g ham 100g hesab.
- **Evidence:** `CartService dbAdd weight`, `OrderService 36 weightSum`, `CheckoutController 44 roundedWeight`.

### 13) Callback lookup index nadarad — JSON field scan

- `OrderService::callback` => `Order::where('transaction_info->post->Authority', $value)` — MySQL JSON extract, **index nadarad**. Ba 100k order scan sangin.
- 4 gateway 3 field mokhtalef: `Authority, token, id, trackId` — Saman/Sadad ham `id` straight.
- **Evidence:** `OrderService:168 findByField`, `routes finish*`.

### 14) `deposit_price` vs `payment_price` branching — toman string + null

- `checkout` => `intval(str_replace(',','', deposit_price))!==0 ? deposit_price : payment_price` — agar deposit_price `null` ya `''` => `intval('')=0` => fallback be payment_price. Agar har do 0 va `discount_id != null` => `handleFreeOrder` => `paid` bedoone verify.
- **Risk:** Discount 100% ba discount_id majazi => free order.
- **Evidence:** `OrderService:122-128`.

### 15) Cron `everyFiveMinutes --minutes=15` — window 10min gap

- `routes/console.php: Schedule::command('orders:cancel-expired --minutes=15')->everyFiveMinutes()->withoutOverlapping()` — har 5min `paying` haye >15min ghabl cancel. Order 14:59 sakhte => 15:00 cron ejra (age 1min) cancel nemishavad, 15:05 cancel (20min wait). Avg 17.5min, max 20min. `withoutOverlapping` agar job >5min tul bekeshad skip mishavad (vali job sari ast).
- **Evidence:** `routes/console.php`, `CancelExpiredOrders --minutes=15`.

### 16) `validateStock` faghat `unavailable` ra hazf mikonad

- `CheckoutController::validateStock` => `validateCartItems` => `insufficient` (stock kam vali >0) **hazf nemishavad** — faghat `unavailable (stock<=0)` remove. `insufficient` dar response mimanad vali user mitavanad baz `POST /checkout/create` bezanad va ba `RuntimeException` movajeh shavad.
- **Evidence:** `CheckoutController:260 validateStock remove loop only unavailable`.

## SECURITY / SIDE EFFECTS

- `transaction_info` JSON `post+verify` loge Authority/RefID — hich `password` filter nist vali `RequestLog` faghat `password` ra filter mikonad — card data leak potential (cross-module gotcha).
- Callback `ANY` bedoone `throttle` ya `signed` — spam verify.
- `Auth::loginUsingId(order.user_id)` dar `handlePayment/handleVerify` — session hijack agar callback ba Authority hads zade shavad (Authority random 36 char ZarinPal, risk kam).
- `InventoryTransaction` description Farsi — agar locale avaz shavad search dushvar.

## AI Checklist — Qabl az dast zadan be Orders

- [ ] `Order.php:getStatusAttribute` 7 status ra check kardam — `pending_confirmation` ra az migration comment nakhandam
- [x] `OrderObserver::created` FIXED (P0-01) — unconditional history; `updated` uses wasChanged
- [x] Double restoreStock FIXED (P0-02) — observer stock-free; cron re-check race documented in Inventory gotcha #1
- [ ] `forceDelete` vs `delete` tafaavot ra daram — soft-deleted order ra ba `withTrashed` peyda mikonam
- [ ] Toman string price ra `intval(str_replace(',','', ...))` parse kardam, na direct int cast
- [ ] Rial x10 `.'0'` ra faramush nakardam
- [x] `mergeGuestCart` VERIFIED (P1-05) — OTP is the only login; no password login exists
- [ ] `discount_id && price<=0` free order branch ra test kardam
- [ ] Cron path `routes/console.php` ast na `Kernel.php`
- [ ] Callback 4 gateway field mapping ra az jadval api-contract khandam

## Evidence

- `app/Models/Order.php:114 getStatusAttribute 7`, `OrderHistory.php match`, `migration:18 missing pending_confirmation`
- `app/Observers/OrderObserver.php` — FIXED (P0-01/P0-02): unconditional created-history, wasChanged history-only updated
- `app/Services/OrderService.php` — 124 toman parse, cancelOrder restore, callback lookup, create DB::transaction (P0-09)
- `app/Services/BankPaymentService.php` — handlePayment failure branch FIXED (P0-03: restoreStock→unpaid→audit, no delete)
- `app/Services/StockService.php:24 DB::transaction lockForUpdate, RuntimeException`
- `app/Payments/ZarinPal.php:20 rial x10, 38 code 100, 75 code 100||101`
- `app/Services/CartService.php:290 mergeGuestCart`, `app/Http/Controllers/Site/OtpController.php:139`, `SiteAuthController no merge`
- `routes/console.php everyFiveMinutes --minutes=15`, `CancelExpiredOrders:16 paying cutoff`
- `database/migrations/2026_08_31_000001 receipt_tracking_code`
