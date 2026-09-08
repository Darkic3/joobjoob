# Wishlist / RecentlyViewed — Gotchas & Known Bugs (Verified Audit)

---

## 1. No `mergeGuestWishlist` on login — wishlist LOST (MISSING FEATURE)

**Bug:** Unlike Cart (which has `mergeGuestCart` called from `OtpController::verify:146`), Wishlist has **no merge mechanism**. When a guest adds items to wishlist, then logs in (OTP), the session is regenerated (`session()->regenerate()`) and the wishlist array is lost.

**Impact:** HIGH — guest users lose their wishlist on login. Cart has this feature; Wishlist doesn't.

**Fix:** Implement `WishlistService::mergeGuestWishlist(array $guestIds)` called from `OtpController::verify` after session regenerate, similar to Cart's merge pattern.

---

## 2. No cap on wishlist items — unbounded session growth

**Bug:** `WishlistService::toggle()` adds product IDs without any limit. A user (or bot) can add thousands of IDs to the session array.

**Impact:** Low — session storage is cheap, but `content()` loads ALL products via `whereIn` which could be slow with 1000+ IDs.

**Fix:** Add `maxItems = 100` cap (or similar) in `toggle()`.

---

## 3. No product validation in `ids()` — stale IDs accumulate

**Bug:** `ids()` returns whatever is in session without checking if products still exist or are active. If a product is soft-deleted or deactivated, its ID remains in the session until `content()` is called (which filters via `whereIn`).

**Impact:** Low — `content()` handles it, but `count()` includes stale IDs.

**Fix:** Either validate on `ids()` (expensive) or accept that `count()` may be slightly inflated.

---

## 4. `RecentlyViewedService::add()` — N+1 if called per product

**Bug:** `add()` only writes to session (no DB query), so it's fast. But if called in a loop (e.g., from a product listing), the `array_search` + `array_unshift` + `array_slice` runs per call.

**Impact:** Negligible — pure PHP array operations.

---

## 5. No auth middleware on toggle — CSRF protection only

**Bug:** `POST /wishlist/toggle/{product:slug}` has no `auth` middleware. Guest users can toggle. CSRF is handled by Laravel's web middleware.

**Impact:** None — this is intentional design (guest wishlist).

---

## 6. `content()` N+1 risk in views

**Bug:** `WishlistService::content()` does `Product::with(['images', 'category'])->whereIn('id', $ids)` — batched query, not N+1. But if the view accesses `$product->brand` or other relations not eager-loaded, it triggers N+1.

**Impact:** Low — depends on view implementation.

---

## 7. RecentlyViewed — `is_active` filter but no `SoftDeletes` check

**Bug:** `RecentlyViewedService::content()` filters `where('is_active', true)` but doesn't use `withTrashed()`. Soft-deleted products are excluded by default (global scope). This is correct behavior but undocumented.

---

## 8. Session driver dependency — `array` driver loses everything

**Bug:** Both services use `session()`. If `SESSION_DRIVER=array` (used in tests), all data is lost between requests. Tests must use `database` or `file` driver.

**Impact:** Test-only issue.

---

## AI Checklist before coding Wishlist

- [ ] Checked if `mergeGuestWishlist` exists (it doesn't — add if implementing login merge)
- [ ] Verified session key is unique (`wishlist` / `recently_viewed`)
- [ ] Checked product eager loading in `content()` — add relations as needed
- [ ] Remembered: no DB tables — all session-based
- [ ] Remembered: no auth required — guest can toggle
- [ ] Checked max items cap (wishlist: none, recently viewed: 20)

## Evidence

- `app/Services/WishlistService.php` — 85 lines, session-based toggle/remove/count
- `app/Services/RecentlyViewedService.php` — 100 lines, max 20, add/remove/clear
- `app/Http/Controllers/Site/WishlistController.php` — 35 lines, index + toggle (no auth)
- `routes/site.php:60-61` — 2 routes (no auth middleware)
- `app/Http/Controllers/Site/ProductController.php:185` — `$inWishlist` passed to view
- `app/Http/Controllers/Site/OtpController.php:135-147` — session regenerate (wishlist lost here)
