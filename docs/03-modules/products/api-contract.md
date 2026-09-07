# Products — API Contract (Deep Trace)

> **Source:** `routes/admin.php:46`, `routes/site.php:47-57`, `app/Http/Controllers/Admin/ProductController.php`, `app/Http/Controllers/Site/ProductController.php`, `app/Http/Requests/Admin/ProductRequest.php` — trace-e mostaghim, bedoon hads
> **Tag:** `08d468f` or later

---

## 1. Naghshe-e Kolli-e Route-ha

Shopora 2 gorouh-e route baraye products darad:

- **Admin (CRUD):** `Route::resource('products', ProductController::class)` zir-e `prefix admin + name admin. + middleware [auth, adminPermission, log.admin]` — `routes/admin.php:22, 32, 46`
- **Site (Public):** 7 route-e mostaghim dar closure-e `$siteRoutes` — `routes/site.php:47-57` — ke **2 bar** sabt mishavand: yek bar bedoon pishvand (canonical `fa`) va yek bar ba `prefix {locale} + name locale.` (`routes/site.php:161-170`). Natije: har URI-e Site 2 name darad.

Tafavot-e mohem: `Route::resource` baraye `products` **hameye 7 action** ra misazad (shamel `show`) — bar aks-e `product-categories/brands` ke `except('show')` darand. `Admin/ProductController` amma **method-e `show` nadarad** — 6 method-e public darad: `index, create, store, edit, update, destroy` (`ProductController.php:20-245`). Natije: `GET admin/products/{product}` (`admin.products.show`) dar RouteCollection vojood darad vali request be `Controller@show` ke vojood nadarad Exception 500 khahad dad — **route-e morde**. Dar tree-e `config/permissions.php` ham `products.show` tarif nashode — faqat `index/create/store/edit/update/destroy`.

---

## 2. Jadval-e Kamel-e Route-ha (Exhaustive)

### 2.1 Admin — `routes/admin.php`

> Prefix: `admin` | Name prefix: `admin.` | Middleware-e gorouh: `web, auth, adminPermission, log.admin` (`routes/admin.php:32`)

| # | Method | URI | Name | Middleware (gathered) | Controller@Method | File:Line | Vaziat |
|---|--------|-----|------|----------------------|-------------------|-----------|--------|
| A1 | `GET` | `admin/products` | `admin.products.index` | `web, auth, adminPermission, log.admin` | `Admin\ProductController@index` | `routes/admin.php:46` + `ProductController.php:20` | ✅ fa'al — list + 8 filter chip |
| A2 | `GET` | `admin/products/create` | `admin.products.create` | `web, auth, adminPermission, log.admin` | `Admin\ProductController@create` | `routes/admin.php:46` + `ProductController.php:93` | ✅ fa'al — form-e kham |
| A3 | `POST` | `admin/products` | `admin.products.store` | `web, auth, adminPermission, log.admin` | `Admin\ProductController@store` | `routes/admin.php:46` + `ProductController.php:111` | ✅ fa'al — DTO + Service::store |
| A4 | `GET` | `admin/products/{product}` | `admin.products.show` | `web, auth, adminPermission, log.admin` | `Admin\ProductController@show` | `routes/admin.php:46` | ⚠️ **morde** — method dar controller vojood **nadarad** (`ReflectionClass` 6 method). Route dar collection hast vali call Exception. Dar `config/permissions.php:permissions.products.access` ham kilid-e `show` tarif nashode. |
| A5 | `GET` | `admin/products/{product}/edit` | `admin.products.edit` | `web, auth, adminPermission, log.admin` | `Admin\ProductController@edit` | `routes/admin.php:46` + `ProductController.php:123` | ✅ fa'al — eager 8 relation + formData |
| A6 | `PUT/PATCH` | `admin/products/{product}` | `admin.products.update` | `web, auth, adminPermission, log.admin` | `Admin\ProductController@update` | `routes/admin.php:46` + `ProductController.php:229` | ✅ fa'al — DTO + Service::update |
| A7 | `DELETE` | `admin/products/{product}` | `admin.products.destroy` | `web, auth, adminPermission, log.admin` | `Admin\ProductController@destroy` | `routes/admin.php:46` + `ProductController.php:239` | ✅ fa'al — Service::destroy (softDelete + file delete) |

