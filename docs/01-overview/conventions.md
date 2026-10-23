# Gharardad-ha va Convention-ha — Joobjoob Conventions

> **Manba-e Haghighi:** `database/migrations/*.php`, `app/Models/*.php`, `app/DTOs/*.php`, `app/Services/*.php`, `app/Helpers/*.php`, `routes/*.php`, `config/*.php`
> **Ghanoon:** Har gharardad ba dalil-e file va khat — hich chize pishfarz-e farzi nist.

---

## 1. JSON Multilingual — title / description / name

### 1.1 Kodoom Setoon-ha JSON Hastand?

| Model | Setoon-haye JSON | Cast | File |
|---|---|---|---|
| `Product` | `name`, `short_description`, `description` | `'name'=>'array'` | `app/Models/Product.php:24 casts` |
| `ProductCategory` | `name`, `description` | `array` | `app/Models/ProductCategory.php: casts` |
| `Blog` | `title`, `description` (content) | `array` | `app/Models/Blog.php: casts` |
| `BlogCategory` | `title`, `description` | `array` | `app/Models/BlogCategory.php: casts` |
| `Brand` | `name` | `array`? (check — brand name JSON) | `app/Models/Brand.php: casts` |
| `HeroSlide` | `title`, `desc`, `badge`, `btn_text`, `trusts` (array of {icon, text:{fa,en}}) | `array` | `app/Models/HeroSlide.php: casts` |
| `TrustBadge` | `title`, `description` (translatable — migration 2026_08_10) | `array` | `app/Models/TrustBadge.php: casts` |
| `Specification` | `title` | `array` | `app/Models/Specification.php: casts` |
| `Property` | `title` (json fa/en) | `array` | `app/DTOs/ProductDTO.php:72 title fa/en` |
| `Order` | `address` (array snapshot) | `array` | `app/Models/Order.php: casts address array` |

### 1.2 Sakhtar-e JSON — Mesal-e Vaghe'i

```json
// products.name
{"fa": "گوشی موبایل سامسونگ", "en": "Samsung Mobile"}

// hero_slides.title
{"fa": "تخفیف ویژه", "en": "Special Offer"}

// hero_slides.trusts
[{"icon":"✓","text":{"fa":"ارسال رایگان","en":"Free Shipping"}}, ...] // max 5
```

- Migration-ha: `2026_08_01_124237_create_product_categories_table.php` — `name json`, `2026_07_31_152013_create_hero_slides_table.php` — `title json`.
- DTO: `ProductDTO::fromRequest` — `name: $request->validated('name')` (ke `array: [fa=>..., en=>...]` ast), `HeroSlideDTO` — `title: ['fa'=> input('title.fa'), 'en'=> input('title.en')]`.

### 1.3 Khondan — Helper trans()

```php
// app/Models/Product.php:253
public function trans(string $field): ?string {
    $value = $this->$field;
    if (is_array($value) || is_object($value)) {
        return $value[app()->getLocale()] ?? $value['fa'] ?? null;
    }
    return $value;
}
// Usage: $product->trans('name') → string be zabane jari, fallback fa
// Ya: $heroSlide->title[app()->getLocale()] ?? $heroSlide->title['fa']
```

- Evidence:
  - `app/Models/Product.php:23-26 casts name/short_description/description array, 253 trans()`
  - `app/DTOs/ProductDTO.php:79 name: $request->validated('name')` (array)
  - `app/DTOs/HeroSlideDTO.php:46 title: ['fa'=>..., 'en'=>...], 71 normalizeTrusts`
  - `database/migrations/2026_08_01_124237_create_product_categories_table.php: name json`
  - `database/migrations/2026_07_31_152013_create_hero_slides_table.php: title json`

---

## 2. SoftDeletes — 20 Model

### 2.1 List-e Kamel (Verified via `grep SoftDeletes app/Models`)

| # | Model | File | `use SoftDeletes;` |
|---|---|---|---|
| 1 | `Address` | `app/Models/Address.php:12` | ✓ |
| 2 | `Bank` | `app/Models/Bank.php:10` | ✓ — plus `config` json |
| 3 | `Blog` | `app/Models/Blog.php:11` | ✓ |
| 4 | `BlogCategory` | `app/Models/BlogCategory.php:12` | ✓ |
| 5 | `Brand` | `app/Models/Brand.php:12` | ✓ |
| 6 | `City` | `app/Models/City.php:12` | ✓ |
| 7 | `Discount` | `app/Models/Discount.php:12` | ✓ |
| 8 | `InventoryTransaction` | `app/Models/InventoryTransaction.php:10` | ✓ |
| 9 | `Order` | `app/Models/Order.php:14` | ✓ — plus `OrderObserver` restoreStock |
| 10 | `OrderHistory` | `app/Models/OrderHistory.php:10` | ✓ |
| 11 | `OrderItem` | `app/Models/OrderItem.php:11` | ✓ |
| 12 | `OrderShippingStatus` | `app/Models/OrderShippingStatus.php:10` | ✓ |
| 13 | `Product` | `app/Models/Product.php:14` | ✓ |
| 14 | `ProductCategory` | `app/Models/ProductCategory.php:13` | ✓ |
| 15 | `ProductSpecification` | `app/Models/ProductSpecification.php:18` | ✓ |
| 16 | `ProductVariant` | `app/Models/ProductVariant.php:13` | ✓ |
| 17 | `Review` | `app/Models/Review.php:14` | ✓ |
| 18 | `ShippingMethod` | `app/Models/ShippingMethod.php:12` | ✓ |
| 19 | `Specification` | `app/Models/Specification.php:14` | ✓ |
| 20 | `State` | `app/Models/State.php:12` | ✓ |

