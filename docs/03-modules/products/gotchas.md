# Products — Gotchas & Known Bugs (Deep Trace)

> **Tag:** `08d468f` or later — hameye mored az code-e vaghe'i, bedoon hads
> **Mokhatab:** AI agent va developer-e ayande — ghabl az dast zadan be products hatman bekhan

---

## 1. Critical Gotchas for AI (Dam-haye Bozorg)

### 1.1 `final_price` vs `price` — Hamishe `final_price` estefade kon

- **Sharkh:** `ProductService::prepareData` (`app/Services/ProductService.php:167-169`) `final_price = discount_price>0 ? discount_price : price` computed mikonad. `Product::getFinalPriceAttribute` (`app/Models/Product.php:195-204`) ham hamin fallback ra darad. Site `index` filter/sort (`Site/ProductController.php:35-40, 57-63`) va Admin `getPaginatedProducts` (`ProductService.php:129-130,139-143`) **hame bar asas-e `final_price` hastand** — na `price`. `price` faghat ghimat-e asli (bedoon takhfif) ast. Agar agent query ba `where('price', ...)` benevisad natayej-e eshtebah (mahsulat-e takhfif-dar ra az dast midahad).
- **Hoshdar:** `discount_price > price` guard nadarad — `ProductRequest.php:50` faghat `min:0`, `prepareData` ham check nemikonad. Agar `discount_price=50000000` va `price=10000000`, `final_price=50000000` mishavad (gerantar az ghimat-e asli!). Test bayad in ra begirad.
- **Fix/Check:** Dar validation `discount_price lte:price` ezafe kon ya dar `prepareData` `min(discount, price)` estefade kon. Hala check nadarad — agent bayad bedanad.

### 1.2 JSON Multilingual — `name->fa` na `name`

- `Product.name/short_description/description` casts `array` (`Product.php:24-26`) — dar DB JSON `{fa:"...", en:"..."}` zakhire. Query **bayad** `where('name->fa','like',%search%)` bashad (`ProductService.php:106-107`, `Site/ProductController.php:51-52`) — `where('name','like',...)` kar nemikonad. Namayesh bayad `trans('name')` (`Product.php:253-261`) ya `$product->name['fa']` — na `$product->name` (array barmigardad). Hamin baraye `Brand.title`, `Specification.title`, `Property.title`, `ProductImage.alt_text`.
- **Dom:** Agar agent `Product::where('name','like','%گوشی%')` benevisad 0 natije barmigardad — bayad `name->fa` ya `name->en`.

### 1.3 Slug Auto-generation — `HasSlug` dar `prepareForValidation`

- `ProductRequest::prepareForValidation` (`ProductRequest.php:19-26`) agar `slug` khali va `name.fa` por bashad `prepareSlug(name.fa)` (`HasSlug.php:8` `Str::slug`) auto generate mikonad — **ghabl az validation**. `UrlRule` (`app/Rules/UrlRule.php:22-33`) uniqueness ra `where slug=value whereNull deleted_at where id != exceptId` check mikonad. Duplicate slug `422` barmigardad. Agent nabayad slug ra mostaghim `Str::slug` bedoon `UrlRule` check benevisad.
- **Talle:** `UrlRule` faghat `deleted_at null` ra check mikonad — slug-e mahsul-e softDeleted dobare ghabel-e estefade ast (amdan — chon softDelete).

### 1.4 `price/stock` nullable — Variant-driven

- `ProductRequest.php:49,51` `price/stock nullable` chon agar variant vojood darad ghimat/mojoodi az variant-ha `aggregateVariantPrices` (`ProductService.php:303-329`) jam mishavad. `ProductDTO::fromRequest` (`ProductDTO.php:48-50`) radif-haye variant-e `price<=0 && stock<=0 && empty(id)` ra hazf mikonad. Agar frontend `variants: []` befrestad vali `price: ""`, `price=0` zakhire mishavad — `final_price=0` va mahsul dar Site filter `min_price>0` namayesh dade nemishavad. Agent bayad validation-e frontend ra check konad — ya `price` required konad agar `variants` khali.
- **Baztolid:** Create product bedoon variant va bedoon price → `price=0, final_price=0` → dar Site `index` `where final_price >=1000000` hichgah namayesh dade nemishavad — bug-e khamush.

