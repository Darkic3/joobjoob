# Orders — Gotchas & Known Bugs

> **Source:** `app/Models/Order.php:114`, `database/migrations/2026_08_26_000008_create_orders_table.php:18`, `app/Observers/OrderObserver.php:14`, `app/Services/OrderService.php:83,122`, `app/Services/BankPaymentService.php:35`, `app/Services/StockService.php`, `app/Payments/ZarinPal.php:20`, `app/Services/CartService.php:290`, `routes/console.php`
> **Desc:** 7 status dar code vali 6 ta dar migration؛ double restoreStock؛ Observer created isDirty bug؛ hard/soft delete inconsistency؛ toman string price؛ rial x10؛ mergeGuestCart UNKNOWN؛ va ...

## CRITICAL — Bayad Avval Khund

### 1) Migration comment `pending_confirmation` nadarad — 7 vs 6 divergence

- **Code:** `Order.php:getStatusAttribute` 7 value (`paying, paid, deposit_paid, wait_for_verification, pending_confirmation, unpaid, cancelled`) + `OrderHistory:getStatusNameAttribute` ham 7.
- **Migration:** `2026_08_26_000008_create_orders_table.php:18 comment 'paying|paid|deposit_paid|unpaid|cancelled|wait_for_verification'` — **`pending_confirmation` ja oftade.**
- **Risk:** AI ya DBA agar comment ra source truth begirad, `pending_confirmation` ra invalid mifahmad. هچ check constraint ham nadarad (string nullable) pas DB har string ra mipasirad, vali doc gumrah mikonad.
- **Evidence:** `Order.php:114 match pending_confirmation`, `migration:18 comment missing`, `OrderSeeder: pending_confirmation estefade shode`.

### 2) Double `restoreStock` — Observer + cancelOrder (+ cron)

- **Flow A:** `Admin/OrderController::changeOrderStatus` -> agar `cancelled` => `OrderService::cancelOrder` => `order.update(cancelled)` + `restoreStock` (in_array previous paid/deposit_paid/pending_confirmation/wait_for_verification).
- **Observer:** `OrderObserver::updated` => `wasChanged(order_status) && cancelled && getOriginal in [paid,...]` => dobare `restoreStock`.
- **Natije:** `cancelOrder` update mikonad => Observer fire mishavad => stock **2 bar** `increment` + `InventoryTransaction type=add` 2 bar. Mojudi 2x barmigardad.
- **Flow B (cron):** `CancelExpiredOrders::handle` => `order.update(cancelled)` + `restoreStock` => baz ham Observer fire => 2x.
- **Fix (faqat doc, no code):** Ya `cancelOrder` bayad `withoutEvents` update konad, ya Observer bayad idempotency check (InventoryTransaction already exists) dashte bashad, ya cron bayad `cancelOrder` seda bezanad na direct update.
- **Evidence:** `OrderService:230 cancelOrder restoreStock`, `OrderObserver:30-40 wasChanged restoreStock`, `CancelExpiredOrders:40-50 update+restoreStock`.

### 3) `OrderObserver::created` hichvaght history nemisazad — `isDirty` bug

```php
public function created(Order $order): void {
  if($order->isDirty('order_status') || $order->isDirty('shipping_status_id')) // always false!
    OrderHistory::create(...)
}
```

- `created` **bad az INSERT** seda zade mishavad — model dg dirty nist (`syncOriginal` shode). Shart hamishe `false` => hich history baraye sefaresh jadid sakhte nemishavad.
- `updated` dorost ast (qabl az sync dirty true ast).
- **Natije:** `histories` baraye `paying` aval khali ast؛ admin timeline napadid.
- **Evidence:** `OrderObserver:14-22`.

### 4) Hard delete vs Soft delete inconsistency

| Path | Code | Type |
|------|------|------|
| Stock insufficient | `OrderService::create catch RuntimeException => $order->forceDelete()` | **hard** (bypass softDeletes, InventoryTransaction ham nist) |
| Bank token failed | `BankPaymentService::handlePayment if failed => $order->delete()` | **soft** (trash) |
| - | `Order` model `use SoftDeletes` | soft expected |

