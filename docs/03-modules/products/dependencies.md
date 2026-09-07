# Products — Dependencies (Deep Trace)

> **Module:** `products` — Catalog-e markazi
> **Tag:** `08d468f` or later — trace-e mostaghim az code

---

## 1. Graph-e Vabastegi-ha (Dependency Graph)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         Admin/ProductController                             │
│  index/create/store/edit/update/destroy (+ activeFilterChips, formData)     │
│  DI: ProductService (constructor promotion :18)                               │
└──────┬──────────────────────────────────────────────────────────────────────┘
       │ :20 index → ProductCategory, Brand (where is_active/active)         │
       │ :27 → ProductService::getPaginatedProducts(all filters)            │
       │ :31 → activeFilterChips(8 filters)                                  │
       │ :93 create → ProductCategory, Brand, Specification(with children),  │
       │ │           Specification text, Product(id,name,slug) + formData(null)│
       │ :114 store → ProductDTO::fromRequest → ProductService::store        │
       │ :125 edit → Product load 8 relations + moshabeh create + formData(product)│
       │ :231 update → ProductDTO::fromRequest → ProductService::update       │
       │ :241 destroy → ProductService::destroy                               │
       ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                           ProductService                                     │
│  UPLOAD_FOLDER='products', IMAGE_SIZES=[thumb 300, medium 800, large 1200]  │
│  store / update / destroy (DB::transaction) + getPaginatedProducts          │
│  prepareData (final_price ternary) + 6 sync + 4 image helpers               │
├─────────────────────────────────────────────────────────────────────────────┤
│  prepareData ───────────► ProductDTO (26 getters)                          │
│  appendImages ──────────► ImageHandler::handle(folder, file, sizes)         │
│  │                         ├─► ImageUploader::upload(folder, file)          │
│  │                         │     └─► public/<uploads>/products/*.webp       │
│  │                         │     └─► convertToWebP (GD: imagewebp q90)     │
│  │                         └─► ImageResizer::resize(file, folder, sizes)   │
│  │                               └─► imagecreatefromwebp + cover-crop       │
│  │                               └─► public/<uploads>/products/thumb|medium|large/*.webp│
│  setMainImage ──────────► ProductImage (is_primary, sort_order)            │
│  │                         └─► Product.main_image column sync               │
│  deleteImagesByIds ─────► ImageHandler::delete(folder, path, sizes)        │
│  deleteOldImages ───────► ImageHandler::delete (loop all images)           │
│  syncSpecifications ────► ProductSpecification (parent row + child rows)   │
│  │                         └─► Specification (whereNull parent_id)          │
│  syncVariants ──────────► ProductVariant (updateOrCreate id+product_id)    │
│  │                         ├─► Variant ↔ Specification pivot (sync)         │
│  │                         └─► aggregateVariantPrices (min variant → product)│
│  syncProperties ────────► Property (title JSON, sort_order)                │
│  syncAssociations ──────► ProductAssociation (type=related/complement)     │
│  getPaginatedProducts ──► Product::query with[category,images,brand]       │
└──────┬──────────────────────────────────────────────────────────────────────┘
       │ writes: products, product_images, product_specification,             │
       │         product_variants, product_variant_specification,             │
       │         properties, product_associations                             │
       │ reads:  specifications, brands, product_categories (validation)      │
       ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                              Models                                          │
│  Product (SoftDeletes, 21 fillable, 11 casts, 12 relations, 7 accessors)  │
│  ├─► ProductCategory (is_active, parent/children/products, trans)          │
│  ├─► Brand (active, title JSON, trans, itemImage)                          │
│  ├─► ProductImage (path, is_primary, sort_order, url/thumb/medium/large)   │
│  ├─► ProductSpecification (product_specification, parent_id, value, SoftDeletes)│
│  │    └─► Specification (title JSON, type text/select, parent/children)    │
│  ├─► ProductVariant (SoftDeletes, price float, stock int, specifications)  │
│  │    └─► Specification (pivot product_variant_specification)              │
│  ├─► Property (title JSON, value, sort_order)                              │
│  ├─► ProductAssociation (product_id, related_product_id, type)            │
│  ├─► Review (product->reviews approved, guest_name/phone, votes)           │
│  └─► ProductVariant / ProductImage / ProductSpecification / Property       │
└──────┬──────────────────────────────────────────────────────────────────────┘
       │ DTO/Request
       ├─► ProductDTO (readonly, 26 fields, fromRequest variant/property filtering)
       ├─► ProductRequest (HasSlug prepareSlug, 30 rule groups, UrlRule)
       └─► UrlRule (where slug + whereNull deleted_at + exceptId)
       │ Config
       ├─► config/paths.php:upload = env('UPLOAD_PATH','uploads')
       ├─► config/permissions.php:products.access (6 keys)
       └─► database/migrations/*products* (11 migration files)

┌─────────────────────────────────────────────────────────────────────────────┐
│                        Site/ProductController                                │
│  DI: RecentlyViewedService (readonly), TrustBadgeService (readonly) :19-22   │
│  index / category / show / quickView / storeReview / storeReply / voteReview│
├─────────────────────────────────────────────────────────────────────────────┤
│  index ─────────────────► Product::with[images,category,brand] where is_active│
│  │                        ├─► categoryIdsWithDescendants(slugs) BFS        │
│  │                        ├─► final_price filter, brand whereHas url        │
│  │                        ├─► name->fa/en like search, match sort          │
│  │                        ├─► paginate 12 withQueryString                  │
│  │                        ├─► ProductCategory withCount products            │
│  │                        └─► Brand where active                            │
│  category ──────────────► ProductCategory where slug→ categoryIdsWithDescendants│
│  show ──────────────────► Product where slug load 10 relations             │
│  │                        ├─► related fallback (same category take 7)      │
│  │                        ├─► RecentlyViewedService::add(product)           │
│  │                        ├─► Review approved + ReviewVote userVotes       │
│  │                        ├─► prepareShowData(mainImage/specGroups/textValues/mainSpecs/variantsData/minVariant)│
│  │                        ├─► TrustBadgeService::getActiveForSite(4)       │
│  │                        └─► dealEndsAt (timer_active/end_timer/deal_ends_at)│
│  buildReviewTree ───────► 2-pass map → tree ba replies collect             │
│  storeReview ───────────► Review create is_approved=false + NotificationService│
│  storeReply ────────────► Review create parent_id + STATUS_PENDING         │
│  voteReview ────────────► ReviewVote toggle (add/update/remove) + JSON    │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                          StockService (Mostaghel)                            │
│  reserveStock / restoreStock (DB::transaction + lockForUpdate)              │
│  validateCartItems / getAvailableStock (withTrashed find)                  │
│  decreaseVariantStock: decrement + sum(stock) → product update              │
│  + InventoryTransaction create + AuditService::productStockChanged           │
│  Used by: CartService, OrderService, CheckoutController — na ProductService │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                         Shared / Cross-cutting                               │
│  ImageHandler (handle/delete) ───► ImageUploader + ImageResizer (GD/WebP)   │
│  HasSlug (prepareSlug Str::slug) ──► ProductRequest                         │
│  UrlRule (unique slug where deleted_at null) ──► ProductRequest slug       │
│  HasFactory, SoftDeletes ──► Product, Variant, Spec, Category, Brand        │
│  TrustBadgeService ──► Site show (4 badges)                                 │
│  RecentlyViewedService (session, max 20, singleton AppServiceProvider)      │
│  NotificationService (review.new → admins) ──► Site storeReview             │
│  AuditService (productStockChanged) ──► StockService                       │
│  CartService (dbContent N+1 Product::find per item) ──► Product/Variant    │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Service → Model (Neveshtan/Khandan)

| Service | Writes (DB) | Reads | Transaction | Evidence |
|---------|-------------|-------|-------------|----------|
| `ProductService::store` | `products` (create), `product_images` (append), `product_specification` (sync parent+child+text), `product_variants` (updateOrCreate), `product_variant_specification` (sync), `properties` (create), `product_associations` (create) — hame dakhel `DB::transaction` | `specifications/brands/categories` ghair mostaghim via validation, `products` baraye `final_price` compute | ✅ `DB::transaction` `:30` | `ProductService.php:30-48` |
| `ProductService::update` | moshabeh store + `deleteImagesByIds` + `product->update` | moshabeh | ✅ `DB::transaction` `:56` | `ProductService.php:56-79` |
| `ProductService::destroy` | `product_images` file delete + `product_images` hard delete + `products` softDelete | `product.images` relation | ✅ `DB::transaction` `:87` | `ProductService.php:87-91` |
| `ProductService::getPaginatedProducts` | — (read-only) | `products with category/images/brand`, `brand title->fa` via `orWhereHas`, `specifications` na | — | `ProductService.php:99-159` |
| `StockService::reserveStock` | `products/variants stock decrement`, `inventory_transactions create (type subtract)` | `products/variants withTrashed + lockForUpdate` | ✅ `DB::transaction` + `lockForUpdate` | `StockService.php:12-26, 49,95` |
| `StockService::restoreStock` | `products/variants stock increment`, `inventory_transactions create (type add)` | moshabeh | ✅ | `StockService.php:28-37, 105,148` |
| `Site/ProductController::index` (direct query, na service) | — | `products with images/category/brand + ProductCategory + Brand` | — | `Site/ProductController.php:26-82` |
| `Site/ProductController::show` | `reviews create` (storeReview/Reply) + `ReviewVote create/update/delete` | `products eager 10 relations + related fallback + reviews/votes` | — (no transaction) | `Site/ProductController.php:132-200, 312-479` |

---

## 3. Controller → Service (Tazrigh-e Vabastegi)

| Controller | Service | DI Type | Method-haye seda zade | Evidence |
|------------|---------|---------|-----------------------|----------|
| `Admin/ProductController` | `ProductService` | constructor promotion `protected ProductService $productService` | `getPaginatedProducts` (index), `store` (store), `update` (update), `destroy` (destroy) | `ProductController.php:18,27,117,233,241` |
| `Site/ProductController` | `RecentlyViewedService` | `private readonly RecentlyViewedService $recentlyViewed` | `add(product)` dar `show` | `Site/ProductController.php:20,161` |
| `Site/ProductController` | `TrustBadgeService` | `private readonly TrustBadgeService $trustBadgeService` | `getActiveForSite(4)` dar `show` | `Site/ProductController.php:21,185` |
| `Site/ProductController` | `NotificationService` | `app(NotificationService::class)` (service locator) | `notifyAdmins('review.new', ...)` dar `storeReview` try/catch | `Site/ProductController.php:361` |
| `Site/ProductController` | *(no ProductService)* | — | `index` mostaghim `Product::with` query — **service nadarad** (40% controllers ba direct Model) | `Site/ProductController.php:26` |
| `CartService` (kharej az products vali vabaste) | `Product` + `ProductVariant` | direct `Product::find`, `variants()->find` | `dbContent`, `dbAdd`, `sessionAdd` | `CartService.php:47,91,161` |
| `StockService` | `Product` + `ProductVariant` | direct Eloquent | `getAvailableStock`, `decrease*Stock`, `increase*Stock` | `StockService.php:66-76,49-180` |

**Nokte:** `Admin` kamelan service-oriented, `Site` hybrid (index/category/show direct query + recentlyViewed/trustBadge service). In tafkik amdan ast — Site query-ha sade va filter-mehvar, niaz be transaction nadarand.

---

## 4. Tightly Coupled (Vabastegi-haye Tangatnag)

| Joda | Vaziat | Khatar |
|------|--------|--------|
| **ProductService ↔ ImageHandler** | `store/update` hamishe `appendImages` + `setMainImage` + `deleteImagesByIds` ba `ImageHandler::handle/delete` seda mizanad — file I/O dakhel-e DB transaction | Agar `ImageResizer` exception (`file not found`, `GD not installed \Error`), transaction rollback vali file-haye ghabli-e upload shode dar `public/` baghi mimanand (leak). `@unlink` dar `ImageUploader:58` suppress. |
| **Product ↔ Variant ↔ Specification** | `syncVariants` `spec_ids` ra `sync` mikonad — agar `specifications` table structure taghir konad (masal `parent_id` hazf), variant sync break | `spec_ids.* integer exists:specifications,id` vali parent/child consistency check nadarad — child mitavanad bedoon parent-e sahih sabt shavad. |
| **Product ↔ ProductSpecification (2-level)** | `syncSpecifications` model-e parent row + child rows darad — `parent_id` FK be `product_specification.id` (self-referential) | Agar `parentRows` map eshtebah bashad, child rows orphan mishavand. SoftDelete-e ghabli + create-e jadid atomic vali `deleted_at` filter dar `specifications` relation (`wherePivotNull deleted_at`) mitavanad data-e ghabli ra penhan konad. |
| **ProductDTO ↔ ProductRequest** | DTO `fromRequest` variant/property filtering logic darad — Request validation ghabl az DTO | Test bayad DTO unit test dashte bashad — Request test tanhayi filter-e DTO ra poshesh nemidahad. |
| **Product.main_image ↔ ProductImage.is_primary** | Dual source — column + relation — `setMainImage` har do ra sync mikonad, accessor fallback darad | Agar kasi mostaghim `Product::update(['main_image'=>...])` bedoon `setMainImage` anjam dahad, `is_primary` desync mishavad. Hich DB constraint. |
| **Site show ↔ Review tree** | `buildReviewTree` 2-pass dar controller — kharej az service | Logic-e derakht dar controller — reuse dushvar, test-e unit nadarad. |

---

## 5. Reusable / Shared (Ghabel-e Estefade-ye Mojadad)

| Component | Scope-e estefade | Evidence |
|-----------|------------------|----------|
| `ImageHandler::handle/delete` | `ProductService` + `BrandService` + `BlogService` + `ProductCategoryService` — hameye upload-ha | `app/Helpers/ImageHandler.php:14-48` |
| `ImageUploader::upload` | WebP conversion + `public/uploads/<folder>/` | `app/Helpers/ImageUploader.php:13-78` |
| `ImageResizer::resize` | 3 size `thumb/medium/large` cover-crop | `app/Helpers/ImageResizer.php:14-68` |
| `HasSlug::prepareSlug` | `ProductRequest` + `BrandRequest` + `ProductCategoryRequest` + `BlogRequest` | `app/Traits/HasSlug.php:8` `Str::slug` |
| `UrlRule` | `ProductRequest` slug unique check (where deleted_at null) | `app/Rules/UrlRule.php:22-33` |
| `RecentlyViewedService` | `Site/ProductController@show` — session max 20, singleton `AppServiceProvider` | `app/Services/RecentlyViewedService.php` |
| `StockService` | `CartService`, `OrderService`, `CheckoutController` — na ProductService | `app/Services/StockService.php:12-180` |
| `TrustBadgeService` | `Site/ProductController@show` + `SiteLayoutComposer` | `app/Services/TrustBadgeService.php` |
| `Product.trans(field)` | JSON fa/en locale fallback — dar Product, Brand, Category, Specification, Property | `Product.php:253-261` |
| `config/paths.upload` | Single source of truth baraye `public/<uploads>/` — `ImageHandler`, `ProductImage::url`, `Product::mainImageUrl` | `config/paths.php:14` `env('UPLOAD_PATH','uploads')` |

---

## 6. External / DB / File (Khareji / DB / File)

| Layer | Resource | Joziat | Evidence |
|-------|----------|--------|----------|
| **DB tables** | `products` | `id, product_category_id FK, brand_id FK nullable, name JSON, slug unique, main_image nullable, short_description JSON, description JSON, sku unique nullable, price int, discount_price int nullable, final_price int default 0 index, stock int, weight int, is_active bool, is_featured bool, is_best_seller bool, deal_ends_at datetime nullable, timer_active bool, start_timer datetime nullable, end_timer datetime nullable, softDeletes, timestamps` | `database/migrations/2026_08_01_124250_create_products_table.php` + `2026_08_04_000008_add_brand_and_timer` + `2026_08_04_000009_add_final_price (index + backfill CASE WHEN discount_price>0 THEN discount_price ELSE price END)` |
| | `product_images` | `id, product_id FK cascade, path string, alt_text JSON nullable, is_primary bool default false, sort_order int, timestamps` — **bedoon softDeletes** | `2026_08_01_124353_create_product_images_table.php` |
| | `product_specification` | `id, product_id FK, specification_id FK, value string nullable, parent_id FK nullable (self-referential), softDeletes, timestamps` — pivot-e 2-sat-he | `2026_08_04_000003_create_product_specification_table.php` |
| | `product_variants` | `id, product_id FK cascade, price double default 0, discounted_price double default 0, final_price double default 0 index, stock int, weight double, softDeletes, timestamps` | `2026_08_04_000004_create_product_variants_table.php` |
| | `product_variant_specification` | `product_variant_id FK, specification_id FK` — pivot-e variant specs | `2026_08_04_000005_create_product_variant_specification_table.php` |
| | `properties` | `id, product_id FK cascade, title JSON, value string, sort_order int, timestamps` — bedoon softDeletes | migration properties |
| | `product_associations` | `id, product_id FK, related_product_id FK, type enum related/complement, timestamps` — self-referential | `2026_08_04_000007_create_product_associations_table.php` |
| | `specifications` | `id, title JSON, type enum text/select, parent_id FK nullable, active bool, is_filter bool, is_color bool, color_code string nullable, softDeletes` | `create_specifications` |
| | `product_categories` | `id, parent_id FK nullable, name JSON, slug unique, icon, image, description JSON, meta JSON, sort_order, is_active bool, softDeletes` | `2026_08_01_124237_create_product_categories_table.php` |
| | `brands` | `id, title JSON, url string (slug), description JSON, image, active bool, show_in_first_page bool, sort_order, softDeletes` | `create_brands` |
| **File** | `public/<uploads>/products/` | Har tasvir: `*.webp` asli + `thumb/*.webp` (300×300) + `medium/*.webp` (800×800) + `large/*.webp` (1200×1200) — `ImageHandler::handle` + `ImageResizer` cover-crop `imagewebp q90` | `app/Helpers/ImageHandler.php:14-48` + `ImageResizer.php:14-68` |
| **Cache** | Session `recently_viewed` | `RecentlyViewedService` max 20 product id dar session — na Redis/cache | `app/Services/RecentlyViewedService.php` |
| **External API** | *(hichi)* | Hich API-e khareji — faghat GD extension (`imagecreatefromjpeg/png/webp`, `imagewebp`, `imagecopyresampled`) | `ImageUploader.php:58-68` + `ImageResizer.php:38-60` |

**Indexes-e mohem:**

- `products.slug` unique index — baraye `UrlRule` + Site `where slug`
- `products.final_price` index (`2026_08_04_000009:14` `index('final_price')`) + backfill — baraye sort/filter-e ghimat
- `product_variants.final_price` index (`2026_08_04_000004:23` `index('final_price')`) — baraye `aggregateVariantPrices`
- `product_categories.parent_id` FK index — baraye `categoryIdsWithDescendants` loop
- **Missing:** composite `(is_active, final_price)` baraye `Site index` sort-e default (`stock desc, final_price desc, id desc`) — potential missing index (gotchas.md)

---

## 7. Nemoudar-e Vabastegi-e Module-ha (Module Dependency Map)

```
products (in module)
  ├──► product-categories (ProductCategory — parent/children tree, is_active filter)
  │     Evidence: Admin/ProductController.php:23,93,136 + Site/ProductController.php:68,88-98
  ├──► brands (Brand — active filter, title JSON)
  │     Evidence: Admin/ProductController.php:24,96,137 + Site/ProductController.php:73
  ├──► specifications (Specification — parent/children, type text/select, is_color)
  │     Evidence: Admin/ProductController.php:99-100,139-140 + ProductService.php:203-253
  ├──► cart (CartService — dbContent N+1 Product::find per item, uses Product stock/final_price)
  │     Evidence: app/Services/CartService.php:47,91,161 + app/Services/StockService.php
  ├──► orders (StockService reserveStock/restoreStock — InventoryTransaction + AuditService)
  │     Evidence: app/Services/StockService.php:12-180 + app/Models/Order.php
  ├──► reviews (Review/ReviewVote — product->reviews approved, vote toggle)
  │     Evidence: Site/ProductController.php:136-179,312-479 + app/Models/Review.php
  ├──► trust-badges (TrustBadgeService::getActiveForSite)
  │     Evidence: Site/ProductController.php:185
  ├──► audit-logs (AuditLogController::productHistory — GET audit-logs/product/{productId})
  │     Evidence: routes/admin.php:115
  └──► wishlist (WishlistController — session-based, product slug toggle)
        Evidence: routes/site.php:62 + app/Services/WishlistService.php
```

---

## 8. Evidence Matrix

| Claim | File:Line |
|-------|-----------|
| Admin DI ProductService | `app/Http/Controllers/Admin/ProductController.php:18` |
| Admin index 8 filters delegation | `app/Http/Controllers/Admin/ProductController.php:20-34` + `app/Services/ProductService.php:99-160` |
| Admin activeFilterChips 8 filters | `app/Http/Controllers/Admin/ProductController.php:41-91` |
| Admin formData 4 sakhtar + oldVal | `app/Http/Controllers/Admin/ProductController.php:156-227` |
| ProductService DB::transaction store/update/destroy | `app/Services/ProductService.php:30,56,87` |
| ProductService IMAGE_SIZES WebP | `app/Services/ProductService.php:19-23` + `app/Helpers/ImageHandler.php:14-48` |
| ProductDTO 26 fields fromRequest filtering | `app/DTOs/ProductDTO.php:10-39, 41-107` |
| ProductRequest HasSlug 30 rules | `app/Http/Requests/Admin/ProductRequest.php:7-102` + `app/Traits/HasSlug.php:8` |
| Product 12 relations | `app/Models/Product.php:45-166` (4 BelongsTo/HasMany + 8 list) |
| Site RecentlyViewed + TrustBadge DI | `app/Http/Controllers/Site/ProductController.php:19-22,161,185` |
| Site categoryIdsWithDescendants BFS | `app/Http/Controllers/Site/ProductController.php:88-99` |
| StockService withTrashed + lockForUpdate | `app/Services/StockService.php:49,66-76,95,120,148` |
| Migrations final_price index + backfill | `database/migrations/2026_08_04_000009_add_final_price_to_products_table.php:13-19` |
| Config paths.upload + permissions.products | `config/paths.php:14` + `config/permissions.php:products` |
| Routes admin 7 (1 morde) + site 7×2 locale | `routes/admin.php:46` + `routes/site.php:47-57,167-170` + `php artisan route:list` |

