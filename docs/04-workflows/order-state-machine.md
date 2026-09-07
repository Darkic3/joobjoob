# Order State Machine

> **Source of Truth:** `app/Models/Order.php:18,112-124` , `app/Models/OrderHistory.php:12,26-34` , `app/Services/OrderService.php:44-280` , `app/Services/BankPaymentService.php:64` , `app/Console/Commands/CancelExpiredOrders.php:21,46` , `app/Observers/OrderObserver.php:21-53` , `app/Http/Controllers/Admin/OrderController.php:130-140` , `app/Http/Controllers/Site/CheckoutController.php:249-255`

## 1. Status-ha (7) — Hich Enum Nist

Status string dar `orders.order_status` zakhire mishavad. Hich `enum` ya `const VALUES` tarif nashode — enumeration faghat dar `Order::getStatusAttribute()` match va `OrderHistory::getStatusNameAttribute()` match.

| Status | Label (Farsi) | Badge | Source |
|--------|---------------|-------|--------|
| `paying` | در حال پرداخت | warning | `Order.php:115` |
| `pending_confirmation` | در انتظار تأیید پرداخت | warning | `Order.php:119` |
| `paid` | پرداخت شده | success | `Order.php:116` |
| `deposit_paid` | بیعانه پرداخت شده | success | `Order.php:117` |
| `wait_for_verification` | در انتظار تایید فیش ها | info | `Order.php:118` — legacy, dar flow-e fe'li set nemishavad |
| `unpaid` | پرداخت نشده | danger | `Order.php:120` |
| `cancelled` | لغو شده | danger | `Order.php:121` |

`shipping_status_id` joda az `order_status` ast — dar `order_shipping_statuses` table (default via `OrderShippingStatus::default()`). Observer har taghir-e har do ra dar `order_histories` sabt mikonad.

## 2. Valid Transitions — Kamel

> Transition = `old_status -> new_status` . Actor: User (checkout/callback), Admin (panel), System/Cron (scheduler + observer). Side-effect daghigh zekr shode. "Admin any->any" ghabl az in jadval joda tozih dade shode.

| # | Transition | Actor | Trigger (file:line) | Side-Effect-ha (Step-by-Step) |
|---|------------|-------|---------------------|-------------------------------|
| T1 | `∅ -> paying` | User | `OrderService::create:53-81` — `Order::create(order_status='paying')` | 1) `OrderItem::create` loop 2) `OrderObserver::created` → `OrderHistory::create` [SYNC] 3) `StockService::reserveStock` → `DB::transaction` + `lockForUpdate` + `decrement` + `InventoryTransaction type=subtract` + `AuditService::productStockChanged` 4) `AuditService::orderCreated` → `audit_logs event=order.created` 5) `NotificationService::notifyUser(order.created)` + `notifyAdmins(order.new)` [SYNC, try/catch] |
| T2 | `paying -> pending_confirmation` | User | `OrderService::checkout:148` — `if bank_type==='card_to_card'` | `Order::update(order_status='pending_confirmation')` → Observer `OrderHistory` . Hich notification injā — notification-e `order.card_to_card` dar `DashboardController::updateReceipt` ast. |
| T3 | `paying -> paid` | System | `OrderService::handleFreeOrder:158` — `if discount_id!==null && price<=0` | `Order::update(order_status='paid')` . Coupon `DiscountUsage` ghablan dar `CheckoutController::create` sabt shode. |
| T4 | `paying -> paid` / `paying -> deposit_paid` | User (callback) | `OrderService::handleSuccessOrderPayment:197` — `intval(deposit_price)!=0 ? 'deposit_paid' : 'paid'` | 1) `Order::update(order_status=...)` 2) `AuditService::orderStatusChanged(old, new, 'سیستم - پرداخت موفق')` 3) `notifyUser(order.paid)` + `notifyAdmins(order.paid_admin)` |
| T5 | `paying -> unpaid` | System | `OrderService::handleFailedPayment:232` + `BankPaymentService::handleVerify:64` | 1) `Order::update(order_status='unpaid')` [BankPaymentService] 2) `AuditService::orderStatusChanged(..., 'unpaid', 'سیستم - پرداخت ناموفق')` 3) `StockService::restoreStock` → `increment` + `InventoryTransaction type=add` . Guard: `if order_status==='unpaid' return` — idempotent. |
| T6 | `paying -> cancelled` | System/Cron | `CancelExpiredOrders:21,46` — `where order_status='paying' && created_at < now-15min` + `update(cancelled)` | 1) `Order::update(cancelled)` 2) `StockService::restoreStock` 3) `OrderObserver::updated` duplicate `restoreStock` (gotcha) + `OrderHistory` 4) `Log::info` |
| T7 | `paying -> cancelled` | User/Admin | `OrderService::cancelOrder:246` — `update(cancelled)` | 1) `AuditService::orderStatusChanged(old, 'cancelled', Auth::user()->name ?? 'سیستم')` 2) `notifyUser(order.cancelled)` 3) `if previous∈[paid,deposit_paid,pending_confirmation,wait_for_verification]` → `StockService::restoreStock` 4) Observer duplicate restore |
| T8 | `pending_confirmation -> cancelled` | User/Admin/Cron | `cancelOrder` ya Admin `changeOrderStatus` | Haman T7 |
| T9 | `paid -> cancelled` | Admin | `OrderController::changeOrderStatus:135` → `cancelOrder` | Haman T7 — restoreStock anjam mishavad |
| T10 | `deposit_paid -> cancelled` | Admin | Haman | Haman |
| T11 | `unpaid -> cancelled` | Admin | Haman | `cancelOrder` vali `previous=unpaid` ∉ list → `restoreStock` dar Service **skip** — vali Observer ham check `previous∈[...]` → skip. Pas unpaid→cancelled stock restore nemikonad (dorost — stock ghablan dar T5 restore shode). |
| T12 | `* -> *` (Admin any->any) | Admin | `OrderController::changeOrderStatus:138` — `order->update(['order_status'=>newStatus])` hich guard nist | Faghat `OrderHistory` via Observer. Agar `newStatus==='cancelled'` → `cancelOrder` path (ba restore). Vagar na — hich audit/notification nist dar in path (bug-e ehtemali — bayad `AuditService` seda zade mishod). |