### 2.2 Bedoun-e SoftDeletes (Amdi)

| Model | Dalil |
|---|---|
| `User` | Hichvaght hazf-e narm nemishavad — deactivate via `is_active=false` |
| `Cart` | Session/DB movaghat — `clear()` hazf-e sakht |
| `Notification` | Tarikhche — ba `markRead` modiriat |
| `AuditLog`, `RequestLog` | Log — hichvaght hazf nemishavad |
| `Language`, `Setting` | Tanzim — hazf-e sakht ya toggle `is_active` |
| `HeroSlide`, `TrustBadge`, `PromoBanner`, `ProductImage`, `Property`, `ProductAssociation` | Content — `delete()` mostaghim vali bedoun softDeletes (baz ham `Cache::forget` dar `saved/deleted` event) |

### 2.3 Gharardad-e Hazf

```php
// app/Services/*Service.php — olgouye yeksan
public function destroy(Model $model): bool {
    // be lotf-e SoftDeletes, hazf-e narm anjam mishavad
    return $model->delete(); // soft delete — deleted_at set
}
// Bazyabi: Model::withTrashed()->find($id)->restore();
// Hichvaght forceDelete estefade nashode — magar migration rollback
```

- Migration-ha: `softDeletes()` dar `Schema::create` — hame ba `deleted_at timestamp nullable`.
- Evidence: `grep -l SoftDeletes app/Models/*.php` → 20 file, `app/Services/BrandService.php:59 comment SoftDeletes`, `app/Services/BlogService.php:42 delete()`

---

## 3. Fillable + Casts — Gharardad-e Model

### 3.1 Olgou-ye Fillable

Har model faghat field-hayi ke az form ya service miyayad ra dar `fillable` darad — mass assignment-e baghiye block.

```php
// app/Models/Product.php:16 — nemoune
protected $fillable = [
    'product_category_id', 'brand_id', 'name', 'slug', 'main_image', 'short_description', 'description',
    'sku', 'price', 'discount_price', 'final_price', 'stock', 'weight',
    'is_active', 'is_featured', 'is_best_seller', 'deal_ends_at',
    'timer_active', 'start_timer', 'end_timer',
];

// app/Models/Order.php — nemoune
protected $fillable = ['user_id', 'order_status', 'shipping_status_id', 'total_price', 'discount_amount', 'shipping_price', 'address', 'payment_method', ...];

// app/Models/User.php
protected $fillable = ['name', 'mobile', 'email', 'password', 'is_active', 'is_admin', 'locale', 'theme', 'confirm_code', 'confirm_code_expires_at'];
```

- **Hoshdar:** Agar field jadid ezafe shod, hatman be `fillable` ezafe kon — vagarna `create/update` silent fail → null.

### 3.2 Casts — Gharardad-ha

| Cast | Setoon | Model | Evidence |
|---|---|---|---|
| `array` | `name, description, title, badge, address, transaction_info` | Product, Blog, HeroSlide, Order | `Product.php:24 name array`, `Order.php: address array, transaction_info array` |
| `integer` | `price, discount_price, final_price, stock, weight, viewers, basket_minimum_price` | Product, HeroSlide, Discount | `Product.php:27 price integer`, `Discount.php: basket_minimum_price integer` |
| `boolean` | `is_active, is_featured, is_best_seller, timer_active, is_admin, is_default` | Product, Language, User, Specification | `Product.php:32 is_active boolean`, `Language.php: is_active, is_default boolean` |
| `datetime` | `deal_ends_at, start_timer, end_timer, starts_at, expires_at, confirm_code_expires_at` | Product, Discount, User | `Product.php:36 deal_ends_at datetime`, `Discount.php: starts_at, expires_at datetime` |
| `decimal` / `float` | `shipping_price, total_price, discount_amount, weight` | Order, ShippingMethod | `Order.php: shipping_price decimal:2` (agar hast) |
| `json` (auto) | `payload` dar RequestLog, `value` dar Setting (type=json) | RequestLog, Setting | `RequestLog.php: payload json`, `SettingService: castValue json_decode` |