- `forceDelete` dar reserve fail: order az DB pak mishavad — audit/log ham mimanad vali order ghabel bazgasht nist. Agar `forceDelete` nakard, `paying` order ba stock nakafi baghi mimanad.
- `delete` dar token fail: soft — admin mitavanad trash ra bebinad.
- **AI trap:** Agar `Order::withTrashed` nazani, force-deleted order ra hichvaght peida nakhahi kard.
- **Evidence:** `OrderService:83 forceDelete`, `BankPaymentService:35 delete`.

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

### 8) `payment_receipt` dead write

- `OrderService:64 'payment_receipt' => $orderData['payment_receipt']??null` — vali `2026_08_31_000001_replace_payment_receipt_with_tracking_fields` in column ra drop karde va `receipt_tracking_code/receipt_payer_card` gozashte. Assignment silent ignore (fillable nist ya column nist).
- **Evidence:** `OrderService:64`, `migration 2026_08_31`.

### 9) `address` JSON snapshot + FK hamzaman

- `Order` ham `address_id FK` darad ham `address JSON` (state,city,address,receiptor_mobile,postal_code) snapshot. Agar Address bad az sefaresh edit shavad, JSON snapshot ghadimi mimanad (dorost) vali `location()` relation address jadid ra neshun midahad — divergence.
- **Evidence:** `Order fillable address array cast`, `CheckoutController:205-215 build address snapshot`.

## LOGIC TRAPS

### 10) No state machine — any->any via admin

- `Admin::changeOrderStatus` hich validate enum ya transition guard nadarad — `order_status` har string ra `update` mikonad. Mitavan `cancelled -> paid` kard bedun pardakht.
- Bayad AI guard nasazad, vali dar test bayad invalid transition ra expect konad ke alan pass mishavad.
- **Evidence:** `Admin OrderController:78 changeOrderStatus no rules`.

### 11) `mergeGuestCart` — UNKNOWN / conditional

- `CartService::mergeGuestCart(array $guestItems)` vojood darad va `OtpController:139` seda mizanad (`app(CartService::class)->mergeGuestCart($guestCart)` bad az OTP verify).
- **UNKNOWN:** `SiteAuthController::login` (normal password login) `mergeGuestCart` seda **nemizanad** — faghat OTP flow merge mikonad. Agar user ba password login konad, cart session mehman gom mishavad (`sessionClear` dar merge nist vali guest items merge nemishavand).
- **Risk:** AI agar fekr konad hame login ha merge mikonand, eshtebah ast.
- **Evidence:** `CartService:290 mergeGuestCart`, `OtpController:139 call`, `SiteAuthController no merge`.

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
- [ ] `OrderObserver::created` bug ra fix nakardam (doc only) — history jadid ra expected nist
- [ ] `restoreStock` ra 2 bar seda nazadam — test double restore ra assert kardam
- [ ] `forceDelete` vs `delete` tafaavot ra daram — soft-deleted order ra ba `withTrashed` peyda mikonam
- [ ] Toman string price ra `intval(str_replace(',','', ...))` parse kardam, na direct int cast
- [ ] Rial x10 `.'0'` ra faramush nakardam
- [ ] `mergeGuestCart` faghat OTP flow ast — normal login merge nadarad
- [ ] `discount_id && price<=0` free order branch ra test kardam
- [ ] Cron path `routes/console.php` ast na `Kernel.php`
- [ ] Callback 4 gateway field mapping ra az jadval api-contract khandam

## Evidence

- `app/Models/Order.php:114 getStatusAttribute 7`, `OrderHistory.php match`, `migration:18 missing pending_confirmation`
- `app/Observers/OrderObserver.php:14 isDirty bug, 30 wasChanged double restore`
- `app/Services/OrderService.php:83 forceDelete, 124 toman parse, 146 cancelOrder restore, 168 callback lookup`
- `app/Services/BankPaymentService.php:35 delete soft, 20-60 handlePayment`
- `app/Services/StockService.php:24 DB::transaction lockForUpdate, RuntimeException`
- `app/Payments/ZarinPal.php:20 rial x10, 38 code 100, 75 code 100||101`
- `app/Services/CartService.php:290 mergeGuestCart`, `app/Http/Controllers/Site/OtpController.php:139`, `SiteAuthController no merge`
- `routes/console.php everyFiveMinutes --minutes=15`, `CancelExpiredOrders:16 paying cutoff`
- `database/migrations/2026_08_31_000001 receipt_tracking_code`
