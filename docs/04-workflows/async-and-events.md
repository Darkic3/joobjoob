# Async & Events — Overview

> **Source of Truth:** `app/Events/`, `app/Listeners/`, `app/Jobs/`, `app/Providers/EventServiceProvider.php`, `app/Console/Kernel.php` dar project **vojoud nadarand** (audit 2026-09-04). `config/queue.php` default `database` vali hich `dispatch()` vojoud nadarad. Tamam-e flow-ha **SYNC EXECUTION** dar request lifecycle ejra mishavand. `routes/console.php` tanha schedule-e fe'li ra negah midarad.

## Execution Mode — Kolle Project

| Layer | Mode | Evidence |
|-------|------|----------|
| Events / Listeners | ❌ Vojoud nadarad — `event()` 0 hit | `app/Events` / `app/Listeners` not found |
| Jobs / Queues | ❌ Vojoud nadarad — `ShouldQueue` 0 hit | `app/Jobs` not found, `dispatch(` 0 hit |
| Observers | ✅ 1 observer — `OrderObserver` | `AppServiceProvider::boot() → Order::observe` |
| Scheduler | ✅ 1 job — `orders:cancel-expired` | `routes/console.php → everyFiveMinutes` |
| Notifications (DB) | ✅ SYNC — `Notification::create` | `NotificationService` — hich queue |
| SMS (Kavenegar) | ✅ SYNC — cURL mostaghim | `Sms::sendLookup` — timeout 30s |

## File Map — 4 Document-e Modular + 1 Overview

| File | Mohtava |
|------|---------|
| [`events-listeners.md`](./events-listeners.md) | Map-e kamel-e har dispatch-e manteghhi (OrderCreated, PaymentVerified, Otp, Contact, Review, Shipping) — payload, listener, table, sequence |
| [`jobs-queues.md`](./jobs-queues.md) | Queue config (`database`, `retry_after=90`), har job-e manteghhi be surat SYNC service call + handle/failed logic, rahnameye async kardan |
| [`commands-schedule.md`](./commands-schedule.md) | 2 command (`CancelExpiredOrders`, `LangNew`), `routes/console.php` schedule (`*/5 * * * *` + `0 * * * *`), crontab, timing gotcha |
| [`notifications.md`](./notifications.md) | Channel-ha (DB ✅, SMS OTP ✅, Mail ❌), `NotificationService::TYPES` (11 type), `Sms` helper (4 method), OTP flow, template tokens |
| `async-and-events.md` (in file) | Overview + Mermaid sequence diagram-e Order Creation & Payment Flow |

## Mermaid — Order Creation & Payment Flow (SYNC)

> Hame step-ha SYNC EXECUTION hastand — hich queue nist. Agar Vibe Coding job async ezafe konad, in diagram be `queue` lane taghir mikonad.

