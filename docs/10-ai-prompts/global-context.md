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
- **Cart dual**: guest session key `cart` (md5 rowId), auth DB `carts` (user_id). mergeGuestCart exists but no caller — guest cart may be lost.
- **Checkout session keys**: checkout_address_id, checkout_shipping_method_id, checkout_shipping_price, coupon (code,discount_id,type,value,discount_amount,...)
- **is_admin bypasses all RBAC** (AdminPermission middleware).
- **Cache rememberForever** for settings/languages + flush() on update but not on direct DB edit.
- **Image WebP** via GD, stored public/uploads/{folder}/.
- **SoftDeletes** on 16 models; products need withTrashed for stock.
- **OrderObserver created bug** isDirty false -> no history for new order.
- **Double restoreStock** possible (Observer + cancelOrder).
- **RequestLog logs sensitive** card data (only password filtered).
- **Chapar BROKEN** authToken=''.

## Naming
- JSON multilingual: title/description/name as json. Use trans() helper.
- Routes: admin.* + locale.* prefix for Site (duplicated $siteRoutes).
- Throttle: otp send/verify 5/1, resend 3/1, contact 5/1.

Copy this file into every AI context.