- Evidence:
  - `app/Models/Product.php:23-39 casts (name array, price integer, is_active boolean, deal_ends_at datetime)`
  - `app/Models/Discount.php: casts (basket_minimum_price integer, starts_at datetime, transaction_info array)`
  - `app/Models/Order.php: casts (address array, shipping_status_id integer)`
  - `app/Models/User.php: casts (is_admin boolean, confirm_code_expires_at datetime)`

---

## 4. Route Naming — admin.* + locale.*

### 4.1 Admin Routes — `prefix admin` + `name admin.` + middleware

```php
// routes/admin.php:24
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AdminAuthController::class, 'showLogin'])->name('login'); // public
    Route::post('login', [AdminAuthController::class, 'postLogin'])->name('login.submit')->middleware('throttle:5,1');
    Route::middleware(['auth', 'adminPermission', 'log.admin'])->group(function () {
        Route::post('logout', ...)->name('logout');
        Route::resource('products', ProductController::class); // → admin.products.index/create/store/show/edit/update/destroy
        Route::resource('hero-slides', HeroSlideController::class)->except('show'); // hich show nist — tele 403
        // ... 20 resource
        Route::post('hero-slides/reorder', ...)->name('hero-slides.reorder');
        Route::post('trust-badges/reorder', ...)->name('trust-badges.reorder');
        Route::patch('languages/{language}/toggle', ...)->name('languages.toggle');
        Route::patch('languages/{language}/make-default', ...)->name('languages.make-default');
    });
});
```

| Gharardad | Value | Dalil |
|---|---|---|
| Prefix | `admin` | `routes/admin.php:24 prefix('admin')` |
| Name prefix | `admin.` | `routes/admin.php:24 name('admin.')` → `admin.products.index` |
| Middleware | `auth` + `adminPermission` + `log.admin` | `routes/admin.php:34` |
| Resource except | `show` baraye `hero-slides, promo-banners, product-categories, roles, specifications` — show amdan hazf ta ba `permissions` tree tadakhol nakond (403 trap) | `routes/admin.php:41 ->except('show')` |
| Reorder routes | `POST {resource}/reorder` → `name {resource}.reorder` — ba permission `edit` | `routes/admin.php:67-70` |
| Free routes | `admin.ckeditor.upload, admin.logout, admin.dashboard.index` — faghat auth, bedoun adminPermission | `config/permissions.php:11 free` |

### 4.2 Site Routes — Canonical + Prefixed Duplication

```php
// routes/site.php:42-171
$siteRoutes = function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product:slug}', ...)->name('products.show');
    Route::post('/cart/add/{product}', ...)->name('cart.add');
    Route::prefix('cart')->name('cart.')->group([...]); // index/update/remove
    Route::middleware('auth')->post('/cart/coupon', ...)->name('cart.coupon');
    Route::middleware('auth')->prefix('checkout')->name('checkout.')->group([...]); // address/payment/create/success
    Route::get('/login', ...)->name('login');
    Route::get('/login/otp', [OtpController::class, 'show'])->name('login.otp');
    // ... 60 route
};
// 1) canonical — bedoune pishvand, zabane pishfarz
$siteRoutes();
// 2) pishvandar — ba {locale}, name locale.*
Route::prefix('{locale}')->where(['locale'=>$localePattern])->name('locale.')->group($siteRoutes);
// → locale.home, locale.products.index, locale.cart.add, ...
```

| Gharardad | Value | Dalil |
|---|---|---|
| Closure | `$siteRoutes = function () { ... }` — yek bar tarif, do bar sabt | `routes/site.php:42 $siteRoutes` |
| Canonical | Bedoune `{locale}` — `home, products.index, cart.add, checkout.index` | `routes/site.php:165 $siteRoutes()` |
| Prefixed | `prefix {locale} where localePattern name locale.` — `locale.home, locale.products.index` | `routes/site.php:168 prefix('{locale}')->name('locale.')` |
| LocalePattern | `^(?:fa\|en\|ar\|tr\|...)$` — az `config/languages` + DB | `routes/site.php:28-39 catalogCodes + locale()->codes()` |
| SEO | `/` → fa canonical, `/en/` → en, `/fa/` → 301 be `/` | `app/Http/Middleware/SetLocale.php:42 redirect 301 agar locale==default` |
| Hoshdar | Har route jadid **bayad dar `$siteRoutes`** ezafe shavad — dar gheir-e in sorat faghat canonical ya faghat prefixed kar mikonad | Duplicate by design — Laravel `{locale?}` optional dar ebteda kar nemikonad |

- Evidence: `routes/site.php:28-39 localePattern, 42 $siteRoutes, 165 $siteRoutes(), 168 prefix locale`, `routes/admin.php:24 prefix admin, 34 middleware`, `config/permissions.php: free list`

