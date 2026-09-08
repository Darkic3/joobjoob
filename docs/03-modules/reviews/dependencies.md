# Reviews — Dependencies (Verified Audit)

---

## Direct Dependencies

### Models
- `Review` — self-referential threaded reviews with moderation
- `ReviewVote` — like/dislike votes per user per review
- `Product` — parent entity (cascadeOnDelete)
- `User` — author (cascadeOnDelete, nullable for guests)

### Services
- `NotificationService` — sends `review.new` to admins on new review

### Helpers
- `setting()` — not used directly in review code
- `trans()` — used in validation messages and accessors

### External
- None — pure DB, no external APIs

---

## Inbound Dependencies (who interacts with Reviews)

| Consumer Module | Interaction | Notes |
|-----------------|-------------|-------|
| **Products** | Reviews loaded via `Product::reviews()` relation | Eager-loaded in `show()` with `user` and `votes` |
| **Products** | `buildReviewTree()` constructs nested reply tree | Called in `ProductController::show()` |
| **Products** | `storeReview` creates notification | `notifyAdmins('review.new', ...)` |
| **Admin** | Full CRUD + moderation panel | `Admin\ReviewController` |
| **Notifications** | `review.new` type sent to admins | Fan-out to all active admins |

---

## Outbound Dependencies (what Reviews calls)

| Dependency | Usage | Notes |
|------------|-------|-------|
| `Product::reviews()` | HasMany relation | `cascadeOnDelete` |
| `Review::replies()` | HasMany self | `approved()->oldest()` |
| `ReviewVote::create()` | Vote insertion | Unique constraint `(review_id, user_id)` |
| `NotificationService::notifyAdmins()` | New review alert | Wrapped in try/catch |

---

## Cross-Module Gotchas

### Products → Reviews
- `ProductController::show()` loads `reviews.user` + `reviews.votes` — N+1 if `currentUserVote` accessed
- `quickView()` loads `reviews` without user/votes — minimal data
- `buildReviewTree()` is O(n) — good for moderate review counts

### Notifications → Reviews
- `review.new` notification sent on every new review (including guest reviews)
- Title is IGNORED (Notifications gotcha #1) — raw key stored
- Admin reply does NOT send notification to review author

### Admin → Reviews
- `updateApproval` changes moderation status (AJAX)
- `toggleFeatured` controls homepage display
- `reply` creates auto-approved child review
- `destroy` uses SoftDeletes — replies become orphaned

---

## Schema

### `reviews` table
```
id              BIGINT PK AUTO INCREMENT
product_id      BIGINT FK → products.id (cascadeOnDelete)
user_id         BIGINT FK → users.id (cascadeOnDelete, nullable for guests)
parent_id       BIGINT NULLABLE FK → reviews.id (self-referential for replies)
guest_name      VARCHAR NULLABLE
guest_phone     VARCHAR NULLABLE
rating          TINYINT UNSIGNED (1-5)
comment         TEXT NULLABLE
is_approved     TINYINT DEFAULT 0 (0=pending, 1=approved, 2=rejected)
is_featured     BOOLEAN DEFAULT FALSE
deleted_at      TIMESTAMP NULLABLE (SoftDeletes)
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

### Indexes
- `(product_id, is_approved)` — composite for listing approved reviews per product

### `review_votes` table
```
id              BIGINT PK AUTO INCREMENT
review_id       BIGINT FK → reviews.id (cascadeOnDelete)
user_id         BIGINT FK → users.id (cascadeOnDelete)
type            ENUM('like', 'dislike')
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

### Indexes
- `(review_id, user_id)` — UNIQUE constraint (one vote per user per review)

---

## Coupling Assessment

**Loose coupling:** Reviews is a leaf node — it reads Products/Users but triggers nothing except a notification.

**Tight coupling:** `Product::reviews()` relation — deleting a product cascades all reviews.

**Risk:** No throttle on review submission — spam flood possible. Guest reviews have no phone verification.

---

## Evidence

- `app/Models/Review.php` — self-referential, 3-state, accessors
- `app/Models/ReviewVote.php` — like/dislike, unique constraint
- `app/Http/Controllers/Site/ProductController.php:312-479` — review actions
- `app/Http/Controllers/Admin/ReviewController.php` — moderation panel
- `app/Http/Requests/Site/StoreReviewRequest.php` — validation
- `app/Http/Requests/Site/StoreReviewReplyRequest.php` — validation
- `routes/site.php:49-55` — 3 site routes
- `routes/admin.php:72-76` — 6 admin routes
- `database/migrations/2026_08_01_170059_create_reviews_table.php` — base
- `database/migrations/2026_08_31_000001_create_review_votes_table.php` — votes
