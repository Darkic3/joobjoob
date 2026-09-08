# Reviews — Logic (Verified Audit)

> **Models:** `Review` (145 lines), `ReviewVote` (35 lines)
> **Controllers:** `Site\ProductController` (storeReview, storeReply, voteReview), `Admin\ReviewController` (6 actions)
> **Requests:** `StoreReviewRequest`, `StoreReviewReplyRequest`
> **Migration:** 6 migrations (create, guest fields, status change, parent_id, is_featured, review_votes)
> **No dedicated ReviewController on Site** — review actions live inside `ProductController`

---

## 1. Purpose & Architecture

Self-referential threaded review system with moderation (3-state approval), like/dislike voting, guest support, and admin moderation panel. Reviews belong to Products; replies are stored in the same `reviews` table via `parent_id` (no separate `review_replies` table).

**Key design decisions:**
- Self-referential threading (parent_id on same table)
- 3-state moderation: pending → approved/rejected
- Guest reviews allowed (guest_name + guest_phone)
- Votes stored in separate `review_votes` table with unique constraint
- No throttle on review submission (spam risk)

---

## 2. Models

### Review (`app/Models/Review.php`)

**Fillable:** `product_id, user_id, parent_id, guest_name, guest_phone, rating, comment, is_approved, is_featured`
**Casts:** `rating → integer`, `is_approved → integer`, `is_featured → boolean`
**SoftDeletes:** ✅

**Constants:**
- `STATUS_PENDING = 0`
- `STATUS_APPROVED = 1`
- `STATUS_REJECTED = 2`

**Relations:**
| Name | Type | Target | Notes |
|------|------|--------|-------|
| `product` | BelongsTo | Product | `cascadeOnDelete` |
| `user` | BelongsTo | User | `cascadeOnDelete` (nullable for guests) |
| `parent` | BelongsTo | Review (self) | For replies |
| `replies` | HasMany | Review (self) | `->approved()->oldest()` |
| `votes` | HasMany | ReviewVote | `cascadeOnDelete` |
| `currentUserVote` | HasOne | ReviewVote | `where('user_id', auth()->id())` |

**Scopes:** `approved()`, `featured()`, `pending()`
**Accessors:** `display_name` (user name or guest_name), `avatar_letter`, `is_reply`, `is_approved_label`, `likes_count`, `dislikes_count`

### ReviewVote (`app/Models/ReviewVote.php`)

**Fillable:** `review_id, user_id, type`
**Constants:** `TYPE_LIKE = 'like'`, `TYPE_DISLIKE = 'dislike'`
**Unique constraint:** `(review_id, user_id)` — one vote per user per review
**No SoftDeletes**

---

## 3. Site Controllers (inside ProductController)

### `storeReview(StoreReviewRequest $request)`
1. Find product by slug → `firstOrFail()`
2. Build data: `user_id, rating, comment, is_approved=false`
3. If guest → add `guest_name, guest_phone`
4. `$product->reviews()->create($data)`
5. **Notification:** `notifyAdmins('review.new', 'نظر جدید ثبت شد', null, route('admin.reviews.index'), [...])` — wrapped in try/catch
6. Redirect to product show with `#tab-reviews` fragment

### `storeReply(StoreReviewReplyRequest $request)`
1. Find product + parent review
2. Build data: `user_id, parent_id=review->id, rating=review->rating` (reply inherits rating), `is_approved=STATUS_PENDING`
3. If guest → add `guest_name, guest_phone`
4. `$product->reviews()->create($data)`
5. **No notification** for replies (only top-level reviews notify admins)

### `voteReview(Request $request, Product $product, Review $review)`
1. Validate `type ∈ {like, dislike}`
2. Toggle logic:
   - Same type exists → delete (remove vote)
   - Different type exists → update
   - No vote → create
3. If `expectsJson()` → JSON `{success, action, likes, dislikes, user_vote}`
4. Else → redirect to product show

---

## 4. Admin Controller (`Admin\ReviewController`)

| Action | Method | Description |
|--------|--------|-------------|
| `index` | GET | Paginated list with search (comment, guest_name, user.name, product.name) + status/rating filters |
| `show` | GET | Review detail with product, user, replies |
| `updateApproval` | PATCH | AJAX — set `is_approved` to 0/1/2 |
| `toggleFeatured` | PATCH | AJAX — toggle `is_featured` boolean |
| `reply` | POST | AJAX — admin reply (auto-approved) |
| `destroy` | DELETE | Soft delete review |