---

## 5. Session Keys — Kelid-haye Hayati

### 5.1 List-e Kamel (Verified via `grep session(` + `sessionKey`)

| Key | Type | Nevesande | Khanande | File |
|---|---|---|---|---|
| `cart` | `array[rowId => {rowId, product_id, variant_id, qty, price, original_price, stock, weight, color, size, image}]` — guest only | `CartService::sessionAdd/Update/Remove/Clear` | `CartService::sessionContent()`, `content()` | `app/Services/CartService.php:15 sessionKey=cart` |
| `wishlist` | `list<int> product_id` | `WishlistService::toggle()` | `WishlistService::ids(), content()` | `app/Services/WishlistService.php:14 wishlist` |
| `recently_viewed` | `list<int> product_id` max 20 newest first | `RecentlyViewedService::add()` | `content(), ids()` | `app/Services/RecentlyViewedService.php:14 recently_viewed` |
| `checkout_address_id` | `int` (Address id) | `CheckoutController::shipments()` → `session(['checkout_address_id'=>id])` | `CheckoutController::index/payment/create`, `CartController` | `app/Http/Controllers/Site/CheckoutController.php:56, 85, 169, 227 forget` |
| `checkout_shipping_method_id` | `int` (ShippingMethod id) | `CheckoutController::setShipment()` | `CheckoutController::payment/create`, `ShippingCalculator` | `CheckoutController.php:73, 86, 170, 228` |
| `checkout_shipping_price` | `int` (toman) | `CheckoutController::setShipment()` | `CheckoutController::create()` → `OrderService` | `CheckoutController.php:74, 87, 171, 229` |
| `coupon` | `array{code, discount_id, type, value, discount_amount, title, with_discount, pay_type, basket_minimum_price, ...}` | `CartService::applyCoupon()` / `CouponService::apply()` → `session(['coupon'=>array])` | `CartService::discount()->calculateForCart()`, `CheckoutController::create()` | `app/Services/CartService.php:340 session('coupon'), 374 applyCoupon`, `app/Services/CouponService.php: apply()` |
| `locale` | `string` code (fa/en/ar/tr) | `SetLocale` middleware — 7-step priority | `SetLocale`, `LocaleComposer`, `app()->getLocale()` | `app/Http/Middleware/SetLocale.php:47 session(['locale'=>locale])` |
| `site_theme` (cookie, na session) | `string` — `light-neumorphism` default | User toggle → cookie `site_theme` | `SetTheme` middleware → `view()->share(activeTheme)` | `app/Http/Middleware/SetTheme.php:13 cookie site_theme` |

### 5.2 Checkout Flow — Cheshm-andaz-e Session

```
1. POST /checkout/shipments {address_id} → AddressService::findOne → session[checkout_address_id]=id
   → ShippingCalculator::getAvailableMethods(address, roundedWeight=ceil(totalWeight/100)*100, subtotal)
2. POST /checkout/set-shipment {shipping_method_id, shipping_price} → session[checkout_shipping_method_id, checkout_shipping_price]
3. POST /cart/coupon {code} → CouponService::apply(code, subtotal) → session[coupon]=array — ya error
4. POST /checkout/create → require checkout_address_id && checkout_shipping_method_id → OrderService::create(address, shipping, coupon) → clear cart + forget coupon + forget checkout_*
5. Callback /checkout/finish?Authority=...&Status=OK → verifyTransaction → update Order status
```

- **Forget dar payan:** `session()->forget('checkout_address_id'); forget('checkout_shipping_method_id'); forget('checkout_shipping_price');` + `CartService::clear()` + `session()->forget('coupon')` dar `CheckoutController::create()` ya `success()`.
- **Cart key-e mehman:** `cart` — guest cart dar session array, login cart dar DB `carts` table (hybrid dar CartService).
- Evidence:
  - `app/Services/CartService.php:15 cart, 340 coupon, 357 count`
  - `app/Services/WishlistService.php:14 wishlist`
  - `app/Services/RecentlyViewedService.php:14 recently_viewed`
  - `app/Http/Controllers/Site/CheckoutController.php:56 checkout_address_id, 73 checkout_shipping_method_id, 74 checkout_shipping_price, 227-229 forget checkout_*`
  - `app/Http/Middleware/SetLocale.php:47 session locale`

---

## 6. Amount — Toman → Rial via '0' (String Concat)

### 6.1 Gharardad-e Pouli

- **Zakhiere dar DB:** Hame mabalegh be **toman** (IRT) va `integer` — `price, discount_price, final_price, total_price, shipping_price, discount_amount`.
- **Gateway niaz be rial darad:** ZarinPal va Chapar mabalegh ra be **rial** mikhahand — toman * 10.

