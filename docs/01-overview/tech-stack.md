# Stack-e Fanni — Joobjoob Tech Stack

> **Manba-e Haghighi:** `composer.json`, `package.json`, `vite.config.js`, `tailwind.config.js`, `config/*.php`, `bootstrap/app.php`, `app/Providers/AppServiceProvider.php`, `.env` / `.env.example`
> **Ghanoon:** Hame adad-ha va config-ha mostaghim az file khande shode — hich hadsi nist.

---

## 1. Naghshe-e Rah — Stack Overview

```
┌─────────────────────────────────────────────────────────────┐
│  PHP 8.2  +  Laravel 11  +  MySQL (joobjoob)                 │
│  Vite 5 + Tailwind 3 + Alpine 3 + Swiper + GSAP            │
│  Cache=database  Queue=database  Session=database           │
│  Mail=log  Filesystem=local/public/uploads                   │
│  Auth=session (mobile OTP via Kavenegar)  Gate=admin        │
└─────────────────────────────────────────────────────────────┘
```

---

## 2. Core — Laravel 11 + PHP 8.2

### 2.1 PHP Version

| Item | Value | Evidence |
|---|---|---|
| PHP requirement | `^8.2` | `composer.json:11 "php": "^8.2"` |
| Features used | `readonly class` (DTOs), `match` expression (Factory, Gate) | `app/DTOs/ProductDTO.php:8 readonly`, `app/Payments/PaymentGatewayFactory.php:16 match` |
| Runtime | `APP_TIMEZONE=Asia/Tehran` (prod), `UTC` in `.env.example` | `.env: APP_TIMEZONE=Asia/Tehran`, `config/app.php: timezone` |

### 2.2 Laravel Framework

| Item | Value | Evidence |
|---|---|---|
| `laravel/framework` | `^11.0` | `composer.json:12 "laravel/framework": "^11.0"` |
| Skeleton | New `bootstrap/app.php` with `Application::configure()` — no `app/Http/Kernel.php` | `bootstrap/app.php:12 Application::configure(basePath: ...)` |
| `laravel/tinker` | `^2.9` | `composer.json:13` |
| Other prod deps | **Hich** — RBAC, upload, image, OTP hame custom (no `spatie/permission`, no `intervention/image`) | `composer.json: require` faghat 3 package |
| Dev deps | `fakerphp/faker ^1.23`, `laravel/pint ^1.30`, `laravel/sail ^1.26`, `mockery ^1.6`, `nunomaduro/collision ^8.0`, `phpunit ^10.5`, `spatie/laravel-ignition ^2.4` | `composer.json:15-22 require-dev` |
| Autoload PSR-4 | `App\ => app/`, `Database\Factories => database/factories` | `composer.json:24 psr-4` |
| Autoload files | 6 global helpers | `composer.json:30 files: [jdate.php, hijri.php, date.php, settings.php, locale.php, format_price.php]` |
| DB file | `database/database.sqlite` vojoud darad vali `DB_CONNECTION=mysql` dar `.env` — SQLite faghat artifact-e test | `ls database/database.sqlite` exists, `.env: DB_CONNECTION=mysql` |
| `.env.example` | `DB_CONNECTION=sqlite` (baraye setup-e sari) | `.env.example: DB_CONNECTION=sqlite` |

### 2.3 Laravel 11 — Tafavot ba 10

- **No Kernel:** `app/Http/Kernel.php` hazf — middleware dar `bootstrap/app.php::withMiddleware()` sabt mishavad.
- **No RouteServiceProvider:** Route-ha dar `bootstrap/app.php::withRouting(web: routes/web.php)` load mishavand.
- **Providers:** `bootstrap/providers.php` list-e provider-ha (faghat `AppServiceProvider` custom).
- Evidence: `bootstrap/app.php:12-38`, `bootstrap/providers.php: [AppServiceProvider::class]`

---

## 3. Frontend Build — Vite 5 + Tailwind 3 + Alpine 3 + Swiper + GSAP

### 3.1 Vite 5

