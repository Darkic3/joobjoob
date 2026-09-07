# Events & Listeners — Map Kamel

> **Source of Truth:** `app/Events/` , `app/Listeners/` , `app/Providers/EventServiceProvider.php` dar project **vojoud nadarad** (`ls -R app` — 2026-09-04 verify). Hich `event(new X)` ya `#[AsListener]` ya `ShouldQueue` dar `app/` dispatch nemishavad. Kolle flow-haye event-driven be surat **SYNC EXECUTION** tavassote direct service call + yek `Eloquent Observer` piyasi sazi shode. In file daghighan moshakhas mikonad kodum dispatch kojast, payload chist, va kodum method naghsh-e Listener ra bazi mikonad.

## 1. Natije Audit — Hich Event/Listener Class Vojoud Nadarad

| Check | Natije | Evidence |
|-------|--------|----------|
| `app/Events/*.php` | ❌ Directory vojoud nadarad | `ls app/Events → No such file` |
| `app/Listeners/*.php` | ❌ Directory vojoud nadarad | `ls app/Listeners → No such file` |
| `app/Providers/EventServiceProvider.php` | ❌ File vojoud nadarad | `ls app/Providers → faghat AppServiceProvider.php` |
| `#[AsListener]` attribute | ❌ 0 estefade | `grep -rn AsListener app/ → 0` |
| `event()` / `Event::dispatch()` | ❌ 0 dispatch | `grep -rn "event(" app/ → 0` |
| `ShouldQueue` / `Dispatchable` | ❌ 0 estefade | `grep -rn ShouldQueue app/ → 0` |
| `OrderObserver` | ✅ Yegane observer-e sabt shode | `AppServiceProvider::boot() → Order::observe(OrderObserver::class)` |

**Natijeh baraye Vibe Coding:** vaghti AI mikhahad side-effect ezafe konad (masalan SMS bad az `order.paid`), nabayad `Event` jadid besazad va entezar dashte bashad auto-discover shavad — bayad mostaghim dar `OrderService` (ya Observer) call sync ezafe konad, daghighan mesle olgouye mojoud.

---

## 2. Observer — Yegane Event Dispatcher-e Fe'li

### 2.1 Sabt

```php
// app/Providers/AppServiceProvider.php: boot()
Order::observe(OrderObserver::class);
```

### 2.2 `OrderObserver` — Payload va Listeners

**File:** `app/Observers/OrderObserver.php` — DI: `StockService` via constructor.

| Hook | Payload (constructor args / properties) | Shart-e Ejra | Kar-e Listener (SYNC EXECUTION) |
|------|----------------------------------------|--------------|---------------------------------|
| `created(Order $order)` | `Order $order` — model-e taze sakhte shode ba `$order->order_status`, `$order->shipping_status_id` | `isDirty('order_status') \|\| isDirty('shipping_status_id')` (dar `created` hame field-ha dirty hastand → hamishe true) | `OrderHistory::create([order_id, order_status, shipping_status_id])` |
| `updated(Order $order)` | `Order $order` — model-e update shode; `getOriginal('order_status')` dar dast | 1) `isDirty('order_status') \|\| isDirty('shipping_status_id')` → `OrderHistory::create(...)` <br> 2) `wasChanged('order_status') && order_status==='cancelled' && previous∈[paid,deposit_paid,pending_confirmation,wait_for_verification]` → `StockService::restoreStock($order)` (try/catch + Log::error) | 2 side-effect-e joda dar yek hook |

> **Double-restore gotcha:** `OrderService::cancelOrder()` khodash `restoreStock()` ra seda mizanad **va** `OrderObserver::updated()` ham dobare `restoreStock()` ra seda mizanad. Har do `try/catch` shode vali duplicate `InventoryTransaction` ijad mishavad — dar `docs/03-modules/orders/gotchas.md` sabt shode.

---

## 3. Event Map — Har Dispatch-e Manteghi ba Payload va Listeners

> Har radif yek "event"e manteghist ke dar code ba `try { Service::method() } catch` mostaghim seda zade mishavad — **SYNC EXECUTION**, hich queue nist. Soton-e Dispatch daghighan file:line ra moshakhas mikonad.

### 3.1 Order Created — `OrderService::create()` (app/Services/OrderService.php:86-130)

