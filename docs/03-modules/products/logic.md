# Products — Logic (Deep Trace)

> **Module:** `products` — Catalog-e asli-e Joobjoob
> **Stack:** `Product` + `ProductCategory` + `Brand` + `Specification` + `ProductVariant` + `ProductImage` + `Property` + `ProductAssociation` + `RecentlyViewedService` + `StockService` + `TrustBadgeService`
> **Tag:** `08d468f` or later — trace-e mostaghim az code, bedoon hads

---

## 1. Purpose (Hadaf-e Module)

Products module masoul-e catalog-e markazi-e Joobjoob ast: CRUD-e mahsoul, modiriat-e tasavir (WebP + 3 size resize), modiriat-e moshakhasat-e entekhabi (`select`) va matni (`text`), variant-ha (SKU ghabel-e entekhab ba ghimat/mojoodi-e joda), vizhegi-haye azad (`Property` ba `title/value` JSON), va ertebatat-e `related/complement` ast. Hadaf-e asli joda kardan-e logic-e form va filter az View va Service ast — `Admin/ProductController::formData` va `activeFilterChips` mesal-e roshan-e in jodasazi hastand. Samt-e Site niz listing, quickView, category page, product detail ba derakht-e nazarat va moshakhasat-e variant-driven ra poshesh midahad.

**Evidence:**
- `app/Models/Product.php:12` — `class Product extends Model { use HasFactory, SoftDeletes }`
- `app/Services/ProductService.php:15` — `class ProductService { const UPLOAD_FOLDER='products', IMAGE_SIZES=[thumb 300x300, medium 800x800, large 1200x1200] }`
- `app/DTOs/ProductDTO.php:8` — `readonly class ProductDTO { 26 fields }`
- `app/Http/Controllers/Admin/ProductController.php:16` — `__construct(protected ProductService $productService)`

---

## 2. Controllers (Controller-ha)

### 2.1 Admin/ProductController — `app/Http/Controllers/Admin/ProductController.php`

Controller-e Admin tamame CRUD + listing-e filter-dar + sakht-e data-e form ra bar ohde darad. DI az `ProductService` (`:18` constructor promotion). Hich authorization-e dakheli nadarad — hameye route-ha zir-e `auth, adminPermission, log.admin` gharar darand (route file).

#### 2.1.1 `index(Request $request): View` — `ProductController.php:20-34`

```php
$categories = ProductCategory::where('is_active', true)->get();           // :23
$brands     = Brand::where('active', true)->orderBy('id')->get();         // :24
$products   = $this->productService->getPaginatedProducts($request->all());// :27
$products->appends($request->query());                                     // :28
$activeFilters = $this->activeFilterChips($request, $categories, $brands);// :31
return view('admin.products.index', compact('products','categories','brands','activeFilters')); // :33
```

- Filter-ha be `ProductService::getPaginatedProducts` delegat mishavand — controller hich `where` mostaghim neminevisad.
- `appends($request->query())` pagination link-ha ra ba query string hefz mikonad.
- `activeFilters` ghabl az ersal be view sakhte mishavad ta logic-e chip az Blade kharej bamanad.

#### 2.1.2 `activeFilterChips(Request $request, $categories, $brands): array` — `ProductController.php:41-91`

Private helper ke **8 filter-e fa'al** ra be chip-e ghabel-e hazf tabdil mikonad. Har chip `['label'=>string, 'url'=>string]` ast ke `url` ba `route('admin.products.index', $request->except(['page', <filter>]))` sakhte mishavad.

| # | Filter key | Shart-e fa'al | Label logic | Hazf-e param |
|---|------------|---------------|-------------|--------------|
| 1 | `search` | `$request->filled('search')` `:57` | `__('admin.common.search').': «'.$request->search.'»'` `:58` | `except(['page','search'])` |
| 2 | `category` | `filled('category')` `:60` | `$categories->firstWhere('id',(int)$request->category)?->trans('name') ?? __('admin.products.no_category')` `:61` | `except(['page','category'])` |
| 3 | `brand_id` | `filled('brand_id')` `:64` | `$brands->firstWhere('id',(int)$request->brand_id)?->trans('title') ?? __('admin.products.no_brand')` `:65` | `except(['page','brand_id'])` |
| 4 | `status` | `$request->has('status') && $request->status !== ''` `:68` | `status=='1' ? active : inactive` `:69` | `except(['page','status'])` |
| 5 | `sort` | `filled('sort') && sort !== 'newest'` `:71` | `$sortLabels[$request->sort] ?? $request->sort` ba map-e 6 ghaz `:43-50` | `except(['page','sort'])` |
| 6 | `availability` | `filled('availability')` `:74` | `$availabilityLabels[$request->availability] ?? availability` `:75` | `except(['page','availability'])` |
| 7 | `price_min` / `price_max` | `filled('price_min')` `:77` / `filled('price_max')` `:80` | `number_format((int)$request->price_min/max)` `:78,81` | `except(['page','price_min/max'])` |
| 8a | `featured` | `filled('featured')` `:83` | `__('admin.products.only_featured')` `:84` | `except(['page','featured'])` |
| 8b | `best_seller` | `filled('best_seller')` `:86` | `__('admin.products.only_best')` `:87` | `except(['page','best_seller'])` |

Sort labels-e kamel (`:43-50`): `newest/oldest/price_asc/price_desc/name_asc/name_desc`. Availability labels (`:51-54`): `in_stock/out_of_stock`.

#### 2.1.3 `create(): View` — `ProductController.php:93-109`

```php
$categories = ProductCategory::where('is_active', true)->orderByDesc('id')->get(); // :95
$brands     = Brand::where('active', true)->orderBy('id')->get();                // :96
$specifications = Specification::whereNull('parent_id')->where('active',true)->with('children')->orderBy('id')->get(); // :99
$textSpecifications = Specification::where('type','text')->whereNull('parent_id')->where('active',true)->orderBy('id')->get(); // :100
$products = Product::where('is_active',true)->orderBy('id')->get(['id','name','slug']); // :103
return view('admin.products.create', array_merge(compact(...), $this->formData(null))); // :105-108
```

Nokte: `specifications` faghat gorouh-haye parent (`parent_id IS NULL`) + `children` eager ast — baraye variant picker. `textSpecifications` faghat `type='text'`.

#### 2.1.4 `store(ProductRequest $request)` — `ProductController.php:111-121`

```php
$productDTO = ProductDTO::fromRequest($request); // :114
$this->productService->store($productDTO);       // :117
return redirect()->route('admin.products.index')->with('success', __('admin.products.created')); // :119
```

Hich logic-e ezafi — hame chiz dar DTO + Service.

#### 2.1.5 `edit(Product $product): View` — `ProductController.php:123-148`

Eager load-e kamel ghabl az `formData`:

```php
$product->load(['images','brand','productSpecifications.specification',
  'productSpecifications.children.specification','variants.specifications',
  'properties','related','complement']); // :125-134
```