| Item | Value | Evidence |
|---|---|---|
| `vite` | `^5.0` | `package.json:15 "vite": "^5.0"` |
| `laravel-vite-plugin` | `^1.0` | `package.json:12 "laravel-vite-plugin": "^1.0"` |
| Entries (5) | `resources/css/app.css`, `resources/css/app-admin.css`, `resources/js/ui-effects.js`, `resources/js/app.js`, `resources/js/app-admin.js` | `vite.config.js:7-13 input: [...]` |
| Refresh | `refresh: true` | `vite.config.js:14` |
| Scripts | `npm run dev → vite`, `npm run build → vite build` | `package.json:4-7 scripts` |
| Type | `"type": "module"` (ESM) | `package.json:3` |

```js
// vite.config.js — actual
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
export default defineConfig({
    plugins: [laravel({
        input: ['resources/css/app.css','resources/css/app-admin.css','resources/js/ui-effects.js','resources/js/app.js','resources/js/app-admin.js'],
        refresh: true,
    })],
});
```

### 3.2 Tailwind CSS 3.4

| Item | Value | Evidence |
|---|---|---|
| `tailwindcss` | `^3.4` | `package.json:14 "tailwindcss": "^3.4"` |
| `postcss` | `^8.4` + `autoprefixer ^10.4` | `package.json:11,13` |
| Config | `tailwind.config.js` — `darkMode: 'class'`, content `resources/views/**/*.blade.php`, font `Vazirmatn`, colors `primary` indigo scale, spacing `18/88`, screen `3xl:1920px` | `tailwind.config.js:5 darkMode, 6 content, 12 fontFamily sans: ['Vazirmatn', ...]` |
| No UI framework | No React/Vue — faghat Blade + Tailwind + Alpine | `package.json` — hich `react`/`vue` nist |

### 3.3 Alpine.js 3.14

| Item | Value | Evidence |
|---|---|---|
| `alpinejs` | `^3.14` | `package.json:10 "alpinejs": "^3.14"` |
| `@alpinejs/collapse` | `^3.15.12` | `package.json:9` |
| Usage | Dropdown, modal, mobile sidebar, collapse — dar `resources/js/app.js` + `app-admin.js` | `resources/js/app.js: import Alpine from 'alpinejs'` + `resources/views/**/*.blade.php: x-data, x-show` |

### 3.4 Swiper + GSAP — No React/Vue

| Item | Value | Evidence |
|---|---|---|
| `swiper` | `^14.0.7` | `package.json:18 "swiper": "^14.0.7"` |
| `gsap` | `^3.15.0` | `package.json:19 "gsap": "^3.15.0"` |
| Usage Swiper | Hero slider, product carousel `resources/views/components/product-card.blade.php`, `resources/views/site/home.blade.php` | `resources/js/ui-effects.js` + `HomeController` |
| Usage GSAP | Page transitions, announce bar, scroll effects | `resources/js/ui-effects.js: import gsap from 'gsap'` |

### 3.5 Frontend Evidence Table

| File | Evidence |
|---|---|
| `package.json:8-20` | `devDependencies: @alpinejs/collapse, alpinejs, autoprefixer, laravel-vite-plugin, postcss, tailwindcss, vite` + `dependencies: gsap, swiper` |
| `vite.config.js:4-17` | 5 entries + refresh |
| `tailwind.config.js:1-42` | darkMode class, content blade, theme extend Vazirmatn, primary indigo |
| `postcss.config.js` | `tailwindcss, autoprefixer` |
| `resources/css/app.css` / `app-admin.css` | `@tailwind base/components/utilities` |
| `resources/js/app.js` | `import Alpine, collapse, Swiper, gsap` |

---

## 4. Laravel Subsystems — Cache / Queue / Session / Mail / Filesystem

### 4.1 Cache — Database Driver

```php
// config/cache.php:18
'default' => env('CACHE_STORE', 'database'),

// config/cache.php:41-46
'database' => [
    'driver' => 'database',
    'table' => env('DB_CACHE_TABLE', 'cache'),
    'connection' => env('DB_CACHE_CONNECTION', null),
]
```