**Dispatch point:** `OrderService::create(array $cartItems, array $orderData, ?string $userDescription): Order` — bad az `Order::create()` + `OrderItem::create()` loop.

| Step | Payload | Listener (method) | Table/Effect |
|------|---------|-------------------|--------------|
| 1 | `Order $order` (ba `user_id`, `payment_price`, `address` array, `shipping_status_id`) | `StockService::reserveStock($order)` — `DB::transaction` + `lockForUpdate` + `decrement stock` + `InventoryTransaction type=subtract` | `products.stock`, `product_variants.stock`, `inventory_transactions` |
| 2 | `Order $order` | `AuditService::orderCreated($order)` — `event='order.created'`, `new_values=[order_status,total_price,discount_price,payment_price,shipping_price]`, `meta=[items,items_count,total_items_qty,shipping_method,bank,discount_code,address]` | `audit_logs` |
| 3 | `User $order->user`, `type='order.created'`, `title='سفارش شما ثبت شد'`, `body="سفارش #{$id} به مبلغ {$paymentPrice} تومان"`, `link=route('dashboard.order-detail',$id)`, `data=[order_id,amount]` | `NotificationService::notifyUser()` — `Notification::create(notifiable_type=User::class, notifiable_id, type, title, body, icon=📦, color=accent)` | `notifications` |
| 4 | `type='order.new'`, `title='سفارش جدید ثبت شد'`, `body="سفارش #{$id} از {$user->name} ..."`, `link=route('admin.orders.show',$id)`, `data=[order_id,user_name,amount]` | `NotificationService::notifyAdmins()` — loop `User::where(is_admin,is_active)->get()` + `Notification::create` per admin | `notifications` × N_admin |

**Error handling:** reserveStock agar `RuntimeException` (stock na kafi) → `Log::warning` + `$order->forceDelete()` + `throw $e` (order kamelan hazf). Audit/Notification har kodam `try/catch Throwable → Log::error` va flow ra nemishkanand.

### 3.2 Checkout — `OrderService::checkout()` (app/Services/OrderService.php:140-165)

**Dispatch:** `CheckoutController::create()` → `OrderService::checkout(Order $order): RedirectResponse`

| Branch | Payload | Effect |
|--------|---------|--------|
| `bank_type==='card_to_card'` | `Order $order` | `order_status='pending_confirmation'` (update sync), redirect `checkout.success` — hich notification injā (notification-e card_to_card dar `DashboardController::updateReceipt` ast) |
| `discount_id!==null && price<=0` (free order) | `Order $order` | `order_status='paid'` via `handleFreeOrder()` |
| ZarinPal | `Bank $bank, Order $order, int $price` | `BankPaymentService::handlePayment()` → `ZarinPal::getBankToken()` → `transaction_info.post=[Authority]` → `header(Location: StartPay/Authority)` |

### 3.3 Payment Verified Success — `OrderService::handleSuccessOrderPayment()` (app/Services/OrderService.php:190-225)

**Dispatch point:** `OrderService::callback(field, value, data)` → `BankPaymentService::handleVerify()` true → `handleSuccessOrderPayment()`

| Step | Payload | Listener |
|------|---------|----------|
| 1 | `Order $order`, `oldStatus`, `newStatus='paid'|'deposit_paid'` | `AuditService::orderStatusChanged($order, $oldStatus, $newStatus, 'سیستم - پرداخت موفق')` → `audit_logs event=order.status_changed` |
| 2 | `type='order.paid'` | `NotificationService::notifyUser(user, 'order.paid', 'پرداخت سفارش تایید شد', ...)` |
| 3 | `type='order.paid_admin'` | `NotificationService::notifyAdmins('order.paid_admin', ...)` |

### 3.4 Payment Failed — `OrderService::handleFailedPayment()` (app/Services/OrderService.php:227-243)

| Step | Payload | Listener |
|------|---------|----------|
| 1 | `oldStatus → 'unpaid'` | `AuditService::orderStatusChanged(..., 'unpaid', 'سیستم - پرداخت ناموفق')` |
| 2 | `Order $order` | `StockService::restoreStock($order)` — `increment stock` + `InventoryTransaction type=add` |

### 3.5 Order Cancelled — `OrderService::cancelOrder()` + `OrderObserver::updated` (app/Services/OrderService.php:245-270)

