# Contact — API Contract

> **Status:** ✅ verified-trace — بازنویسی از کد واقعی (2026-09-06)
> Source: `routes/site.php:71-73` + `routes/admin.php:78-80` + `_generated/routes-dump.md:70-75, 380-382`
> ⚠️ HICH `store/update/status`-i baraye admin vojood NADARAD — doc-e qabli (resource + PUT) eshtebah bud.

## Route Table (فقط روت‌های واقعی)

| Method | URI | Name | Middleware | Controller@Action | Auth | Source |
|---|---|---|---|---|---|---|
| POST | `contact` | `site.contact.send` | `web, throttle:5,1` | `Site\PageController@sendContact` | public | `routes/site.php:71-73` |
| POST | `{locale}/contact` | `locale.site.contact.send` | `web, throttle:5,1` | `Site\PageController@sendContact` | public | `routes/site.php:172-176` (closure-e `$siteRoutes:42` do-bar register mishavad) |
| GET | `/page/contact` | `site.page` | `web` | `Site\PageController@show` | public | `routes/site.php:70` (white-list-e `PAGES` dar `PageController.php:16`) |
| GET | `admin/contact` | `admin.contact.index` | `web, auth, adminPermission, log.admin, refreshRememberMe` | `Admin\ContactController@index` | admin | `routes/admin.php:78` |
| GET | `admin/contact/{contact}` | `admin.contact.show` | hamun balayi | `Admin\ContactController@show` | admin | `routes/admin.php:79` |
| DELETE | `admin/contact/{contact}` | `admin.contact.destroy` | hamun balayi | `Admin\ContactController@destroy` | admin | `routes/admin.php:80` |

## Notes

- **Throttle haqiqi = `throttle:5,1`** (5 request / 1 daghighe) — route-level middleware (`routes/site.php:72`). Dar routes-dump niz tasdiq shode (`routes-dump.md:380,382`).
- Admin contact route-ha **resource nistand** — 3 route-e dasti (index/show/destroy) dar `routes/admin.php:78-80`, dakhel-e middleware group-e `auth, adminPermission, log.admin, refreshRememberMe` (`:34`).
- **Route model binding:** `admin/contact/{contact}` → `ContactRequest $contact` (implicit binding ba param name).
- POST `/contact` motavaset-e admin notification misazad vali hich admin route-e write baraye contact vojood nadarad (payload pas az show-side-effect update mishavad).
- Naam-e route-e locale copy: `locale.` prefix + `site.` asli = `locale.site.contact.send`.
- Form-e site hameshe be `route('site.contact.send')` POST mikonad (`resources/views/site/pages/_contact.blade.php:40`) — `locale.site.contact.send` baraye URL-sazi-e enik nadarad; faghat baraye SEO/{locale} URL copy shode ast.

## Payloads

**POST /contact** (inline validation — `PageController.php:38-44`):

| Field | Rules |
|---|---|
| `name` | `required, string, max:120` |
| `email` | `required, email, max:190` |
| `phone` | `nullable, string, max:40` |
| `department` | `nullable, string, max:40` (hich `in:` checki nist) |
| `message` | `required, string, min:10, max:2000` |

Response: `302 back()` + flash `success` = `messages.pages.contact.success`. Fail: `302 back()` + errors standard Laravel.

**Admin GET endpoints:** HTML views — API JSON nistand. Query params-e `admin.contact.index`: `search` (like ruye name/email/message — na phone), `status=1` (faghat unread), `page`.
