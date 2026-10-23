# V2 Event-Driven Migration Guide (V1 -> V1.1)

> **Hadaf:** Jodayi-e `OrderService::create` az `NotificationService` / `AuditService` / `CouponService` — bedone baznevisi-e risk-dar dar V1.0.
> **Mode:** Ready-to-Merge — 5 file-e jadid already created, 1 wiring dar `AppServiceProvider` sabt shod. V1 synchronous hanuz kar mikonad (dual-write). Dar V1.1 3 bakhsh hazf va 1 khat ezafe mishavad.

---

## 1. File-haye Jadid (Created — V1.0 deploy mishavand, ghayre faal ta migration)

| File | Naghsh | Ghabele deploy dar V1.0? |
|------|--------|--------------------------|
| `app/Events/OrderCreated.php` | Domain Event — `ShouldDispatchAfterCommit`, `public readonly Order $order` | Bale, hich side-effect |
| `app/Events/OrderPaid.php` | Takmil-e `markPaid` — ghabele estefade dar webhook bank | Bale |
| `app/Listeners/SendOrderNotificationListener.php` | `ShouldQueue` (queue=notifications) — `shouldNotify()` gate + `notifyAdmins` | Bale, wiring sabt shod amma event hanuz dispatch nemishavad |
| `app/Listeners/RecordAuditListener.php` | `ShouldQueue` (queue=audit) — `OrderCreated|OrderPaid` -> `AuditService::orderCreated/statusChanged` | Bale |
| `app/Listeners/RecordCouponUsageListener.php` | `ShouldQueue` (queue=default) — `discount_id` null => skip | Bale |
| `app/Providers/AppServiceProvider.php` | `Event::listen` 4 wiring | Bale — comment-e migration dar khodesh |

---

## 2. Wiring-e Feli dar `AppServiceProvider::boot` (Committed)

```php
use App\Events\OrderCreated;
use App\Events\OrderPaid;
use App\Listeners\RecordAuditListener;
use App\Listeners\RecordCouponUsageListener;
use App\Listeners\SendOrderNotificationListener;
use Illuminate\Support\Facades\Event;

Event::listen(OrderCreated::class, SendOrderNotificationListener::class);
Event::listen(OrderCreated::class, RecordAuditListener::class);
Event::listen(OrderCreated::class, RecordCouponUsageListener::class);
Event::listen(OrderPaid::class, RecordAuditListener::class);
```

> In wiring dar V1.0 **bi-khatar** ast — listener ha faghat vaghti seda zade mishavand ke `event(new OrderCreated($order))` seda zade shavad. Hala hanuz hich event dispatch nemishavad -> 0 execution.

---

## 3. Kodam Bakhsh az `OrderService::create` Bayad Hazf Shavad (Dar V1.1)

> **File:** `app/Services/OrderService.php` — method `create(array $cartItems, array $orderData, ...)`
> **Har 3 bakhsh-e zir dar V1.0 mostaghim dar Service hastand — dar V1.1 be Listener montaghel.**

### 3.1 Audit — hazf

```php
// GHABLI (OrderService.php ~:109):
try { AuditService::orderCreated($order); } catch (\Throwable $e) { Log::error(...); }

// BAD az migration: hazf kamel — RecordAuditListener anjam midahad.
// OrderService digar `use App\Services\AuditService` niaz nadarad baraye in khat.
```

### 3.2 Notification — hazf

```php
// GHABLI (OrderService.php ~:112-114):
try {
    $notifService = app(NotificationService::class);
    $notifService->notifyAdmins('order.new', ...);
} catch (\Throwable $e) { Log::error(...); }

// BAD: hazf kamel — SendOrderNotificationListener ba ShouldQueue + shouldNotify().
```

### 3.3 Coupon Usage — enfeal (agar mojud)

Agar `OrderService::create` ya `CheckoutController::create` mostaghim `CouponService::recordUsage` seda mizand:

```php
// GHABLI:
$couponService->recordUsage($order->discount_id, $order->user_id, $order->id, $order->discount_price);

// BAD: hazf — RecordCouponUsageListener ba `discount_id` null check.
```

> **Stock reservation (`$this->stockService->reserveStock`) NABAYAD hazf shavad — bayad synchronous bemanad (ghabl az dispatch) chon fail => `cancelled`. Stock exception ham ghabl az event ast.**

---

## 4. Kodam Bakhsh Ezafe Shavad

Dar payan-e `OrderService::create`, bad az `reserveStock` va ghabl az `return $order;`:

```php
use App\Events\OrderCreated;

// ... reserveStock block (hamishe synchronous bemanad) ...

// ── V1.1 Event dispatch — bad az DB commit (ShouldDispatchAfterCommit) ──
event(new OrderCreated($order));

return $order;
```

> **Nokte:** Chon `OrderCreated implements ShouldDispatchAfterCommit`, agar `create` dakhel-e `DB::transaction` bashad, dispatch ta bad az commit moavvagh mishavad — Listener Order ra hargez ghabl az commit nemibinad.

### 4.1 Dar `OrderService::markPaid` / webhook (baraye `OrderPaid`)

```php
use App\Events\OrderPaid;

$previous = $order->order_status;
$order->update(['order_status' => $newStatus]); // paid / deposit_paid
event(new OrderPaid($order->fresh(), $previous));
```

---

## 5. Taghirat-e `.env` (Lazem baraye Queue)

```env
# Dar .env.example va deploy .env:
QUEUE_CONNECTION=database
# Ghablan `sync` bood dar local — rooye host haman `database` kafi (migration jobs table mojood).
# Baraye V2 (Redis): QUEUE_CONNECTION=redis — faghat vaghti redis nasb shod.
```

### 5.1 Supervisor / Cron baraye Worker

```ini
# /etc/supervisor/conf.d/joobjoob-worker.conf  (ya cPanel cron fallback)
[program:joobjoob-worker]
command=php /home/joobjoob/artisan queue:work --sleep=3 --tries=3 --queue=notifications,audit,default --max-time=3600
autostart=true
autorestart=true
user=joobjoob
numprocs=1
```

Fallback shared host (bedone supervisor):

```cron
* * * * * cd /home/joobjoob && php artisan queue:work --stop-when-empty >> /dev/null 2>&1
# Ya schedule: $schedule->command('queue:work --stop-when-empty')->everyMinute()->withoutOverlapping();
```

> **Hoshdar:** Agar worker run nashavad, Listener ha dar `jobs` table saf mikeshand amma ejra nemishavand — notification geche mineshinad.

---

## 6. Tartib-e Migration-e V1.0 -> V1.1 (6 gam)

| # | Gham | Dastoor |
|---|------|---------|
| 1 | Deploy V1.0 ba 5 file-e jadid + wiring (hame already committed) | `git deploy` — 0 risk |
| 2 | `.env` `QUEUE_CONNECTION=database` + `php artisan queue:table` agar nakarde + `php artisan migrate` | rooye host |
| 3 | Supervisor/cron worker ra roshan kon va `php artisan queue:work --once` test kon | `jobs` khali bashad |
| 4 | **Merge day:** `OrderService.php` 3 bakhsh-e synchronous (Audit/Notification/Coupon) ra hazf kon va `event(new OrderCreated($order))` ezafe kon | 1 commit |
| 5 | Test: 1 order besaz → check `jobs` → `queue:work` → 3 notification/audit/usage sakhte shavand | `php artisan tinker` |
| 6 | Dual-write check: agar listener fail kard, order hanuz `paying` ast — queue `failed_jobs` log check | `php artisan queue:failed` |

---

## 7. Rollback Plan

Agar V1.1 listener bug dad:

```php
// Rollback 30s — 3 khat-e ghabl ra bargardan + dispatch ra comment kon:
// event(new OrderCreated($order)); // <- comment
// va Audit/Notification block ha ra az git history paste kon.
php artisan queue:flush  // jobs ezafi pak kon
```

---

## 8. Tafavot-e `ShouldDispatchAfterCommit` vs `ShouldQueue`

| Interface | Kodum taraf? | Asar |
|-----------|--------------|------|
| `OrderCreated implements ShouldDispatchAfterCommit` | **Event** | Dispatch ta commit-e DB moavvagh — Listener Order void nemibinad |
| `SendOrderNotificationListener implements ShouldQueue` | **Listener** | Ejra dar background worker — HTTP request monsete nemimanad |

Har do lazem — yeki zaman-e dispatch, digari zaman-e ejra.

---

## Appendix — File Map

```
app/Events/OrderCreated.php              — payload: Order $order
app/Events/OrderPaid.php                 — payload: Order $order + ?previousStatus
app/Listeners/SendOrderNotificationListener.php  — queue=notifications, shouldNotify gate
app/Listeners/RecordAuditListener.php            — queue=audit, OrderCreated|OrderPaid
app/Listeners/RecordCouponUsageListener.php      — queue=default, skip null discount_id
app/Providers/AppServiceProvider.php     — 4 Event::listen
docs/11-metrics/V2_Event_Driven_Migration_Guide.md — hamin file
.env / .env.example                     — QUEUE_CONNECTION=database
```