**Tedad-e vaghe'i-e fa'al:** 6 az 7 — A4 bayad `except('show')` mishod mesl-e `product-categories`.

**RBAC:** Har route ba `AdminPermission` (`app/Http/Middleware/AdminPermission.php:15-55`) check mishavad: `is_admin` → bypass, vagarna `routeName` bayad dar `user->canAccess(routeName)` bashad. `config/permissions.php:products.access` 6 kilid darad: `index/create/store/edit/update/destroy` — `show` nadarad pas A4 dar har surat 403 baraye non-superadmin.

**Audit log (product history):** `GET admin/audit-logs/product/{productId}` → `AuditLogController@productHistory` (`routes/admin.php:115`) — joz-e module-e products nist vali baraye tarikhche-e product estefade mishavad.

### 2.2 Site — `routes/site.php:47-57` — Canonical (bedoon pishvand)

> Middleware-e gorouh: `web` (default) | 2 route ba `auth` ezafi

| # | Method | URI | Name | Middleware | Controller@Method | File:Line | Auth | Input |
|---|--------|-----|------|------------|-------------------|-----------|------|-------|
| S1 | `GET` | `products` | `products.index` | `web` | `Site\ProductController@index` | `routes/site.php:47` + `ProductController.php:24` | public | `?category (string|string[], slug) & min_price & max_price (int, final_price) & brand (slug/url) & q (string, name->fa/en) & sort (price_asc/price_desc/newest/best_selling/default) & page` → `paginate 12, withQueryString` |
| S2 | `GET` | `products/{product:slug}` | `products.show` | `web` | `Site\ProductController@show` | `routes/site.php:48` + `ProductController.php:132` | public | `{product:slug}` implicit binding → `where slug = param firstOrFail` + query param `?` nist |
| S3 | `POST` | `products/{product:slug}/reviews` | `products.reviews.store` | `web` | `Site\ProductController@storeReview` | `routes/site.php:49` + `ProductController.php:312` | public (guest mojaz) | `{product:slug}` + body: `rating 1-5 required, comment 3-1000 required, guest_name 2-100 required if guest, guest_phone regex 0[0-9]{9,} required if guest` → `is_approved=false, NotificationService notifyAdmins` |
| S4 | `POST` | `products/{product:slug}/reviews/{review}/reply` | `products.reviews.reply` | `web` | `Site\ProductController@storeReply` | `routes/site.php:51` + `ProductController.php:379` | public (guest mojaz) | `{product:slug},{review}` (`Review::findOrFail`) + body: `comment 3-500 required, guest_name/phone if guest` → `parent_id=review.id, rating=review.rating, is_approved=STATUS_PENDING(0)` |
| S5 | `POST` | `products/{product:slug}/reviews/{review}/vote` | `products.reviews.vote` | `web, auth` | `Site\ProductController@voteReview` | `routes/site.php:53` + `ProductController.php:430` | **auth required** | `{product:slug},{review}` + body: `type in:like,dislike required` → toggle logic (add/update/remove) → agar `expectsJson()` → `JSON {success,action,likes,dislikes,user_vote}` vagarna redirect ba fragment `tab-reviews` |
| S6 | `GET` | `products/{product:slug}/quick-view` | `products.quick-view` | `web` | `Site\ProductController@quickView` | `routes/site.php:56` + `ProductController.php:104` | public | `{product:slug}` → `load [images,category,brand,reviews]` → `site.products.partials.quick-view` |
| S7 | `GET` | `product-categories/{category:slug}` | `products.category` | `web` | `Site\ProductController@category` | `routes/site.php:57` + `ProductController.php:116` | public | `{category:slug}` → `abort_unless is_active 404` + `whereIn product_category_id descendants paginate 12` |
| C1 | `POST` | `cart/add/{product}` | `cart.add` | `web` | `Site\CartController@add` | `routes/site.php:81` | public (guest, session cart) | `{product}` (id) + body: `qty, variant_id?, color?, size?` → `Product final_price/stock` check |
| W1 | `POST` | `wishlist/toggle/{product:slug}` | `wishlist.toggle` | `web` | `Site\WishlistController@toggle` | `routes/site.php:62` | public (session) | `{product:slug}` |

