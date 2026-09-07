# Contact — Logic (Deep Trace)

> **Status:** ✅ verified-trace — بازنویسی از کد واقعی (2026-09-06)
> **Module:** `contact` — فرم تماس سایت + صندوق پیام‌های ادمین (Inbox)
> **Stack:** `ContactRequest` + `PageController (Site)` + `Admin\ContactController` + `NotificationService`
> **Tag:** trace-e mostaghim az code, bedoon hads

---

## 1. Purpose (Hadaf-e Module)

Contact module sade-tarin module-e Shopora ast: karbar-e site (guest ya login-shode — farqi nemikonad) form-e «تماس با ما» ra por mikonad, peyam dar jadval-e `contact_requests` zakhire mishavad, be admin-ha notification-e in-app `contact.new` dade mishavad, va admin peyam-ha ra dar inbox-e panel mibinad, be onvane «خوانده‌شده» alamat mizanad ya hazf mikonad. **Hich Service/DTO/FormRequest/Event vojood nadarad** — validation inline dar controller, write yek `create`-e sade.

**Evidence:**
- `app/Models/ContactRequest.php:8` — `class ContactRequest extends Model` (bedoon SoftDeletes, bedoon HasFactory)
- `app/Http/Controllers/Site/PageController.php:36` — `sendContact(Request $request): RedirectResponse`
- `app/Http/Controllers/Admin/ContactController.php:11` — `class ContactController extends Controller` (bedoon constructor/DI)
- `routes/site.php:71-73` — POST `/contact` ba `throttle:5,1`

---

## 2. Namayesh-e Page-e Contact (Site)

### 2.1 `PageController::show(string $page): View` — `PageController.php:18-31`

Route-e `GET /page/{page}` (`site.page`). Logik:

1. `abort_unless(in_array($page, self::PAGES, true), 404)` (`:20`) — `PAGES = ['contact','about','faq','terms','privacy']` (`:16`). Page-e gheyr-e list = 404.
2. `$content = (array) trans("messages.pages.{$page}")` (`:23`) — **hameye matn-e page az file-e tarjome** miayad, na DB.
3. Baraye contact: `pageData('contact', $content)` (`:28`) data-e ekhtesasi misazad → `view('site.pages.show')`.

### 2.2 `pageData` baraye contact — `PageController.php:68-81`

```php
'contact' => array_merge($content, [                 // :71
  'info' => [                                        // :72-76
    ['icon'=>'phone','label'=>...,'value'=>setting('phone'),        'dir'=>'ltr'],
    ['icon'=>'email','label'=>...,'value'=>setting('email'),        'dir'=>'ltr'],
    ['icon'=>'pin',  'label'=>...,'value'=>setting('address_one'),  'dir'=>null],
  ],
  'departments' => $content['departments'] ?? [],    // :77
]),
```

- **Manba-e info:** `setting()` helper (`app/Helpers/settings.php:10`) — key-haye `phone`, `email`, `address_one` az modul-e Settings.
- **Manba-e departments:** file-e tarjome — `messages.pages.contact.departments` dar `resources/lang/{fa,en,ar,tr}/messages.php` (fa: `:288-292`) = 4 meghdar-e **key-dar**: `sales` (فروش و سفارش‌ها), `support` (پشتیبانی مشتریان), `technical` (مشکلات فنی سایت), `other` (سایر). **Na config, na DB** — in key-ha value-e `department` dar form mishavand.
- View-e form: `site.pages.show` dar `:27` partial-e `site.pages._contact` ra include mikonad (`@include('site.pages._'....)`) — `resources/views/site/pages/_contact.blade.php`.

### 2.3 Form-e _contact.blade.php

