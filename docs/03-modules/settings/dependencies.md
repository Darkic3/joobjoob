# Settings — Dependencies (Verified Audit)

---

## Direct Dependencies

### Models
- `Setting` — key-value store (no SoftDeletes)

### Services
- `SettingService` — cache + file upload + CRUD

### Helpers
- `app/Helpers/settings.php` — `setting()`, `settingOverride()`, `trans_or()`
- `app/Helpers/ImageHandler.php` — WebP conversion for image uploads
- `app/Helpers/Uploader.php` — non-image file uploads

### Config
- `config/settings.php` — tab definitions, field schemas, defaults

### External
- None — pure DB + filesystem

---

## Outbound Dependencies (what Settings consumes)

| Dependency | Usage | Notes |
|------------|-------|-------|
| `Cache::rememberForever()` | Unified cache | Single key `shopora_settings_all` |
| `DB::transaction()` | Atomic group update | All fields in group updated together |
| `ImageHandler::handle()` | Image upload → WebP | For `file` type fields |
| `Uploader::upload()` | Non-image upload | For `video` type fields |
| `ImageHandler::delete()` | Old file cleanup | After successful new upload |

---

## Inbound Dependencies (who calls setting())

The `setting()` helper is called **everywhere** — it's the primary config mechanism:

| Consumer Module | Keys Used | Notes |
|-----------------|-----------|-------|
| **Layout** | `site_name`, `site_slogan`, `logo`, `favicon` | Header, meta tags |
| **Home** | `slider_title`, `home_*_title`, `announce_*` | Section titles |
| **Auth-OTP** | `otp_template`, `kavenegar_key`, `kavenegar_sender` | SMS config |
| **Appearance** | `festive_mode`, `festive_style`, `festive_badge_text` | Confetti toggle |
| **Smart Notifications** | `allow_open_requests`, `enable_ai` | Feature toggles (default OFF) |
| **Contact** | `email`, `phone`, `mobile`, `address_one`, `working_hours` | Footer, contact page |
| **Socials** | `social_*` | Footer social links |
| **Footer** | `footer_description`, `footer_copyright` | Footer content |

---

## Cross-Module Gotchas

### Auth-OTP → Settings
- `OtpService::send()` reads `setting('otp_template')` and `setting('kavenegar_key')` via config fallback
- If settings not configured → SMS fails silently (Kavenegar returns error)
- `config/kavenegar.php` has defaults; `settings` table overrides

### Appearance → Settings
- `festive_mode` boolean → `@if(setting('festive_mode'))` in hero section
- `festive_style` select → determines confetti CSS class
- Toggle is instant (cache flushed on save)

### Smart Notifications → Settings
- `allow_open_requests` → `layouts/site/app.blade.php:90`
- `enable_ai` → **zero consumers** (future feature)
- Both default `false` — admin toggle exists but limited functionality

---

## Schema

### `settings` table
```
id         BIGINT PK AUTO INCREMENT
key        VARCHAR UNIQUE INDEX (e.g., "site_name")
value      TEXT NULLABLE (stores strings, JSON, file paths)
type       VARCHAR DEFAULT "text" (text|textarea|file|video|boolean|select)
group      VARCHAR DEFAULT "general" INDEX (e.g., "general", "sms", "home")
label      VARCHAR NULLABLE (Persian label for admin UI)
options    JSON NULLABLE (for select fields: {key: label})
sort       INTEGER DEFAULT 0 (unused in current UI)
created_at TIMESTAMP
updated_at TIMESTAMP
```

### Indexes
- `key` — unique + index (fast lookup)
- `group` — index (group queries)

---

## Coupling Assessment

**Loose coupling:** Settings is a leaf node — it provides config but triggers nothing. All consumers read via `setting()` helper, which is a global function.

**Tight coupling:** None — no inverse dependencies from Settings to other modules.

**Risk:** The `setting()` helper is called everywhere — a cache corruption or flush failure affects the entire site.

---

## Evidence

- `app/Services/SettingService.php` — cache, file upload, CRUD
- `app/Models/Setting.php` — static helpers, schema
- `app/Helpers/settings.php` — global helpers
- `config/settings.php` — tab/field definitions
- `app/Http/Controllers/Admin/SettingController.php` — admin UI
- `app/Http/Requests/Admin/UpdateSettingsRequest.php` — validation
- `database/migrations/2026_07_25_094140_create_settings_table.php` — schema
- `routes/admin.php:58-59` — 2 routes
- `resources/views/layouts/site/app.blade.php:90` — allow_open_requests
