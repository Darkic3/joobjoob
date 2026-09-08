# Wishlist / RecentlyViewed — Logic (Verified Audit)

> **Services:** `WishlistService` (85 lines), `RecentlyViewedService` (100 lines)
> **Controller:** `Site\WishlistController` (35 lines)
> **No DB tables** — both services are 100% session-based
> **No DB models** — Product is the only model involved

---

## 1. Purpose & Architecture

Two session-based "soft" services with no persistence:
- **WishlistService:** Product wishlist (heart icon toggle) — stored as `[product_id, ...]` in session
- **RecentlyViewedService:** Recently viewed products (slider) — stored as `[product_id, ...]` in session (max 20, newest first)

Both are **guest-compatible** — no login required. No merge on login (unlike Cart).

---

## 2. WishlistService

### Session Key: `wishlist`
### Storage: `array<int>` of product IDs (ordered by insertion)

### Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `ids()` | `(): array` | Returns filtered int array of product IDs |
| `content()` | `(): Collection` | `Product::with(['images','category'])->whereIn('id', $ids)` — preserves insertion order |
| `has()` | `(Product\|int): bool` | `in_array($id, $this->ids(), true)` |
| `toggle()` | `(Product): bool` | Add if missing, remove if present. Returns new state (`true` = in wishlist) |
| `remove()` | `(Product\|int): void` | Remove by ID |
| `count()` | `(): int` | `count($this->ids())` |

### Key Design Decisions
- **No DB table** — purely session storage
- **No merge on login** — wishlist is lost on session destroy (unlike Cart's `mergeGuestCart`)
- **No cap on items** — can grow indefinitely (unlike RecentlyViewed's max 20)
- **No product validation** — if product is deleted, ID remains in session until content() filters it out

---

## 3. RecentlyViewedService

### Session Key: `recently_viewed`
### Storage: `array<int>` of product IDs (newest first, max 20)

### Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `ids()` | `(): array` | Filtered int array |
| `content()` | `(int $limit): Collection` | `Product::with(['images','category','brand'])->whereIn('id', $ids)->where('is_active', true)` — preserves order |
| `add()` | `(Product\|int): void` | Remove existing + `array_unshift` + `array_slice(0, 20)` |
| `remove()` | `(Product\|int): void` | Remove by ID |
| `clear()` | `(): void` | `session()->forget($key)` |
| `count()` | `(): int` | `count($this->ids())` |
| `has()` | `(Product\|int): bool` | `in_array` check |

### Key Design Decisions
- **Max 20 items** — oldest auto-pruned on add
- **Only active products** — `where('is_active', true)` filters deleted/inactive
- **Newest first** — `array_unshift` on add, `sortBy` on content for display
- **No merge on login** — session-based only

---

## 4. Controller — `Site\WishlistController`

### `index()`
- Returns `site.wishlist.index` view with `$items = $this->wishlist->content()`
- No auth required — guest can view wishlist

### `toggle(Request $request): JsonResponse`
- Find product by slug: `Product::where('slug', $request->route('product'))->firstOrFail()`
- Call `$this->wishlist->toggle($product)`
- Return JSON: `{success: true, in_wishlist: bool, count: int}`
- **No auth required** — guest can toggle

---

## 5. Routes

```
GET  /wishlist                    → WishlistController@index    (guest/auth)
POST /wishlist/toggle/{product:slug} → WishlistController@toggle (guest/auth, JSON)
```

**No admin routes** — wishlist is user-facing only.

---

## 6. Business Rules

| Rule | Evidence |
|------|----------|
| Session-only storage | `session($this->sessionKey, [])` — no DB |
| No login required | No `auth` middleware on routes |
| No merge on login | No `mergeGuestWishlist` exists (unlike Cart) |
| No cap on wishlist items | `toggle()` adds without limit |
| Max 20 recently viewed | `array_slice($ids, 0, $this->maxItems)` |
| Only active products in recently viewed | `where('is_active', true)` |
| Insertion order preserved | `sortBy(fn($p) => array_search($p->id, $ids))` |
| Product lookup N+1 in content() | Per-item eager load with `whereIn` (batched, not N+1) |

---

## 7. Cross-Module Integration

### Products → Wishlist
- `ProductController::show()` calls `$this->wishlistService->has($product)` to pass `$inWishlist` to view
- Heart icon state rendered via `$inWishlist`

### Cart → Wishlist
- No integration — wishlist and cart are independent session stores

### Auth → Wishlist
- No merge on login — wishlist is session-only
- **Missing feature:** `mergeGuestWishlist` (like Cart's `mergeGuestCart`) — discussed but not implemented

---

## 8. Evidence

- `app/Services/WishlistService.php` — 85 lines, session-based, toggle/remove/count
- `app/Services/RecentlyViewedService.php` — 100 lines, session-based, max 20, add/remove/clear
- `app/Http/Controllers/Site/WishlistController.php` — 35 lines, index + toggle
- `routes/site.php:60-61` — 2 routes (no auth)
- `app/Http/Controllers/Site/ProductController.php:185` — `$inWishlist` passed to view
