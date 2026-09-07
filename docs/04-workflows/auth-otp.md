# Auth / OTP Workflow

> **Source of Truth:** `app/Services/OtpService.php`, `app/Http/Controllers/Site/OtpController.php`, `app/Helpers/Sms.php`, `app/Models/User.php`

## Overview
Mobile-only authentication via 4-digit OTP sent by Kavenegar. No password login for Site. Admin uses separate guard? Actually AdminAuthController uses password + is_admin.

## Step-by-step trace
```
GET /login -> SiteAuthController@showLogin (if auth->check redirect dashboard)
POST /login/check-phone {mobile} -> OtpController@checkPhone -> OtpService::normalizeMobile (persian digits, 0098/98/9 handling) -> userExists -> returns {exists, needName}
POST /login/send {mobile, name?} throttle:5,1 -> OtpService::send:
  1. normalizeMobile, isValidMobile /^09\d{9}$/
  2. if user && !is_active -> blocked
  3. if !user && !name -> error name_required
  4. if !user -> User::create(name,mobile,password=Str::random(32),is_active=true) -> WelcomeDiscountService::createForUser + notifyUser discount.welcome
  5. generateCode random_int(0,9999) 4 digits -> update confirm_code + confirm_code_expires_at now+2min (config kavenegar.otp_ttl_minutes)
  6. Sms->sendLookup(template=setting('otp_template')||'verify', [token=>code, token2=>cleanSiteName], mobile)
POST /login/otp/verify {mobile, code} throttle:5,1 -> OtpService::verify:
  1. find user by mobile
  2. check is_active, confirm_code !== null && === code
  3. check expires_at isPast -> expired
  4. update confirm_code=null, expires_at=null
  5. Auth::login(user, true remember)
POST /login/otp/resend throttle:3,1 -> same send
POST /logout -> Auth::logout + session invalidate
```

## Key details
- TTL 2 minutes. Brute force 10k combos + throttle.
- `mergeGuestCart` exists but no caller found — guest cart may be lost after login (UNKNOWN).
- Evidence: `OtpService.php:20-60`, `Sms.php:sendLookup`

## Gotchas for AI
- Never invent email/password login for Site.
- Always normalize mobile before query.
- Remember to clear confirm_code after verify (one-time).