**Search:** Searches `comment`, `guest_name`, `guest_phone`, `user.name`, `product.name_fa`, `product.name_en` — uses `orWhereHas` for relations.

---

## 5. Validation

### StoreReviewRequest
```php
'rating'  => ['required', 'integer', 'min:1', 'max:5']
'comment' => ['required', 'string', 'min:3', 'max:1000']
'guest_name'  => ['required', 'string', 'min:2', 'max:100']     // guest only
'guest_phone' => ['required', 'string', 'regex:/^0[0-9]{9,}$/'] // guest only
```

### StoreReviewReplyRequest
```php
'comment' => ['required', 'string', 'min:3', 'max:500']         // shorter max
'guest_name'  => ['required', 'string', 'min:2', 'max:100']     // guest only
'guest_phone' => ['required', 'string', 'regex:/^0[0-9]{9,}$/'] // guest only
```

**Custom messages:** Full Persian translations for all rules.

---

## 6. Routes

### Site (3 routes — inside product routes)
```
POST /products/{product:slug}/reviews                    → storeReview   (no throttle!)
POST /products/{product:slug}/reviews/{review}/reply     → storeReply    (no throttle!)
POST /products/{product:slug}/reviews/{review}/vote      → voteReview    (auth required)
```

### Admin (6 routes)
```
PATCH  /admin/reviews/approval    → updateApproval  (AJAX, before resource)
PATCH  /admin/reviews/featured    → toggleFeatured  (AJAX, before resource)
POST   /admin/reviews/reply       → reply           (AJAX, before resource)
GET    /admin/reviews             → index
GET    /admin/reviews/{review}    → show
DELETE /admin/reviews/{review}    → destroy
```

---

## 7. Business Rules

| Rule | Evidence |
|------|----------|
| Rating 1-5 integer | `StoreReviewRequest:18` |
| Comment 3-1000 chars (review), 3-500 (reply) | Request classes |
| Guest reviews allowed | `guest_name/guest_phone` conditional validation |
| Reply inherits parent rating | `StoreReviewReplyRequest` has no `rating` field; controller sets `rating=review->rating` |
| Review moderation: pending by default | `is_approved=false` in `storeReview` |
| Admin reply auto-approved | `is_approved=STATUS_APPROVED` in admin `reply` |
| Vote toggle | Same type → delete, different → update, none → create |
| Unique vote per user per review | DB unique constraint `(review_id, user_id)` |
| Guest phone regex | `^0[0-9]{9,}$` (Iranian mobile) |
| No throttle on review submission | Routes have no throttle middleware |
| SoftDeletes on reviews | `Review` uses SoftDeletes |
| cascadeOnDelete for product_id, user_id, review_id | Migrations |

---

## 8. Notification Flow

When a new review is submitted:
```php
app(NotificationService::class)->notifyAdmins(
    'review.new',
    'نظر جدید ثبت شد',        // ← IGNORED by notifyUser (gotcha: raw key)
    null,                       // body
    route('admin.reviews.index'), // link
    ['product_id' => ..., 'reviewer' => ..., 'product_name' => ...]
);
```

⚠️ **Known issue:** Title `'نظر جدید ثبت شد'` is IGNORED — `notifyAdmins` overwrites with `__("notifications.review.new.title")` which doesn't exist → raw key string stored (Notifications gotcha #1).

---

## 9. Evidence

- `app/Models/Review.php` — self-referential, 3-state, accessors
- `app/Models/ReviewVote.php` — like/dislike, unique constraint
- `app/Http/Controllers/Site/ProductController.php:312-479` — storeReview, storeReply, voteReview
- `app/Http/Controllers/Admin/ReviewController.php` — full CRUD + approval + featured + reply
- `app/Http/Requests/Site/StoreReviewRequest.php` — rating 1-5, comment 3-1000
- `app/Http/Requests/Site/StoreReviewReplyRequest.php` — comment 3-500
- `routes/site.php:49-55` — 3 site routes (no throttle)
- `routes/admin.php:72-76` — 6 admin routes
- `database/migrations/2026_08_01_170059_create_reviews_table.php` — base schema
- `database/migrations/2026_08_31_000001_create_review_votes_table.php` — votes + unique