### 2.3 Site — Locale Duplicate — `routes/site.php:167-170`

Hameye 7 route-e S1-S7 ba `prefix {locale} + where locale ~ ^(fa|en|...)$ + name locale.` dobare sabt mishavand:

| # | Method | URI | Name | Controller@Method |
|---|--------|-----|------|-------------------|
| L1 | `GET` | `{locale}/products` | `locale.products.index` | `Site\ProductController@index` |
| L2 | `GET` | `{locale}/products/{product:slug}` | `locale.products.show` | `Site\ProductController@show` |
| L3 | `POST` | `{locale}/products/{product:slug}/reviews` | `locale.products.reviews.store` | `Site\ProductController@storeReview` |
| L4 | `POST` | `{locale}/products/{product:slug}/reviews/{review}/reply` | `locale.products.reviews.reply` | `Site\ProductController@storeReply` |
| L5 | `POST` | `{locale}/products/{product:slug}/reviews/{review}/vote` (+auth) | `locale.products.reviews.vote` | `Site\ProductController@voteReview` |
| L6 | `GET` | `{locale}/products/{product:slug}/quick-view` | `locale.products.quick-view` | `Site\ProductController@quickView` |
| L7 | `GET` | `{locale}/product-categories/{category:slug}` | `locale.products.category` | `Site\ProductController@category` |

**Jame-e kol-e route-haye product-related dar RouteCollection:** 7 (admin) + 7 (site canonical) + 7 (site locale) + 2 (wishlist/cart product) + 1 (audit product-history) + locale doubl-e wishlist/cart = **~24 route** ba filter `product` dar `route:list`.

---

## 3. Voroodi-ha va Validation (Inputs & Validation)

### 3.1 Admin Store/Update — `ProductRequest.php:32-102`

Request class: `App\Http\Requests\Admin\ProductRequest` (`:10`). `authorize()` hamishe `true` (`:14-17`) — auth dar middleware-e route. `prepareForValidation()` slug auto az `name.fa` (`:19-26`). 30 rule gorouh dar `:36-101` (joziat dar `logic.md` §5.2). Error messages-e custom dar `messages()` (`:105-115`).

**Body-e store/update (multipart/form-data chon `images` file darad):**

| Field | Rule | Required | Tozih |
|-------|------|----------|-------|
| `product_category_id` | `required|exists:product_categories,id` | ✅ | FK be categories — active check dar controller (filter) vali validation faghat exists |
| `brand_id` | `nullable|exists:brands,id` | — | optional — null mojaz |
| `name` | `required|array` + `name.fa/en required string max:255` | ✅ | JSON fa/en — har do required |
| `slug` | `required string max:255 + UrlRule(Product,id)` | ✅ (auto agar khali) | unique dar `products.slug where deleted_at null` + ignore own id dar update |
| `short_description` | `nullable|array` | — | JSON — validation-e `fa/en` nadarad (nullable array) |
| `description` | `nullable|array` | — | JSON — moshabeh |
| `sku` | `nullable string max:100 unique:products,sku,id` | — | unique + nullable |
| `price/discount_price/stock/weight` | `nullable integer min:0` | — | nullable chon agar variant darad az variant jam mishavad — DTO `:48` radif-e variant-e khali ra hazf mikonad |
| `is_active/is_featured/is_best_seller` | `boolean` | — | checkbox — `boolean` cast |
| `deal_ends_at` | `nullable date after:now` | — | legacy deal timer |
| `timer_active` | `boolean` | — | |
| `start_timer` | `nullable date` | — | |
| `end_timer` | `nullable date after:start_timer` | — | |
| `specs` | `nullable array, specs.* array, specs.*.* integer exists:specifications,id` | — | `[parent_id => [child_id,...]]` |
| `text_specs` | `nullable array, text_specs.* nullable string max:500` | — | `[spec_id => value]` |
| `variants` | `nullable array` + 6 sub-rule | — | `variants.*.id nullable exists:product_variants,id` + `spec_ids nullable array + exists` + `price/discount_price/stock/weight nullable integer min:0` |
| `properties` | `nullable array` + `title/value nullable string` | — | `properties.*.title max:255, value max:500` — DTO `:66-68` agar title ya value khali hazf |
| `related_ids / complement_ids` | `nullable array, *.integer exists:products,id` | — | self-referential — khod-e product ham mojaz? (validation check nemikonad) |
| `images` | `nullable array, images.* image mimes:jpg,jpeg,png,webp max:3072` | — | max 3MB har file |
| `delete_images` | `nullable array, delete_images.* integer` | — | id-haye tasavir-e ghabli baraye hazf |
| `main_image_id` | `nullable integer` | — | id-e tasvir-e ghabli baraye main |
| `main_new_index` | `nullable integer` | — | index-e file-e jadid baraye main |

