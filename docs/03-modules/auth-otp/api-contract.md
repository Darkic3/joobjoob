# Auth / OTP — API Contract (Verified Audit)

> **Source:** `routes/site.php:129-149` — canonical + `{locale}` duplicates (`locale.*` names)
> The old doc's `/{locale}/auth-otp` and `admin.auth-otp.*` resource rows were **phantom routes** — nothing like them exists. There is no admin CRUD for OTP; the template/API-key come from the `settings` table.

## Route table (verified)

| Method | URI | Name | Middleware | Controller@Method | Notes |
|--------|-----|------|------------|-------------------|-------|
| GET | `/login` | `login` | web | `SiteAuthController@showLogin` | authed → dashboard; `?change=1` clears `otp_mobile`/`otp_new_user` |
| POST | `/logout` | `logout` | **auth** | `SiteAuthController@logout` | invalidate + regenerateToken |
| GET | `/login/otp` | `login.otp` | web | `OtpController@show` | backward-compat redirect → `login` |
| POST | `/login/check-phone` | `login.otp.check` | web | `OtpController@checkPhone` | ⚠️ **NO THROTTLE** — `{mobile}` → `{exists, valid}` (user enumeration) |
| POST | `/login/send` | `login.otp.send` | **throttle:5,1** | `OtpController@send` | `{mobile, name?}` → redirect `login.verify`; new user w/o name → back + `otp_new_user` flag |
| POST | `/login/send-ajax` | `login.otp.send-ajax` | **throttle:5,1** | `OtpController@sendAjax` | `SendOtpAjaxRequest` (registration-only) → JSON `{success, message, verifyUrl}` or `{success:false, field, registered, error}` |
| GET | `/login/verify` | `login.verify` | web | `OtpController@showVerify` | session `otp_mobile` gate |
| POST | `/login/otp/resend` | `login.otp.resend` | **throttle:3,1** | `OtpController@resend` | 30s session cooldown (`otp_last_sent_at`) |
| POST | `/login/otp/verify` | `login.otp.verify` | **throttle:5,1** | `OtpController@verify` | `{code digits:4}` → login → session regenerate → mergeGuestCart (P1-05) → intended dashboard |

## `SendOtpAjaxRequest` contract

- Normalizes mobile via `OtpService::normalizeMobile` **inside `rules()`** (mutates request).
- `mobile`: required + custom closure — invalid format (`otp_mobile_invalid`) OR existing user (`otp_mobile_registered`) fail.
- `name`: required|string|min:2|max:120 (always required — this endpoint is registration-only).
- `failedValidation()` throws `HttpResponseException` with the exact JSON shape the front-end register box expects: mobile error first, then name, then generic fallback.

## `OtpService::send` return shapes

```
{success:false, message: otp_mobile_invalid | otp_mobile_blocked | otp_name_required | otp_sms_not_configured | otp_sms_failed}
{success:true,  message: otp_code_sent}   (only if Kavenegar return.status === 200)
```

## `OtpService::verify` return shapes

```
{success:false, message: otp_code_invalid | otp_code_expired}
{success:true,  message: otp_login_success}   → Auth::login($user, remember: true)
```

## SMS safety (local)

`config/kavenegar.php`: `local_mobile` (LOCAL_MOBILE env) redirects lookup recipients in local; unset → SMS skipped with `Log::info`. `otp_ttl_minutes` default 2 (env `OTP_TTL_MINUTES`). API key + sender from `settings` table.
