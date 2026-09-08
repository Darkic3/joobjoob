# Notifications — Dependencies (Verified Audit)

---

## Direct Dependencies

### Models
- `Notification` — custom DB model (NOT Laravel's database notification)
- `User` — notifiable entity (morph target)

### Services
- `NotificationService` — core service (146 lines)
- `AuditService` — called indirectly via StockService (not directly)

### Helpers
- `setting()` — for OTP template (via OtpService)
- `locale()` — for i18n (via app locale)

### External
- None — pure DB, no mail/SMS/push channels

---

## Inbound Dependencies (who calls NotificationService)

| Caller Module | Method Called | Type | Notes |
|---------------|--------------|------|-------|
| **Orders** | `notifyUser` | `order.created`, `order.paid`, `order.shipped`, `order.cancelled` | OrderService, Admin/OrderController |
| **Orders** | `notifyAdmins` | `order.new`, `order.paid_admin`, `order.card_to_card` | OrderService, DashboardController |
| **Payments** | `notifyUser` | `order.paid` | OrderService::handleSuccessOrderPayment |
| **Payments** | `notifyAdmins` | `order.paid_admin`, `order.card_to_card` | OrderService::handleSuccessOrderPayment, DashboardController |
| **Auth-OTP** | `notifyUser` | `discount.welcome` | OtpService:~104 |
| **Products** | `notifyAdmins` | `review.new` | ProductController:343 |
| **Contact** | `notifyAdmins` | `contact.new` | PageController:47 |

---

## Outbound Dependencies (what NotificationService calls)

| Dependency | Usage | Notes |
|------------|-------|-------|
| `Notification::create()` | Synchronous INSERT | No queue, no retry |
| `User::where(is_admin)` | Admin fan-out | No `is_active` filter (gotcha #5) |
| `__("notifications.{type}.title")` | Translation | No keys exist (gotcha #1) |
| `app()->getLocale()` | Locale capture | Per-iteration in notifyAdmins (gotcha #8) |

---

## Cross-Module Gotchas

### Orders → Notifications
- `OrderService::cancelOrder` sends `order.cancelled` inside try/catch — if notification fails, order still cancelled (notification is non-critical)
- `CancelExpiredOrders` cron does NOT call `cancelOrder` — updates status directly → **no notification sent** (gotcha #4)

### Auth-OTP → Notifications
- `OtpService:~104` sends `discount.welcome` after creating welcome discount
- Wrapped in try/catch — failure logged, not rethrown

### Products → Notifications
- `ProductController:343` sends `review.new` to admins after new review
- Wrapped in try/catch — failure logged, not rethrown

---

## Schema

### `notifications` table
```
id              BIGINT PK AUTO INCREMENT
notifiable_type VARCHAR (morph type = "App\Models\User")
notifiable_id   BIGINT (morph id = user ID)
type            VARCHAR (e.g., "order.created")
title           VARCHAR (raw key string — no translation)
body            TEXT NULLABLE
icon            VARCHAR (e.g., "📦")
link            VARCHAR NULLABLE (e.g., "/account/orders/123")
is_read         BOOLEAN DEFAULT FALSE
data            JSON NULLABLE
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

### Indexes
- `(notifiable_type, notifiable_id, is_read)` — composite for unread count queries
- `(created_at)` — for chronological ordering

---

## Coupling Assessment

**Loose coupling:** Notifications is a leaf node — it receives events but triggers nothing. Failure in notification creation does not affect order/payment/auth flows (all wrapped in try/catch).

**Tight coupling:** None — no inverse dependencies.

---

## Evidence

- `app/Services/NotificationService.php` — full audit
- `app/Models/Notification.php` — scopes, morph
- `app/Http/Controllers/Site/NotificationController.php` — 5 actions
- `app/Http/Controllers/Admin/NotificationController.php` — 6 actions
- `database/migrations/2026_09_03_000001_create_notifications_table.php` — schema
- `routes/site.php:164-168` — site routes
- `routes/admin.php:107-112` — admin routes