**Content-Type:** `multipart/form-data` (chand file upload). Redirect-e movafagh: `redirect()->route('admin.products.index')->with('success', __('admin.products.created/updated'))` (`Admin/ProductController.php:119,235`).

### 3.2 Site Reviews — Inline validation dar controller

- **storeReview (`Site/ProductController.php:318-339`):** `rating required integer 1-5, comment required 3-1000` + `guest_name required 2-100 + guest_phone regex 0[0-9]{9,}` agar `!auth()->check()`. Message-e farsi-e custom dar `:324-330`.
- **storeReply (`Site/ProductController.php:386-404`):** `comment required 3-500` + guest fields moshabeh.
- **voteReview (`Site/ProductController.php:432-433`):** `type required in:like,dislike` — faghat authenticated.

---

## 4. Pasokh-ha (Responses)

| Route | Success | Error |
|-------|---------|-------|
| `admin.products.index` | `200 view admin.products.index` ba `products (LengthAwarePaginator 15), categories, brands, activeFilters` | `401 redirect admin.login` agar na auth, `403` agar `adminPermission` fail |
| `admin.products.create` | `200 view admin.products.create` ba `categories, brands, specifications (+children), textSpecifications, products (id,name,slug), isEdit=false, oldVal, selectedSpecs, textSpecValues, existingVariants, existingProperties, relatedIds, complementIds` | `401/403` |
| `admin.products.store` | `302 redirect admin.products.index` ba `success __('admin.products.created')` | `422 validation` ba `errors` + `old()` — `prepareForValidation` slug auto ghabl az validation |
| `admin.products.edit` | `200 view admin.products.edit` ba `product (eager 8 relations) + haman data-e create + isEdit=true` | `404` agar product softDeleted (implicit binding `Product` bedoon `withTrashed`) |
| `admin.products.update` | `302 redirect admin.products.index` ba `success __('admin.products.updated')` | `422 / 404` |
| `admin.products.destroy` | `302 redirect admin.products.index` ba `success __('admin.products.deleted')` | `404` |
| `products.index` | `200 view site.products.index` ba `products (paginate 12 withQueryString), categories (withCount), brands, filters, selectedCategories, selectedBrand, selectedSort` | `200` ba list-e khali agar filter hich natije |
| `products.show` | `200 view site.products.show` ba `product (eager 10), related (manual ya fallback 7), complement, reviewTree, trustBadges(4), dealEndsAt, viewData{mainImage,specGroups,textValues,mainSpecs,variantsData,minVariant}, userVotes` | `404` agar slug nabashad |
| `products.reviews.store` | `302 redirect products.show#tab-reviews` ba `success __('messages.review_submitted')` + `NotificationService try/catch` | `422` agar validation fail |
| `products.reviews.reply` | `302 redirect products.show#tab-reviews` ba `success __('messages.reply_submitted')` | `422 / 404` review not found |
| `products.reviews.vote` | Agar JSON: `200 JSON {success,action:added/updated/removed,likes,dislikes,user_vote}` — vagarna `302 redirect products.show#tab-reviews` | `401 agar !auth, 422 agar type invalid` |
| `products.quick-view` | `200 view site.products.partials.quick-view` ba `product` | `404` |
| `products.category` | `200 view site.product-categories.show` ba `category (+parent), products paginate 12` | `404` agar slug nabashad ya `is_active=false` (`abort_unless 404`) |
| `admin.products.show` (morde) | `500 MethodNotFound` — controller method nadarad | n/a |