Bad az an `categories/brands/specifications/textSpecifications/products` mesle `create` + `$this->formData($product)` (`:144-147`).

#### 2.1.6 `formData(?Product $product): array` — `ProductController.php:156-227`

Bozorgtarin helper-e controller — hameye data-e form ra az View joda mikonad. Return type-e daghigh dar PHPDoc `:154`.

**`$oldVal` Closure (`:161-182`):**
- Aval `old($key) !== null` check mikonad — agar validation fail shode, meghdar-e ghabli-e form bargardande mishavad.
- Agar `isEdit && $product`, ba `explode('.', $key)` ruye object/array traverse mikonad (`$value->$k` ya `$value[$k]`).
- Dar gheir in surat `default` (pishfarz `''`) barmigardad.

**4 sakhtar-e asli dar halat-e edit (`:191-224`):**

1. **`$selectedSpecs` (`:192-201`):** Har `ProductSpecification` ke `value===null && parent_id===null` (radif-e parent) ast, `children->pluck('specification_id')` be onvane meghdar-e entekhabi zakhire mishavad. Mapped ba `$specification_id` parent.
2. **`$textSpecValues` (`:198-200`):** Har `ProductSpecification` ke `value !== null` darad, be `$textSpecValues[specification_id]=value` mirizad.
3. **`$existingVariants` (`:203-212`):** Har `Variant` ba `id, spec_ids (sorted int[]), price, discount_price, stock, weight` tabdil mishavad.
4. **`$existingProperties` (`:214-220`):** Har `Property` ba `id, title(trans('title')), value` tabdil mishavad.
5. **`$relatedIds / $complementIds` (`:222-223`):** `pluck('id')->map(int)->all()` az relation-haye `related/complement`.

Return: `compact('isEdit','oldVal','selectedSpecs','textSpecValues','existingVariants','existingProperties','relatedIds','complementIds')` (`:226`).

#### 2.1.7 `update(ProductRequest $request, Product $product)` — `ProductController.php:229-237`

```php
$productDTO = ProductDTO::fromRequest($request); // :231
$this->productService->update($product, $productDTO); // :233
return redirect()->route('admin.products.index')->with('success', __('admin.products.updated')); // :235-236
```

#### 2.1.8 `destroy(Product $product)` — `ProductController.php:239-245`

```php
$this->productService->destroy($product); // :241
return redirect()->route('admin.products.index')->with('success', __('admin.products.deleted')); // :243-244
```

---

### 2.2 Site/ProductController — `app/Http/Controllers/Site/ProductController.php`

DI: `RecentlyViewedService` + `TrustBadgeService` readonly (`:19-22`). 7 action-e asli.

#### 2.2.1 `index(Request $request)` — `Site/ProductController.php:24-82`

Query-e mahsulate fa'al ba 5 filter + sort:

```php
$query = Product::with(['images','category','brand'])->where('is_active', true); // :26
```

- **Filter category (`:29-31`):** `$slugs = is_array($request->category) ? ...` → `whereIn('product_category_id', categoryIdsWithDescendants($slugs))`. Shamel-e hameye zireshakhe-ha.
- **Filter price (`:35-40`):** `where('final_price','>=', (int)min_price)` va `where('final_price','<=', (int)max_price)` — bar asas-e `final_price` na `price`.
- **Filter brand (`:43-45`):** `whereHas('brand', fn($q)=>$q->where('url', $request->brand))` — bar asas-e `slug/url` na `id`.
- **Search (`:48-54`):** `where('name->fa','like',%q%) orWhere('name->en','like',%q%)` — JSON multilingual search.
- **Sort match (`:57-63`):** `price_asc → final_price asc`, `price_desc → final_price desc`, `newest → created_at desc`, `best_selling → is_best_seller desc + id desc`, `default → stock desc + final_price desc + id desc`.
- Pagination `12` (`:65`), `withQueryString()` hefz-e filter dar pagination.
- Ezafi: `ProductCategory::where('is_active',true)->withCount('products')` (`:68-70`), `Brand::where('active',true)` (`:73`), `$filters = $request->except(['sort','page'])` (`:76`).

#### 2.2.2 `categoryIdsWithDescendants(array $slugs): array` — `Site/ProductController.php:88-99`

Iterative BFS-e descent: `whereIn('slug',$slugs)->pluck('id')` → loop `whereIn('parent_id',$found)->pluck('id')` ta hameye sat-he omgh jamavari shavad (`:93-96`), `array_unique` (`:98`). Baraye `index` va `category` estefade mishavad.

#### 2.2.3 `quickView(Request $request)` — `Site/ProductController.php:104-111`

```php
$product = Product::where('slug', $request->route('product'))->firstOrFail(); // :106
$product->load(['images','category','brand','reviews']); // :108
return view('site.products.partials.quick-view', compact('product')); // :110
```

Faghat partial render — dakhel-e modal-e sarasari gharar migirad.

#### 2.2.4 `category(Request $request)` — `Site/ProductController.php:116-130`

```php
$category = ProductCategory::where('slug', $request->route('category'))->firstOrFail(); // :118
abort_unless($category->is_active, 404); // :120
$category->load('parent'); // :122
$products = Product::with(['images','category','brand'])->where('is_active',true)
  ->whereIn('product_category_id', categoryIdsWithDescendants([$category->slug]))->paginate(12); // :124-127
return view('site.product-categories.show', compact('category','products')); // :129
```

#### 2.2.5 `show(Request $request)` — `Site/ProductController.php:132-200`

Bozorgtarin action-e site — 3 bakhsh:

**Load (`:134-147`):**
```php
$product = Product::where('slug', $request->route('product'))->firstOrFail(); // :134
$product->load(['images','category','brand','reviews.user',
  'productSpecifications.children.specification','productSpecifications.specification',
  'variants.specifications.parent','properties','related.images','complement.images']); // :136-147
```

**Related fallback (`:150-158`):**
Agar `related` manual khali bashad, 7 mahsul-e ham-daste az `product_category_id` jaygozin mishavad.

**Recently viewed (`:161`):** `$this->recentlyViewed->add($product)` — session max 20.

**Review tree (`:164-179`):** `reviews()->with(['user','votes'])->approved()->oldest()->get()` + sakht-e derakht dar `buildReviewTree` + `ReviewVote where user_id=auth()->id()` baraye `userVotes` map.

**prepareShowData (`:182-185`):** `$viewData = $this->prepareShowData($product)` — hameye data-e namayeshi.

**TrustBadges (`:185`):** `$this->trustBadgeService->getActiveForSite(4)`.

**Wishlist check (VERIFIED ADDITION):** `$inWishlist = app(\App\Services\WishlistService::class)->has($product)` — computed and passed to the view (shows heart state on PDP).

**Deal timer (`:188-193`):** `timer_active && end_timer->isFuture() ? dealEndsAt = end_timer : dealEndsAt = deal_ends_at->isFuture()`.

