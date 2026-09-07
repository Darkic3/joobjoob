# Discounts — Dependencies (Verified Audit)

## Call graph

```
CouponService
  ├─→ Discount (find by code, isCurrentlyValid, hasReachedLimit, calculateDiscount,
  │            appliesToDiscountedItems, isEligibleCartItem, pay_type pivots)
  ├─→ DiscountUsage::create (recordUsage — P1-07: only after payment confirmation)
  ├─→ session('coupon') write/read/forget
  └─→ Auth::user() (first_purchase + user-specific checks)

WelcomeDiscountService
  ├─→ Discount::create (10%, WELCOME-XXXXXX, count=1, per-user=1, first_purchase, 30d)
  ├─→ Discount->users()->attach (discount_users pivot)
  └─→ CALLED BY OtpService:100 (OTP registration of new user) → then NotificationService 'discount.welcome'

DiscountController (Admin)
  ├─→ DiscountRequest (validation incl. withValidator after-hook)
  ├─→ Discount::create/update/delete/toggle + withCount('usageRecords')
  ├─→ syncDiscountable: morphedByMany ProductCategory/Brand/Product (pivot 'discountable', syncWithoutDetaching)
  ├─→ syncUsers: belongsToMany users (pivot 'discount_users', sync)
  └─→ filterUsers: User query builder (7 filters)

OrderService (cross-module consumer)
  ├─→ handleSuccessOrderPayment → CouponService::recordUsage + session()->forget('coupon')  [P1-07]
  └─→ handleFreeOrder           → same (100%-discount free orders)

Cart entry points (cross-module)
  ├─→ CheckoutController::applyDiscount → CouponService::apply
  └─→ CartService::applyCoupon:430     → CouponService::apply
```

## Model relations

| Model | Relation | Target |
|-------|----------|--------|
| `Discount` | belongsTo | `User` (user_id owner) |
| `Discount` | belongsToMany | `User` via `discount_users` (unique pair) |
| `Discount` | morphedByMany | `ProductCategory`, `Brand`, `Product` via `discountable` pivot |
| `Discount` | hasMany | `DiscountUsage` (usageRecords), `Order` (orders.discount_id — **unused in scope**) |
| `DiscountUsage` | belongsTo | `Discount`, `User`, `Order` |

## Schema

- `discounts` — base (000012) + enhance (2026-09-01: `code` unique + `DISC-` backfill, `is_active`, `starts_at`, `expires_at`), softDeletes, index `user_id`.
- `discountable` — `discount_id` FK cascade + morphs (000013).
- `discount_users` — unique `[discount_id, user_id]`, FKs cascade (2026-09-02).
- `discount_usage` — FKs: discount cascade, user/order nullOnDelete; `discount_amount` decimal(12,0); index `[discount_id, user_id]`.

## Coupling notes

- Coupon timing is owned by **Orders** module (P1-07: recordUsage + session forget happen only on payment success; session intentionally preserved through failed payments — `CheckoutController:216`).
- Predictable code formats + unthrottled apply endpoints are a cross-cutting security note (gotchas #4) — any new apply endpoint must add `throttle`.
- `Discount::orders()` relation currently unused — orders reference discounts via `orders.discount_id` only.

## Evidence

- `app/Services/CouponService.php` / `app/Services/WelcomeDiscountService.php` — full audit.
- `app/Models/Discount.php` / `app/Models/DiscountUsage.php`.
- `app/Http/Controllers/Admin/DiscountController.php` / `app/Http/Requests/Admin/DiscountRequest.php`.
- `app/Services/OrderService.php` (handleSuccessOrderPayment / handleFreeOrder), `app/Services/OtpService.php:100`.
- `database/migrations/2026_08_26_000012_*, _000013_*, 2026_09_01_160445_*, 2026_09_02_000001_*`.
