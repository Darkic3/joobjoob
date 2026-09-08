# Wishlist / RecentlyViewed — API Contract (Verified Audit)

> **Source:** `routes/site.php:60-61`

---

## Site Routes (2 routes)

| Method | URI | Name | Middleware | Controller@Method | Auth |
|--------|-----|------|------------|-------------------|------|
| GET | `/wishlist` | `wishlist.index` | web | `WishlistController@index` | guest/auth |
| POST | `/wishlist/toggle/{product:slug}` | `wishlist.toggle` | web | `WishlistController@toggle` | guest/auth |

**Notes:**
- No `auth` middleware — guest users can view and toggle wishlist
- `{product:slug}` uses route model binding on slug
- `toggle` returns JSON (AJAX endpoint)
- No admin routes — wishlist is user-facing only
- No throttling on any endpoint

---

## Dual-Mode Behavior

| User State | Storage | API Behavior |
|------------|---------|--------------|
| Guest (not logged in) | Session `wishlist` array | Toggle reads/writes session |
| Authenticated | `wishlists` DB table | Toggle reads/writes DB |
| Just logged in (OTP) | Merged: session → DB | `mergeGuestWishlist` called automatically |

---

## Request/Response Patterns

### GET `/wishlist`
**Response:** Blade view `site.wishlist.index` with `$items` (Collection of Product models)

### POST `/wishlist/toggle/{product:slug}`
**Request:** No body needed (product identified by route param)
**Response (JSON):**
```json
{
  "success": true,
  "in_wishlist": true,
  "count": 5
}
```

**Error (product not found):** 404

---

## Validation

- No FormRequest classes — route model binding handles product lookup
- No input validation on toggle (product identified by slug)
- No rate limiting

---

## Notes

- Wishlist now persists for authenticated users (DB table)
- Guest wishlist is merged into DB on OTP login via `mergeGuestWishlist`
- RecentlyViewed remains session-only (no persistence)