Return `view('site.products.show', array_merge(compact('product','related','complement','reviewTree','trustBadges','dealEndsAt'), $viewData, ['userVotes'=>...]))` (`:195-199`).

#### 2.2.6 `buildReviewTree($allReviews): array` — `Site/ProductController.php:205-224`

2-pass algorithm: pass 1 `replies=collect()` + map ba `id`; pass 2 agar `parent_id` dar map hast → `replies->push()`, vagarna → `tree[]`. Khoroji `array` az review-haye root ba `replies` collection-e nested.

#### 2.2.7 `prepareShowData(Product $product): array` — `Site/ProductController.php:233-307`

4 khoroji:

1. **`mainImage` (`:235`):** `$product->main_image_url ?? asset('assets/admin/images/image-not-found.png')`.

2. **`specGroups` (`:238-245`):** Har `productSpecifications` ke `value===null && parent_id===null && specification` darad → `group id => ['title'=>trans('title'), 'values'=>children map trans('title') implode '، ']`.

3. **`textValues` (`:248-253`):** Har `ps` ke `value !== null` → `['title'=>trans('title'), 'value'=>value]`.

4. **`mainSpecs + variantsData + minVariant` (`:257-304`):**
   - `mainSpecs`: group ba `id, title, is_color, children[ id, title, color_code, variant_ids[] ]` — baraye variant picker-e JS.
   - `variantsData`: collection map `id, spec_ids(sorted int[]), price, discount_price, final_price, stock, percent`.
   - `minVariant`: `where stock>0 && final_price>0 sorted by final_price first`.

#### 2.2.8 `storeReview / storeReply / voteReview` — `Site/ProductController.php:312-479`

- **storeReview (`:312-374`):** validation via **`StoreReviewRequest` FormRequest** (NOT inline — corrected): `rating 1-5 + comment 3-1000 + guest_name/phone agar guest`, create ba `is_approved=false`, notification be admin via `NotificationService::notifyAdmins('review.new', ...)` dar try/catch (`:358-368`), redirect ba fragment `tab-reviews`.
- **storeReply (`:379-425`):** validation via **`StoreReviewReplyRequest` FormRequest** (corrected); `parent_id=review->id`, `rating=review->rating` (pasokh emtiaz nadarad), `is_approved=STATUS_PENDING`.
- **voteReview (`:430-479`):** `type in:like,dislike` validation, toggle logic: agar `existingVote.type === validated type → delete (removed)`, agar motefavet → `update`, vagarna → `create`. Agar `expectsJson()` → JSON ba `likes/dislikes/user_vote` (`:462-472`).

---

## 3. Services (Service-ha)

### 3.1 ProductService — `app/Services/ProductService.php`

Markazi-tarin service — hameye write-ha transaction-dar, hameye read-ha filter-dar. Constants: `UPLOAD_FOLDER='products'` (`:16`), `IMAGE_SIZES=[thumb 300x300, medium 800x800, large 1200x1200]` (`:19-23`).

#### 3.1.1 `store(ProductDTO $dto): Product` — `ProductService.php:28-49`

```php
return DB::transaction(function() use ($dto) {           // :30
  $data = $this->prepareData($dto);                      // :31
  $product = Product::create($data);                     // :33
  $newImages = $this->appendImages($product, $dto->getImages()); // :36
  $this->setMainImage($product, $dto->getMainImageId(), $dto->getMainNewIndex(), $newImages); // :39
  $this->syncSpecifications($product, $dto);             // :42
  $this->syncVariants($product, $dto);                   // :43
  $this->syncProperties($product, $dto);                 // :44
  $this->syncAssociations($product, $dto);               // :45
  return $product;                                       // :47
}); // :48
```

Tartib mohem: `prepareData → create → appendImages → setMainImage → syncSpecifications → syncVariants (shamel aggregate) → syncProperties → syncAssociations`. Agar har marhale exception, transaction rollback.

#### 3.1.2 `update(Product $product, ProductDTO $dto): bool` — `ProductService.php:54-80`

```php
return DB::transaction(function() use ($product,$dto) { // :56
  $data = $this->prepareData($dto);                     // :57
  $product->update($data);                              // :59
  if (!empty($dto->getDeleteImages()))                  // :62
    $this->deleteImagesByIds($product, $dto->getDeleteImages()); // :63
  $newImages = $this->appendImages($product, $dto->getImages()); // :67
  $this->setMainImage($product, ...);                   // :70
  $this->syncSpecifications($product,$dto);             // :73
  $this->syncVariants($product,$dto);                   // :74
  $this->syncProperties($product,$dto);                 // :75
  $this->syncAssociations($product,$dto);               // :76
  return true;                                          // :78
}); // :79
```

Tafavot ba store: `deleteImagesByIds` ghabl az `appendImages` — hazf-e entekhabi-e tasavir-e ghabli.

#### 3.1.3 `destroy(Product $product): bool` — `ProductService.php:85-92`

```php
return DB::transaction(function() use ($product) { // :87
  $this->deleteOldImages($product);                // :88 — hazf-e fiziki-e hame tasavir
  return $product->delete();                       // :90 — softDelete
});
```

#### 3.1.4 `getPaginatedProducts(array $filters, int $perPage=15)` — `ProductService.php:97-160`

Eager: `with(['category','images','brand'])` (`:100`) — jelogiri az N+1.

**Filters (8 gorouh):**

| Filter | Code | Joziat |
|--------|------|--------|
| `search` | `:103-112` | `where('name->fa' like %search% OR 'name->en' like %search% OR sku like % OR slug like % OR whereHas brand title->fa like %)` |
| `category` | `:115` | `when(!empty(filters['category']), where('product_category_id', category))` — exact match (bar aks-e Site ke descendants ra shamel mishavad) |
| `brand_id` | `:118` | `when(!empty(brand_id), where('brand_id', brand_id))` |
| `status` | `:121-122` | `when(status==='1', where is_active true)` + `when(status==='0', where is_active false)` — string compare |
| `availability` | `:125-126` | `when('in_stock', where stock>0)` + `when('out_of_stock', where stock<=0)` |
| `price_min/max` | `:129-130` | `when(isset && !=='', where final_price >= min / <= max)` — bar asas-e `final_price` |
| `featured` | `:133` | `when(!empty(featured), where is_featured true)` |
| `best_seller` | `:134` | `when(!empty(best_seller), where is_best_seller true)` |

**Sort (`:137-157`):**

```php
$sort = $filters['sort'] ?? 'newest'; // :137
switch($sort) {
  case 'price_asc':  orderBy('final_price','asc'); break;  // :139-140
  case 'price_desc': orderBy('final_price','desc'); break; // :142-143
  case 'name_asc':   orderBy('name->en'); break;           // :145-146
  case 'name_desc':  orderByDesc('name->en'); break;       // :148-149
  case 'oldest':     orderBy('created_at','asc'); break;   // :151-152
  default:           latest(); break;                       // :154-155 — newest
}
return $query->paginate($perPage); // :159
```