| Item | Value | Evidence |
|---|---|---|
| Default store | `database` | `config/cache.php:18 env('CACHE_STORE','database')`, `.env: CACHE_STORE=database` |
| Table | `cache` (plus `cache_locks`) | `config/cache.php:43`, `database/migrations/0001_01_01_000001_create_cache_table.php` |
| No Redis | `redis` config vojoud darad vali estefade nemishavad — `CACHE_STORE` database ast | `config/cache.php:73 redis` defined vali default nist |
| Prefix | `Str::slug(APP_NAME,'_').'_cache_'` | `config/cache.php:105` |
| Pattern used | `Cache::rememberForever(key, fn)` + `Cache::forget(key)` — na `remember` ba TTL | `app/Services/SettingService.php:53 rememberForever`, `app/Services/LocaleService.php:41 rememberForever` |

- **Key-haye asli:** `joobjoob_settings_all` (SettingService), `joobjoob_languages_all` (LocaleService), `promo_banners_active`, `trust_badges_active`, `admin.permissions.version` + per-user `admin.permissions.v{version}.{id}`.
- Evidence: `config/cache.php:18,41`, `.env: CACHE_STORE=database`, `app/Services/SettingService.php:17 CACHE_ALL`, `app/Services/LocaleService.php:21 CACHE_ALL`

### 4.2 Queue — Database Driver

```php
// config/queue.php:16
'default' => env('QUEUE_CONNECTION', 'database'),

// config/queue.php:37-44
'database' => [
    'driver' => 'database',
    'table' => env('DB_QUEUE_TABLE', 'jobs'),
    'queue' => env('DB_QUEUE', 'default'),
    'retry_after' => 90,
]
```

| Item | Value | Evidence |
|---|---|---|
| Default | `database` | `config/queue.php:16`, `.env: QUEUE_CONNECTION=database` |
| Tables | `jobs`, `job_batches`, `failed_jobs` | `config/queue.php:40-43, 90-91, 109`, `database/migrations/0001_01_01_000002_create_jobs_table.php` |
| No Horizon | Hich `laravel/horizon` nist — suprvisore niaz nist | `composer.json` — hich horizon |
| Schedule | `orders:cancel-expired` har 5 daghighe (`everyFiveMinutes`) | `routes/console.php` ya `app/Console/Commands/CancelExpiredOrders.php` |
| Dispatch? | **Hich `dispatch()` dar code nist** — queue config shode vali job dispatch nemishavad | `grep -r "dispatch" app/` → hich natije baraye Job |

### 4.3 Session — Database Driver

```php
// config/session.php:21
'driver' => env('SESSION_DRIVER', 'database'),

// config/session.php:35
'lifetime' => env('SESSION_LIFETIME', 120),

// config/session.php:89
'table' => env('SESSION_TABLE', 'sessions'),
```

| Item | Value | Evidence |
|---|---|---|
| Driver | `database` | `config/session.php:21`, `.env: SESSION_DRIVER=database` |
| Lifetime | `120` minutes | `config/session.php:35`, `.env: SESSION_LIFETIME=120` |
| Table | `sessions` | `config/session.php:89`, `database/migrations/0001_01_01_000000_create_users_table.php` contains `sessions` |
| Encrypt | `false` | `config/session.php:50`, `.env: SESSION_ENCRYPT=false` |
| Cookie | `Str::slug(APP_NAME,'_').'_session'`, `path=/`, `same_site=lax`, `http_only=true` | `config/session.php:131-203` |
| Keys used | `cart`, `wishlist`, `recently_viewed`, `checkout_address_id`, `checkout_shipping_method_id`, `checkout_shipping_price`, `coupon`, `locale` | `app/Services/CartService.php:15 cart`, `app/Services/WishlistService.php:14 wishlist`, `app/Http/Controllers/Site/CheckoutController.php:56 checkout_address_id` |

### 4.4 Mail — Log Driver

```php
// config/mail.php:17
'default' => env('MAIL_MAILER', 'log'),

// config/mail.php:68
'log' => ['transport' => 'log', 'channel' => env('MAIL_LOG_CHANNEL')],
```

