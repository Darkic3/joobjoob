# Reviews — Gotchas & Known Bugs (Verified Audit)

---

## 1. No throttle on review submission (SECURITY — spam risk)

**Bug:** `POST /products/{product:slug}/reviews` and `/reply` have **no throttle middleware**. Any visitor (including guests) can submit unlimited reviews.

**Impact:** Spam flood — a bot can submit thousands of fake reviews in minutes.

**Evidence:** `routes/site.php:49-55` — no `throttle` middleware on review routes.

**Fix:** Add `throttle:10,1` (10 reviews per minute) to review submission routes.

---

## 2. Guest reviews bypass user verification (SPAM vector)

**Bug:** Guest reviews require only `guest_name` (min 2 chars) and `guest_phone` (regex `^0[0-9]{9,}$`). No phone verification (OTP/SMS) is performed. Anyone can submit with any phone number.

**Impact:** Fake reviews with arbitrary names and phones — no accountability.

**Evidence:** `StoreReviewRequest:24-25` — regex only format-checks, no verification.

---

## 3. `voteReview` N+1 query on JSON response

**Bug:** In `voteReview` JSON path, after `$review->refresh()`, the code does:
```php
'likes' => $review->votes()->where('type', ReviewVote::TYPE_LIKE)->count(),
'dislikes' => $review->votes()->where('type', ReviewVote::TYPE_DISLIKE)->count(),
'user_vote' => ReviewVote::where('review_id', $review->id)->where('user_id', auth()->id())->value('type'),
```
Three separate queries per vote. If called rapidly (double-click), multiple DB hits.

**Impact:** Minor performance — but 3 queries where 1 would suffice.

**Fix:** Use `$review->loadCount('votes')` or a single combined query.

---

## 4. `storeReview` — no duplicate review prevention

**Bug:** A user can submit multiple reviews for the same product. No unique constraint on `(product_id, user_id)` for non-reply reviews.

**Impact:** Same user can spam reviews on one product.

**Evidence:** `reviews` table has `product_id` + `user_id` but no unique constraint (only composite index on `product_id, is_approved`).

**Fix:** Add unique constraint or check `Review::where('product_id', $id)->where('user_id', auth()->id())->exists()` before create.

---

## 5. `storeReply` — no check if parent belongs to same product

**Bug:** `storeReply` finds review by `$request->route('review')` but doesn't verify `$review->product_id === $product->id`. A reply could be attached to a review from a different product.

**Impact:** Cross-product reply corruption — reply appears under wrong product.

**Evidence:** `ProductController.php:381-383` — `Review::findOrFail($request->route('review'))` without product_id check.

**Fix:** `Review::where('product_id', $product->id)->findOrFail($request->route('review'))`

---

## 6. `admin reply` — no notification to review author

**Bug:** When admin replies to a review, no notification is sent to the original reviewer (or guest).

**Impact:** User doesn't know admin responded — poor UX.

---

## 7. `admin destroy` — no cascade cleanup of child replies

**Bug:** `Review::delete()` uses SoftDeletes. Child replies with `parent_id` pointing to the soft-deleted review still exist but their `parent()` relation returns null (soft-deleted parent). The `replies()` scope filters by `approved()` but doesn't filter orphaned replies.

**Impact:** Orphaned replies appear in the review tree without a parent context.

**Evidence:** `Review::replies()` scope `approved()` doesn't check if parent is soft-deleted.

---

## 8. `admin search` — SQL injection safe but JSON column search missing

**Bug:** Admin search uses `orWhereHas('product', fn($q) => $q->where('name_fa', 'like', ...))` — searches `name_fa` and `name_en` columns. But `Product.name` is JSON column (`cast array`). The correct search should be `where('name->fa', 'like', ...)`.

**Impact:** Search may not work correctly on JSON columns depending on MySQL version.

**Evidence:** `Admin/ReviewController.php:23-28` — `where('name_fa', ...)` on JSON column.

**Fix:** Use `where('name->fa', 'like', ...)` for JSON multilingual search.

---

## 9. `currentUserVote` accessor — N+1 risk in review tree

**Bug:** `Review::currentUserVote()` is a `hasOne` relation scoped to `auth()->id()`. In `show()`, reviews are loaded with `->with(['user', 'votes'])` but `currentUserVote` is NOT eager-loaded. If the view accesses `$review->currentUser_vote`, it triggers a per-review query.

**Impact:** N+1 if view uses `currentUserVote` accessor.

**Evidence:** `ProductController.php:164-170` — loads `votes` but not `currentUserVote`.

---

## 10. `review.new` notification — raw key title (cross-ref Notifications gotcha #1)

**Bug:** `notifyAdmins('review.new', 'نظر جدید ثبت شد', ...)` — the title string is IGNORED by `notifyAdmins` which overwrites with `__("notifications.review.new.title")`. No translation key exists → raw key stored.

**Impact:** Admin sees `notifications.review.new.title` instead of translated label.

**Evidence:** `NotificationService.php:26` — `$title = __("notifications.{$type}.title")`

---

## 11. `is_featured` toggle — no check if review is approved

**Bug:** Admin can feature a pending or rejected review. No guard `is_approved === STATUS_APPROVED` before toggling `is_featured`.

**Impact:** Featured reviews section could show unapproved content.

---

## 12. `comment` nullable in migration but required in validation

**Bug:** Migration `create_reviews_table` has `$table->text('comment')->nullable()`. But `StoreReviewRequest` requires `comment` with `min:3`. Direct DB insert or API could create reviews with null comment.

**Impact:** Minor — only if bypassing form request.

---

## AI Checklist before coding Reviews

- [ ] Added throttle to review submission routes (gotcha #1)
- [ ] Checked parent review belongs to same product (gotcha #5)
- [ ] Prevented duplicate reviews per user per product (gotcha #4)
- [ ] Used `where('name->fa', ...)` for JSON search, not `name_fa` (gotcha #8)
- [ ] Eager-loaded `currentUserVote` if accessing in view (gotcha #9)
- [ ] Checked `review.new` notification uses raw key (gotcha #10)

## Evidence

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