```php
// app/Payments/ZarinPal.php:29 — actual code
$amountRial = intval($amount . '0'); // toman → rial — string concat, na *10 (jalogiri az float error)
// Mesal: 50000 toman → "50000" . "0" → "500000" → intval → 500000 rial

// app/Services/ChaparShipment.php — haman olgou baraye Chapar (agar estefade shavad)
// ShippingCalculator ham toman hesab mikonad — hich tabdil dar mohasebat-e dakheli nist
```

| Nokte | Value | File |
|---|---|---|
| Sakhtar-e DB | `integer` — toman | `database/migrations/*_create_products_table.php: price integer`, `*_create_orders_table.php: total_price integer` |
| Display | `format_price(amount)` → `NumberHelper::formatPrice` — toman ba joda-konande | `app/Helpers/format_price.php:14`, `app/Helpers/NumberHelper.php:40 formatPrice` |
| Gateway | `intval($amount . '0')` — rial | `app/Payments/ZarinPal.php:29, 59` (request + verify) |
| Chapar | Haman — agar Chapar estefade shavad | `app/Services/ChaparShipment.php` |
| Format dar Blade | `{{ format_price($product->final_price) }}` → `۵٬۰۰۰٬۰۰۰` (fa) ya `5,000,000` (en) | `resources/views/**/*.blade.php` |

- **Hoshdar:** Hich ja `*10` ya `* 10` nanevis — az `intval($amount.'0')` estefade kon ta float precision az beyn naravad. In gharardad dar hame ja yeksan ast.
- Evidence: `app/Payments/ZarinPal.php:29 intval($amount.'0'), 59 intval($price.'0')`, `app/Helpers/NumberHelper.php:40 formatPrice toman`, `app/Models/Product.php:27 price integer, 28 discount_price integer`

---

## 7. linkable_id — String (Na Integer)

### 7.1 Migration — 2026_08_08

```php
// database/migrations/2026_08_08_000001_change_linkable_id_to_string_in_hero_slides_table.php:19
$table->string('linkable_id')->nullable()->change(); // az unsignedBigInteger → string

// Ghabl: 2026_07_31_152013_create_hero_slides_table.php:35
$table->unsignedBigInteger('linkable_id')->nullable();
$table->index(['linkable_type', 'linkable_id']); // composite index hefz mishavad
```

| Item | Ghabl | Ba'd | Dalil |
|---|---|---|---|
| Type | `unsignedBigInteger nullable` | `string nullable` | `2026_08_08 migration:19 ->change()` |
| Dalil-e taghir | `page` → `linkable_id = 'contact'` (key-e safhe), `custom` → `linkable_id = 'https://example.com'` (URL) — adad nist | `app/DTOs/HeroSlideDTO.php:109 getLinkableId(): ?string` |
| DTO getter | `getLinkableId(): ?string` — hame ja string | `app/DTOs/HeroSlideDTO.php:109` |
| Model fillable | `linkable_id` string | `app/Models/HeroSlide.php:20 fillable` |

### 7.2 Meghdar-haye linkable_id bar hasab link_type

| `link_type` (DTO) | `linkable_type` (DB) | `linkable_id` (string) | Mesal |
|---|---|---|---|
| `product` | `App\Models\Product` | `"123"` (id-e product be soorat string) | `linkable_id = (string) $product->id` |
| `product_category` | `App\Models\ProductCategory` | `"5"` |  |
| `blog` | `App\Models\Blog` | `"42"` |  |
| `blog_category` | `App\Models\BlogCategory` | `"3"` |  |
| `page` | `"page"` | `"contact"` ya `"about"` (key-e safhe) | `HeroSlide::getLinkableUrlAttribute` → `localizedRoute('site.page', ['page'=>id])` |
| `custom` | `"url"` | `"https://example.com"` (URL-e kamel) | `getLinkableUrlAttribute` → `linkable_id` mostaghim |

```php
// app/DTOs/HeroSlideDTO.php:96
public function getLinkableType(): ?string {
    return match ($this->link_type) {
        'product' => Product::class,
        'product_category' => ProductCategory::class,
        'blog' => Blog::class,
        'blog_category' => BlogCategory::class,
        'page' => 'page',
        'custom' => 'url',
        default => null,
    };
}
public function getLinkableId(): ?string { return $this->link_value; } // string — hata baraye product
```

- Evidence: `database/migrations/2026_08_08_000001_change_linkable_id_to_string...php:19`, `app/DTOs/HeroSlideDTO.php:96 getLinkableType, 109 getLinkableId string`, `app/Models/HeroSlide.php:20 fillable, 78 getLinkableUrlAttribute`, `app/Services/HeroSlideService.php:76 linkable_id => $dto->getLinkableId()`

---

## 8. Image — WebP via GD, public/uploads

### 8.1 Masir-e Upload