---

## 5. Middleware va Auth — Tafsil

- **Admin:** `web` (session, csrf, cookies) + `auth` (redirect be `admin.login` agar guest, JSON 401) + `AdminPermission` (`app/Http/Middleware/AdminPermission.php:15-55`): `!user → 401/redirect`, `!is_admin && !roles()->exists() → redirect home 403`, `is_admin → bypass`, `routeName in config('permissions.free') → pass`, `user->canAccess(routeName) → pass else 403` + `log.admin` (RequestLog).
- **Site reviews vote:** `web, auth` — faghat `products.reviews.vote` auth mikhahad — `Site/ProductController.php:430` dobare `auth()->id()` estefade mikonad.
- **Site digar:** faghat `web` — public.
- **Locale:** `SetLocale` dar `bootstrap/app.php` — `{locale}` param ra az URL mikhanad, `app()->setLocale` + `404` agar code dar `config/languages + DB locale()->codes()` nabashad.

---

## 6. Validation-e Vaziat-e Route (Route Health)

| Check | Natije |
|-------|--------|
| `admin.products.show` method dar controller? | ❌ **nadarad** — `ReflectionClass Admin\ProductController` 6 method: `index/create/store/edit/update/destroy`. Route dar collection hast vali call 500. Bayad `Route::resource(...)->except('show')` mishod. |
| `products.show` implicit binding `{product:slug}` | ✅ — `Product` ba `slug` (na `id`) — migration `slug unique index`, search `where('slug', route('product'))` dar controller `:106,118,134` |
| `product-categories/{category:slug}` binding | ✅ — `ProductCategory` ba `slug` |
| `reviews/{review}` binding | ✅ — `Review::findOrFail(route('review'))` (`Site/ProductController.php:382`) — na `{review:id}` binding, mostaghim `findOrFail` |
| `wishlist/toggle/{product:slug}` | ✅ — `WishlistController@toggle` — session-based |
| `cart/add/{product}` | ✅ — `CartController@add` — `Product $product` implicit (id, na slug) |
| CSRF baraye POST reviews/vote | ✅ — `web` middleware CSRF check — form bayad `@csrf` dashte bashad |
| Throttle baraye reviews | ❌ **nadarad** — bar aks-e `contact (throttle:5,1)` va `otp (5,1)` — review spam mitigation nadarad |

---

## 7. Example cURL (Namoune)

