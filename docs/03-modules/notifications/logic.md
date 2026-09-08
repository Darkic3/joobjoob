# Notifications — Logic (Verified Audit)

> **Module:** Custom DB-only notification system — NOT Laravel's channel/Notifiable stack
> **Service:** `NotificationService` (146 lines) | **Model:** `Notification` (97 lines)
> **Controllers:** `Admin/NotificationController` (117), `Site/NotificationController` (98)
> **Migration:** `2026_09_03_000001_create_notifications_table` (composite index + created_at)
> **⚠️ CRITICAL: No `notifications.*` translation keys exist in ANY locale — stored titles are raw key strings**

---

## 1. Purpose & Architecture

Custom synchronous DB notification system. `NotificationService` does `Notification::create` inserts (no queue, no mail/SMS channels, no retry). User model has the Notifiable trait but **ZERO** `$user->notify()` usage — vestigial. No schema clash with Laravel default notifications migration.

**Key design decisions:**
- Title/body stored in the ACTING request's locale (callback/cron = default `fa`)
- Labels are frozen at write time — no re-translation on read
- Icons/colors from `TYPES` constant map (fallback 🔔/accent)

---

## 2. Types (11 verified senders)

| Type | Icon | Color | Sender | Target |
|------|------|-------|--------|--------|
| `order.created` | 📦 | accent | `OrderService::create` | User |
| `order.paid` | ✅ | success | `OrderService::handleSuccessOrderPayment` | User + Admin |
| `order.shipped` | 🚚 | info | `Admin/OrderController:94` | User |
| `order.cancelled` | ❌ | danger | `OrderService::cancelOrder` | User |
| `discount.welcome` | 🎁 | success | `OtpService:~104` | User |
| `discount.expiring` | ⏰ | warning | **DEAD — zero senders, no scheduler** | — |
| `order.new` | 📦 | accent | `OrderService::create` | Admin |
| `order.paid_admin` | ✅ | success | `OrderService::handleSuccessOrderPayment` | Admin |
| `order.card_to_card` | 💳 | warning | `DashboardController:90` | Admin |
| `review.new` | 💬 | info | `ProductController:343` | Admin |
| `contact.new` | ✉️ | accent | `PageController:47` | Admin |

---

## 3. Service API

### `notifyUser(User $user, string $type, string $title, ?string $body, ?string $link, ?array $data): Notification`

⚠️ **`$title` PARAMETER IS IGNORED** — overwritten by `__("notifications.{$type}.title", [], locale)`. Body: only translated if caller passed one. Icon/color from TYPES map.

### `notifyAdmins(string $type, ...): int`

Fan-out to `User::where(is_admin)->where(is_active)->get()`, one INSERT per admin, locale read per iteration. Returns created count.

### Read/Unread methods

- `getUnreadCount(User)` / `getAdminUnreadCount()` — single aggregate queries
- `markAsRead(id, user)` — scope-protected
- `markAllAsRead(user)` — one UPDATE
- `getUserNotifications(user, perPage=20)` — `forUser()->userNotifications()->latest()->paginate()`
- `getAdminNotifications(perPage=20, filter?)` — `forAdmins()->latest()` with optional filter

---

## 4. Model Scopes

- `forUser(userId)` — morph match (notifiable_type + notifiable_id)
- `forAdmins()` — `whereIn` subquery on `User::where(is_admin)` — ⚠️ **no `is_active` filter** (stale admin accounts receive notifications)
- `userNotifications()` — excludes 5 admin types by `type NOT IN`
- `unread()` — `where is_read false`
- `ofType(type)` — `where type = ?`

**No N+1 anywhere** — all counters use single aggregate queries.

---

## 5. i18n Strategy (frozen-at-write)

Title/body stored in the ACTING request's locale:
- Callback/cron = default `fa`
- Site user actions = user's current locale
- Admin actions = admin's locale

`getTypeLabelAttribute()` falls back to raw type string if translation key missing: `return $label === $key ? (string) $this->type : $label`.

⚠️ **BUG: No `notifications.*` keys exist in `resources/lang/{fa,en,ar,tr,es}/`** — every stored title is a raw key string like `notifications.order.created.title`.

---

## 6. Controllers

### Site/NotificationController (`app/Http/Controllers/Site/NotificationController.php`)

- `index()` — `NotificationService::getUserNotifications(auth()->user())` → `site.notifications.index`
- `markRead(id)` — `NotificationService::markAsRead(id, auth()->user())` → JSON `{success, unread_count}`
- `markAllRead()` — `NotificationService::markAllAsRead(auth()->user())` → JSON
- `destroy(id)` — delete by id (no scope check — potential IDOR)
- `unreadCount()` — AJAX JSON `{count}` for bell badge

### Admin/NotificationController (`app/Http/Controllers/Admin/NotificationController.php`)

- `index()` — `getAdminNotifications(20, filter)` → `admin.notifications.index`
- `markRead(id)` — `markAsRead` → JSON
- `markAllRead()` — `markAllAsRead` → JSON
- `destroy(id)` — delete by id
- `unreadCount()` — JSON
- `show(id)` — **BUG: first-page-only lookup** — `Notification::forAdmins()->latest()->simplePaginate(20)` then `->firstWhere('id', $id)`. If notification is on page 2+, returns 404.

---

## 7. Routes

### Site (5 routes)
```
GET  /account/notifications           → Site\NotificationController@index
POST /account/notifications/{id}/read → markRead
POST /account/notifications/read-all  → markAllRead
DELETE /account/notifications/{id}    → destroy
GET  /account/notifications/unread-count → unreadCount (AJAX)
```

### Admin (6 routes)
```
GET    /admin/notifications           → Admin\NotificationController@index
POST   /admin/notifications/{id}/read → markRead
POST   /admin/notifications/read-all  → markAllRead
DELETE /admin/notifications/{id}      → destroy
GET    /admin/notifications/unread-count → unreadCount
GET    /admin/notifications/{id}      → show (BUG: page-1-only)
```

---

## 8. Business Rules

| Rule | Evidence |
|------|----------|
| Title ignored (translation override) | `NotificationService:26` `$title = __("notifications.{$type}.title")` |
| Frozen locale at write | `app()->getLocale()` at create time |
| Admin types excluded from user view | `userNotifications()` scope NOT IN 5 types |
| No queue/sync only | `Notification::create` direct, no dispatch |
| `is_read` default false | Migration + create call |
| Composite index `(notifiable_type, notifiable_id, is_read)` | Migration line 18 |
| `created_at` indexed | Migration line 19 |

---

## 9. Evidence

- `app/Services/NotificationService.php` — TYPES map, notifyUser/notifyAdmins, read/unread
- `app/Models/Notification.php` — scopes, markAsRead, counters
- `app/Http/Controllers/Site/NotificationController.php` — 5 actions
- `app/Http/Controllers/Admin/NotificationController.php` — 6 actions (show is buggy)
- `database/migrations/2026_09_03_000001_create_notifications_table.php` — schema
- `resources/lang/{fa,en,ar,tr,es}/` — **zero `notifications.*` keys** (grep verified)
- `routes/site.php:164-168` — site routes
- `routes/admin.php:107-112` — admin routes
