# Auth / OTP — Gotchas & Known Bugs (Verified Audit)

## FIXED — mergeGuestCart caller verified (P1-05) ✅
Old claim "mergeGuestCart has no caller — UNKNOWN / guest cart lost after login" is **WRONG**.
Caller: `OtpController::verify:146`. Flow: capture `session('cart')` pre-login → `otpService->verify`
(login) → `session()->regenerate()` → `mergeGuestCart($guestCart)` → session cleared inside merge.
Remove this from every stale doc that still carries the UNKNOWN claim.

## 1) Plaintext OTP storage + comparison
`users.confirm_code` stored plaintext; `verify()` compares with `!==` (no `hash_equals`, no hashing).
Any DB read access (dump, log, admin query) exposes live login codes.
**Improvement:** hash like password-reset tokens + `hash_equals` compare.

## 2) No per-account attempt counter / lockout
Only per-IP `throttle:5,1` on verify. Math: 10,000 combos × 2-min TTL → ~10 tries per code
window per IP = 0.1%/window/IP; distributed IPs raise odds linearly.
**Improvement:** attempt counter column on user + lockout after N misses.

## 3) `/login/check-phone` is UNTHROTTLLED
User enumeration (`exists: true/false`) + unbounded DB probes. Every other OTP endpoint is
throttled — this one was missed. **Fix:** `throttle:10,1` minimum.

## 4) `confirm_code` NOT in `User::$hidden`
`$hidden = ['password','remember_token']` only. Any serialized User (admin panels, future APIs,
notification payloads) would expose live OTP codes. Add `'confirm_code', 'confirm_code_expires_at'`.

## 5) `remember = TRUE` on OTP login
`Auth::login($user, true)` — every OTP login gets a long-lived remember cookie by default.
Deliberate UX choice? Document intent; if not intended, pass `false`.

## 6) `clearRecallerCookie()` is effectively a no-op
Only unqueues a cookie if `Cookie::hasQueued($name)` — nothing queues it in the logout flow.
Real invalidation comes from `cycleRememberToken` inside `Auth::logout` (stale browser cookie
fails server-side). To actually clear the browser cookie: `Cookie::queue(Cookie::forget($name))` unconditionally.

## 7) TOCTOU duplicate-registration race (minor)
Controller `userExists` → service re-check → `User::create`. Two concurrent same-mobile
registrations → `users.mobile` UNIQUE throws `QueryException` → uncaught 500 (no try/catch
in `send()`). Data integrity safe; UX degrades to error page. Catch and treat as login.

## 8) resend resets the code TTL
Every `send()`/`resend()` overwrites `confirm_code` and extends expiry by a fresh 2 minutes.
Combined with #2: continuous resends keep a code window alive (bounded by `throttle:3,1` + 30s session cooldown).

## 9) Enumeration by design (documented tradeoff)
`check-phone` (`exists`) + `send-ajax` (`registered:true`) reveal account existence — inherent
to the "name field appears only for new users" UX. Throttling (#3) is the mitigation.

## Cross-module references
- Welcome discount creation: `OtpService:100` → `WelcomeDiscountService::createForUser` (once per user, mobile-unique-guarded).
- Cart merge on login: P1-05 (see Cart module docs).
- Payments use a separate mechanism: `Auth::loginUsingId` in BankPaymentService (not OTP).

## AI Checklist before coding Auth / OTP
- [ ] Did NOT re-add stock/restore logic anywhere near the observer (that belongs to Orders)
- [ ] Preserved the pre-login cart capture → post-regenerate merge ordering (P1-05)
- [ ] Added throttle to ANY new unauthenticated endpoint (gotcha #3)
- [ ] Considered confirm_code exposure in serialization (gotcha #4)
- [ ] Kept `random_int` (CSPRNG) for code generation

## Evidence
- `app/Services/OtpService.php` — plaintext compare, TTL config, welcome hook.
- `app/Http/Controllers/Site/OtpController.php:135-147` — P1-05 flow; `:46` checkPhone (unthrottled route).
- `routes/site.php:129-149` — throttle middleware map.
- `app/Models/User.php:23-25` — `$hidden` gap.
- `app/Http/Controllers/Site/SiteAuthController.php:50-57` — clearRecallerCookie no-op.