Khoroji `LengthAwarePaginator` ba 15 item per page (default).

#### 3.1.5 `prepareData(ProductDTO $dto): array` — `ProductService.php:165-192`

```php
$finalPrice = $dto->getDiscountPrice() && $dto->getDiscountPrice()>0
  ? $dto->getDiscountPrice() : $dto->getPrice(); // :167-169
return [
  'product_category_id'=> $dto->getProductCategoryId(), // :172
  'brand_id'           => $dto->getBrandId(),           // :173
  'name'               => $dto->getName(),              // :174 — array fa/en
  'slug'               => $dto->getSlug(),              // :175
  'short_description'  => $dto->getShortDescription(),  // :176 — array
  'description'        => $dto->getDescription(),       // :177 — array
  'sku'                => $dto->getSku(),               // :178
  'price'              => $dto->getPrice(),             // :179
  'discount_price'     => $dto->getDiscountPrice(),     // :180
  'final_price'        => $finalPrice,                  // :181 — computed
  'stock'              => $dto->getStock(),             // :182
  'weight'             => $dto->getWeight(),            // :183
  'is_active'          => $dto->getIsActive(),          // :184
  'is_featured'        => $dto->getIsFeatured(),        // :185
  'is_best_seller'     => $dto->getIsBestSeller(),      // :186
  'deal_ends_at'       => $dto->getDealEndsAt(),        // :187
  'timer_active'       => $dto->getTimerActive(),       // :188
  'start_timer'        => $dto->getStartTimer(),        // :189
  'end_timer'          => $dto->getEndTimer(),          // :190
];
```

Logic-e `final_price`: agar `discount_price>0` → `final_price=discount_price` vagarna `price`. Hich check-e `discount_price<price` vojood nadarad — gotcha.

#### 3.1.6 `syncSpecifications(Product $product, ProductDTO $dto)` — `ProductService.php:203-253`

```php
ProductSpecification::where('product_id',$product->id)->delete(); // :206 — soft delete hameye ghabli
$rows = [];
foreach($dto->getSpecs() as $parentId=>$childIds)   // :211 — [parent_id => [child_id,...]]
  $rows[] = ['specification_id'=>$parentId,'value'=>null,'parent_id'=>null]; // :212-216
foreach($dto->getTextSpecs() as $specId=>$value)     // :220 — [spec_id => value]
  $rows[] = ['specification_id'=>$specId,'value'=>$value,'parent_id'=>null]; // :221-225
$parentRows = [];
foreach($rows as $row) {
  $ps = ProductSpecification::create(array_merge($row,['product_id'=>$product->id])); // :231
  if($row['value']===null) $parentRows[$row['specification_id']] = $ps->id; // :232-233
}
foreach($dto->getSpecs() as $parentId=>$childIds) { // :238 — radif-e farzand
  $parentRowId = $parentRows[$parentId] ?? null;    // :239
  foreach($childIds as $childId)
    ProductSpecification::create(['product_id'=>$product->id,'specification_id'=>$childId,'value'=>null,'parent_id'=>$parentRowId]); // :245-250
}
```

Model-e 2-sat-he: radif-e parent (`specification_id=parent,val null,parent_id null`) + radif-e farzand (`specification_id=child,val null,parent_id=parentRowId`). Text specs faghat radif-e mostaghel ba `value=text`.

#### 3.1.7 `syncVariants(Product $product, ProductDTO $dto)` — `ProductService.php:264-297`

```php
$submitted = $dto->getVariants(); // :266
$submittedIds = array_filter(array_column($submitted,'id')); // :269
$variantsQuery = $product->variants(); // :270
if($submittedIds) $variantsQuery->whereNotIn('id',$submittedIds); // :271-272
$variantsQuery->get()->each->delete(); // :274 — soft delete variant-haye hazf shode

foreach($submitted as $variantData) {
  $finalPrice = !empty($variantData['discount_price']) && $variantData['discount_price']>0
    ? $variantData['discount_price'] : $variantData['price']; // :277-279
  $variant = ProductVariant::updateOrCreate(
    ['id'=>$variantData['id'] ?? null,'product_id'=>$product->id], // :281-282
    ['price'=>$variantData['price'],'discounted_price'=>$variantData['discount_price'],
     'final_price'=>$finalPrice,'stock'=>$variantData['stock'],'weight'=>$variantData['weight'] ?:100] // :283-289
  );
  $variant->specifications()->sync($variantData['spec_ids']); // :292 — pivot sync
}
$this->aggregateVariantPrices($product); // :296
```

Nokte: `updateOrCreate` ba `id` + `product_id` — agar `id` null bashad create, vagarna update. `sync` hameye spec pivot-haye variant ra jaygozin mikonad.

#### 3.1.8 `aggregateVariantPrices(Product $product)` — `ProductService.php:303-329`

```php
if(!$product->variants()->exists()) return; // :305-307
$sumStock = $product->variants()->sum('stock'); // :309
$minVariant = $product->variants()->where('stock','>',0)->where('final_price','>',0)
  ->orderBy('final_price','asc')->first(); // :311-315
if($minVariant) {
  $product->update(['price'=>$minVariant->price,'discounted_price'=>$minVariant->discounted_price,
    'final_price'=>$minVariant->final_price,'stock'=>$sumStock]); // :318-322
} else {
  $product->update(['stock'=>$sumStock]); // :327 — faghat stock jam, ghimat-e ghabli hefz
}
```

Qaede (VERIFIED): `stock` product = jam-e stock hameye variant-ha — **stock dar HAR DO shakhe update mishavad** (ba minVariant hamrah-e price, bedoone minVariant tanha). `price/final_price` = arzan-tarin variant-e mojood (`stock>0 && final_price>0`) — faghat ba vojood-e `minVariant` update mishavad; vagarna ghimat-e ghabli hefz mishavad.

#### 3.1.9 `syncProperties` / `syncAssociations` — `ProductService.php:335-368`

```php
// syncProperties :337-346
Property::where('product_id',$product->id)->delete(); // :337 — hard delete
foreach($dto->getProperties() as $index=>$property)
  Property::create(['product_id'=>$product->id,'title'=>$property['title'],'value'=>$property['value'],'sort_order'=>$index]); // :340-345

// syncAssociations :353-368
$product->associations()->delete(); // :355
foreach($dto->getRelatedIds() as $id) $associations[] = ['related_product_id'=>$id,'type'=>'related']; // :358-359
foreach($dto->getComplementIds() as $id) $associations[] = ['related_product_id'=>$id,'type'=>'complement']; // :361-362
foreach($associations as $assoc) $product->associations()->create($assoc); // :365-367
```

`Property.title` JSON fa/en, `sort_order` tartib-e vorood. `ProductAssociation` pivot ba `type` discriminator.