### 1.5 WebP Pipeline — Hargez `Storage::put` mostaghim be `uploads/products`

- `ImageHandler::handle` (`app/Helpers/ImageHandler.php:14-27`) → `ImageUploader::upload` (`ImageUploader.php:13-78`) file ra ba `md5(microtime()).ext` dar `public/<uploads>/products/` mizarad, be WebP tabdil (`imagewebp q90`), 3 resize `thumb/medium/large` ba `ImageResizer` cover-crop. Agar agent mostaghim `Storage::put('products/...', $file)` anjam dahad: 1) WebP nemishavad, 2) resize ha sakhte nemishavand, 3) `path` dar DB bedoon `.webp` extension zakhire mishavad va `ProductImage::url` broken. **Hatman** `ImageHandler` seda bezan.
- **Joziat-e tabdil:** `ImageUploader.php:32-48` — agar `ext===webp|svg|gif` tabdili anjam nemishavad. `ImageResizer.php:38` — `imagecreatefromwebp` — agar GD webp nadashte bashad silently `return` (resize ha sakhte nemishavand vali upload movafagh). `convertToWebP` agar GD nasb nabashad `Error` throw mikonad ke `catch \Throwable` (`ImageUploader.php:58`) migirad va `unlink` + `return null` — image save nemishavad vali transaction rollback nemishavad (file I/O birun az DB transaction).

### 1.6 `main_image` Dual Source — Column + Relation

- `Product.main_image` column (`Product.php:130-142` `getMainImageAttribute`) va `ProductImage.is_primary` relation har do `main image` ra negah midarand. `ProductService::setMainImage` (`ProductService.php:424-456`) har do ra sync: `images()->update is_primary false` → `target is_primary true` → `product update main_image=path`. Accessor fallback: agar column khali, `firstWhere is_primary` ya `first()->url`. Agar kasi mostaghim `Product::update(['main_image'=>'foo.webp'])` bedoon `setMainImage` anjam dahad desync: column por vali `is_primary` false. Hich DB constraint ya observer baraye sync.

### 1.7 SoftDeletes — `withTrashed` dar StockService

- `Product` + `ProductVariant` + `Specification` + `ProductCategory` + `Brand` softDeletes darand; `ProductImage`, `Property`, `ProductAssociation` hard delete. `StockService::getAvailableStock` (`StockService.php:66-76`) `withTrashed()->find` estefade mikonad — yani mahsul-e hazf shode hanuz stock barmigardad. `CartService::dbContent` (`CartService.php:47`) ham `Product::find` bedoon `withTrashed` — mahsul-e softDeleted dar cart `null` va `original_price` set nemishavad. Inconsistency.

### 1.8 Associations — `type` discriminator

- `Product.related` (`Product.php:108-112`) `wherePivot type=related`, `complement` (`Product.php:114-120`) `wherePivot type=complement`. `ProductService::syncAssociations` (`ProductService.php:353-368`) `type` ra set mikonad. Agar kasi `attach` ya `sync` bedoon `pivot type` anjam dahad (`$product->related()->attach($id)`), `type` null zakhire va dar query-e `related/complement` namayesh dade nemishavad (wherePivot filter). **Bayad** `associations()->create(['related_product_id'=>id,'type'=>...])` ya `attach` ba pivot data.

### 1.9 Stock — Variant vs Product (Do Manba-e Mojoodi)