| Step | Payload | Listener |
|------|---------|----------|
| 1 | `previousStatus → 'cancelled'` | `AuditService::orderStatusChanged(..., 'cancelled', Auth::user()->name ?? 'سیستم')` |
| 2 | `type='order.cancelled'` | `NotificationService::notifyUser(..., 'سفارش شما لغو شد')` |
| 3 | `previousStatus∈[paid,deposit_paid,pending_confirmation,wait_for_verification]` | `StockService::restoreStock()` (SYNC) + duplicate dar Observer |

### 3.6 OTP / New User — `OtpService::send()` (app/Services/OtpService.php:70-130)

| Step | Payload | Listener |
|------|---------|----------|
| 1 | `User $user` (jadid) | `WelcomeDiscountService::createForUser($user)` → `Discount code=WELCOME-XXXXXX, 10%, 30 days, first_purchase=true` → `discounts` + `discount_user` pivot |
| 2 | `type='discount.welcome'` | `NotificationService::notifyUser(user, 'discount.welcome', 'کد تخفیف خوشامدگویی', ...)` |
| 3 | `mobile, code, siteName` | `Sms::sendLookup(template, [token=>code, token2=>siteName], mobile)` — SYNC cURL be Kavenegar |

### 3.7 Contact Message — `PageController::sendContact()` (app/Http/Controllers/Site/PageController.php:40-60)

| Payload | Listener |
|---------|----------|
| `validated=['name','email','phone','message']`, `type='contact.new'` | `NotificationService::notifyAdmins('contact.new', 'پیام تماس جدید', ...)` |

### 3.8 Review Created — `ProductController::storeReview()` (app/Http/Controllers/Site/ProductController.php:355-370)

| Payload | Listener |
|---------|----------|
| `type='review.new'`, `product_id`, `reviewer` | `NotificationService::notifyAdmins('review.new', 'نظر جدید ثبت شد', ...)` |

### 3.9 Card-to-Card Receipt — `DashboardController::updateReceipt()` (app/Http/Controllers/Site/DashboardController.php:40-70)

| Payload | Listener |
|---------|----------|
| `type='order.card_to_card'`, `order_id`, `tracking_code` | `NotificationService::notifyAdmins('order.card_to_card', 'فیش واریزی جدید دریافت شد', ...)` |

### 3.10 Shipping Status Changed — `OrderController::changeShippingStatus()` (app/Http/Controllers/Admin/OrderController.php:50-75)

| Payload | Listener |
|---------|----------|
| `type='order.shipped'`, `statusName` | `NotificationService::notifyUser(user, 'order.shipped', 'وضعیت ارسال سفارش تغییر کرد', ...)` |

---

## 4. Sequence — Order Created (SYNC)

```
Step 1  User → CheckoutController::create()
Step 2        → OrderService::create() → Order::create() + OrderItem loop
Step 3        → OrderObserver::created() → OrderHistory::create()            [SYNC, auto]
Step 4        → StockService::reserveStock() → products.stock-- + inventory_transactions  [SYNC]
Step 5        → AuditService::orderCreated() → audit_logs                     [SYNC, try/catch]
Step 6        → NotificationService::notifyUser('order.created')              [SYNC]
Step 7        → NotificationService::notifyAdmins('order.new')                [SYNC]
Step 8  Return Order → CheckoutController → OrderService::checkout() → BankPaymentService
```

---

## 5. Invariants baraye Vibe Coding

- **Hich queue nist** — hame step-ha dar request lifecycle-e asli ejra mishavad. Agar side-effect sangin shod (SMS, stock), `try/catch` + log kon ta checkout nashkanad — olgouye `OrderService::create` ra donbal kon.
- **Observer faghat Order** — `OrderHistory` va `restoreStock` bar `cancelled` dar Observer anjam mishavad. Baraye model-e jadid observer ezafe mikoni, dar `AppServiceProvider::boot()` sabt kon.
- **Payload-e Notification hamishe:** `type` (az `NotificationService::TYPES`), `title`, `body`, `link` (route), `data` (array ba `order_id` va field-haye marboot). `icon`/`color` auto az `TYPES` miayad.
- **Idempotency:** `restoreStock` double-call mishavad (Service + Observer) — agar job async mikoni, guard `wasChanged` ra hefz kon va ya idempotency key be `InventoryTransaction` ezafe kon.
