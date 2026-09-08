# Reviews — API Contract (Verified Audit)

> **Source:** `routes/site.php:49-55`, `routes/admin.php:72-76`

---

## Site Routes (3 routes)

| Method | URI | Name | Middleware | Controller@Method | Auth |
|--------|-----|------|------------|-------------------|------|
| POST | `/products/{product:slug}/reviews` | `products.reviews.store` | web | `ProductController@storeReview` | guest/auth |
| POST | `/products/{product:slug}/reviews/{review}/reply` | `products.reviews.reply` | web | `ProductController@storeReply` | guest/auth |
| POST | `/products/{product:slug}/reviews/{review}/vote` | `products.reviews.vote` | web, auth | `ProductController@voteReview` | auth |

**Notes:**
- No throttle middleware on any review route (spam risk)
- `storeReview` and `storeReply` allow guests (guest_name + guest_phone)
- `voteReview` requires auth
- `{product:slug}` uses route model binding on slug
- `{review}` uses route model binding on ID

---

## Admin Routes (6 routes)

| Method | URI | Name | Middleware | Controller@Method | Auth |
|--------|-----|------|------------|-------------------|------|
| PATCH | `/admin/reviews/approval` | `reviews.approval` | auth, adminPermission, log.admin | `ReviewController@updateApproval` | admin |
| PATCH | `/admin/reviews/featured` | `reviews.featured` | auth, adminPermission, log.admin | `ReviewController@toggleFeatured` | admin |
| POST | `/admin/reviews/reply` | `reviews.reply` | auth, adminPermission, log.admin | `ReviewController@reply` | admin |
| GET | `/admin/reviews` | `admin.reviews.index` | auth, adminPermission, log.admin | `ReviewController@index` | admin |
| GET | `/admin/reviews/{review}` | `admin.reviews.show` | auth, adminPermission, log.admin | `ReviewController@show` | admin |
| DELETE | `/admin/reviews/{review}` | `admin.reviews.destroy` | auth, adminPermission, log.admin | `ReviewController@destroy` | admin |

**Notes:**
- `approval`, `featured`, `reply` are defined BEFORE `resource` to avoid route conflicts
- All admin routes use `auth, adminPermission, log.admin` middleware

---

## Request/Response Patterns

### POST `/products/{product:slug}/reviews` (Site)
**Request:** `multipart/form-data`
```
rating = 5                    // required, integer 1-5
comment = "عالی بود!"         // required, string 3-1000
guest_name = "علی"            // required if guest, string 2-100
guest_phone = "09121234567"   // required if guest, regex ^0[0-9]{9,}$
```
**Response:** Redirect to product show with `#tab-reviews`

### POST `/products/{product:slug}/reviews/{review}/reply` (Site)
**Request:** `multipart/form-data`
```
comment = "ممنون از نظر شما"  // required, string 3-500
guest_name = "علی"            // required if guest
guest_phone = "09121234567"   // required if guest
```
**Response:** Redirect to product show with `#tab-reviews`

### POST `/products/{product:slug}/reviews/{review}/vote` (Site)
**Request:** JSON `{ "type": "like" }` or `{ "type": "dislike" }`
**Response (JSON):**
```json
{
  "success": true,
  "action": "added|updated|removed",
  "likes": 5,
  "dislikes": 1,
  "user_vote": "like"
}
```

### PATCH `/admin/reviews/approval` (Admin)
**Request:** `{ "id": 1, "is_approved": 1 }` — 0=pending, 1=approved, 2=rejected
**Response:** `{ "success": true, "message": "..." }`

### PATCH `/admin/reviews/featured` (Admin)
**Request:** `{ "id": 1 }`
**Response:** `{ "success": true, "is_featured": true, "message": "..." }`

### POST `/admin/reviews/reply` (Admin)
**Request:** `{ "review_id": 1, "comment": "..." }` — max 2000 chars
**Response:**
```json
{
  "success": true,
  "message": "...",
  "reply": {
    "id": 42,
    "display_name": "Admin",
    "comment": "...",
    "created_at": "2 hours ago"
  }
}
```

---

## Validation Rules Summary

| Field | Review | Reply | Vote | Admin Reply |
|-------|--------|-------|------|-------------|
| `rating` | required, 1-5 | — (inherited) | — | — |
| `comment` | required, 3-1000 | required, 3-500 | — | required, max:2000 |
| `type` | — | — | required, in:like,dislike | — |
| `guest_name` | required if guest, 2-100 | required if guest | — | — |
| `guest_phone` | required if guest, regex | required if guest | — | — |
