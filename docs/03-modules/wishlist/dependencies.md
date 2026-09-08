# Wishlist / RecentlyViewed — Dependencies (Verified Audit)

---

## Direct Dependencies

### Models
- `Product` — the only model involved (read-only via `whereIn`)

### Services
- `WishlistService` — session-based wishlist (85 lines)
- `RecentlyViewedService` — session-based recently viewed (100 lines)

### Controllers
- `Site\WishlistController` — index + toggle (35 lines)
- `Site\ProductController` — calls `WishlistService::has()` for heart icon state

### External
- None — pure session, no DB, no external APIs

---

## Inbound Dependencies (who uses Wishlist/RecentlyViewed)

| Consumer Module | Interaction | Notes |
|-----------------|-------------|-------|
| **Products** | `ProductController::show()` calls `$this->wishlistService->has($product)` | Passes `$inWishlist` to view for heart icon |
| **Products** | `ProductController::show()` calls `$this->recentlyViewed->add($product)` | Adds to recently viewed list |
| **Auth-OTP** | `OtpController::verify()` regenerates session | **Wishlist lost** (no merge — unlike Cart) |

---

## Outbound Dependencies (what Wishlist calls)

| Dependency | Usage | Notes |
|------------|-------|-------|
| `Product::with(['images','category'])->whereIn('id', $ids)` | Batch load products | In `content()` — not N+1 |
| `session($key)` | Read/write session array | No DB interaction |
| `Product::where('slug', ...)->firstOrFail()` | Product lookup in toggle | Route model binding |

---

## Cross-Module Gotchas

### Auth-OTP → Wishlist
- `OtpController::verify()` calls `session()->regenerate()` after login
- Wishlist session array is NOT preserved — **guest wishlist lost on login**
- Cart has `mergeGuestCart`; Wishlist has NO equivalent

### Products → Wishlist
- `ProductController::show()` reads `$inWishlist` for heart icon
- No write interaction — toggle is separate AJAX endpoint

---

## Schema

### No database tables
Both services use Laravel session storage:
- `session('wishlist')` → `array<int>` of product IDs
- `session('recently_viewed')` → `array<int>` of product IDs (max 20, newest first)

### Session keys
| Key | Service | Storage | Max Items |
|-----|---------|---------|-----------|
| `wishlist` | WishlistService | `array<int>` | Unlimited |
| `recently_viewed` | RecentlyViewedService | `array<int>` | 20 |

---

## Coupling Assessment

**Loose coupling:** Wishlist/RecentlyViewed are leaf nodes — they read Products but trigger nothing.

**Tight coupling:** None — no inverse dependencies.

**Risk:** Session-only storage means data is lost on session destroy (login, timeout, driver change). No persistence means no recovery.

---

## Evidence

- `app/Services/WishlistService.php` — 85 lines, session-based
- `app/Services/RecentlyViewedService.php` — 100 lines, max 20
- `app/Http/Controllers/Site/WishlistController.php` — 35 lines, no auth
- `app/Http/Controllers/Site/ProductController.php:185` — `$inWishlist`
- `app/Http/Controllers/Site/ProductController.php:161` — `recentlyViewed->add()`
- `routes/site.php:60-61` — 2 routes
