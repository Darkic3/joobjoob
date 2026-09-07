# Auth / OTP — Logic (Verified Audit)

> **Service:** `OtpService` (218 lines) | **Controllers:** `Site\OtpController` (237), `Site\SiteAuthController` (55)
> **Request:** `Site\SendOtpAjaxRequest` (exists) — `VerifyOtpRequest` does **NOT** exist (inline `digits:4`)
> **Model:** `User` (fillable/hidden/casts) | **Helper:** `Sms::sendLookup:118` | **Config:** `config/kavenegar.php`
> **Migrations:** `0001_01_01_000000_create_users_table` (`mobile` **unique**), `2026_08_16_000001_add_confirm_code_to_users_table`

---

## Login flow (OTP only — no password login exists)

1. `GET /login` (`SiteAuthController::showLogin`) — authed → dashboard; `?change=1` clears session keys `otp_mobile` + `otp_new_user`.
2. `POST /login/check-phone` (AJAX) — `{exists: bool, valid: bool}` — drives name-field visibility. **UNTHROTTLLED** (see gotchas).
3a. `POST /login/send` (form, `throttle:5,1`) — `normalizeMobile` → inline format validation → new user without name → back with `otp_new_user` session flag + name error → else `OtpService::send`.
3b. `POST /login/send-ajax` (`throttle:5,1`) — `SendOtpAjaxRequest` (REGISTRATION-ONLY: rejects existing mobile with `registered:true`); custom `failedValidation` JSON `{success:false, field:'mobile'|'name', registered, error}` → on success `{success, message, verifyUrl}`.
4. `GET /login/verify` (`showVerify`) — session `otp_mobile` gate; authed → dashboard.
5. `POST /login/otp/verify` (`throttle:5,1`) — `code` digits:4 → `OtpService::verify` → on success: forget `otp_mobile` → `session()->regenerate()` → **`mergeGuestCart(session cart captured pre-login)`** [P1-05 ✅, OtpController:135-147] → `redirect()->intended(localizedRoute('dashboard'))`.
6. `POST /login/otp/resend` (`throttle:3,1`) — 30s session cooldown (`otp_last_sent_at`, `RESEND_COOLDOWN_SECONDS=30`) → send again.
7. `POST /logout` (auth) — `Auth::logout` + session invalidate + regenerateToken + `clearRecallerCookie` (⚠️ no-op — see gotchas).
8. `GET /login/otp` (`show`) — backward-compat redirect → `login`.

## `OtpService`

- `normalizeMobile()` — `Helper::persian2LatinDigit` → strip non-digits → accepts `0098` (len 14), `98` (len 12), `9`-prefix (len 10) → `0`-prefixed. `isValidMobile()` = `^09\d{9}$`.
- `send(mobile, name?)`:
  - blocked users (`is_active=0`) rejected (`otp_mobile_blocked`).
  - NEW user → `User::create(name, mobile, password=Str::random(32), is_active=true)`
    → **`WelcomeDiscountService::createForUser($user)`** (line ~100 — P1-07 anchor)
    → `NotificationService` `discount.welcome` (try/catch logged).
  - Code: 4 digits, `random_int(0, 9999)` left-padded (CSPRNG), stored **PLAINTEXT** on `users.confirm_code`, expiry = `now() + config('kavenegar.otp_ttl_minutes', 2)`.
  - SMS: `Sms::sendLookup(template = setting('otp_template') ?: 'verify', token=code, token2=cleanSiteName(...))` — Kavenegar.
    - `RedirectResponse` → `otp_sms_not_configured`; `false` → `otp_sms_failed`; `return.status === 200` → ok; exceptions logged + generic message.
- `verify(mobile, code)` — user + `is_active` → **plaintext `!==` compare** → expiry `isPast` → one-time (code + expiry nulled) → **`Auth::login($user, true)`** (remember = TRUE).

## `cleanSiteName` quirk

token2 = first word; if ≥2 words and first is «فروشگاه» → second word. Cosmetic SMS branding.

## Session keys

`otp_mobile`, `otp_new_user`, `otp_last_sent_at` — consumed by showLogin/showVerify/verify/resend; `?change=1` clears the first two.

## Schema

- `users.mobile` — **unique** (integrity guard for registration race, see gotchas #7).
- `confirm_code` string(10) nullable, `confirm_code_expires_at` timestamp nullable (migration 2026-08-16). Cast: `confirm_code_expires_at => datetime`.
- User model: `confirm_code` + expiry in `$fillable`; **`$hidden` = [password, remember_token] only** — confirm_code NOT hidden (gotchas #4).

## LOCAL safety

`Sms::sendLookup` redirects recipient to `config('kavenegar.local_mobile')` in local env; if unset → SMS **skipped entirely** with `Log::info` (no accidental real SMS in dev). `base_url`/`otp_ttl_minutes`/`test_number` in `config/kavenegar.php`; API key + sender live in the `settings` table (admin panel), not env.

## Evidence

- `app/Services/OtpService.php` — normalize/send/verify/generateCode/cleanSiteName (full audit).
- `app/Http/Controllers/Site/OtpController.php` — checkPhone:46, send:66, showVerify:110, verify:122 (P1-05 at :135-147), resend:152, sendAjax:194.
- `app/Http/Controllers/Site/SiteAuthController.php` — showLogin:21, logout:41, clearRecallerCookie:50.
- `app/Http/Requests/Site/SendOtpAjaxRequest.php` — rules, `failedValidation`, `normalizedMobile()`.
- `app/Models/User.php:14-43` — fillable/hidden/casts.
- `routes/site.php:129-149` — throttle map: send 5,1 / send-ajax 5,1 / resend 3,1 / verify 5,1 / **check-phone NONE**.
