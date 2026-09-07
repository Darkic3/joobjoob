# Global Context — Always include for AI

Include this in every AI prompt for Shopora. Concise minimum to prevent wrong implementation.

## Stack
Laravel 11, PHP 8.2, Blade+Alpine, Vite/Tailwind, DB MySQL, Cache/Session/Queue = database, Filesystem local public/uploads.

## Architecture
Controller -> Service/Helper/DTO -> Model. ~40% Controllers call Model directly — not pure. No Repository/Action/Event/Job/Policy. Singleton: CartService, WishlistService, RecentlyViewedService. ViewComposers for Locale/Admin/Footer. Helpers as service locator: setting(), locale(), jdate.

## Critical Gotchas (must know)
- **Unit: Toman -> Rial via `intval($amount . '0')` x10** in ZarinPal and ShippingCalculator. Mistake = 10x payment bug. Evidence ZarinPal.php:20
- **hero_slides.linkable_id is string** (not int) for page/custom. DTO getLinkableId returns string.
- **Order status 7 values, no state machine**: paying, paid, deposit_paid, pending_confirmation, wait_for_verification, unpaid, cancelled. Admin can set any.
- **Cart dual**: guest session key `cart` (md5 rowId), auth DB `carts` (user_id). mergeGuestCart exists — OTP caller OK, password login caller missing (P1-05).
- **Checkout session keys**: checkout_address_id, checkout_shipping_method_id, checkout_shipping_price, coupon (code,discount_id,type,value,discount_amount,...)
- **is_admin bypasses all RBAC** (AdminPermission middleware).
- **Cache rememberForever** for settings/languages + flush() on update but not on direct DB edit.
- **Image WebP** via GD, stored public/uploads/{folder}/.
- **SoftDeletes** on 16 models; products need withTrashed for stock.
- **OrderObserver FIXED 2026-09-07**: `created` unconditional, `updated` wasChanged — isDirty bug fixed.
- **RestoreStock FIXED**: owner is Service/Command only, Observer no longer restores — double restore fixed.
- **BankPayment FIXED**: token fail -> unpaid + restore + audit, no hard delete.
- **RequestLog logs sensitive** card data (only password filtered) — filter receipt_tracking_code etc.
- **Chapar BROKEN** authToken='' + HTTP (no TLS) — do not use.

## Naming
- JSON multilingual: title/description/name as json. Use trans() helper.
- Routes: admin.* + locale.* prefix for Site (duplicated $siteRoutes).
- Throttle: otp send/verify 5/1, resend 3/1, contact 5/1.

## Lessons Learned from V1 P0/P1 Fixes — Critical Rules for Future AI Agents (2026-09-07)

> Source: `documents/01_P0_Critical_Blockers.md` (9 P0) + `documents/02_P1_V1_Essential_Features.md` (7 P1). Hargez in eshtebahat ra tekraar nakon.

### Rule 1 — Observer: isDirty vs wasChanged
`created` event: `isDirty()` hamishe false (already saved). `updated` event: `isDirty` mid-save, `wasChanged` post-save. Fix: `created` unconditional, `updated` wasChanged. Ref: P0-01.

### Rule 2 — Single Owner for Stock Restore
Double restore = stock inflation. Owner faghat `OrderService::cancelOrder` + `CancelExpiredOrders`. Observer hargez `restoreStock` nakone. Ref: P0-02.

### Rule 3 — Never Hard-Delete Orders
Token fail / reserve fail -> `update cancelled/unpaid` + `restoreStock` + `AuditLog`. `delete()`/`forceDelete()` = data loss, admin blind. Ref: P0-03, P0-09.

### Rule 4 — Transaction Wrapping
`Order::create + OrderItem::create` hatman `DB::transaction`. Reserve fail -> `cancelled` na delete. Ref: P0-09.

### Rule 5 — Dead Column = Silent Bug
Migration column ra hazf kard ama Service hanuz minevisad (`payment_receipt`). Mass-assignment filter silent ignore — hich error. Har migration ba `grep -rn columnName app/` check shavad. Ref: P0-04.

### Rule 6 — External Auth Token: Empty = BROKEN
`ChaparShipment::$authToken=''` = shipping hamishe 0. Ya token az `config/env` bekhan, ya type `chapar` ra ghire-faal kon. Ref: P0-05.

### Rule 7 — Scheduler Niaz be Cron-e System Darad
`Schedule::command()->everyFiveMinutes()` bedoone `* * * * * php artisan schedule:run` hargez ejra nemishavad. Ref: P0-06.

### Rule 8 — DB Mismatch Local vs Host
`.env.example` sqlite, host mysql (JSON column). `.env` host hatman `DB_CONNECTION=mysql`. Ref: P0-07.

### Rule 9 — SSL: Sms FIXED, Chapar HTTP
`Sms.php` alan `VERIFYPEER=true/VERIFYHOST=2` FIXED. `ChaparShipment` HTTP ast — MITM. Ya HTTPS ya disable. Ref: P0-05/P0-08.

### Rule 10 — Guest Cart Merge Gap
`OtpController::verify` merge OK, `SiteAuthController::login` merge nadarad. Har auth entry point bayad `mergeGuestCart` seda bezanad. Ref: P1-05.

### Rule 11 — FormRequest Coverage
22 Request (15 Admin + 7 Site) — CartController inline `validate()` esterna. "Every write through FormRequest" dorost nist baraye Cart. Ref: 05-conventions/validation.md.

Copy this file into every AI context.
