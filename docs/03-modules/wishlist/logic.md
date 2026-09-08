# Wishlist / RecentlyViewed — Logic (Verified Audit)

> **Services:** `WishlistService` (165 lines), `RecentlyViewedService` (100 lines)
> **Model:** `Wishlist` (new — DB persistence for authenticated users)
> **Controller:** `Site\WishlistController` (35 lines)
> **Migration:** `2026_09_08_000001_create_wishlists_table` (unique constraint)
> **Dual-mode:** Session for guests, DB for authenticated users + auto-merge on login

---

## 1. Purpose & Architecture

Two services with different storage strategies:
- **WishlistService:** Dual-mode — Session for guests, DB for authenticated users. Merges on OTP login.
- **RecentlyViewedService:** Session-only (max 20, newest first). No DB persistence.

---

## 2. WishlistService — Dual-Mode Architecture

### Storage Strategy

| State | Storage | Table/Key | Max Items |
|-------|---------|-----------|-----------|
| Guest (not logged in) | Session | `session('wishlist')` = `[product_id, ...]` | Unlimited |
| Authenticated | DB | `wishlists` table (user_id, product_id) | Unlimited |

### Methods

| Method | Signature | Guest | Auth |
|--------|-----------|-------|------|
| `ids()` | `(): array` | Session array | `Wishlist::where(user_id)->pluck(product_id)` |
| `content()` | `(): Collection` | Batch `whereIn` + sort | Same |
| `has()` | `(Product\|int): bool` | `in_array` | `Wishlist::where()->exists()` |
| `toggle()` | `(Product): bool` | Session toggle | DB insert/delete toggle |
| `remove()` | `(Product\|int): void` | Session filter | DB delete |
| `count()` | `(): int` | `count(array)` | `Wishlist::where()->count()` |
| `mergeGuestWishlist()` | `(array $guestIds): void` | — | `insertOrIgnore` + session clear |

### Key Design Decisions
- **Transparent to callers** — `has()` and `toggle()` auto-detect guest vs auth
- **No data loss on login** — `mergeGuestWishlist` transfers session IDs to DB
- **Unique constraint** — `(user_id, product_id)` prevents duplicates
- **insertOrIgnore** — safe upsert without race conditions

---

## 3. mergeGuestWishlist — Integration with OTP Login

### Flow (OtpController::verify)

```
1. $guestCart = session('cart', [])        // capture before login
2. $guestWishlist = session('wishlist', []) // capture before login
3. otpService->verify()                    // login (Auth::login)
4. session()->regenerate()                 // new session ID
5. CartService::mergeGuestCart($guestCart) // merge cart
6. WishlistService::mergeGuestWishlist($guestWishlist) // merge wishlist
7. redirect dashboard
```

### mergeGuestWishlist internals
```php
public function mergeGuestWishlist(array $guestIds): void
{
    if (! $this->isAuth() || empty($guestIds)) return;

    $rows = array_map(fn(int $productId) => [
        'user_id'    => Auth::id(),
        'product_id' => $productId,
        'created_at' => now(),
        'updated_at' => now(),
    ], array_filter(array_map('intval', $guestIds), fn(int $id) => $id > 0));

    if (! empty($rows)) {
        DB::table('wishlists')->insertOrIgnore($rows);
    }

    session()->forget('wishlist'); // clear guest session
}
```

---

## 4. RecentlyViewedService — Session-Only (unchanged)

### Session Key: `recently_viewed`
### Storage: `array<int>` of product IDs (newest first, max 20)

Same as before — no DB persistence, no merge on login.

| Method | Description |
|--------|-------------|
| `add()` | Remove existing + `array_unshift` + `array_slice(0, 20)` |
| `content()` | `Product::with(['images','category','brand'])->whereIn('id', $ids)->where('is_active', true)` |
| `remove()` / `clear()` / `count()` / `has()` | Standard array operations |

---

## 5. Controller — `Site\WishlistController`

### `index()`
- Returns `site.wishlist.index` view with `$items = $this->wishlist->content()`
- No auth required — guest can view wishlist

### `toggle(Request $request): JsonResponse`
- Find product by slug: `Product::where('slug', $request->route('product'))->firstOrFail()`
- Call `$this->wishlist->toggle($product)`
- Return JSON: `{success: true, in_wishlist: bool, count: int}`
- No auth required — guest can toggle

---

## 6. Routes

```
GET  /wishlist                         → WishlistController@index    (guest/auth)
POST /wishlist/toggle/{product:slug}   → WishlistController@toggle   (guest/auth, JSON)
```

---

## 7. Business Rules

| Rule | Evidence |
|------|----------|
| Dual-mode: session for guest, DB for auth | `WishlistService::isAuth()` check |
| Merge on login via `mergeGuestWishlist` | `OtpController::verify:139-141` |
| Unique constraint `(user_id, product_id)` | Migration + `insertOrIgnore` |
| No cap on wishlist items | No limit in toggle |
| Product soft-delete handled | `whereIn` filters non-existent IDs |
| Session cleared after merge | `session()->forget('wishlist')` |

---

## 8. Schema — `wishlists` table

```
id         BIGINT PK AUTO INCREMENT
user_id    BIGINT FK → users.id (cascadeOnDelete)
product_id BIGINT FK → products.id (cascadeOnDelete)
created_at TIMESTAMP
updated_at TIMESTAMP
```

### Indexes
- `(user_id, product_id)` — UNIQUE constraint

---

## 9. Evidence

- `app/Services/WishlistService.php` — 165 lines, dual-mode, mergeGuestWishlist
- `app/Models/Wishlist.php` — new model, user/product relations
- `app/Services/RecentlyViewedService.php` — 100 lines, session-only
- `app/Http/Controllers/Site/WishlistController.php` — 35 lines, index + toggle
- `app/Http/Controllers/Site/OtpController.php:139-141` — mergeGuestWishlist call
- `database/migrations/2026_09_08_000001_create_wishlists_table.php` — schema
- `routes/site.php:60-61` — 2 routes (no auth)