#### 3.1.10 Image pipeline — `ProductService.php:379-467`

**`appendImages` (`:379-399`):**
```php
$startSort = (int)$product->images()->max('sort_order'); // :382
foreach($uploadedFiles as $index=>$file) {
  $imagePath = ImageHandler::handle(self::UPLOAD_FOLDER,$file,self::IMAGE_SIZES); // :385 — WebP + resize
  if(!$imagePath) continue; // :387-389
  $created[$index] = $product->images()->create(['path'=>$imagePath,'is_primary'=>false,'sort_order'=>$startSort+$index+1]); // :391-395
}
return $created; // :398 — indexed ba index file ersali
```

`ImageHandler::handle` (`app/Helpers/ImageHandler.php:14-27`): `ImageUploader::upload` → `ImageResizer::resize` ba `cover-crop`. `ImageUploader` (`app/Helpers/ImageUploader.php:13-78`): file ra ba `md5(microtime()).ext` dar `public/<uploads>/products/` mizarad, sepas be WebP tabdil (`imagewebp` quality 90), agar `webp/svg/gif` bashad tabdili anjam nemishavad. `ImageResizer` (`app/Helpers/ImageResizer.php:14-68`): `imagecreatefromwebp` → `cover-crop` ba `imagecopyresampled` baraye har size dar zirpush-e `thumb/medium/large/`.

**`deleteImagesByIds` (`:404-418`):** `whereIn('id',$ids)->get()` → har image `ImageHandler::delete(folder, path, sizes)` (`:415`) ke file asli + 3 resize ra `unlink` mikonad → `$image->delete()`.

**`setMainImage` (`:424-456`):**
Olaviat: 1) `mainImageId` az tasavir-e mojood (`:429-431`), 2) `mainNewIndex` az tasavir-e jadid (`:434-436`), 3) avalin tasvir-e gallery (`:440`). Agar target peyda shavad: `images()->update(['is_primary'=>false])` → `target->update(['is_primary'=>true])` (`:451-452`) → `product->update(['main_image'=>$target->path])` (`:455`). Agar hich tasviri nabashad: `product->update(['main_image'=>null])` (`:445`).

**`deleteOldImages` (`:461-467`):** Baraye `destroy` — loop hameye images → `ImageHandler::delete` + `delete()`.

### 3.2 StockService — `app/Services/StockService.php`

Service-e mostaghel-e mojoodi — baraye Product/Variant stock check va reserve/restore dar order lifecycle.

- `getAvailableStock(int $productId, ?int $variantId): int` (`:66-76`): agar `variantId` → `ProductVariant::withTrashed()->find(variantId)->stock`, vagarna `Product::withTrashed()->find(productId)->stock`, `max(0, stock)`.
- `validateCartItems(array $cartItems): array` (`:38-64`): har item `getAvailableStock` → tafkik be `unavailable (stock<=0)` va `insufficient (stock<requestedQty)`.
- `reserveStock / restoreStock` (`:12-37`): loop `items` (OrderItem) → `decreaseItemStock / increaseItemStock` dakhel-e `DB::transaction`. `decreaseVariantStock` (`:95-140`) ba `lockForUpdate`, `decrement` + jam-e `sum(stock)` ruye product + `InventoryTransaction create (type subtract)` + `AuditService::productStockChanged`. `decreaseProductStock` (`:49-73`) moshabeh vali mostaghim ruye product.

---

## 4. DTO — `app/DTOs/ProductDTO.php`

### 4.1 Sاختار — `ProductDTO.php:8-39`

`readonly class ProductDTO` ba 26 field-e private dar constructor:

```php
productCategoryId:int, brandId:?int, name:array, slug:string, shortDescription:?array, description:?array,
sku:?string, price:int, discountPrice:?int, stock:int, weight:?int,
isActive:bool, isFeatured:bool, isBestSeller:bool,
dealEndsAt:?string, startTimer:?string, endTimer:?string, timerActive:bool,
specs:array, textSpecs:array, variants:array, properties:array,
relatedIds:array, complementIds:array, images:array, deleteImages:array,
mainImageId:?int, mainNewIndex:?int
```

26 getter-e `get*()` dar `:142-281`.

### 4.2 `fromRequest(ProductRequest $request): self` — `ProductDTO.php:41-107`

Bozorgtarin logic-e DTO — filtering va normalization ghabl az Service:

**Variant filtering (`:43-60`):**
```php
foreach((array)$request->input('variants',[]) as $v) {
  $price = (int)($v['price'] ?? 0);
  $stock = (int)($v['stock'] ?? 0);
  if($price<=0 && $stock<=0 && empty($v['id'])) continue; // :48-50 — radif-e khali bedoon id hazf
  $variants[] = [
    'id'=> !empty($v['id']) ? (int)$v['id'] : null, // :53
    'spec_ids'=> array_filter(array_map('intval',(array)($v['spec_ids'] ?? []))), // :54
    'price'=>$price, 'discount_price'=>(int)($v['discount_price'] ?? 0), // :55-56
    'stock'=>$stock, 'weight'=>(int)($v['weight'] ?? 100), // :57-58
  ];
}
```

Hadaf: radif-haye khali-e JS (bedoon price/stock/id) be Service naresand.

**Property filtering (`:62-75`):**
```php
$title = trim((string)($p['title'] ?? ''));
$value = trim((string)($p['value'] ?? ''));
if($value==='' || $title==='') continue; // :66-68
$properties[] = ['id'=>..., 'title'=>['fa'=>$title,'en'=>$title], 'value'=>$value]; // :70-74
```

`title` be JSON fa/en tabdil mishavad (yek meghdar baraye har do zaban).

**Specs/TextSpecs (`:96-97`):** `self::normalizeSpecs($request->input('specs',[]))` va `normalizeTextSpecs`.

**Related/Complement/Images (`:100-105`):** `array_filter(array_map('intval',...))` baraye `related_ids/complement_ids`, `request->file('images') ?? []`, `delete_images` int filter, `main_image_id/main_new_index` int ya null.

### 4.3 Helpers-e DTO

- `normalizeSpecs($input): array` (`:112-123`): `[parent_id => [child_id,...]]` → `intval+filter` har child list, hazf-e gorouh-haye khali.
- `normalizeTextSpecs($input): array` (`:128-139`): `[spec_id => value]` → `trim + hazf value haye khali`.

---

## 5. Validation — `app/Http/Requests/Admin/ProductRequest.php`

### 5.1 `prepareForValidation()` — `ProductRequest.php:19-30`

```php
use HasSlug; // :7 — trait app/Traits/HasSlug.php
protected function prepareForValidation(): void {
  if(empty($this->slug) && !empty($this->name['fa'])) { // :22
    $this->merge(['slug'=> $this->prepareSlug($this->name['fa'], null)]); // :23-25 — Str::slug(fa name)
  }
}
```

