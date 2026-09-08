# Notifications — API Contract (Verified Audit)

> **Source:** `routes/site.php:164-168`, `routes/admin.php:107-112`

---

## Site Routes (5 routes)

| Method | URI | Name | Middleware | Controller@Method | Auth |
|--------|-----|------|------------|-------------------|------|
| GET | `/account/notifications` | `site.notifications.index` | web, auth | `Site\NotificationController@index` | auth |
| POST | `/account/notifications/{id}/read` | `site.notifications.mark-read` | web, auth | `Site\NotificationController@markRead` | auth |
| POST | `/account/notifications/read-all` | `site.notifications.mark-all-read` | web, auth | `Site\NotificationController@markAllRead` | auth |
| DELETE | `/account/notifications/{id}` | `site.notifications.destroy` | web, auth | `Site\NotificationController@destroy` | auth |
| GET | `/account/notifications/unread-count` | `site.notifications.unread-count` | web, auth | `Site\NotificationController@unreadCount` | auth |

**Notes:**
- All site routes require `auth` middleware
- `unread-count` is AJAX endpoint for bell badge
- No throttling on any endpoint

---

## Admin Routes (6 routes)

| Method | URI | Name | Middleware | Controller@Method | Auth |
|--------|-----|------|------------|-------------------|------|
| GET | `/admin/notifications` | `admin.notifications.index` | auth, adminPermission, log.admin | `Admin\NotificationController@index` | admin |
| POST | `/admin/notifications/{id}/read` | `admin.notifications.mark-read` | auth, adminPermission, log.admin | `Admin\NotificationController@markRead` | admin |
| POST | `/admin/notifications/read-all` | `admin.notifications.mark-all-read` | auth, adminPermission, log.admin | `Admin\NotificationController@markAllRead` | admin |
| DELETE | `/admin/notifications/{id}` | `admin.notifications.destroy` | auth, adminPermission, log.admin | `Admin\NotificationController@destroy` | admin |
| GET | `/admin/notifications/unread-count` | `admin.notifications.unread-count` | auth, adminPermission, log.admin | `Admin\NotificationController@unreadCount` | admin |
| GET | `/admin/notifications/{id}` | `admin.notifications.show` | auth, adminPermission, log.admin | `Admin\NotificationController@show` | admin |

**Notes:**
- All admin routes use `auth, adminPermission, log.admin` middleware
- `show` route has page-1-only bug (gotcha #3)
- No throttling on any endpoint

---

## Request/Response Patterns

### Index (Site)
```json
{
  "data": [
    {
      "id": 1,
      "type": "order.created",
      "title": "notifications.order.created.title",
      "body": "...",
      "icon": "📦",
      "link": "/account/orders/123",
      "is_read": false,
      "created_at": "2026-09-03T10:00:00Z"
    }
  ],
  "links": { "next": "..." },
  "meta": { "current_page": 1, "last_page": 5 }
}
```

### Mark Read
```json
{ "success": true, "unread_count": 3 }
```

### Unread Count
```json
{ "count": 7 }
```

---

## Validation

- No FormRequest classes — inline validation where needed
- `markRead` / `destroy` validate `{id}` is integer via route model binding
- No input validation on index (query params for filter only)

---

## Notes

- Site routes duplicated under `{locale}` prefix with `locale.` name prefix — keep both
- No CRUD `store`/`update` routes — notifications are system-generated only
- No pagination query params documented (Laravel default: `?page=N`)
