# Contact — Porting Checklist

> **Status:** ✅ verified-trace — بازنویسی از کد واقعی (2026-09-06)
> Hich Service/DTO/Event nadarad — port-e in module sabok ast (≈12 file).

## 1. PHP Files (copy as-is)

| # | File | Note |
|---|---|---|
| 1 | `app/Models/ContactRequest.php` | Bedoon dependency — faqat Eloquent (`:1-44`) |
| 2 | `app/Http/Controllers/Site/PageController.php` | ⚠️ dar Joobjoob in controller 5 static page-ra ham handle mikonad (`PAGES` const `:16`). Agar target-e shoma page-manager nadarad, faghat `sendContact` (`:36-60`) + `pageData`-e contact (`:71-78`) ra bekonid |
| 3 | `app/Http/Controllers/Admin/ContactController.php` | Copy-e kamel (`:1-46`) |

## 2. Database

- [ ] Migration: `database/migrations/2026_08_11_000001_create_contact_requests_table.php` (`contact_requests`: name, email, phone(40), department(40), message text, read_at, timestamps — bedoon index).
- [ ] Optional seeder: `database/seeders/ContactRequestSeeder.php` (3 record-e sample — `:12-16`); register dar `DatabaseSeeder` (dar Joobjoob: `:57`).

## 3. Routes

- [ ] Site: `POST /contact` → `PageController::sendContact` **ba middleware `throttle:5,1`** → name `site.contact.send` (`routes/site.php:71-73`). Dar Joobjoob dakhel-e closure-e `$siteRoutes` ast ke do-bar register mishavad (canonical `:169` + `{locale}` prefix `:172-176` → `locale.site.contact.send`) — agar localization dar target nist, faghat copy-e canonical kafi ast.
- [ ] Admin: 3 route-e dasti (resource nist!) dar `routes/admin.php:78-80` — `GET contact` (index), `GET contact/{contact}` (show), `DELETE contact/{contact}` (destroy) — dakhel-e middleware group-e admin (`routes/admin.php:34`).
- [ ] Optional: `GET /page/{page}` (`site.page`) baraye safhe-ye form-e contact (`PageController::show:18`).

## 4. Views (Blade)

| # | File | Note |
|---|---|---|
| 1 | `resources/views/site/pages/_contact.blade.php` | Form + info-cards; action ba `route('site.contact.send')` (`:40`) |
| 2 | `resources/views/site/pages/show.blade.php` | Wrapper-e partial (dar Joobjoob dar `:27` partial-e dynamic include mishavad) |
| 3 | `resources/views/admin/contact/list.blade.php` | Inbox — search, checkbox-e unread, badge, delete-form |
| 4 | `resources/views/admin/contact/show.blade.php` | Detail — delete-form, label-ha, `show_date()` |

Layout-ha (`layouts.admin.app`, layout-e site) + helper-ha (`show_date` az `app/Helpers/date.php:17`, `setting` az `app/Helpers/settings.php:10`) bayad dar target mojood ya adapt shavand.

## 5. Throttle wiring

- [ ] `throttle:5,1` roye POST `/contact` (default-e Laravel — RateLimiter custom talab nist). Key-e pishfarz: domain+IP baraye guest.
- [ ] Dar Joobjoob admin login niz `throttle:5,1` darad (`routes/admin.php:28`) — motaalleq-e contact nist, copy nakonid.

## 6. Notification (optional — joda-portable)

- [ ] Agar `NotificationService` dar target nist: ya seda-zan ra hazf konid (`PageController.php:48-57` try/catch-shode hast — hazfesh script-ra nemi-shkanad), ya service-e sade ba hamin signature port konid: `notifyAdmins(type, title, ?body, ?link, ?data): int` (`app/Services/NotificationService.php:61-67`) + type-e `contact.new` dar `TYPES` (`:27`, icon ✉️). Notification model/table-e `notifications` ham lazem mishavad.

## 7. Translations

- [ ] `messages.pages.contact.*` (title, form_title, name/email/phone/department/message + placeholder-ha, submit, success, info_phone/info_email/info_address) dar `resources/lang/{fa,en}/messages.php` (fa: `~:270-295`).
- [ ] Kelid-e **`departments`** (`:288-292`) — `sales/support/technical/other` — in keys **value-e form** mishavand; dar DB store mishavand. Agar taghir midahid, data-ye qadimi migehad.
- [ ] `admin/contact.*` (tab, title, search_ph, unread_only, status, unread, read, no_department, department, phone, message, back, view, deleted).

## 8. RBAC (agar target admin panel-e permission-dar darad)

- [ ] 3 permission: `admin.contact.index`, `admin.contact.show`, `admin.contact.destroy` — dar Joobjoob az config `config/permissions.php:209-215` generate mishavand (gheyr-e mostaghim: access `destroy` → key `.delete`).
- [ ] Middleware-e `adminPermission` + `User::canAccess` (`app/Models/Traits/HasAdminPermissions.php:49-56`) ya mo-adel-e aan.
- [ ] Dar views-e admin, button-ha ba `canAccess(...)` check mishavand (`list.blade.php:99,104`, `show.blade.php:20`).

## 9. Config for departments?

- [ ] **Hich config/database baraye departments vojood nadarad** — tanha manba: file-haye lang (`PageController.php:77` az trans mikhonad). Config jaygozin nakhahid sakht.
- [ ] Info-e safhe (phone/email/address) az Settings module miayad (`setting()` `:73-75`) — keys: `phone`, `email`, `address_one`. Agar target settings nadarad, sabet koded konid.

## 10. Tests

- [ ] `tests/Feature/StaticPagesTest.php` — 6 test-e contact (`:69-181`): page render, store+redirect, validation, admin 403, admin list/show(markAsRead)/delete, superadmin access. `adminUserWith()` helper-e khod-e file (`:121-134`) lazem ast.

## 11. Gotchas-e lazem-al-tavajjoh hengam-e port

- `department` free-text ast (validation `in:` nist) — be gotchas.md #1.
- show = markAsRead (side-effect) — #2.
- Hard delete (SoftDeletes nist) — #3.
- Search ruye phone nist — #4.
- Notification faghat be `is_admin=true && is_active=true` — #6.
- `orderByRaw('read_at IS NOT NULL, created_at DESC')` — SQL-specific; baraye DB-e digar adapt konid.