`HasSlug::prepareSlug($title,$url)` (`app/Traits/HasSlug.php:8`): `return Str::slug($url ?: $title)`. Slug agar khali bashad auto az `name.fa` generate mishavad — ghabl az validation.

### 5.2 `rules(): array` — `ProductRequest.php:32-102` — 30 rule gorouh

```php
$id = $this->route('product')?->id; // :34 — baraye unique ignore
return [
  'product_category_id'=>'required|exists:product_categories,id', // :37
  'brand_id'         =>'nullable|exists:brands,id',              // :38
  'name'             =>'required|array',                         // :39
  'name.fa'          =>'required|string|max:255',                // :40
  'name.en'          =>'required|string|max:255',                // :41
  'slug'             =>['required','string','max:255', new UrlRule(Product::class,$id)], // :42 — unique + UrlRule
  'short_description'=>'nullable|array', 'description'=>'nullable|array', // :44-45
  'sku'              =>'nullable|string|max:100|unique:products,sku,'.$id, // :47
  'price'            =>'nullable|integer|min:0',                 // :49 — nullable chon variant darad
  'discount_price'   =>'nullable|integer|min:0',                 // :50
  'stock'            =>'nullable|integer|min:0',                 // :51
  'weight'           =>'nullable|integer|min:0',                 // :52
  'is_active'        =>'boolean', 'is_featured'=>'boolean', 'is_best_seller'=>'boolean', // :54-56
  'deal_ends_at'     =>'nullable|date|after:now',                // :58
  'timer_active'     =>'boolean', 'start_timer'=>'nullable|date', 'end_timer'=>'nullable|date|after:start_timer', // :59-61
  'specs'            =>'nullable|array', 'specs.*'=>'array', 'specs.*.*'=>'integer|exists:specifications,id', // :64-66
  'text_specs'       =>'nullable|array', 'text_specs.*'=>'nullable|string|max:500', // :69-70
  'variants'         =>'nullable|array', 'variants.*.id'=>'nullable|integer|exists:product_variants,id', // :73-74
  'variants.*.spec_ids'=>'nullable|array', 'variants.*.spec_ids.*'=>'integer|exists:specifications,id', // :75-76
  'variants.*.price' =>'nullable|integer|min:0', 'variants.*.discount_price'=>'nullable|integer|min:0', // :77-78
  'variants.*.stock' =>'nullable|integer|min:0', 'variants.*.weight'=>'nullable|integer|min:0', // :79-80
  'properties'       =>'nullable|array', 'properties.*.title'=>'nullable|string|max:255', 'properties.*.value'=>'nullable|string|max:500', // :83-85
  'related_ids'      =>'nullable|array', 'related_ids.*'=>'integer|exists:products,id', // :88-89
  'complement_ids'   =>'nullable|array', 'complement_ids.*'=>'integer|exists:products,id', // :90-91
  'images'           =>'nullable|array', 'images.*'=>'image|mimes:jpg,jpeg,png,webp|max:3072', // :94-95
  'delete_images'    =>'nullable|array', 'delete_images.*'=>'integer', // :97-98
  'main_image_id'    =>'nullable|integer', 'main_new_index'=>'nullable|integer', // :100-101
];
```

Nokte-e mohem: `price/stock/weight` **nullable** hastand — chon agar variant vojood dashte bashad ghimat/mojoodi az variant-ha jam mishavad. `authorize(): bool { return true; }` (`:14-17`) — auth dar middleware-e route.

---

## 6. Models & Relations (Model-ha va Rabete-ha)

### 6.1 Product — `app/Models/Product.php`

**Fillable (`:16-21`):** `product_category_id, brand_id, name, slug, main_image, short_description, description, sku, price, discount_price, final_price, stock, weight, is_active, is_featured, is_best_seller, deal_ends_at, timer_active, start_timer, end_timer` — 21 field.

**Casts (`:23-39`):** `name/short_description/description => array` (JSON), `price/discount_price/final_price/stock/weight => integer`, `is_featured/is_best_seller/is_active/timer_active => boolean`, `deal_ends_at/start_timer/end_timer => datetime`. SoftDeletes active (`:14`).

**8+ Relations:**

| # | Name | Type | Code | Joziat |
|---|------|------|------|--------|
| 1 | `category` | BelongsTo ProductCategory | `:45-48` | `belongsTo(ProductCategory::class,'product_category_id')` |
| 2 | `brand` | BelongsTo Brand | `:50-53` | `belongsTo(Brand::class,'brand_id')` |
| 3 | `images` | HasMany ProductImage | `:55-58` | `hasMany(ProductImage::class)->orderBy('sort_order')` |
| 4 | `specifications` | BelongsToMany Specification (pivot) | `:64-71` | `belongsToMany(Specification, 'product_specification')->withPivot('value','parent_id')->wherePivotNull('value')->wherePivotNull('parent_id')->wherePivotNull('deleted_at')` — faghat radif-haye parent |
| 5 | `specificationValues` | HasMany ProductSpecification | `:76-82` | `hasMany(ProductSpecification)->whereNotNull('value')->whereNull('deleted_at')->with('specification')` — faghat text values |
| 6 | `productSpecifications` | HasMany ProductSpecification | `:87-90` | `hasMany(ProductSpecification)->with('children','specification')` — hameye radif-ha baraye panel |
| 7 | `variants` | HasMany ProductVariant | `:92-95` | `hasMany(ProductVariant)->with('specifications')` |
| 8 | `properties` | HasMany Property | `:97-100` | `hasMany(Property)->orderBy('sort_order')` |
| 9 | `related` | BelongsToMany Product (self) | `:106-112` | `belongsToMany(Product, 'product_associations','product_id','related_product_id')->withPivot('type')->wherePivot('type','related')` |
| 10 | `complement` | BelongsToMany Product (self) | `:114-120` | moshabeh vali `wherePivot('type','complement')` |
| 11 | `associations` | HasMany ProductAssociation | `:122-125` | `hasMany(ProductAssociation,'product_id')` — raw pivot |
| 12 | `reviews` | HasMany Review | `:163-166` | `hasMany(Review)->approved()` — faghat taiid shode |

**Accessors (`:130-246`):**

- `getMainImageAttribute(?string $value): ?string` (`:130-142`): agar `main_image` column por bashad hamon, vagarna `relationLoaded('images') ? firstWhere is_primary : images()->where is_primary->first()` → `path`.
- `getMainImageUrlAttribute(): ?string` (`:147-161`): `asset(config('paths.upload').'/products/'.$main_image)` ya `images->firstWhere is_primary ?? first()->url` ya `images()->orderBy sort_order->first()->url`.
- `getFinalPriceAttribute(): int` (`:195-204`): agar `attributes['final_price']>0` bargardan, vagarna `discount_price>0 ? discount_price : price`. DB column olaviat darad, mohasebe fallback.
- `getHasDiscountAttribute(): bool` (`:206-209`): `final_price>0 && discount_price>0 && discount_price<price`.
- `getDiscountPercentageAttribute(): ?int` (`:211-218`): `round((price-final_price)/price*100)`.
- `getHasVariantsAttribute(): bool` (`:223-228`): `relationLoaded ? variants->isNotEmpty() : variants()->exists()`.
- `getAverageRatingAttribute(): float` (`:230-237`): `reviews avg rating` ba/bedoon relation.
- `getReviewsCountAttribute(): int` (`:239-246`): `reviews count`.
- Scopes: `scopeActive` (`:172-175`): `where is_active true`, `scopeFeatured` (`:177-180`), `scopeBestSeller` (`:182-185`).