| Item | Value | Evidence |
|---|---|---|
| Default mailer | `log` | `config/mail.php:17`, `.env: MAIL_MAILER=log` |
| Means | Hich email ersal nemishavad — log be `storage/logs/laravel.log` | `config/mail.php:68` + `LOG_CHANNEL=stack` |
| From | `hello@example.com` / `Joobjoob` | `config/mail.php:99`, `.env: MAIL_FROM_ADDRESS` |
| SMTP config | Vojoud darad vali estefade nemishavad (host 127.0.0.1:2525) | `config/mail.php:39 smtp` + `.env: MAIL_HOST=127.0.0.1` |

### 4.5 Filesystem — Local + Public/Uploads

```php
// config/filesystems.php:16
'default' => env('FILESYSTEM_DISK', 'local'),

// config/filesystems.php:31-45
'local'  => ['driver'=>'local', 'root'=>storage_path('app'), 'throw'=>false],
'public' => ['driver'=>'local', 'root'=>storage_path('app/public'), 'url'=>APP_URL.'/storage', 'visibility'=>'public'],
'links'  => [public_path('storage') => storage_path('app/public')],

// config/paths.php
'upload' => env('UPLOAD_PATH', 'uploads'),
```

| Item | Value | Evidence |
|---|---|---|
| Default disk | `local` → `storage/app` | `config/filesystems.php:16`, `.env: FILESYSTEM_DISK=local` |
| Upload path | `public/uploads/{folder}/` — na `storage/app/public` — mostaghim dar `public/` | `config/paths.php:12 'upload' => env('UPLOAD_PATH','uploads')`, `app/Helpers/ImageHandler.php:38 public_path("{$baseUpload}/{$folderName}")` |
| Env | `UPLOAD_PATH=uploads` | `.env.example: UPLOAD_PATH="uploads"` |
| Symlink | `public/storage → storage/app/public` (standard) vali upload-ha azash estefade **nemikonand** | `config/filesystems.php:72 links` |
| S3 | Config vojoud darad vali estefade nemishavad | `config/filesystems.php:46 s3` |
| Handler | `ImageHandler::handle(folder, file, sizes)` → `ImageUploader::upload` + `ImageResizer::resize` → GD `imagewebp` | `app/Helpers/ImageHandler.php:12`, `app/Helpers/ImageUploader.php:62`, `app/Helpers/ImageResizer.php:23` |

### 4.6 Logging + Broadcast

| Item | Value | Evidence |
|---|---|---|
| Log channel | `stack` → `single` (file `storage/logs/laravel.log`) | `config/logging.php`, `.env: LOG_CHANNEL=stack, LOG_STACK=single` |
| Log level | `debug` | `.env: LOG_LEVEL=debug` |
| Broadcast | `log` | `config/broadcasting.php`, `.env: BROADCAST_CONNECTION=log` — hich websocket/pusher nist |

---

## 5. Auth — Session + Mobile OTP via Kavenegar

### 5.1 Auth Config — Session Guard

```php
// config/auth.php:18-45
'defaults' => ['guard' => env('AUTH_GUARD','web'), 'passwords'=>'users'],
'guards'   => ['web' => ['driver'=>'session', 'provider'=>'users']],
'providers'=> ['users' => ['driver'=>'eloquent', 'model'=>env('AUTH_MODEL', User::class)]],
```

| Item | Value | Evidence |
|---|---|---|
| Guard | `web` — `driver: session` | `config/auth.php:40-45` |
| Provider | `eloquent` → `App\Models\User` | `config/auth.php:65` |
| No JWT/Sanctum | Hich `sanctum` ya `passport` nist — session cookie mehvar | `composer.json` — hich sanctum |
| Redirect guests | `admin/* → admin.login`, digar → `login` | `bootstrap/app.php:30 redirectGuestsTo` |
| User fields | `mobile` unique (OTP), `email` nullable unique, `password` (random 32 for OTP users), `is_active`, `is_admin`, `confirm_code` 4-digit, `confirm_code_expires_at`, `locale`, `theme` | `app/Models/User.php`, `database/migrations/0001_01_01_000000_create_users_table.php` + `2026_08_06_000001_add_is_admin`, `2026_08_16_000001_add_confirm_code` |