```mermaid
sequenceDiagram
    autonumber
    actor User
    participant CheckoutController as Checkout<br/>Controller
    participant OrderService as OrderService
    participant OrderObserver as OrderObserver<br/>(Eloquent)
    participant StockService as StockService
    participant AuditService as AuditService
    participant NotificationService as Notification<br/>Service
    participant DB as DB<br/>(orders, notifications,<br/>audit_logs, inventory)
    participant BankPaymentService as BankPayment<br/>Service
    participant ZarinPal as ZarinPal<br/>(Kavenegar-style)
    participant Gateway as ZarinPal Gateway<br/>(external)
    participant Scheduler as Scheduler<br/>(cron every 5min)

    User->>CheckoutController: POST /checkout/create<br/>(cart, address, bank_id)
    CheckoutController->>OrderService: create(cartItems, orderData)
    OrderService->>DB: Order::create(order_status=paying)
    DB-->>OrderObserver: created(Order)
    OrderObserver->>DB: OrderHistory::create
    OrderService->>StockService: reserveStock(order)
    StockService->>DB: lockForUpdate + decrement stock<br/>+ InventoryTransaction subtract
    StockService->>AuditService: productStockChanged
    AuditService->>DB: audit_logs (product.stock_deducted)
    OrderService->>AuditService: orderCreated(order)
    AuditService->>DB: audit_logs (order.created)
    OrderService->>NotificationService: notifyUser(order.created)
    NotificationService->>DB: notifications (user)
    OrderService->>NotificationService: notifyAdmins(order.new)
    NotificationService->>DB: notifications × N_admin
    OrderService-->>CheckoutController: Order (fresh)

    CheckoutController->>OrderService: checkout(order)
    alt bank_type == card_to_card
        OrderService->>DB: update order_status=pending_confirmation
        OrderService-->>User: redirect checkout.success
    else bank_type == zarinPal
        OrderService->>BankPaymentService: handlePayment(bank, order, price)
        BankPaymentService->>ZarinPal: getBankToken(amount*10, order)
        ZarinPal->>Gateway: POST request.json<br/>{merchant_id, amount, callback_url}
        Gateway-->>ZarinPal: {data: {code:100, authority}}
        ZarinPal-->>BankPaymentService: PaymentPostDTO(success, Authority)
        BankPaymentService->>DB: update transaction_info.post={Authority}
        BankPaymentService->>User: header Location: StartPay/Authority (redirect)
    end

    User->>Gateway: pay on ZarinPal
    Gateway->>CheckoutController: GET /checkout/finish/zarin-pal<br/>?Authority=...&Status=OK
    CheckoutController->>OrderService: callback(field, Authority, data)
    OrderService->>BankPaymentService: handleVerify(order, data, price)
    BankPaymentService->>ZarinPal: verifyTransaction(data, price)
    ZarinPal->>Gateway: POST verify.json {merchant_id, authority, amount}
    Gateway-->>ZarinPal: {data: {code:100, ref_id}}
    ZarinPal-->>BankPaymentService: PaymentVerifyDTO(success, RefID)
    BankPaymentService->>DB: update transaction_info.verify={RefID}
    alt verify success
        OrderService->>DB: update order_status=paid
        OrderService->>AuditService: orderStatusChanged(paying→paid)
        AuditService->>DB: audit_logs
        OrderService->>NotificationService: notifyUser(order.paid)
        NotificationService->>DB: notifications (user)
        OrderService->>NotificationService: notifyAdmins(order.paid_admin)
        NotificationService->>DB: notifications × N_admin
        OrderService-->>User: redirect checkout.success
    else verify failed
        OrderService->>DB: update order_status=unpaid
        OrderService->>StockService: restoreStock(order)
        StockService->>DB: increment stock + InventoryTransaction add
        OrderService-->>User: redirect checkout.failed
    end

    Note over Scheduler,DB: Har 5 daghighe — SYNC via cron
    Scheduler->>DB: Order where order_status=paying<br/>and created_at < now-15min
    Scheduler->>StockService: restoreStock per order
    StockService->>DB: increment stock + InventoryTransaction
    Scheduler->>DB: update order_status=cancelled
    DB-->>OrderObserver: updated(Order, cancelled)
    OrderObserver->>DB: OrderHistory::create + restoreStock (duplicate)
```

### Khanesh-e Diagram baraye Vibe Coding

- **Hame arrow-ha SYNC** — hich `queue` lane vojoud nadarad. Har `->DB` yek `INSERT/UPDATE` dar haman request ast.
- **Side-effect-ha faramoosh nashavad:** bad az `Order::create` hatman `reserveStock` + `orderCreated` + 2 notification. Bad az `paid` hatman `orderStatusChanged` + 2 notification. Bad az `failed/unpaid` hatman `restoreStock`.
- **Scheduler joda:** `CancelExpiredOrders` kharej az request lifecycle — via `php artisan schedule:run` har 5min. Avg 17.5min delay ta cancel (gotcha dar `commands-schedule.md`).
- **OTP joda:** `OtpService::send` → `WelcomeDiscountService` + `discount.welcome` notification + `Sms::sendLookup` — hame SYNC dar `POST /otp/send`.

## Che Chizi Queue Nist — Va Che Bayad Queue Shavad

| Alan SYNC | Pishnahad Queue (ayande) |
|-----------|--------------------------|
| `NotificationService::notifyUser/Admins` — DB insert | `SendNotificationJob` on `notifications` queue — 3 tries, backoff 10/30/60s |
| `Sms::sendLookup` — cURL 30s | `SendOtpSmsJob` on `sms` queue — timeout 30s, retry 3 |
| `AuditService::log` — DB insert | mitavan sync mand — sari va mohem |
| `StockService::reserve/restore` | **nabayad async** — bayad dar transaction-e asli bemanad (lockForUpdate) |

Tafsil-e queue config va failed strategy dar [`jobs-queues.md`](./jobs-queues.md) — section 4 va 5.