- `StockService::getAvailableStock` (`StockService.php:66-76`): agar `variantId` → `variant.stock`, vagarna `product.stock`. `ProductService::aggregateVariantPrices` (`ProductService.php:309`) `product.stock = sum(variant stock)`. `StockService::decreaseVariantStock` (`StockService.php:95-140`) `variant decrement` + `sum` dobare. Agar mahsul variant darad, `product.stock` hamishe jam-e variant-ha ast — na mostaghel. Agent agar `Product::decrement('stock')` mostaghim ruye mahsul-e variant-dar anjam dahad, `aggregate` dar update-e badi baznevisi mikonad. **Hargez** stock-e mahsul-e variant-dar ra mostaghim dastkari nakon — ruye variant amal kon.

### 1.10 N+1 — CartService na ProductService

- `ProductService::getPaginatedProducts` eager `with[category,images,brand]` (`ProductService.php:100`) — N+1 nadarad. `Site/ProductController::index` ham `with[images,category,brand]` (`Site/ProductController.php:26`). Vali `CartService::dbContent` (`CartService.php:47-50`) per item `Product::find` + `variants()->find` — N+1. `Site/ProductController::show` `related` fallback `Product::where category take 7 get` (`Site/ProductController.php:152-156`) bedoon `with` — har related `->images` dar view N+1. Gotcha-e cross-module.

---

## 2. Missing Indexes & Performance (Index-haye Gomshode)

| Index | Vaziat | Asar |
|-------|--------|------|
| `products.slug` unique | ✅ hast — migration products | Site `where slug` + `UrlRule` |
| `products.final_price` | ✅ hast — `2026_08_04_000009:14` `index('final_price')` + backfill | Sort/filter-e ghimat |
| `product_variants.final_price` | ✅ hast — `2026_08_04_000004:23` `index('final_price')` | `aggregateVariantPrices` sort |
| `product_categories.parent_id` FK | ✅ FK index | `categoryIdsWithDescendants` loop |
| `products.product_category_id` FK | ✅ FK index | Filter-e category |
| `products.is_active` | ❓ **nadarad** — migration `is_active` index nadarad | `Site index where is_active true` + `ProductService where is_active` — full scan agar table bozorg |
| `products.is_featured / is_best_seller` | ❓ nadarad | `where is_featured true` — low selectivity, index kam asar |
| Composite `(is_active, final_price)` | ❌ **nadarad** — mohem | `Site index where is_active true orderBy final_price` — optimizer nemitavanad index scan estefade konad, filesort |
| Composite `(is_active, product_category_id)` | ❌ nadarad | `where is_active true whereIn product_category_id (...)` — category filter |
| `product_specification.product_id` | ✅ FK | `syncSpecifications where product_id` |
| `product_images.product_id` | ✅ FK | `images()->where product_id` |

**Tosiye:** `CREATE INDEX products_active_final_price ON products(is_active, final_price)` va `CREATE INDEX products_active_category ON products(is_active, product_category_id)` ezafe shavad.

---

## 3. Known Bugs (Bug-haye Shenakhte Shode — Doc Only, Fix Nist)