**Helper `trans(string $field): ?string` (`:253-261`):** `value[locale] ?? value['fa'] ?? null` — baraye JSON field-ha.

### 6.2 ProductVariant — `app/Models/ProductVariant.php`

Fillable `:9` `product_id, price, discounted_price, final_price, stock, weight`; casts `:13-16` `price/discounted_price/final_price=>float, stock=>int, weight=>float`; SoftDeletes. Relation `product BelongsTo` + `specifications BelongsToMany` ba pivot `product_variant_specification` + `with('parent')` (`:24-28`). Scope `available` (`:30-33`) `where stock>0`. Accessor `hasDiscount` (`:35-38`), `percent` (`:40-47`) `round((price-final_price)/price*100)`, `label` (`:52-58`) `specifications sortBy parent_id map trans('title') implode ' / '` mesal "قرمز / L".

### 6.3 ProductImage — `app/Models/ProductImage.php`

Fillable `:7` `product_id, path, alt_text, is_primary, sort_order`; casts `:9-13` `alt_text=>json, is_primary=>bool, sort_order=>int`; **bedoon SoftDeletes**. Accessors: `getUrlAttribute` (`:20-23`) `asset(uploads/products/path)`, `getThumbUrl/MediumUrl/LargeUrl` (`:26-38`) `resizedUrl(sizeKey)`, `resizedUrl` (`:43-53`) `is_file(public_path(.../size/path)) ? asset(...) : null` fallback be null, `getAltAttribute` (`:55-60`) JSON locale fallback.

### 6.4 ProductSpecification — `app/Models/ProductSpecification.php`

Table `product_specification` (`:15`), SoftDeletes, fillable `:19` `product_id, specification_id, value, parent_id`. Relations: `product BelongsTo`, `specification BelongsTo`, `parent HasOne (id,parent_id)`, `children HasMany (parent_id)->with('children')` (`:31-39`) recursive, `getParentSpecificationAttribute` (`:41-44`) `parent?->specification`. Model-e 2-sat-he: parent row + child rows + text rows.

### 6.5 Property — `app/Models/Property.php`

Fillable `:8` `product_id, title, value, sort_order`; casts `:10-13` `title=>json, sort_order=>int`. `trans(field)` helper moshabeh Product. Title JSON fa/en, value plain string (max 500 dar Request).

### 6.6 ProductAssociation — `app/Models/ProductAssociation.php`

Fillable `:8` `product_id, related_product_id, type`; relations `product BelongsTo` + `relatedProduct BelongsTo`. Pivot-e self-referential ba `type` enum `related/complement`.

### 6.7 Specification — `app/Models/Specification.php`

Fillable `:13` `title, type, parent_id, active, is_filter, is_color, color_code`; casts `:16-19` `title=>json, active/is_filter/is_color=>bool`; SoftDeletes, HasFactory. Relations `parent BelongsTo`, `children HasMany orderBy id with children` (`:32-36`), `products BelongsToMany product_specification`, `productValues HasMany`, `variants BelongsToMany product_variant_specification`. Scopes `active`, `parents`. `trans(field)` va `getTypeNameAttribute` (`:59-62`) `text=>نوشتاری`.
### 6.8 ProductCategory / Brand — `app/Models/ProductCategory.php` / `Brand.php`

- **Category:** fillable `parent_id,name,slug,icon,image,description,meta_title,meta_description,sort_order,is_active`; casts JSON + bool; SoftDeletes; `parent BelongsTo`, `children HasMany orderBy sort_order`, `products HasMany`, `trans()`, `getImageUrlAttribute`.
- **Brand:** fillable `title,url,description,image,active,show_in_first_page,sort_order`; casts JSON + bool; SoftDeletes; `products HasMany`, `scopeActive/Ordered`, `getItemImageAttribute`, `trans()`.

---

## 7. Business Rules (Ghavaed-e Kasbokar)

| # | Rule | Kod / Evidence | Tozih |
|---|------|---------------|-------|
| 1 | `price/stock nullable agar variant darad` | `ProductRequest.php:49,51` `price/stock nullable` + `ProductDTO.php:48` `price<=0 && stock<=0 skip` + `ProductService.php:305` `if !variants()->exists() return` dar aggregate | Agar mahsul variant darad, ghimat/mojoodi-e asli az variant-ha jam mishavad va field-haye product mitavanand khali bashand. DTO radif-haye variant-e khali ra hazf mikonad. |
| 2 | `final_price computed` | `ProductService.php:167-169` `discount>0 ? discount : price` + `:277-279` variant + `Product.php:195-204` accessor | `final_price` hamishe dar `prepareData` mohasebe mishavad — dar DB zakhire + dar accessor fallback. Site/Admin filter bar asas-e `final_price` ast na `price`. |
| 3 | `Variant aggregation` | `ProductService.php:303-329` | `stock = sum(variant stock)` — **hamishe** update mishavad (har do shakhe); `price/final_price = min variant where stock>0 && final_price>0 orderBy final_price asc` — faghat agar `minVariant` vojood dashte bashad. |
| 4 | `WebP images` | `ImageUploader.php:32-48` `webpName + convertToWebP imagewebp 90` + `ImageResizer.php:38-60` `imagecreatefromwebp + cover-crop` | Hameye tasavir be WebP tabdil mishavand (be joz webp/svg/gif). 3 resize `thumb/medium/large` ba cover-crop-e markazi. |
| 5 | `main_image dual source` | `Product.php:130-161` + `ProductService.php:424-456` | `main_image` column + `images.is_primary`. Service har do ra sync mikonad. Accessor fallback az relation agar column khali. |
| 6 | `JSON multilingual` | `Product.php:24-26` casts array + `:253-261` trans() + `Site/ProductController.php:51-52` `name->fa like` | `name/short_description/description/title` JSON fa/en — query ba `->` operator, namayesh ba `trans()`. |
| 7 | `Slug auto` | `ProductRequest.php:22-25` + `HasSlug.php:8` `Str::slug` | Agar slug khali va `name.fa` por, slug auto generate. `UrlRule` uniqueness check. |
| 8 | `Specification 2-level` | `ProductService.php:206-252` | Select spec: parent row + child rows (parent_id foreign). Text spec: single row ba value. Soft delete-e ghabli + create-e jadid (replace-all). |
| 9 | `Related/Complement via type` | `ProductService.php:358-367` + `Product.php:108-119` `wherePivot type` | Pivot `product_associations` ba `type` discriminator — related vs complement. |
| 10 | `Review moderation` | `Site/ProductController.php:343-355` `is_approved=false` + `Review.php:51,66` `STATUS_PENDING=0, scopeApproved` | Nazarat pishfarz pending, faghat approved dar Site namayesh. Notification be admin via `NotificationService`. |
| 11 | `Toman amounts` | `Product.php:27-29` casts integer + `ProductService.php:179-182` | Hameye ghimat-ha `integer` toman — na rial, na decimal. |
| 12 | `is_active guard` | `Site/ProductController.php:26,120,124` `where is_active true` + `abort_unless` | Site faghat mahsul/daste-ye fa'al ra namayesh midahad. Admin hame ra mibinad. |