### 5.2 OTP Flow — Kavenegar SMS

```php
// config/kavenegar.php
'base_url'       => env('KAVENEGAR_BASE_URL', 'https://api.kavenegar.com/v1'),
'local_mobile'   => env('LOCAL_MOBILE'),        // dar local faghat be in shomare ersal
'test_number'    => env('SMS_TEST_NUMBER'),
'otp_ttl_minutes'=> (int) env('OTP_TTL_MINUTES', 2),
```

| Item | Value | Evidence |
|---|---|---|
| Provider | **Kavenegar** — `https://api.kavenegar.com/v1` | `config/kavenegar.php:16 base_url` |
| Service | `App\Services\OtpService` (218 lines) + `App\Helpers\Sms` | `app/Services/OtpService.php`, `app/Helpers/Sms.php` |
| Env vars | `KAVENEGAR_BASE_URL`, `LOCAL_MOBILE=09367300130`, `SMS_TEST_NUMBER`, `OTP_TTL_MINUTES=2` | `.env: LOCAL_MOBILE=09367300130`, `config/kavenegar.php:20-26` |
| Code | 4-raghami `str_pad(random_int(0,9999),4,'0')`, zakhire dar `users.confirm_code` | `app/Services/OtpService.php:25 CODE_LENGTH=4, 189 generateCode()` |
| TTL | 2 daghighe → `confirm_code_expires_at = now()->addMinutes(2)` | `app/Services/OtpService.php:120`, `config/kavenegar.php:26` |
| API | `Sms::sendLookup(template, ['token'=>code, 'token2'=>siteName], mobile)` — template default `verify` az `setting('otp_template')` | `app/Services/OtpService.php:123 template, 129 sendLookup` |
| Normalize | `persian2LatinDigit` → `preg_replace(/\D/)` → handle `0098/98/9` prefix → `09xxxxxxxxx` | `app/Services/OtpService.php:33 normalizeMobile()` |
| Validation | `isValidMobile: /^09\d{9}$/` | `app/Services/OtpService.php:52 isValidMobile()` |
| Routes | `POST /login/check-phone`, `POST /login/send (throttle:5,1)`, `POST /login/otp/verify (throttle:5,1)`, `POST /login/otp/resend (throttle:3,1)` | `routes/site.php:130-141` |
| Throttle | OTP send 5/min, verify 5/min, resend 3/min, contact 5/min | `routes/site.php:133 throttle:5,1`, `routes/admin.php` throttle |

**Flow-e kamel:**

```
POST /login/check-phone {mobile} → OtpService::userExists(mobile) → {exists: bool}
POST /login/send {mobile, name?} → OtpService::send():
  - normalize → isValidMobile → check is_active
  - agar !exists && !name → error otp_name_required
  - agar !exists → User::create(name, mobile, password=Str::random(32), is_active=true) + WelcomeDiscountService
  - generateCode → user->update(confirm_code, confirm_code_expires_at)
  - Sms::sendLookup(template, [token:code, token2:cleanSiteName], mobile) → check return.status==200
POST /login/otp/verify {mobile, code} → OtpService::verify():
  - find user → check is_active → check code match → check not expired
  - user->update(confirm_code=null) → Auth::login(user, true) → success
```

- Evidence: `app/Services/OtpService.php:23-218`, `app/Helpers/Sms.php`, `config/kavenegar.php:16-27`, `routes/site.php:128-141`, `.env: LOCAL_MOBILE, OTP_TTL_MINUTES`

### 5.3 Password + Reset

- Password dar User `bcrypt` ba `BCRYPT_ROUNDS=12` (`.env: BCRYPT_ROUNDS=12`).
- `password_timeout 10800` (3 saat) — `config/auth.php:115`.
- Reset table `password_reset_tokens` — vali OTP flow-e asli ast, na reset-e email.
- Evidence: `config/auth.php:94-116`, `.env: BCRYPT_ROUNDS=12`

---

## 6. Authorization — Gate admin (Custom RBAC)

### 6.1 Gate Definition