```php
// config/paths.php:12
'upload' => env('UPLOAD_PATH', 'uploads'), // → public/uploads/{folder}/

// app/Helpers/ImageHandler.php:12
public static function handle(string $folderName, UploadedFile $file, ?array $sizes = null): ?string {
    $fileName = ImageUploader::upload($folderName, $file); // → public/uploads/{folder}/{hash}.webp
    if (! $fileName || ! $sizes) return $fileName;
    ImageResizer::resize($fileName, $folderName, $sizes);
    return $fileName;
}
// ImageHandler::delete(folder, image, sizes) → unlink public/uploads/{folder}/{image} + {size}/{image}
```

| Item | Value | File |
|---|---|---|
| Base path | `public/uploads/` — na `storage/app` | `config/paths.php:12 upload=uploads`, `app/Helpers/ImageHandler.php:38 public_path("{$baseUpload}/{$folderName}")` |
| Zakhiere | `public/uploads/{folder}/{hash}.webp` — hash via `md5(microtime().extension)` | `app/Helpers/Helper.php:44 md5(microtime())`, `app/Helpers/ImageUploader.php` |
| Folder ha | `products`, `categories`, `brands`, `blogs`, `hero-slides`, `trust-badges`, `promo-banners`, `settings` | `app/Services/ProductService.php` → `ImageHandler::handle('products', ...)`, `SettingService: settings` |
| Resize | `ImageResizer::resize(fileName, folder, sizes)` — har size dar zir-push-e `public/uploads/{folder}/{sizeKey}/` | `app/Helpers/ImageResizer.php: resize()`, `ImageHandler.php:23` |

### 8.2 WebP + GD — Na Intervention/Image

| Item | Value | File |
|---|---|---|
| Library | **PHP GD** — `imagecreatefromjpeg/png/webp`, `imagewebp()` — hich `intervention/image` nist | `app/Helpers/Helper.php:92 imagecreatefromjpeg, 97 imagecreatefrompng, 100 imagecreatefromwebp, 123 imagewebp`, `app/Helpers/ImageUploader.php:62 imagecreatefromjpeg/png, 75 imagewebp`, `app/Helpers/ImageResizer.php:23 imagecreatefromwebp, 63 imagewebp` |
| Format khorouji | **WebP** — hame aks-ha be `imagewebp($tmp, $path, 90)` tabdil mishavand | `app/Helpers/Helper.php:123 imagewebp 100, app/Helpers/ImageUploader.php:75 imagewebp 90, ImageResizer.php:63 imagewebp 90` |
| Quality | `90` dar ImageUploader/Resizer, `100` dar Helper legacy (100 → 90 pishnahad) | `ImageUploader.php:75, ImageResizer.php:63 quality 90` |
| Ekhtiyar | `jpg/jpeg/png/webp` → GD, `svg/gif` → mostaghim copy (bedoune GD), `webp` input → mostaghim | `app/Helpers/ImageUploader.php:62 switch IMAGETYPE_JPEG/PNG else webp` |
| Helper legacy | `Helper::uploadImage()` — GD + 2 size (1000x1000 big, 500x500 medium) — dar khate `123 imagewebp($tmp1, $path_big)` | `app/Helpers/Helper.php:55 uploadImage` |
| Hazf | `ImageHandler::delete(folder, image, sizes)` — unlink-e hame version-ha | `app/Helpers/ImageHandler.php:31 delete` |

```php
// app/Helpers/ImageUploader.php — actual
$fileName = md5(microtime() . $extension) . '.webp'; // unique name
$image = match (exif_imagetype($inputPath)) {
    IMAGETYPE_JPEG => imagecreatefromjpeg($inputPath),
    IMAGETYPE_PNG  => imagecreatefrompng($inputPath),
    default        => @imagecreatefromwebp($originalPath),
};
imagewebp($image, public_path("{$baseUpload}/{$folderName}/{$fileName}"), 90);

// app/Helpers/Helper.php:107-124 — legacy 2-size
$new_width1=1000; $new_height1=1000; $tmp1=imagecreatetruecolor(...); imagecopyresampled(...); imagewebp($tmp1, $path_big.$fileName, 100);
$new_width2=500;  $new_height2=500;  $tmp2=...; imagewebp($tmp2, $path_medium.$fileName, 100);
```

- Evidence:
  - `config/paths.php:12 upload`, `app/Helpers/ImageHandler.php:12 handle, 31 delete`
  - `app/Helpers/ImageUploader.php:62 imagecreatefromjpeg/png, 75 imagewebp 90`
  - `app/Helpers/ImageResizer.php:23 imagecreatefromwebp, 63 imagewebp 90`
  - `app/Helpers/Helper.php:92 imagecreatefromjpeg, 100 imagecreatefromwebp, 123 imagewebp`
  - `composer.json` — hich `intervention/image` nist

---

## 9. Cache — rememberForever + flush (Na TTL)

