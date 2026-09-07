# Discounts — API Contract (Verified Audit)

> **Source:** `routes/admin.php:100-104`, `routes/site.php:91,107-108`
> **Note:** The old doc listed a phantom `GET /{locale}/discounts` site route — no site-facing discounts index exists. Site surface is only the checkout/cart coupon endpoints below.

## Admin — routes/admin.php:100-104 (inside `auth, adminPermission, log.admin` group)

| Method | URI | Name | Controller@Method | Input |
|--------|-----|------|-------------------|-------|
| GET | `/admin/discounts/users/filter` | `admin.discounts.users.filter` | `filterUsers` | `?filter=` new_month\|new_week\|no_orders\|has_orders\|no_paid_orders\|inactive_30d\|active_7d → JSON `{users[]}` (limit 500) |
| GET | `/admin/discounts` | `admin.discounts.index` | `index` | `?search&status=active\|inactive&type=general\|welcome\|first_purchase\|category\|brand\|product\|user` → paginate 15 |
| GET | `/admin/discounts/create` | `admin.discounts.create` | `create` | — (categories/brands/products/users for form) |
| POST | `/admin/discounts` | `admin.discounts.store` | `store` | `DiscountRequest` (below) |
| GET | `/admin/discounts/{discount}` | `admin.discounts.show` | `show` | explicit route; resource uses `except('show')` |
| GET | `/admin/discounts/{discount}/edit` | `admin.discounts.edit` | `edit` | — |
| PUT/PATCH | `/admin/discounts/{discount}` | `admin.discounts.update` | `update` | `DiscountRequest` |
| DELETE | `/admin/discounts/{discount}` | `admin.discounts.destroy` | `destroy` | soft delete |
| POST | `/admin/discounts/{discount}/toggle-status` | `admin.discounts.toggle-status` | `toggleStatus` | flips `is_active` |

⚠️ `discounts/users/filter` must stay declared **before** the `{discount}` resource routes (comment in routes file).

## Site — coupon entry points (auth; NO throttle ⚠️ — see gotchas #4)

| Method | URI | Name | Controller@Method | Input | Response |
|--------|-----|------|-------------------|-------|----------|
| POST | `/checkout/apply-discount` | `checkout.apply-discount` | `CheckoutController@applyDiscount` | `{discount_code}` | JSON from `CouponService::apply` → `{success, message, discount?, discount_amount?}` |
| POST | `/checkout/remove-discount` | `checkout.remove-discount` | `CheckoutController@removeDiscount` | — | JSON `{success:true}` |
| POST | `/cart/coupon` | `cart.coupon` | `CartController@applyCoupon` | `{code}` required\|string | `back()` flash `coupon_applied` / `coupon_invalid` (delegates via `CartService::applyCoupon:430`) |

## `DiscountRequest` validation contract

```
title              required|string|max:255
code               required|string|max:50|alpha_dash|unique:discounts,code,{id},id,deleted_at,NULL
amount             required|numeric|min:0|max:100000000
type               required|in:cash,percent
count              required|integer|min:1
basket_minimum_price nullable|integer|min:0
first_purchase     nullable|boolean
with_discount      nullable|boolean
user_id            nullable|exists:users,id
user_ids / user_ids.* nullable|array / exists:users,id     (synced via pivot, not fillable)
max_usage_per_user required|integer|min:0
pay_type           nullable|in:category,brand,product
discountable_ids   nullable|array|integer.*                 (synced via pivot, not fillable)
is_active          required|boolean
starts_at          nullable|date
expires_at         nullable|date|after_or_equal:starts_at
```

**`withValidator` after-hook:**
- `type=percent && amount > 100` → error «درصد تخفیف نمی‌تواند بیشتر از ۱۰۰ باشد.»
- `pay_type` set ⇒ `discountable_ids` required.
- `discountable_ids` existence count-checked against `ProductCategory`/`Brand`/`Product` (count mismatch → error).

## Responses

- store → `redirect admin.discounts.index` flash «کد تخفیف با موفقیت ایجاد شد.»
- update → same flash «به‌روزرسانی شد»
- destroy → flash «کد تخفیف حذف شد.»
- toggleStatus → `back()` flash «وضعیت تغییر کرد»
