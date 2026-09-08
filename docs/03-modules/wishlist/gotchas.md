# Wishlist / RecentlyViewed — Gotchas & Known Bugs (Verified Audit)

---

## 1. ~~No `mergeGuestWishlist` on login~~ — FIXED ✅ (Backlog 3.2)

**Previously:** Guest wishlist lost on OTP login (session regenerate). Cart had `mergeGuestCart`; Wishlist didn't.

**Now (2026-09-08):** `WishlistService::mergeGuestWishlist(array $guestIds)` implemented and called from `OtpController::verify` after session regenerate. Uses `DB::table('wishlists')->insertOrIgnore()` for safe upsert. Session cleared after merge.

---

## 2. No cap on wishlist items — unbounded session/DB growth

**Status:** OPEN — `toggle()` adds without limit. Session mode can grow indefinitely; DB mode grows per-row.

**Impact:** Low — `content()` loads all via `whereIn` which could be slow with 1000+ items.

**Fix:** Add `maxItems = 100` cap in `toggle()`.

---

## 3. No product validation in `ids()` — stale IDs accumulate

**Status:** OPEN — Session IDs are not validated against product existence. DB IDs are filtered by FK cascadeOnDelete (auto-cleanup on product delete).

**Impact:** Low — `content()` handles it via `whereIn`. `count()` may include stale session IDs.

---

## 4. `content()` N+1 risk in views

**Status:** OPEN — `content()` does `Product::with(['images','category'])->whereIn('id', $ids)` — batched, not N+1. But if view accesses `$product->brand` or other non-eager-loaded relations, N+1 triggers.

---

## 5. RecentlyViewed — no merge on login

**Status:** OPEN (by design) — RecentlyViewed is session-only, no DB persistence. Guest recently viewed is lost on login. This is acceptable — recently viewed is ephemeral.

---

## 6. Session driver dependency — `array` driver loses everything

**Status:** OPEN — Both services use `session()`. If `SESSION_DRIVER=array` (tests), all data lost between requests. Tests must use `database` or `file` driver.

---

## 7. Dual-mode consistency — session vs DB after toggle

**Status:** OPEN — After login, `toggle()` switches from session to DB mode. If a guest adds items to session, then logs in, the session items are merged. But if a guest toggles AFTER the merge (race), items could be in session but not DB. The merge runs once at login; subsequent toggles use DB.

---

## AI Checklist before coding Wishlist

- [x] `mergeGuestWishlist` exists and is called from OtpController (FIXED)
- [ ] Checked session vs DB mode via `isAuth()`
- [ ] Verified `insertOrIgnore` for merge safety
- [ ] Checked product eager loading in `content()`
- [ ] Remembered: no cap on wishlist items
- [ ] Remembered: no auth required on toggle route

## Evidence

- `app/Services/WishlistService.php` — 165 lines, dual-mode, mergeGuestWishlist
- `app/Models/Wishlist.php` — new model, user/product relations
- `app/Http/Controllers/Site/OtpController.php:139-141` — mergeGuestWishlist call
- `database/migrations/2026_09_08_000001_create_wishlists_table.php` — schema