```bash
# Admin — list ba filter
curl -b cookies.txt "https://shopora.test/admin/products?search=گوشی&category=3&brand_id=2&status=1&sort=price_asc&availability=in_stock&price_min=1000000&price_max=50000000&featured=1&best_seller=1&page=1"

# Admin — create product (multipart)
curl -b cookies.txt -X POST "https://shopora.test/admin/products" \
  -F "product_category_id=3" -F "brand_id=2" \
  -F "name[fa]=گوشی سامسونگ" -F "name[en]=Samsung Phone" \
  -F "slug=samsung-phone" -F "price=25000000" -F "discount_price=22000000" -F "stock=10" \
  -F "is_active=1" -F "is_featured=1" \
  -F "specs[1][]=5" -F "specs[1][]=6" -F "text_specs[10]=مقدار متنی" \
  -F "variants[0][spec_ids][]=5" -F "variants[0][price]=25000000" -F "variants[0][stock]=5" \
  -F "properties[0][title]=وزن" -F "properties[0][value]=200g" \
  -F "related_ids[]=12" -F "complement_ids[]=15" \
  -F "images[]=@/path/to/img.jpg" -F "main_new_index=0" \
  -H "X-CSRF-TOKEN: $TOKEN"

# Admin — delete image dar update
curl -b cookies.txt -X PUT "https://shopora.test/admin/products/42" \
  -F "_method=PUT" -F "product_category_id=3" -F "name[fa]=..." -F "name[en]=..." -F "slug=..." \
  -F "delete_images[]=7" -F "delete_images[]=8" -F "main_image_id=9"

# Site — product index ba filter JSON search
curl "https://shopora.test/products?category=phones&min_price=1000000&max_price=50000000&brand=samsung&sort=price_asc&q=گوشی"

# Site — quick view (partial HTML)
curl "https://shopora.test/products/samsung-phone/quick-view"

# Site — store review (guest)
curl -X POST "https://shopora.test/products/samsung-phone/reviews" \
  -d "rating=5&comment=عالی بود&guest_name=علی&guest_phone=09123456789" -H "X-CSRF-TOKEN: $TOKEN"

# Site — vote review (auth required, JSON)
curl -b cookies.txt -X POST "https://shopora.test/products/samsung-phone/reviews/123/vote" \
  -d "type=like" -H "Accept: application/json" -H "X-CSRF-TOKEN: $TOKEN"
# → {"success":true,"action":"added","likes":5,"dislikes":2,"user_vote":"like"}
```

---

## 8. Khatat-ha va Hoshdar-ha (Pitfall-e API)

- **admin.products.show morde:** `GET admin/products/{product}` dar `route:list` hast (`php artisan route:list --path=products` radif 4) vali controller `show` nadarad. Test-e smoke-e route bayad 500 bargardand. Fix: `Route::resource('products', ...)->except('show')` ya ezafe kardan-e `show` be controller.
- **Implicit binding-e product ba slug dar Site va ba id dar Admin:** Admin `Product $product` dar `edit/update/destroy` ba `id` resolve mishavad (resource default), Site `where('slug', route('product'))` mostaghim search. Inconsistency vali amdan — admin ba id, site ba slug (SEO).
- **Locale duplicate 2x DB query:** Har request-e Site ba locale (masal `/{locale}/products/{product}`) haman controller ra seda mizanad — `Product::where('slug',...)` dobare ejra — hich caching-e query.
- **No throttle baraye reviews:** `products.reviews.store/reply` hich `throttle` nadarand — spam-e nazar emkanpazir. Contact `throttle:5,1` darad (`routes/site.php:51`).
- **related_ids self-reference check nadarad:** `ProductRequest` `related_ids.* exists:products,id` vali check nemikonad `related_ids` shamel-e khod-e product nabashad — self-loop mojaz (logic bayad filter konad vali nemikonad).

---

## 9. Evidence

| Claim | File:Line |
|-------|-----------|
| Admin resource 7 routes (shamel show morde) | `routes/admin.php:46` + `php artisan route:list` A4 |
| Admin middleware web,auth,adminPermission,log.admin | `routes/admin.php:32` + `app/Http/Middleware/AdminPermission.php:15-55` |
| Admin RBAC permissions.products 6 access | `config/permissions.php:products.access index/create/store/edit/update/destroy` |
| Site 7 routes + locale duplicate 7 | `routes/site.php:47-57` + `:167-170` `$siteRoutes()` ×2 |
| Site auth faqat vote | `routes/site.php:53-55` `middleware('auth')` faqat vote |
| Site review guest + notification | `app/Http/Controllers/Site/ProductController.php:312-374` |
| ProductRequest 30 rules + HasSlug | `app/Http/Requests/Admin/ProductRequest.php:10-102` + `app/Traits/HasSlug.php:8` |
| UrlRule where deleted_at null + exceptId | `app/Rules/UrlRule.php:22-33` |