`<form action="{{ route('site.contact.send') }}" method="POST">` (`_contact.blade.php:40`) + `@csrf`. Field-ha: `name` (`:46`, maxlength 120), `email` (`:53`, maxlength 190, dir=ltr), `phone` (`:62`, maxlength 40), `department` select az `$data['departments']` (`:69-74`), `message` textarea (`:81`, minlength 10 / maxlength 2000). Har field `@error(...)` + `old()` darad. **Nokte:** action hameshe `route('site.contact.send')` (canonical, bedoon locale) ast — az safhe-haye `{locale}/page/contact` ham be hamin route POST mishavad.

---

## 3. Submit-e Form — `sendContact` — `PageController.php:36-60`

### 3.1 Validation (inline — FormRequest nist) — `:38-44`

```php
$validated = $request->validate([
  'name'      => ['required','string','max:120'],
  'email'     => ['required','email','max:190'],
  'phone'     => ['nullable','string','max:40'],
  'department'=> ['nullable','string','max:40'],
  'message'   => ['required','string','min:10','max:2000'],
]);
```

- `min:10` baraye message — dar view niz `minlength="10"` (`_contact.blade.php:81`).
- **`department` hich check-e `in:sales,...` nadarad** — har string-e ta 40 char qabool ast (gotcha — be `gotchas.md` negah kon).
- Fail → `ValidationException` → redirect back + errors (standard Laravel).

### 3.2 Persist — `:46`

```php
ContactRequest::create($validated); // mass assignment az validated keys — name,email,phone,department,message (read_at nemiayad)
```

Transaction/queue/event nist — yek INSERT-e sade. `read_at` az validated nist pas hamishe `null` shoroo mishavad (test niz `read_at => null` assert mikonad — `tests/Feature/StaticPagesTest.php:99`).

### 3.3 Notification be admin — `:48-57`

```php
try {
  app(NotificationService::class)->notifyAdmins(      // :50
    'contact.new',                                    // :51 — dar TYPES: NotificationService.php:27 (icon ✉️, color accent)
    'پیام تماس جدید',                                  // :52 — title hardcode fa (translate nist!)
    "پیام جدید از {$validated['name']} ({$validated['email']}) دریافت شد.", // :53
    route('admin.contact.index'),                     // :54 — link-e notification
    ['name'=>..., 'email'=>...]                       // :55 — data JSON
  );
} catch (\Throwable $e) { Log::error('Contact notification failed', [...]); } // :57
```

- `notifyAdmins(string $type, string $title, ?string $body, ?string $link, ?array $data): int` (`app/Services/NotificationService.php:61-67`) → `User::where('is_admin', true)->where('is_active', true)` (`:68`) → `Notification::create` baraye har admin.
- Try/catch-e tamami: agar notification fail shavad **form-e karbar meydoonad** — faghat Log error.
- Notification **in-app** ast; email/SMS nist.

### 3.4 Response — `:59`

`return back()->with('success', __('messages.pages.contact.success'))` — redirect be hamon page (az Referer/_previous — yaeni az safhe-ye locale-prefixed ham dorost barmigardad).

### 3.5 Throttle

Route middleware `throttle:5,1` (`routes/site.php:72`): **5 request dar 1 daghighe**. Key-e pishfarz-e Laravel: sha1(domain + '|' + IP) baraye guest. Chon route dakhel-e closure-e `$siteRoutes` do bar register mishavad (canonical `:169` + locale `:172-176`), **har copy-e route limit-e khod-e joda darad** (key-e throttle shamel-e route name nist vali domain/IP yeksan ast — amali-han 5/dagh baraye har URI). Code comment (`:70`) migooyad «برای هر IP».

---

## 4. Admin Inbox — `Admin\ContactController` — `app/Http/Controllers/Admin/ContactController.php`

Controller-e khafan ba 3 action, bedoon Service. Route-ha zir-e middleware-e `[auth, adminPermission, log.admin, refreshRememberMe]` (`routes/admin.php:34`).

### 4.1 `index(Request $request): View` — `:13-30`

