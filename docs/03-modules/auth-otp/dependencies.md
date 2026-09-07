# Auth / OTP — Dependencies (Verified Audit)

## Call graph

```
SiteAuthController
  ├─→ Auth (check, logout)
  └─→ Cookie (clearRecallerCookie — no-op, see gotchas)

OtpController
  ├─→ OtpService (normalizeMobile, isValidMobile, userExists, send, verify)
  ├─→ SendOtpAjaxRequest (sendAjax — FormRequest with custom failedValidation)
  ├─→ CartService::mergeGuestCart  [P1-05 — OtpController:146]
  └─→ Session: otp_mobile, otp_new_user, otp_last_sent_at

OtpService
  ├─→ Sms::sendLookup (Kavenegar REST via config/kavenegar.php; local LOCAL_MOBILE safety)
  ├─→ User::create / update (confirm_code + confirm_code_expires_at)
  ├─→ WelcomeDiscountService::createForUser  [line ~100 — P1-07 anchor]
  ├─→ NotificationService ('discount.welcome', try/catch logged)
  ├─→ Helper::persian2LatinDigit
  ├─→ setting('otp_template') ?: 'verify' , setting('site_name')
  └─→ Auth::login($user, remember: true)

SendOtpAjaxRequest
  └─→ App::make(OtpService) (normalize + existence checks inside rules/failedValidation)
```

## Cross-module edges

| Edge | Direction | Notes |
|------|-----------|-------|
| Auth-OTP → Discounts | `OtpService:100` → `WelcomeDiscountService` | once per user (mobile-unique-guarded) |
| Auth-OTP → Cart | `OtpController:146` → `mergeGuestCart` | P1-05, capture pre-login / merge post-regenerate |
| Payments → Auth | `BankPaymentService` → `Auth::loginUsingId` | separate mechanism, NOT OTP |
| RBAC | `User::$fillable` includes `is_admin` | admin gate via `HasAdminPermissions` trait |

## External

- **Kavenegar** REST (`config/kavenegar.php`: base_url, otp_ttl_minutes=2, local_mobile, test_number). API key + sender number live in the `settings` table (admin panel), not env.

## Schema

- `users.mobile` UNIQUE (registration race guard); `confirm_code` string(10) + `confirm_code_expires_at` timestamp (2026-08-16 migration); `$hidden` gap for confirm_code (gotchas #4).

## Evidence

- `app/Services/OtpService.php`, `app/Http/Controllers/Site/OtpController.php`, `app/Http/Controllers/Site/SiteAuthController.php`, `app/Http/Requests/Site/SendOtpAjaxRequest.php`, `app/Helpers/Sms.php:118`, `config/kavenegar.php`, `routes/site.php:129-149`.