| # | Bug | Sharh | File:Line | Severity |
|---|-----|-------|-----------|----------|
| 1 | `discount_price > price` guard nadarad | `ProductRequest.php:50` faghat `min:0`, `prepareData` ham check nemikonad — final_price mitavanad az price bishtar shavad | `ProductRequest.php:50` + `ProductService.php:167-169` | Medium — ghimat-e eshtebah dar Site |
| 2 | `variants.*.spec_ids` parent/child consistency check nadarad | `spec_ids.* exists:specifications,id` vali check nemikonad child zir-e parent-e dorost bashad — variant mitavanad `spec_ids=[child az group A + child az group B]` dashte bashad ke motenaghez bashad | `ProductRequest.php:76` | Medium — variant-e eshtebah |
| 3 | `related_ids` self-reference mojaz | `related_ids.* exists:products,id` vali check nemikonad `!= product.id` — mahsul mitavanad khodash ra related/complement konad | `ProductRequest.php:89,91` | Low — loop dar Site show |
| 4 | `admin.products.show` route morde | `Route::resource('products')` 7 route misazad vali controller `show` nadarad — `GET admin/products/{product}` 500 Exception | `routes/admin.php:46` + `Admin/ProductController.php:246` (6 method) | Medium — 403 ham mishavad vali 500 ham |
| 5 | `delete_images` ownership check nadarad | `deleteImagesByIds` (`ProductService.php:404-418`) `whereIn id in ids` vali check nemikonad image mota'alegh be haman product bashad — agar `product->images()->whereIn` amn ast vali agar `ProductImage` global scope nadasht bayad check shavad (hala amn chon `product->images()` scope darad) | `ProductService.php:412` | Low — hala amn (scoped) |
| 6 | `main_image_id` validation `exists:product_images,id` nadarad | `ProductRequest.php:100` faghat `nullable|integer` — na `exists:product_images,id` — id-e na-motabar `setMainImage` `target null` → fallback be avalin tasvir | `ProductRequest.php:100` | Low — fallback amn |
| 7 | `activeFilterChips` label برaye sort-e jadid | Agar `sort=random` ersal shavad `sortLabels[sort] ?? sort` kham ra namayesh midahad — label tarjome nashode | `Admin/ProductController.php:72` | Low — UI |
| 8 | `formData` `wherePivotNull deleted_at` dar `specifications` | `Product.php:70` `wherePivotNull deleted_at` — agar spec softDeleted bashad, `selectedSpecs` on ra dar edit namayesh nemidahad — user fekr mikonad entekhab naparide vali dar sync dobare create mishavad | `Product.php:70` | Low — data loss-e zaheri |
| 9 | `ImageResizer` silent fail | `ImageResizer.php:38-41` agar `imagecreatefromwebp` fail → `return` (resize ha sakhte nemishavand) — hich log ya exception | `ImageResizer.php:38-41` | Low — tasvir-e resize nashode vali asli hast |
| 10 | `StockService` lockForUpdate darad vali `ProductService` nadarad | 2 admin hamzaman `update` konand — akharin neveshte barande (lost update) — hich `lockForUpdate` ya `optimistic lock` | `ProductService.php:54-80` vs `StockService.php:49,95` | Medium — concurrency |
| 11 | `ProductRequest` `text_specs.* max:500` vali DTO trim | `ProductRequest.php:70` `max:500` vali `DTO normalizeTextSpecs` (`ProductDTO.php:132`) `trim` — agar `value="   "` (3 space) validation pass vali DTO hazf — inconsistency khafif | `ProductDTO.php:132` | Low |
| 12 | `CartService` price az `final_price` vali `StockService` az `stock` | `CartService::dbAdd` (`CartService.php:92`) `final_price` baraye `price`, `StockService::getAvailableStock` `stock` — agar `stock` update nashode vali `final_price` update shode, cart ghimat-e jadid vali stock-e ghadim ra mibiند | `CartService.php:92` + `StockService.php:66` | Low |

---

## 4. Legacy & Deprecated (Mored-haye Legacy)

| Mored | Vaziat | Tozih |
|-------|--------|-------|
| `deal_ends_at / timer_active / start_timer / end_timer` | Legacy deal timer — `Site/ProductController.php:188-193` `timer_active && end_timer->isFuture() ? end_timer : deal_ends_at` — `deal_ends_at` va `timer_active/start/end_timer` 2 system-e joda — shayad yeki monqazi shavad | Migration `2026_08_04_000008_add_brand_and_timer` — hich deprecation notice |
| `product_specification.deleted_at` soft pivot | `ProductSpecification` SoftDeletes darad — `Product.php:70` `wherePivotNull deleted_at` — legacy chon `syncSpecifications` soft delete mikonad va dobare create — `deleted_at` baghi | Bayad ya hard delete ya `withTrashed` handling |
| `Product.specifications` vs `productSpecifications` | 2 relation-e joda: `specifications` (BelongsToMany pivot ba wherePivotNull) va `productSpecifications` (HasMany ba children) — اولي baraye namayesh, dovomi baraye panel — confusion | `Product.php:64-90` |
| `RecentlyViewedService` session max 20 | Session-based, singleton `AppServiceProvider` — na product module vali `Site show` seda mizanad — agar session driver `array` dar test, save nemishavad | `Site/ProductController.php:161` |