**Na-map shode:** `wait_for_verification` dar hich service set nemishavad — legacy status, faghat dar match label vojoud darad. `shipped`/`delivered` status-e `order_status` nist — vaziat-e ersal dar `shipping_status_id` (OrderShippingStatus) ast.

## 3. Mermaid State Diagram

```mermaid
stateDiagram-v2
    [*] --> paying: User create order\n(OrderService::create)
    paying --> pending_confirmation: bank_type=card_to_card\n(OrderService::checkout)
    paying --> paid: price<=0 free order\n(handleFreeOrder)
    paying --> paid: verify success + deposit=0\n(handleSuccess paid)
    paying --> deposit_paid: verify success + deposit!=0\n(handleSuccess deposit_paid)
    paying --> unpaid: verify failed\n(handleFailedPayment)
    paying --> cancelled: Cron 15min\n(CancelExpiredOrders)
    paying --> cancelled: User/Admin cancel\n(cancelOrder)

    pending_confirmation --> cancelled: cancelOrder\n(+restoreStock)
    pending_confirmation --> paid: Admin manual\n(changeOrderStatus)

    paid --> cancelled: Admin cancel\n(cancelOrder + restoreStock)
    deposit_paid --> cancelled: Admin cancel\n(cancelOrder + restoreStock)
    unpaid --> cancelled: Admin cancel\n(no restore — already restored)

    cancelled --> [*]
    paid --> [*]
    deposit_paid --> [*]
    unpaid --> [*]

    note right of paying
      Side-Effect on entry:
      reserveStock + audit
      + notifyUser/Admin
    end note
    note right of cancelled
      Side-Effect:
      restoreStock (duplicate
      Service+Observer) + audit
      + notifyUser + OrderHistory
    end note
```

## 4. Pseudo-Code — Transition Logic-e Daghigh

```php
// OrderService::create — T1
order = Order::create(order_status='paying', ...)
foreach cartItems: OrderItem::create(...)
try { stockService.reserveStock(order) } // DB::transaction + lockForUpdate
catch (RuntimeException e) { order.forceDelete(); throw e }
AuditService::orderCreated(order)
notifyUser('order.created'); notifyAdmins('order.new')

// OrderService::checkout — T2, T3
price = intval(str_replace(',', '', deposit_price ?: payment_price))
if discount_id!==null && price<=0: return handleFreeOrder(update paid)
bank = Bank::find(bank_id)
if bank.bank_type==='card_to_card': order.update(pending_confirmation); return redirect success
else: bankPaymentService.handlePayment(bank, order, price) // T4/T5 via callback

// OrderService::callback — T4, T5
order = findByField(field, value) // transaction_info->post->Authority
if order_status ∈ [paid,deposit_paid]: return redirect success (idempotent)
isSuccess = bankPaymentService.handleVerify(order, data, price)
  // inside: gateway.verifyTransaction → update transaction_info.verify
  // if failed: order.update(unpaid)
if isSuccess: handleSuccessOrderPayment(order) // T4
else: handleFailedPayment(order); return failed // T5

// OrderService::handleSuccessOrderPayment — T4
if order_status==='paid': return redirect success (idempotent)
old = order_status
order.update(order_status = intval(deposit_price)!=0 ? 'deposit_paid' : 'paid')
AuditService::orderStatusChanged(old, new, 'سیستم - پرداخت موفق')
notifyUser('order.paid'); notifyAdmins('order.paid_admin')

// CancelExpiredOrders — T6
cutoff = now - 15min
expired = Order::where(order_status='paying', created_at < cutoff)->get()
foreach order: order.update(cancelled); stockService.restoreStock(order)

// OrderService::cancelOrder — T7-T11
previous = order.order_status
order.update(cancelled)
AuditService::orderStatusChanged(previous, 'cancelled', Auth::user()->name ?? 'سیستم')
notifyUser('order.cancelled')
if previous ∈ [paid,deposit_paid,pending_confirmation,wait_for_verification]:
  stockService.restoreStock(order) // + Observer duplicate

// OrderObserver::updated — side-effect hamishe
if isDirty(order_status) || isDirty(shipping_status_id):
  OrderHistory::create(order_id, order_status, shipping_status_id)
if wasChanged(order_status) && order_status==='cancelled' && getOriginal ∈ [paid,deposit_paid,pending_confirmation,wait_for_verification]:
  stockService.restoreStock(order) // duplicate

// Admin any->any — T12
newStatus = request.order_status
if newStatus==='cancelled': cancelOrder(order)
else: order.update(order_status=newStatus) // no audit, only OrderHistory via observer
```

## 5. Invariants

- `paying` tanha status-e ghabel-e verify ast — `callback` idempotency guard `if paid/deposit_paid return success` darad.
- `unpaid` be `cancelled` stock restore nemikonad — dorost, chon T5 ghablan restore karde.
- Admin `changeOrderStatus` hich guard-e transition nadarad — `paying -> paid` mostaghim momken ast bedoun-e verify (risk-e mali).
- `wait_for_verification` hich transition-e code nadarad — agar estefade mishavad bayad T jaded tarif shavad.
- Har transition-e `cancelled` 2 bar `restoreStock` seda mizanad (Service + Observer) — `InventoryTransaction` duplicate ijad mishavad.