```php
$contacts = ContactRequest::query()
  ->when($request->filled('search'), function ($query) use ($request) {  // :16
      $search = trim($request->search);                                   // :17
      $query->where(function ($q) use ($search) {
          $q->where('name','like',"%{$search}%")
            ->orWhere('email','like',"%{$search}%")
            ->orWhere('message','like',"%{$search}%");                    // :19-21 — phone dar search NIST
      });
  })
  ->when($request->boolean('status') === true, fn($q) => $q->unread())    // :24 — checkbox "فقط خوانده‌نشده"
  ->orderByRaw('read_at IS NOT NULL, created_at DESC')                     // :25 — unread aval, bad az Jadid be Ghadim
  ->paginate(15)                                                           // :26
  ->withQueryString();                                                     // :27
return view('admin.contact.list', compact('contacts'));                    // :29
```

- `scopeUnread` = `whereNull('read_at')` (`ContactRequest.php:26-29`).
- Filter-e `status`: checkbox-e value=1 dar view (`admin/contact/list.blade.php:25-28`) — `$request->boolean('status') === true` yani faghat `?status=1` filter faal mishavad; bedoon param hame peyam-ha.
- Sort ba `orderByRaw` — expression-e SQL (`read_at IS NOT NULL` = 0 baraye unread → unread ha aval).
- View-e `list.blade.php`: badge-e «خوانده‌نشده» ruye peyam-e unread (`:63-67`), label-e department ba tarjome (`:89`), aks-haye show/destroy tanha ba `canAccess`(`admin.contact.show`/`admin.contact.destroy`) (`:99, 104`).

### 4.2 `show(ContactRequest $contact): View` — `:32-37`

```php
$contact->markAsRead();  // :34 — SIDE EFFECT: GET + read-only nist!
return view('admin.contact.show', compact('contact'));
```

`markAsRead()` (`ContactRequest.php:31-38`): agar `read_at === null` → `update(['read_at' => now()])`. Yani **baz kardan-e safhe-ye detail = khandan-shodan**. Route model binding: `{contact}` → `ContactRequest` (implicit binding ba var name).

### 4.3 `destroy(ContactRequest $contact): RedirectResponse` — `:39-45`

```php
$contact->delete();  // :41 — HARD delete (model SoftDeletes nadarad!)
return redirect()->route('admin.contact.index')->with('success', __('admin.contact.deleted')); // :43-44
```

---

## 5. Model — `app/Models/ContactRequest.php`

- **Fillable** (`:10-17`): `name, email, phone, department, message, read_at` — 6 field.
- **Casts** (`:19-24`): `read_at => datetime` (method-e jadid-e `casts()`-e Laravel 11).
- **`scopeUnread(Builder $query)`** (`:26-29`): `whereNull('read_at')`.
- **`markAsRead(): static`** (`:31-38`): idempotent — agar alan khande, dobare update nemikonad.
- **`getIsReadAttribute(): bool`** (`:40-43`): `$this->read_at !== null` — dar Blade ba `$contact->is_read`.
- Relations: **hich** relation nist (peyam-e mostaghel, be hich chizi motevabel nist).
- Status handling: **set-e status-e batch/inline vojood nadarad** — tanha ravesh-e khandan-shodan baz kardan-e `show` ast.

---

## 6. Database — `contact_requests`

`database/migrations/2026_08_11_000001_create_contact_requests_table.php:11-20`:

| Column | Type | Null | Code |
|---|---|---|---|
| id | bigint PK | | `:12` |
| name | string | required | `:13` |
| email | string | required (unique NIST) | `:14` |
| phone | string(40) | nullable | `:15` |
| department | string(40) | nullable | `:16` |
| message | text | required | `:17` |
| read_at | timestamp | nullable | `:18` |
| created_at/updated_at | timestamps | | `:19` |

**Hich index/FK nist** (joz PK). Seeder: `database/seeders/ContactRequestSeeder.php:12-16` — 3 record-e sample (2 unread ba `subDays(2)/subDays(1)`, 1 read ba `subHours(5)` + `read_at subHours(3)`); seda dar `DatabaseSeeder.php:57`.