---

## 8. Transactions & Concurrency (Transaction-ha va Hamzaman-i)

- **ProductService store/update/destroy** har 3 dar `DB::transaction(Closure)` hastand (`:30, :56, :87`). Hameye neveshtan-ha (product + images + specs + variants + properties + associations) atomic hastand — agar har kodam fail, rollback-e kamel.
- **StockService** niz `DB::transaction` + `lockForUpdate` baraye `decrement/increment` estefade mikonad (`app/Services/StockService.php` `lockForUpdate()->first()`). Baraye order reserve/restore — na baraye ProductService (ProductService lock nadarad — potential race agar 2 admin hamzaman mahsul ra update konand, akharin neveshte barande).
- **Audit correction:** `StockService::enrichCartItemsWithStockStatus` DEAD nist — az `CartService::dbContent` seda zade mishavad (Cart module docs).
- **Product accessor:** `getDefaultVariantAttribute` (in-stock variant sortByDesc stock ?? first — `Product.php:236-244`) dar list-e accessor-ha ezafe shavad; CartService variant-repair hamamin semantics ra darad.
- **No nested transactions:** ProductService transaction dakheli-e StockService nist — joda hastand. Order lifecycle (Cart/Checkout) StockService ra seda mizanad.

---

## 9. Caching, Files & External (Cache, File va Khareji)

- **Cache:** Hich cache-e mostaghim dar products nist. `RecentlyViewedService` session-based (max 20, singleton via `AppServiceProvider`).
- **Files:** `public/<uploads>/products/` (config `paths.upload` default `uploads` — `config/paths.php:14`). Har tasvir: 1 file asli WebP + 3 file resize dar `thumb/medium/large/` zirpush. Hazf-e fiziki dar `ImageHandler::delete` (`:31-48`) ke `public_path` + `unlink` estefade mikonad.
- **External:** Hich API-e khareji — faghat GD (`imagecreatefromjpeg/png/webp`, `imagewebp`, `imagecopyresampled`).
- **SoftDeletes:** Product, ProductVariant, ProductSpecification, Specification, ProductCategory, Brand softDelete darand; ProductImage, Property, ProductAssociation hard delete.

---

## 10. Authorization & Middleware (Ehraz-e Hoviat)

- Admin routes: `prefix admin, name admin., middleware [auth, adminPermission, log.admin]` (`routes/admin.php:32-33`). `auth` = login required, `adminPermission` = Gate check bar asas-e `AdminPermission` middleware, `log.admin` = audit logging. `ProductRequest authorize() => true` (`:14-17`) — auth dar middleware-e route, na dar Request.
- Site routes: hich middleware-e auth baraye `index/show/category/quickView` — public. `storeReview/storeReply` public (guest ba `guest_name/phone`), `voteReview` ba `middleware('auth')` (`routes/site.php:53-55`). `category` ba `abort_unless(is_active,404)` (`Site/ProductController.php:120`).

---

## 11. Evidence Matrix (Jadval-e Mostanadat)

| Claim | File:Line |
|-------|-----------|
| Admin index + activeFilterChips 8 filters | `app/Http/Controllers/Admin/ProductController.php:20-34, 41-91` |
| Admin create specs/products picker | `app/Http/Controllers/Admin/ProductController.php:93-109` |
| Admin edit eager load 8 relations | `app/Http/Controllers/Admin/ProductController.php:125-134` |
| Admin formData oldVal + 4 sakhtar | `app/Http/Controllers/Admin/ProductController.php:156-227` |
| Admin store/update DTO delegation | `app/Http/Controllers/Admin/ProductController.php:111-121, 229-237` |
| Site index 5 filters + match sort + descendants | `app/Http/Controllers/Site/ProductController.php:24-82, 88-99` |
| Site show + prepareShowData 4 output | `app/Http/Controllers/Site/ProductController.php:132-200, 233-307` |
| Site buildReviewTree 2-pass | `app/Http/Controllers/Site/ProductController.php:205-224` |
| Site storeReview guest + notification | `app/Http/Controllers/Site/ProductController.php:312-374` |
| ProductService store/update DB::transaction | `app/Services/ProductService.php:30, 56, 87` |
| ProductService getPaginatedProducts 8 filters + 6 sort | `app/Services/ProductService.php:97-160` |
| ProductService prepareData finalPrice ternary | `app/Services/ProductService.php:165-192` |
| ProductService syncSpecifications parent+child | `app/Services/ProductService.php:203-253` |
| ProductService syncVariants updateOrCreate + aggregate | `app/Services/ProductService.php:264-329` |
| ProductService image pipeline WebP + 3 size | `app/Services/ProductService.php:379-467` + `app/Helpers/ImageHandler.php:14-27` + `ImageUploader.php:13-78` + `ImageResizer.php:14-68` |
| ProductDTO 26 fields + fromRequest filtering | `app/DTOs/ProductDTO.php:10-39, 41-107` |
| ProductRequest 30 rules + slug auto HasSlug | `app/Http/Requests/Admin/ProductRequest.php:19-30, 32-102` + `app/Traits/HasSlug.php:8` |
| Product model fillable/casts 8+ relations + accessors | `app/Models/Product.php:16-39, 45-166, 130-261` |
| StockService getAvailableStock withTrashed | `app/Services/StockService.php:66-76` |
| Routes admin resource + site 7 routes locale duplicate | `routes/admin.php:46` + `routes/site.php:47-57, 160-170` |
| Migration final_price backfill + index | `database/migrations/2026_08_04_000009_add_final_price_to_products_table.php:13-19` |

---

## 12. Open Questions (Soalat-e Baz)

- `discount_price > price` guard nadarad — aya amdan? (`ProductRequest.php:50` faghat `min:0`)
- `StockService` lockForUpdate darad vali `ProductService` nadarad — aya race-e admin concurrent pazirofte shode?
- `product_images` jadval-e migration-e `2026_08_01_124353` — aya `sort_order` index darad ya na? (check schema-dictionary)
- `RecentlyViewedService` ba session max 20 — aya dar product show-e admin ham seda zade mishavad ya faghat site?
