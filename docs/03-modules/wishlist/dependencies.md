# Wishlist / RecentlyViewed — Dependencies (Verified Audit)

---

## Direct Dependencies

### Models
- `Wishlist` — NEW: DB persistence for authenticated users
- `Product` — the only model involved (read-only via `whereIn`)
- `User` — FK in wishlists table

### Services
- `WishlistService` — dual-mode (session + DB), 165 lines
- `RecentlyViewedService` — session-only, 100 lines

### Controllers
- `Site\WishlistController` — index + toggle (35 lines)
- `Site\ProductController` — calls `WishlistService::has()` for heart icon state
- `Site\OtpController` — calls `WishlistService::mergeGuestWishlist()` after login

### External
- None — pure session + DB, no external APIs

---

## Inbound Dependencies (who uses Wishlist)

| Consumer Module | Interaction | Notes |
|-----------------|-------------|-------|
| **Products** | `ProductController::show()` calls `$this->wishlistService->has($product)` | Passes `$inWishlist` to view for heart icon |
| **Products** | `ProductController::show()` calls `$this->recentlyViewed->add($product)` | Adds to recently viewed list |
| **Auth-OTP** | `OtpController::verify()` calls `mergeGuestWishlist($guestIds)` | Transfers session wishlist to DB (FIXED) |

---

## Outbound Dependencies (what Wishlist calls)

| Dependency | Usage | Notes |
|------------|-------|-------|
| `Product::with(['images','category'])->whereIn('id', $ids)` | Batch load products | In `content()` — not N+1 |
| `Wishlist::where(user_id, product_id)` | DB queries for auth mode | Unique constraint |
| `DB::table('wishlists')->insertOrIgnore()` | Merge guest wishlist | Safe upsert |
| `session($key)` | Guest mode storage | No DB interaction |

---

## Schema

### `wishlists` table (NEW)
```
id         BIGINT PK AUTO INCREMENT
user_id    BIGINT FK → users.id (cascadeOnDelete)
product_id BIGINT FK → products.id (cascadeOnDelete)
created_at TIMESTAMP
updated_at TIMESTAMP
```

### Indexes
- `(user_id, product_id)` — UNIQUE constraint

### Session keys
| Key | Service | Storage | Max Items |
|-----|---------|---------|-----------|
| `wishlist` | WishlistService | Session (guest) / DB (auth) | Unlimited |
| `recently_viewed` | RecentlyViewedService | Session only | 20 |

---

## Coupling Assessment

**Loose coupling:** Wishlist/RecentlyViewed are leaf nodes — they read Products but trigger nothing.

**Tight coupling:** `OtpController::verify` now depends on `WishlistService::mergeGuestWishlist`.

**Risk:** Dual-mode adds complexity — session vs DB behavior must be transparent to callers.

---

## Evidence

- `app/Services/WishlistService.php` — 165 lines, dual-mode, mergeGuestWishlist
- `app/Models/Wishlist.php` — new model, user/product relations
- `app/Services/RecentlyViewedService.php` — 100 lines, session-only
- `app/Http/Controllers/Site/WishlistController.php` — 35 lines, no auth
- `app/Http/Controllers/Site/OtpController.php:139-141` — mergeGuestWishlist
- `app/Http/Controllers/Site/ProductController.php:185` — `$inWishlist`
- `database/migrations/2026_09_08_000001_create_wishlists_table.php` — schema
- `routes/site.php:60-61` — 2 routes
