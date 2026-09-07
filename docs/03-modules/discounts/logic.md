# Discounts — Logic (Verified Audit)

> **Models:** `Discount` (211 lines), `DiscountUsage` (30 lines)
> **Services:** `CouponService` (214 lines), `WelcomeDiscountService` (67 lines)
> **Controller:** `Admin\DiscountController` (~215 lines) | **Request:** `Admin\DiscountRequest` (110 lines)
> **Migrations:** `2026_08_26_000012_create_discounts_table`, `_000013_create_discountable_table`, `2026_09_01_160445_enhance_discounts_system`, `2026_09_02_000001_create_discount_users_table`
> **Seeder:** `DiscountSeeder` (8 codes, idempotent `updateOrCreate` on code)

---

## 1. Model helpers (`app/Models/Discount.php`)

- `isCurrentlyValid()` — `is_active` + `starts_at`/`expires_at` window check.
- `hasReachedLimit(?int $userId)` — `count <= 0` → **reached** (code disabled); total `usageRecords()` vs `count`; per-user `usageRecords()->where('user_id')` vs `max_usage_per_user`.
- `appliesToDiscountedItems()` — casts `with_discount` to bool.
- `isEligibleCartItem(array $item)` — no `pay_type` → true; else per-item `Product::with('category','brand')->find($productId)` (⚠️ N+1 when called in a loop) + `match` on `pay_type`: `category` / `brand` / `product` pivot existence.
- `calculateDiscount(float $amount)` — `percent` → `amount * (value/100)`; `cash` → value; capped at `min($discount, $amount)`.
- `scopeValid()` — active + time-window (query-builder twin of `isCurrentlyValid`).
- `getAmountNameAttribute()` — «X تومان» (cash) or «X درصد» (percent).
- **`discountable()` alias → `categories()`** — backward-compat used by `DiscountSeeder:138`.
- Relations: `user` belongsTo, `users` belongsToMany (`discount_users` pivot), `orders` hasMany (`orders.discount_id`, unused in scope), `usageRecords` hasMany, `categories/brands/products` morphedByMany (`discountable` pivot).

## 2. `CouponService::apply()` — 8-step pipeline

