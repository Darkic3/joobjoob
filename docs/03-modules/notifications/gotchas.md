# Notifications — Gotchas & Known Bugs (Verified Audit)

---

## 1. Raw Key Strings — titles stored untranslated (CRITICAL)

**Bug:** No `notifications.*` translation keys exist in ANY locale file (`resources/lang/{fa,en,ar,tr,es}/`). Every stored title is a raw key string like `notifications.order.created.title`.

**Impact:** Users see raw key strings instead of translated labels. The `getTypeLabelAttribute()` accessor also falls back to raw type.

**Fix:** Create `resources/lang/*/notifications.php` with keys matching `TYPES` constants.

---

## 2. `$title` parameter ignored in `notifyUser` (API trap)

**Bug:** `notifyUser()` accepts a `$title` parameter but **immediately overwrites it** with `__("notifications.{$type}.title", [], $locale)`. Callers who pass explicit titles get silently ignored.

**Evidence:** `NotificationService.php:26`

**Fix:** Either remove the parameter or use it as fallback when translation is missing.

---

## 3. `show()` page-1-only lookup (Admin)

**Bug:** `Admin/NotificationController::show()` does:
```php
Notification::forAdmins()->latest()->simplePaginate(20)->firstWhere('id', $id)
```
If the notification is on page 2+, `firstWhere` returns null → 404.

**Fix:** Use `Notification::forAdmins()->findOrFail($id)`.

---

## 4. Cron notifications missing — `order.cancelled` gap

**Bug:** `CancelExpiredOrders` command calls `OrderService::cancelOrder` which sends `order.cancelled` notification. BUT: the cron's try/catch swallows exceptions → if `cancelOrder` throws after status update, the notification is lost. Also: `order.cancelled` is only sent for user-side cancellations, NOT for auto-cancel cron.

**Impact:** Users don't know their order was auto-cancelled.

**Fix:** Send notification directly in cron after successful cancel, or ensure `cancelOrder` is called (not inline status update).

---

## 5. `forAdmins()` scope missing `is_active` filter

**Bug:** `Notification::forAdmins()` uses `whereIn('notifiable_id', User::where('is_admin', true)->pluck('id'))` — does NOT filter `is_active`. Deactivated admin accounts still receive notifications.

**Evidence:** `Notification.php:46-49`

**Fix:** Add `->where('is_active', true)` to the subquery.

---

## 6. `discount.expiring` type is DEAD

**Bug:** `TYPES` constant defines `discount.expiring` but zero senders exist anywhere in the codebase. No scheduler sends expiring discount notifications.

**Impact:** Dead code in TYPES map. Potential future feature never implemented.

**Fix:** Remove from TYPES or implement scheduler.

---

## 7. Site `destroy()` has no scope check — IDOR

**Bug:** `Site/NotificationController::destroy()` deletes by `$id` without checking `notifiable_id === auth()->id()`. A user can delete any notification by guessing the ID.

**Evidence:** `Site/NotificationController.php:68-76`

**Fix:** Use `Notification::forUser(auth()->id())->findOrFail($id)` before delete.

---

## 8. `notifyAdmins()` locale read per iteration

**Bug:** Inside the `foreach ($admins as $admin)` loop, `app()->getLocale()` is called per iteration. If locale changes mid-loop (unlikely but possible in queue workers), different admins get different locales.

**Impact:** Minimal in practice (synchronous), but fragile design.

**Fix:** Capture locale before loop: `$locale = app()->getLocale()`.

---

## 9. No `user_id` (actor) in `Notification` model

**Bug:** No field tracks WHO triggered the notification (admin, system, cron). Audit trail incomplete.

**Impact:** Cannot answer "which admin shipped order #123?" from notification alone.

**Fix:** Add `triggered_by` nullable FK to users.

---

## 10. `data` JSON not validated

**Bug:** `Notification::$casts = ['data' => 'array']` — any array structure accepted. No schema validation.

**Impact:** Callers can store arbitrary data; no contract for consumers.

**Fix:** Document expected `data` shape per type, or use DTO.

---

## 11. No bulk delete / cleanup

**Bug:** No command or route to prune old notifications. Table grows indefinitely.

**Impact:** Performance degradation over time.

**Fix:** Add `notifications:prune {--days=90}` artisan command.

---

## AI Checklist before coding Notifications

- [ ] Checked `TYPES` constant for valid type string
- [ ] Used `notifyUser`/`notifyAdmins` — NOT `$user->notify()` (Laravel Notifiable is vestigial)
- [ ] Passed `$title` parameter knowing it will be IGNORED (gotcha #2)
- [ ] Added `notifications.*` keys to lang files if adding new type (gotcha #1)
- [ ] Scoped queries with `forUser()` or `forAdmins()` — never raw `Notification::find()`
- [ ] Checked `is_read` default false in migrations
- [ ] Used `userNotifications()` scope to exclude admin types from user view

## Evidence

- `app/Services/NotificationService.php` — TYPES, notifyUser/notifyAdmins, read/unread
- `app/Models/Notification.php` — scopes, markAsRead, counters
- `app/Http/Controllers/Site/NotificationController.php:68-76` — IDOR in destroy
- `app/Http/Controllers/Admin/NotificationController.php` — show page-1 bug
- `resources/lang/{fa,en,ar,tr,es}/` — grep proved zero `notifications.*` keys
- `database/migrations/2026_09_03_000001_create_notifications_table.php` — schema