---

## 5. Checklist-e AI (Ghabl az Taghir-e Products)

- [ ] **final_price na price** — hameye query/filter/sort ba `final_price` — `price` faghat asli
- [ ] **JSON name** — `name->fa` dar query, `trans('name')` dar view — na `name` sade
- [ ] **Slug auto** — `HasSlug` ghabl az validation — `UrlRule` uniqueness ba `deleted_at null`
- [ ] **Variant vs non-variant price branch** — agar variants hast price/stock mitavanad 0 bashad — DTO filter
- [ ] **ImageHandler na Storage::put** — hatman `ImageHandler::handle/delete` — WebP + 3 resize
- [ ] **is_active guard** — Site faghat `where is_active true` + `abort_unless` — Admin hame
- [ ] **related/complement type** — pivot `type=related/complement` — attach bedoon type bug
- [ ] **Stock variant vs product** — variant-dar → stock az variant sum — direct product decrement nakon
- [ ] **Transaction** — `store/update/destroy` dar `DB::transaction` — file leak dar exception
- [ ] **admin.products.show morde** — `GET admin/products/{product}` 500 — test nakon, fix: `except('show')`
- [ ] **Throttle review nadarad** — spam mitigation nadarad — ezafe kon agar niaz
- [ ] **Locale duplicate** — Site route 2 bar sabt (canonical + locale) — hich caching-e query
- [ ] **UNKNOWN baraye reorder** — products reorder nadarad — `brands/hero-slides/trust-badges` reorder darand

---

## 6. Evidence

| Claim | File:Line |
|-------|-----------|
| final_price ternary + accessor | `app/Services/ProductService.php:167-169` + `app/Models/Product.php:195-204` |
| JSON casts + trans + name->fa search | `app/Models/Product.php:24-26,253-261` + `ProductService.php:106-107` + `Site/ProductController.php:51-52` |
| Slug auto HasSlug + UrlRule | `app/Http/Requests/Admin/ProductRequest.php:19-26` + `app/Traits/HasSlug.php:8` + `app/Rules/UrlRule.php:22-33` |
| price/stock nullable + DTO variant skip | `app/Http/Requests/Admin/ProductRequest.php:49,51` + `app/DTOs/ProductDTO.php:48-50` |
| WebP + 3 resize | `app/Helpers/ImageHandler.php:14-27` + `ImageUploader.php:13-78` + `ImageResizer.php:14-68` + `ProductService.php:19-23,385,415` |
| main_image dual source + setMainImage | `app/Models/Product.php:130-161` + `app/Services/ProductService.php:424-456` |
| SoftDeletes + withTrashed | `app/Models/Product.php:14` + `app/Services/StockService.php:66-76` |
| Associations type discriminator | `app/Models/Product.php:108-120` + `app/Services/ProductService.php:358-367` |
| N+1 ProductService eager ok, Cart N+1 | `app/Services/ProductService.php:100` + `app/Services/CartService.php:47` |
| Missing composite indexes | `database/migrations/2026_08_04_000009:14` (final_price index hast, composite nadarad) |
| admin.products.show morde + permissions | `routes/admin.php:46` + `config/permissions.php:products` (6 access, no show) + `php artisan route:list` A4 |
| No throttle reviews | `routes/site.php:49-55` (no throttle) vs `routes/site.php:51` contact throttle:5,1 |
| discount_price>price no guard | `app/Http/Requests/Admin/ProductRequest.php:50` `min:0` only |
| StockService lockForUpdate | `app/Services/StockService.php:49,95,105,120,148` |