1. Normalize: `strtoupper(trim($code))`
2. Find by `code` (SoftDeletes global scope excludes trashed)
3. `isCurrentlyValid()`
4. `basket_minimum_price > 0 && subtotal < min` → reject
5. `first_purchase` → `$user->orders()->where('order_status','paid')->exists()` (⚠️ counts **only** `paid`, not `deposit_paid` — see gotchas #5)
6. User-specific: `discount->user_id` mismatch → reject
7. `hasReachedLimit($user?->id)`
8. `calculateDiscount($subtotal)` → session write

**Session key `coupon` shape:** `{code, discount_id, type, value, discount_amount, title, with_discount, pay_type}`

## 3. `calculate()` vs `calculateForCart()`

- `calculate(subtotal)` — simple whole-cart: percent → `subtotal * value/100`; cash → `min(value, subtotal)`.
- `calculateForCart(CartService)` — per-item eligibility walk: skips discounted products when `appliesToDiscountedItems()` is false; skips pay_type-ineligible items via `isEligibleCartItem`; sums `calculateDiscount($itemTotal)`; capped by `min(total, cart->subtotal())`. Falls back to simple `calculate()` if discount row missing (e.g. soft-deleted — see gotchas #7).

## 4. `recordUsage()` — P1-07 timing

`DiscountUsage::create([discount_id, user_id, order_id, discount_amount, used_at: now()])`.
Called **only** from `OrderService::handleSuccessOrderPayment` and `OrderService::handleFreeOrder` — i.e. after payment confirmation — and **gated on `session('coupon')` existing** (see gotchas #1 in Payments; cross-ref). Coupon session is preserved through failed payments (CheckoutController:216 comment) and forgotten after success.

## 5. `WelcomeDiscountService`

Constants: 10%, `count=1`, `max_usage_per_user=1`, `first_purchase=true`, `with_discount=false`, `expires_at = now()+30 days`, prefix `WELCOME-` + 6-char `Str::random` unique-loop code.
`createForUser(User)` — `Discount::create` (sets `user_id` **and** attaches `discount_users` pivot).
**Caller:** `OtpService:100` — on OTP registration of a new user. Notification `discount.welcome` follows (try/catch). Created exactly once per user (guarded by `users.mobile` unique).

## 6. Admin CRUD (`Admin\DiscountController`)

- `index(Request)` — `Discount::withCount('usageRecords')`; filters `search` (title/code LIKE, properly grouped closure), `status` (is_active), `type` (⚠️ `general` and `welcome` cases have **ungrouped top-level `orWhere`** — see gotchas #1/#2); `latest()->paginate(15)->withQueryString()`.
- `create()`/`edit()` — eager loads; form data: categories/brands/products/users (⚠️ unbounded `User::...->get()`).
- `store(DiscountRequest)` — `Discount::create($validated)` (⚠️ `user_ids`/`discountable_ids` are in validated data but not fillable → silently discarded by non-strict mass assignment) → `syncDiscountable()` + `syncUsers()`.
- `update()` — explicit `categories/brands/products->detach()` then re-sync; `syncUsers` uses `sync()` (vs `syncWithoutDetaching` in syncDiscountable — equivalent after detach, inconsistent intent).
- `destroy()` — soft delete. `toggleStatus()` — flip `is_active`, no audit.
- `filterUsers(Request)` AJAX — 7 cases: `new_month`/`new_week` (created_at), `no_orders`/`has_orders`, `no_paid_orders` (⚠️ only `paid`), `inactive_30d`/`active_7d` (⚠️ `updated_at` proxy); `limit(500)` no pagination.
- Routes: `routes/admin.php:100-104` — `discounts/users/filter` declared **before** the `{discount}` resource on purpose; resource `->except('show')` + explicit GET show.

## 7. `DiscountRequest` validation

`title` required max255; `code` required `alpha_dash` + soft-delete-aware `unique:discounts,code,...,deleted_at,NULL`; `amount` numeric 0..100000000; `type in:cash,percent`; `count` integer min:1; `max_usage_per_user` required integer min:0; `pay_type` nullable `in:category,brand,product`; `discountable_ids` array of ints; `user_id`/`user_ids.*` exists users; `is_active` required boolean; `starts_at`/`expires_at` date with `after_or_equal`.
**`withValidator` after-hook:** percent ≤ 100; `pay_type` set ⇒ `discountable_ids` required; existence count check against `ProductCategory`/`Brand`/`Product`.
`store()`/`update()` use **identical** rule arrays (match on method reserved for future divergence). Code case: `alpha_dash` allows lowercase — never normalized (gotchas #3).

## 8. Schema (4 migrations)

- Base (000012): `title`, `amount` **string**, `type` string, `count` int default 1, `basket_minimum_price` **string**, `first_purchase`/`with_discount` tinyint, `user_id` int nullable + index, `max_usage_per_user` default 1, `pay_type` string, softDeletes.
- `enhance_discounts_system` (2026-09-01): adds `code` (nullable → backfilled `DISC-` + zero-padded id → **unique index**, guarded by `Schema::hasIndex` — requires Laravel ≥ 11.23; project on v11.55.0 ✅), `is_active`, `starts_at`, `expires_at`; creates `discount_usage` (`discount_id` FK cascade, `user_id`/`order_id` FK nullOnDelete, `discount_amount` decimal 12,0, `used_at`, index `[discount_id, user_id]`).
- `discountable` (000013): `discount_id` FK cascade + `morphs('discountable')`.
- `discount_users` (2026-09-02): unique `[discount_id, user_id]`, FKs cascade.

## 9. Types mismatches (schema vs validation)

`basket_minimum_price`: string column vs integer rule + integer cast. `amount`: string column, no cast, float math in `calculateDiscount`. Works via MySQL coercion; fragile on SQLite (tests).

## 10. Evidence

- `app/Models/Discount.php` — helpers above; `DiscountUsage.php` — fillable + casts, table `discount_usage`.
- `app/Services/CouponService.php` — pipeline §2/§3/§4; `WelcomeDiscountService.php` — §5.
- `app/Http/Controllers/Admin/DiscountController.php` — §6; `app/Http/Requests/Admin/DiscountRequest.php` — §7.
- `database/seeders/DiscountSeeder.php:16-131` — codes `WELCOME15, SAVE50K, CAT10, INACTIVE10, EXPIRED20, VIP50, OPENEND, NOMIN`, idempotent.
- `routes/admin.php:100-104`, `routes/site.php:107-108` + `:91`.