### 9.1 Olgou-ye Cache dar Joobjoob

Joobjoob az `Cache::rememberForever` estefade mikonad — na `remember` ba TTL. In be manaye cache ta zaman-e `flush()` ast.

```php
// app/Services/SettingService.php:17,53
private const CACHE_ALL = 'joobjoob_settings_all';
private static ?array $memory = null; // static memory baraye haman request — yek query bishtar nist
public function all(): array {
    if (self::$memory !== null) return self::$memory;
    return self::$memory = Cache::rememberForever(self::CACHE_ALL, fn() => Setting::all()->mapWithKeys(...)->all());
}
public function flush(): void { self::$memory = null; Cache::forget(self::CACHE_ALL); }

// app/Services/LocaleService.php:21,41 — daghighan haman olgou
private const CACHE_ALL = 'joobjoob_languages_all';
private static ?array $memory = null;
public function all(): array {
    if (self::$memory !== null) return self::$memory;
    return self::$memory = Cache::rememberForever(self::CACHE_ALL, fn() => Language::ordered()->get()->keyBy('code')->all());
}
public function flush(): void { self::$memory = null; Cache::forget(self::CACHE_ALL); }
```

### 9.2 List-e Kamel-e Cache Key-ha

| Cache Key | Nevesande (rememberForever) | Pak-konande (forget/flush) | Trigger-e Flush | File |
|---|---|---|---|---|
| `joobjoob_settings_all` | `SettingService::all()` | `SettingService::flush()` → `Cache::forget` | `SettingService::updateGroup()` ba'd az `DB::transaction` → `flush()` | `app/Services/SettingService.php:17,53,148` |
| `joobjoob_languages_all` | `LocaleService::all()` | `LocaleService::flush()` | `LanguageService/Controller::store/update/destroy/toggle/makeDefault/reorder` → `flush()` | `app/Services/LocaleService.php:21,41,183` |
| `promo_banners_active` | `PromoBannerService::getActive()` (ya PromoBanner model event) | `PromoBanner::saved/deleted` event → `Cache::forget` + `PromoBannerService:113 Cache::forget` | Hame `save/delete` rouye PromoBanner | `app/Models/PromoBanner.php:80 saved/deleted, app/Services/PromoBannerService.php:113` |
| `trust_badges_active` | `TrustBadgeService::getActiveForFooter()` | `TrustBadge::saved/deleted` event → `Cache::forget` + `TrustBadgeService:145` | Hame `save/delete` TrustBadge | `app/Models/TrustBadge.php:63, app/Services/TrustBadgeService.php:145` |
| `admin.permissions.version` | `HasAdminPermissions::version()` → `Cache::rememberForever` | `Cache::forget` dar `HasAdminPermissions` | Taghir-e permission/role assignment | `app/Models/Traits/HasAdminPermissions.php:27` |
| `admin.permissions.v{version}.{userId}` | `HasAdminPermissions::canAccess()` per-user | `Cache::forget("admin.permissions.v{$version}.{$userId}")` | Invalidate dar `version()` bump | `app/Models/Traits/HasAdminPermissions.php:63` |

### 9.3 Static Memory + Cache — 2 Layer

```
Request 1: all() → self::$memory == null → Cache::rememberForever(DB query) → self::$memory = result → return
Request 1 (2nd call): all() → self::$memory !== null → return memory (haman request — bedoune cache hit)
Request 2: all() → self::$memory == null (request jadid) → Cache::rememberForever(hit) → self::$memory = cached → return
After updateGroup(): flush() → self::$memory = null + Cache::forget → next call DB query jadid
```

- **Hoshdar-e mohem:** Agar mostaghim `Setting::where(...)->update()` ya `Language::create()` dar tinker/DB editor anjam dahi, `flush()` seda zade **nemishavad** — cache kohne mimanad ta `php artisan cache:clear` ya `SettingService::flush()`.
- **Mohem:** `Cache::flush()` (kol-e cache ra pak mikonad) **hich ja estefade nashode** — faghat `Cache::forget(key)` baraye key-e moshakhas.
- Evidence:
  - `app/Services/SettingService.php:17 CACHE_ALL, 25 memory static, 53 rememberForever, 148 flush`
  - `app/Services/LocaleService.php:21 CACHE_ALL, 28 memory, 41 rememberForever, 183 flush`
  - `app/Models/PromoBanner.php:80 saved/deleted → forget`, `app/Models/TrustBadge.php:63`
  - `app/Models/Traits/HasAdminPermissions.php:27 rememberForever version, 63 forget per-user`
  - `config/cache.php:18 driver database, 43 table cache`

---

## 10. Checklist-e Taghir-e Gharardad (Che Bayad Kard)