```php
// app/Providers/AppServiceProvider.php:38
Gate::define('admin', fn ($user) => $user->isAdmin());
// User::isAdmin() → $this->is_admin === true (boolean cast)
// User::is_admin fillable + cast boolean — migration 2026_08_06_000001
```

| Item | Value | Evidence |
|---|---|---|
| Gate name | `admin` | `app/Providers/AppServiceProvider.php:38 Gate::define('admin', ...)` |
| Check | `$user->is_admin` boolean — `true` → superadmin | `app/Models/User.php: isAdmin()`, `app/Models/Traits/HasAdminPermissions.php` |
| Superadmin | `is_admin=true` → bypass hame permission check-ha (AdminPermission middleware) | `app/Http/Middleware/AdminPermission.php:43 if ($user->is_admin) return $next` |
| Owner seed | `AdminOwnerSeeder` — env `ADMIN_OWNER_EMAIL=admin@joobjoob.test`, `MOBILE=09120000000` | `.env: ADMIN_OWNER_*`, `database/seeders/AdminOwnerSeeder.php` |

### 6.2 RBAC Tables (Custom — No Package)

| Table | Columns | Evidence |
|---|---|---|
| `roles` | `id, name, title, timestamps` | `database/migrations/2026_08_06_000002_create_roles_table.php` |
| `permissions` | `id, name (route name), title, timestamps` | `2026_08_06_000003_create_permissions_table.php` |
| `permission_role` | `permission_id, role_id` | `2026_08_06_000004_create_permission_role_table.php` |
| `role_user` | `role_id, user_id` | `2026_08_06_000005_create_role_user_table.php` |
| `request_logs` | `user_id, method, url, ip, status_code, payload json` | `2026_08_06_000006_create_request_logs_table.php` |
| `audit_logs` | `user_id, action, auditable_type/id, old_values, new_values` | `2026_09_01_000001_create_audit_logs_table.php` |

- **HasAdminPermissions trait:** `app/Models/Traits/HasAdminPermissions.php` — `canAccess(routeName)` ba `Cache::rememberForever('admin.permissions.version')` + per-user cache `admin.permissions.v{version}.{id}`. `version()` har bar ke permission taghir konad increment mishavad.
- **permissions.php config:** `config/permissions.php` — `free: [admin.ckeditor.upload, admin.logout, admin.dashboard.index]` + `permissions: [products, product-categories, brands, ... 20 group]`.
- **AdminPermission middleware:** Check-e `!is_admin && !roles()->exists()` → redirect home (na admin.login — jaloo-giri az loop).
- Evidence:
  - `app/Providers/AppServiceProvider.php:38 Gate::define('admin', ...)`
  - `app/Models/Traits/HasAdminPermissions.php:27 rememberForever admin.permissions.version, 63 forget per-user`
  - `config/permissions.php:11 free, 17 permissions (products, product-categories, brands, specifications, blogs, blog-categories, hero-slides, trust-badges, promo-banners, settings, languages, users, roles, orders, reviews, contact, shipping-methods, banks, notifications, audit-logs)`
  - `app/Http/Middleware/AdminPermission.php:20-56`
  - `database/seeders/AdminOwnerSeeder.php`

### 6.3 Admin Route Guard

```php
// routes/admin.php:34
Route::middleware(['auth', 'adminPermission', 'log.admin'])->group(function () {
    Route::resource('products', ProductController::class);
    // ... 20+ resource
});
// bootstrap/app.php:30
redirectGuestsTo(fn($req) => str_starts_with($req->path(),'admin') ? route('admin.login') : route('login'))
```

- Evidence: `routes/admin.php:24 prefix admin, name admin., 27 login public, 34 middleware auth+adminPermission+log.admin`

---

## 7. Noda-ha va Tasmim-haye Stack

### 7.1 Chera Database baraye Hame?

- **Cache database:** Sade, niaz be Redis nadarad, baraye traffic-e fe'li kafi. `rememberForever` ba `flush()` kafi.
- **Queue database:** Hich job dispatch nemishavad — faghat schedule-e command. Pas database kafi.
- **Session database:** Behtar az file baraye multi-tab + load balancer-e ayande + 120min lifetime.

### 7.2 Chera Log baraye Mail/Broadcast?

