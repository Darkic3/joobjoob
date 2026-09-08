# Discounts — Gotchas & Known Bugs (Verified Audit)

## 1) `index()` type filter — ungrouped `orWhere` escapes search/status filters (BUG)
The `general` and `welcome` cases chain `->orWhere(...)` at TOP level inside the `match`. SQL
precedence makes the OR branch ignore both the `search` LIKE closure and the `status` filter:

```sql
(search AND is_active AND general-conditions-A) OR (general-conditions-B)
```

Also the two `general` branches (expires_at NULL vs NOT NULL) are logically redundant — together
they simplify to `pay_type IS NULL AND user_id IS NULL AND first_purchase = 0`.
**Fix:** wrap each match case in `->where(function ($q) { ... })`.

## 2) `welcome` filter never matches real welcome codes (BUG — dead filter)
Filter searches `title LIKE '%خوش آمد گویی%'` / `'%웰컴%'` (Korean leftover) / `'%welcome%'`.
Actual `WelcomeDiscountService` title is **«تخفیف خوشامدگویی»** (no space) → zero matches.
**Better:** filter by `code LIKE 'WELCOME-%'` or `user_id NOT NULL + first_purchase = 1`.

## 3) Coupon code case-sensitivity mismatch (BUG)
`CouponService::apply` uppercases input (`strtoupper(trim)`); `DiscountRequest` allows
`alpha_dash` (incl. lowercase) and `store()` saves as-is → an admin-created code `sale10`
is unmatchable (lookup for `SALE10`). Seeder codes are uppercase, welcome codes uppercase —
only hand-typed admin codes break. **Fix:** uppercase normalize in a mutator or drop
`strtoupper` in apply.

## 4) Predictable codes + unthrottled apply endpoints (SECURITY)
Migration backfill generates sequential `DISC-0001`-style codes; seeder codes are
human-readable (`WELCOME15`, `SAVE50K`, `VIP50`…). Both apply endpoints —
`POST /cart/coupon` and `POST /checkout/apply-discount` (auth only) — have **no throttle**
→ code enumeration/brute force. **Fix:** `throttle:5,1` like OTP/contact + opaque codes
for public campaigns.

## 5) `first_purchase` ignores `deposit_paid` (cross-ref Payments gotcha #19)
`apply()` step 5 and `filterUsers` `no_paid_orders` both count only `order_status = 'paid'`.
A user with a deposit_paid order still counts as "first purchase".

## 6) TOCTOU on usage limits — PARTIALLY FIXED ✅ (apply race) / OPEN (cross-request)

**Fixed (2026-09-08):** `CouponService::apply()` now wraps the entire validation pipeline in
`DB::transaction` + `lockForUpdate()` on the Discount row. Two concurrent `apply()` calls for
the same code are serialized — the second blocks until the first's transaction commits.
This prevents the "two users apply same code simultaneously" race.

**Still OPEN:** The cross-request TOCTOU between `apply()` (session write) and `recordUsage()`
(called from `OrderService::handleSuccessOrderPayment` after payment) remains. Between order
creation and payment success, other requests can still apply the same code. A unique constraint
on `discount_usage(discount_id, order_id)` would prevent duplicate recording, and recording
usage at order creation time (with compensation on failure) would close the gap entirely.

## 7) Stale session coupon after soft-delete bypasses eligibility (EDGE)
`destroy()` soft-deletes; an existing session `coupon` survives. `calculateForCart()` gets
`Discount::find() = null` → falls back to simple `calculate()` which **ignores pay_type /
with_discount eligibility**. `apply()` correctly rejects deleted codes (global scope) — the
leak is only through the stale session.

## 8) `count <= 0` silently disables a code
`hasReachedLimit` returns true when `count <= 0`; `DiscountRequest` enforces min:1 but DB
edits/seeders with `count = 0` kill the code with no error surface.

## 9) Schema/validation type mismatches
`basket_minimum_price`: string column vs integer rule + integer cast. `amount`: string
column, no cast, float math in `calculateDiscount`. MySQL coercion hides it; SQLite tests diverge.

## 10) `isEligibleCartItem` N+1
One `Product::with('category','brand')->find()` per cart item inside the
`calculateForCart` loop. Batch with `findMany` over cart product_ids.

## 11) `enhance_discounts_system` migration — compatibility + `down()` notes (VERIFIED, not a bug)
Uses `Schema::hasIndex()` — requires Laravel ≥ 11.23; project on **v11.55.0** ✅.
`down()` drops `code/is_active/starts_at/expires_at` unconditionally → partial-state re-run fails.

## 12) `filterUsers` proxies are misleading
`inactive_30d`/`active_7d` treat `updated_at` as "activity" (any model save counts).
`no_paid_orders` has the `deposit_paid` gap (item 5). `limit(500)` without pagination.

## 13) Mass-assignment silently discards relation inputs
`user_ids` / `discountable_ids` are in `validated()` but not in `Discount::$fillable` —
`Discount::create()` drops them silently (non-strict mass assignment default). Works only
because the controller syncs them separately; a strict-mode enablement would throw.

## Cross-module references
- Payments module: gotcha #18 (recordUsage session-coupling), #19 (first_purchase), #20 (TOCTOU).
- Cart module: coupon session preserved through failed payments, forgotten on success (P1-07).

## Legacy
- `discountable()` alias → categories only — calling `->discountable` on a brand/product
  discount returns categories (trap for AI coders; used by `DiscountSeeder:138`).
- Seeder is idempotent (`updateOrCreate` on code) ✅ — no OrderShippingStatusSeeder-class bug.

## AI Checklist before coding Discounts
- [ ] Wrapped every index() filter case in a query closure (gotcha #1)
- [ ] Normalized code case on write (gotcha #3)
- [ ] Added throttle before exposing any coupon apply endpoint (gotcha #4)
- [ ] Used `whereIn(['paid','deposit_paid'])` for first-purchase semantics (gotcha #5)
- [ ] Batched `isEligibleCartItem` product lookups (gotcha #10)

## Evidence
- `app/Http/Controllers/Admin/DiscountController.php` — index filters (orWhere bug), sync methods, filterUsers.
- `app/Services/CouponService.php` — apply pipeline, first_purchase check, session write.
- `app/Services/WelcomeDiscountService.php` — constants + `WELCOME-` generator.
- `app/Models/Discount.php` — `hasReachedLimit`, `isEligibleCartItem`, `discountable` alias.
- `database/migrations/2026_09_01_160445_enhance_discounts_system.php` — `hasIndex` + DISC backfill + discount_usage.
- `routes/admin.php:100-104`, `routes/site.php:91,107-108`.