| Taghir | File-haye Dargir | Hoshdar |
|---|---|---|
| Setoon-e JSON jadid | `database/migrations/add_column json`, `app/Models/Xxx.php: casts array`, `app/DTOs/XxxDTO.php: array`, `Blade: $model->trans('field')` | Hatman `trans()` ya `locale()` baraye khandan — mostaghim `json_decode` nakon |
| SoftDeletes be model jadid | `migration: $table->softDeletes()`, `model: use SoftDeletes` | Hame `where` ha auto `whereNull(deleted_at)` mishavand — agar niaz be hame hast `withTrashed()` |
| Fillable jadid | `model: fillable[]` | Agar faramush koni, silent null — log nist |
| Route jadid (site) | `routes/site.php: $siteRoutes` — faghat yek ja | Prefix/locale duplicate — update dar canonical + prefixed auto anjam mishavad |
| Route jadid (admin) | `routes/admin.php: middleware auth+adminPermission` + `config/permissions.php: permissions[group][action]` | Agar dar permissions nabashad, RBAC 403 trap |
| Session key jadid | `service/controller` — na hardcoded string | Hamishe dar `Service` constant tarif kon — mesl `CartService::SESSION_KEY` |
| Gateway jadid | `app/Payments/XxxGateway implements PaymentGateway` + `PaymentGatewayFactory::create` match | Toman→rial `intval(amount.'0')` ra faramush nakon |
| Image folder jadid | `ImageHandler::handle('new-folder', file)` + `public/uploads/new-folder/` | Folder auto sakhte nemishavad — ImageHandler bayad `mkdir` konad (agar nakard, khod ezafe kon) |
| Cache key jadid | `Service: const CACHE_KEY + rememberForever + flush()` | Hatman `flush()` ra dar `update/destroy` seda bezan — vagarna tinker edit cache ra invalid nemikonad |

---

## 11. Jadval-e Dalil — Evidence Table

| Mozoue | Masir-e File | Khat |
|---|---|---|
| JSON name | `app/Models/Product.php` | `24 casts name array, 253 trans()` |
| JSON title | `app/Models/HeroSlide.php` | `casts title array, desc array, badge array` |
| SoftDeletes 20 | `app/Models/*.php` | `use SoftDeletes` — Address, Bank, Blog, BlogCategory, Brand, City, Discount, InventoryTransaction, Order, OrderHistory, OrderItem, OrderShippingStatus, Product, ProductCategory, ProductSpecification, ProductVariant, Review, ShippingMethod, Specification, State |
| No SoftDeletes | `app/Models/User.php`, `Cart.php` | `HasFactory` faghat — hich SoftDeletes |
| Fillable Product | `app/Models/Product.php` | `16 fillable: product_category_id, brand_id, name, slug, price, stock, ...` |
| Casts Product | `app/Models/Product.php` | `23 casts: name array, price integer, is_active boolean, deal_ends_at datetime` |
| Admin routes | `routes/admin.php` | `24 prefix admin name admin., 34 middleware auth+adminPermission+log.admin` |
| Site routes | `routes/site.php` | `42 $siteRoutes closure, 165 canonical, 168 prefix locale name locale.` |
| Session cart | `app/Services/CartService.php` | `15 sessionKey=cart` |
| Session coupon | `app/Services/CartService.php` | `340 session('coupon'), 374 applyCoupon` |
| Session checkout | `app/Http/Controllers/Site/CheckoutController.php` | `56 checkout_address_id, 73-74 shipping, 227-229 forget checkout_*` |
| Toman→Rial | `app/Payments/ZarinPal.php` | `29 intval($amount.'0'), 59 intval($price.'0')` |
| linkable_id string | `database/migrations/2026_08_08_*.php` | `19 string linkable_id ->change()` |
| linkable_id getter | `app/DTOs/HeroSlideDTO.php` | `109 getLinkableId(): ?string` |
| Image WebP | `app/Helpers/ImageUploader.php` | `75 imagewebp 90`, `app/Helpers/Helper.php:123 imagewebp` |
| Image GD | `app/Helpers/Helper.php` | `92 imagecreatefromjpeg, 97 png, 100 webp` |
| Cache rememberForever | `app/Services/SettingService.php` | `53 Cache::rememberForever joobjoob_settings_all` |
| Cache rememberForever | `app/Services/LocaleService.php` | `41 Cache::rememberForever joobjoob_languages_all` |
| Cache forget | `app/Models/PromoBanner.php` | `80 saved → Cache::forget promo_banners_active` |
| Cache flush | `app/Services/SettingService.php` | `148 flush() → Cache::forget + memory=null` |
| Upload path | `config/paths.php` | `12 upload => env('UPLOAD_PATH','uploads')` |
| Helper autoload | `composer.json` | `30 files [jdate, hijri, date, settings, locale, format_price]` |

---

*Payan-e conventions.md — hame gharardad-ha az migrations/models/services/routes verify shode (Phase 1.5).*