- Mail hanooz real nashode — OTP via SMS ast, na email. Agar email lazem shod `MAIL_MAILER=smtp` dar `.env` taghir mikonad.
- Broadcast ham hanooz niaz nashode — notification-ha polling via `GET /api/notifications/unread-count`.

### 7.3 Chera Custom RBAC na Spatie?

- Control-e bishtar ru-ye UX-e permission tree (group → access → route name) + versioned cache.
- `config/permissions.php` be onvan-e single source of truth — UI ham az hamin mikhanad.

---

## 8. Jadval-e Dalil — Evidence Table

| Mozoue | Masir-e File | Khat-e Kelidi |
|---|---|---|
| PHP 8.2 | `composer.json` | `11 "php": "^8.2"` |
| Laravel 11 | `composer.json` | `12 "laravel/framework": "^11.0"` |
| Vite 5 | `package.json` | `15 "vite": "^5.0"` |
| Tailwind 3.4 | `package.json` | `14 "tailwindcss": "^3.4"` |
| Alpine 3.14 | `package.json` | `10 "alpinejs": "^3.14"` |
| Swiper | `package.json` | `18 "swiper": "^14.0.7"` |
| GSAP | `package.json` | `19 "gsap": "^3.15.0"` |
| Vite entries | `vite.config.js` | `7-13 input: [app.css, app-admin.css, ui-effects.js, app.js, app-admin.js]` |
| Tailwind config | `tailwind.config.js` | `5 darkMode class, 12 font Vazirmatn, 17 primary indigo` |
| Cache database | `config/cache.php` | `18 default env('CACHE_STORE','database'), 41 database driver` |
| Queue database | `config/queue.php` | `16 default database, 37-44 database connection` |
| Session database | `config/session.php` | `21 driver database, 35 lifetime 120, 89 table sessions` |
| Mail log | `config/mail.php` | `17 default env('MAIL_MAILER','log'), 68 log transport` |
| Filesystem local | `config/filesystems.php` | `16 default local, 31 local root storage/app, 39 public root storage/app/public` |
| Upload path | `config/paths.php` | `12 'upload' => env('UPLOAD_PATH','uploads')` |
| Auth session | `config/auth.php` | `40 guards web driver session, 65 eloquent User` |
| OTP Kavenegar | `config/kavenegar.php` | `16 base_url, 20 local_mobile, 26 otp_ttl_minutes` |
| `.env` prod | `.env` | `SESSION_DRIVER=database, CACHE_STORE=database, QUEUE_CONNECTION=database, MAIL_MAILER=log, FILESYSTEM_DISK=local` |
| Gate admin | `app/Providers/AppServiceProvider.php` | `38 Gate::define('admin', fn($u)=>$u->isAdmin())` |
| Bootstrap | `bootstrap/app.php` | `12 Application::configure, 19 alias adminPermission, 24 web [SetLocale, SetTheme]` |
| Kavenegar env | `.env.example` | `KAVENEGAR_BASE_URL, LOCAL_MOBILE, SMS_TEST_NUMBER, OTP_TTL_MINUTES=2` |

---

## 9. Checklist-e Taghir-e Stack (Agar Niaz Shod)

| Taghir | Che bayad kard | File |
|---|---|---|
| Cache → Redis | `.env: CACHE_STORE=redis` + `composer require predis/predis` | `.env`, `config/cache.php:73 redis`, `config/database.php: redis` |
| Queue → Redis/Horizon | `.env: QUEUE_CONNECTION=redis` + `composer require laravel/horizon` | `.env`, `config/queue.php` |
| Mail → SMTP | `.env: MAIL_MAILER=smtp` + set `MAIL_HOST/PORT/USERNAME` | `.env`, `config/mail.php:39 smtp` |
| Filesystem → S3 | `.env: FILESYSTEM_DISK=s3` + set `AWS_*` | `.env`, `config/filesystems.php:46 s3` |
| Session → Redis | `.env: SESSION_DRIVER=redis` | `.env`, `config/session.php:21` |

---

*Payan-e tech-stack.md — hame adad-ha va config-ha az file-ha verify shode (Phase 1.5).*