---

## 7. Authorization (RBAC)

- 3 permission dar `config/permissions.php:209-215` — gorouh-e `contact`: `index` → `admin.contact.index`, `show` → `admin.contact.show`, `destroy` → `admin.contact.destroy` (gheyr-e mostaghim az naame access: `destroy` → `.delete`).
- Middleware-e `AdminPermission` (`app/Http/Middleware/AdminPermission.php:34-55`): superadmin (`is_admin`) pass (`:43-45`), gheyr-e aan `$user->canAccess($routeName)` (`:53`) az `app/Models/Traits/HasAdminPermissions.php:49-56`.
- Site-e `POST /contact`: **hich auth nist** — public + throttle.

---

## 8. Tests — `tests/Feature/StaticPagesTest.php`

| Test | Line | Chera mikhonad |
|---|---|---|
| `test_contact_page_shows_form_and_info_cards` | `:69` | GET `/page/contact` → assertOk + form fields |
| `test_contact_form_stores_message_and_redirects_back` | `:83` | POST `/contact` → assertRedirect `/page/contact` + DB row ba `read_at null` |
| `test_contact_form_validates_required_fields` | `:106` | POST-e khali → session errors + `assertDatabaseCount 0` |
| `test_admin_contact_index_requires_permission` | `:137` | user-e bedoon permission → `/admin/contact` → assertForbidden |
| `test_admin_can_list_read_and_delete_contact_messages` | `:144` | list + show (`read_at` por mishe) + delete (`DatabaseMissing`) |
| `test_super_admin_can_access_contact_inbox` | `:176` | `is_admin` → assertOk |

---

## 9. Evidence Matrix

| Claim | File:Line |
|---|---|
| PAGES const + show + abort 404 | `app/Http/Controllers/Site/PageController.php:16-20` |
| pageData: info az setting() + departments az lang | `app/Http/Controllers/Site/PageController.php:68-81` |
| sendContact validation 5 field | `app/Http/Controllers/Site/PageController.php:38-44` |
| create + notification try/catch + back | `app/Http/Controllers/Site/PageController.php:46-59` |
| throttle:5,1 + route do-copy | `routes/site.php:71-73, 42, 169, 172-176` |
| departments lang keys (4 ta) | `resources/lang/fa/messages.php:288-292` |
| Admin index search/unread/sort/paginate 15 | `app/Http/Controllers/Admin/ContactController.php:13-30` |
| markAsRead dar show | `app/Http/Controllers/Admin/ContactController.php:34` + `app/Models/ContactRequest.php:31-38` |
| destroy hard delete | `app/Http/Controllers/Admin/ContactController.php:41` + `app/Models/ContactRequest.php:8` |
| scopeUnread / is_read accessor | `app/Models/ContactRequest.php:26-29, 40-43` |
| Migration bedoon index | `database/migrations/2026_08_11_000001_create_contact_requests_table.php:11-20` |
| notifyAdmins is_admin+is_active | `app/Services/NotificationService.php:61-68` |
| Permissions 3 ta | `config/permissions.php:209-215` |
| Admin routes | `routes/admin.php:78-80` (middleware group `:34`) |

## 10. Open Questions (Soalat-e Baz)

- `department` be soorat-e free string zakhire mishavad (faghat max:40) — list-e lang be onvane source-e UI ast, na validation. UNKNOWN: aya amdan-e azaadi baraye department-haye CUSTOM ast?
- Title/body-e notification hardcode fa hastand (`PageController.php:52-53`) — translate nist. UNKNOWN: aya multi-lang-e notification dar backlog ast?
- `orderByRaw('read_at IS NOT NULL, ...')` MySQL/SQLite-specific ast — UNKNOWN: sage Postgres niz `IS NOT NULL` bool-e integer return mikonad, vali test nashode.
