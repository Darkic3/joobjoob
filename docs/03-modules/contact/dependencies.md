# Contact — Dependencies (Graph)

> **Status:** ✅ verified-trace — بازنویسی از کد واقعی (2026-09-06)
> Tamam-e edge-ha ba `file:line` — hich hads nist.

## Graph

```
[Browser/Guest]
   │ POST /contact  (throttle:5,1)
   ▼
routes/site.php:71-73  ──►  Site\PageController::sendContact   (app/Http/Controllers/Site/PageController.php:36)
                                │
                                ├─ inline validation (:38-44)  ── [Laravel Validator]
                                │
                                ├─► ContactRequest::create (:46)        (app/Models/ContactRequest.php:8)
                                │       └─► DB table `contact_requests` (2026_08_11_000001_create_contact_requests_table.php:11-20)
                                │
                                └─► NotificationService::notifyAdmins(:50 → app/Services/NotificationService.php:61)
                                        ├─► User::where(is_admin, is_active) (NotificationService.php:68)
                                        └─► Notification::create (:73-81)   [in-app, rows-e `notifications`]

[Browser]
   │ GET /page/contact
   ▼
routes/site.php (site.page) ──►  Site\PageController::show (:18)
                                ├─► trans("messages.pages.contact") (:23)
                                ├─► setting('phone'|'email'|'address_one') (:73-75)
                                │       └─► setting() helper (app/Helpers/settings.php:10) → [Settings module / cache]
                                └─► view site.pages.show → @include site.pages._contact
                                        (resources/views/site/pages/show.blade.php:27 → _contact.blade.php:40)

[Admin Panel]
   │ GET admin/contact · GET admin/contact/{contact} · DELETE admin/contact/{contact}
   ▼
routes/admin.php:78-80 (middleware group :34: auth, adminPermission, log.admin, refreshRememberMe)
   ──► Admin\ContactController (app/Http/Controllers/Admin/ContactController.php:11)
         ├─ index (:13)  ──► ContactRequest::query + scopeUnread (ContactRequest.php:26)
         ├─ show  (:32)  ──► ContactRequest::markAsRead (:34 → ContactRequest.php:31)
         │                   └─► view admin.contact.show
         └─ destroy(:39) ──► ContactRequest::delete (hard) ──► redirect admin.contact.index
```

## RBAC edges

| From | To | Evidence |
|---|---|---|
| `adminPermission` middleware | `AdminPermission::handle` | `app/Http/Middleware/AdminPermission.php:34-55` |
| AdminPermission | `User::canAccess($routeName)` | `app/Models/Traits/HasAdminPermissions.php:49-56` |
| permission config | gorouh `contact` (index/show/destroy) | `config/permissions.php:209-215` |
| Sidebar / MobileSidebar | `canAccess('admin.contact.index')` | `app/View/Components/Admin/Sidebar.php:79`, `MobileSidebar.php:62` |
| list/show Blade buttons | `canAccess('admin.contact.show'/'admin.contact.destroy')` | `resources/views/admin/contact/list.blade.php:99,104`, `show.blade.php:20` |

## Lang / Static data edges

| From | To | Evidence |
|---|---|---|
| `PageController::pageData` departments | `resources/lang/{fa,en,ar,tr}/messages.php` → `pages.contact.departments` | `PageController.php:77` + fa `:288-292` |
| Admin list/show department label | `__('messages.pages.contact.departments.' . $dept, [], $dept)` | `admin/contact/list.blade.php:89`, `show.blade.php:66` |
| Flash success | `messages.pages.contact.success` | `PageController.php:59` |
| Notification type | `NotificationService::TYPES['contact.new']` (icon ✉️) | `app/Services/NotificationService.php:27` |

## Na-dashtani-ha (Negative dependencies — baraye port mohem)

- **Service / DTO / FormRequest / Event / Job / Observer:** vojood nadarand.
- **Relation:** `ContactRequest` hich `belongsTo/hasMany` nist — be User/Product/etc. motevabel nist (`app/Models/ContactRequest.php` — 44 khat, relation-i nadarad).
- **Cache:** module cache-i nanevisad (faghat `setting()` az cache-e Settings kharij-e module mikhonad).
- **SoftDeletes:** nist — delete = hard delete.
- **`admin/contact_info` views** (`resources/views/admin/contact_info/*`) motaalleq-e **Settings module** hastand (edit-e phone/email/address-e site) — bakhshi az contact module NIST.
- **Email/SMS:** ferestade nemishavad — notification faghat DB-row (in-app) ast.
