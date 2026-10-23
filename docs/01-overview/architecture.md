# Memari-e System — Joobjoob Architecture

> **Manba-e Haghighi:** `app/Providers/AppServiceProvider.php`, `app/Http/Controllers/**`, `app/Services/**`, `app/DTOs/**`, `app/Helpers/**`, `app/Models/**`, `app/Payments/**`, `app/Observers/**`, `app/View/Composers/**`, `app/Http/Middleware/**`, `bootstrap/app.php`, `routes/**`
> **Ghanoon:** Har che dar in file hast bayad dar code vojoud dashte bashad — hich chize ekhtera'i nist.

---

## 1. Negah-e Koll — High-Level Flow (Request Lifecycle)

```
Browser/Client
  │
  ▼
Route (routes/web.php → routes/admin.php + routes/site.php)
  │
  ▼
Middleware Pipeline (bootstrap/app.php)
  │  web group: SetLocale → SetTheme
  │  admin group: auth → adminPermission → log.admin
  │
  ▼
Controller (App\Http\Controllers\Admin\*  |  App\Http\Controllers\Site\*)
  │
  ├─► Service  (App\Services\* — business logic, cache, session)
  ├─► DTO      (App\DTOs\* — readonly + fromRequest, only Admin side)
  ├─► Helper   (app/Helpers/*.php — service-locator functions: setting(), locale(), format_price(), jdate)
  │
  ▼
Model (App\Models\* — Eloquent + SoftDeletes + fillable/casts + relations)
  │
  ▼
Database (MySQL `joobjoob` — migrations in database/migrations/*.php)
  │
  ▼
View (resources/views/**/*.blade.php  +  Alpine.js  +  ViewComposers)
```

**Nokte-ye Mohem:** In `Controller -> Service` yek ghanoon-e sakht nist.
Taqriban ~40% controller-ha mostaghim be Model dastresi darand:

| Controller | Mostaghim Model Call | File |
|---|---|---|
| `Site\CartController` | `Product::where`, `Cart::where` | `app/Http/Controllers/Site/CartController.php` |
| `Site\CheckoutController` | `Order::findOrFail`, `Address::find` | `app/Http/Controllers/Site/CheckoutController.php` |
| `Admin\OrderController` | `Order::with(...)->findOrFail` | `app/Http/Controllers/Admin/OrderController.php` |
| `Admin\ProductController` | `Product::create`, `Product::findOrFail` (via Service) | `app/Http/Controllers/Admin/ProductController.php` |
| `Admin\BankController` | `Bank::find` | `app/Http/Controllers/Admin/BankController.php` |

Poshte in, hich `Repository` laye'i vojoud nadarad — logic dar Service jam shode ast.

---

## 2. Laye-haye System — Controller -> Service / Helper / DTO -> Model

### 2.1 Controller Laye (Voroudi)

- **Masir:** `app/Http/Controllers/Admin/*` (21 controller) + `app/Http/Controllers/Site/*` (10 controller) + `app/Http/Controllers/Controller.php` base.
- **Vazife:** Validation via `FormRequest` (`app/Http/Requests/Admin/*` 15 file + `Site/*` 2 file), seda zadan-e Service/DTO, redirect/json, view return.
- **Mesal-e admin:** `ProductController::store(ProductRequest $request)` → `ProductDTO::fromRequest($request)` → `ProductService::create($dto)` → redirect `admin.products.index`.
- **Mesal-e site:** `CartController::add(Request $request, Product $product)` → `app(CartService::class)->add($product, qty, variantId)` → json/redirect.
- **Evidence:**
  - `app/Http/Controllers/Admin/ProductController.php: store()` uses `ProductDTO::fromRequest`
  - `app/Http/Controllers/Site/CartController.php: add(), index(), update(), remove()`
  - `app/Http/Controllers/Site/CheckoutController.php: index(), address(), shipments(), setShipment(), payment(), create(), finishZarinPal()`
  - `app/Http/Requests/Admin/ProductRequest.php` rules + `Site/CardToCardCheckoutRequest.php`

### 2.2 Service Laye (Business Logic — Markaz-e Asli)

Tamam-e mantegh-e tejari dar Service jam shode, na dar Controller na dar Model.

| Service | File | Naghsh |
|---|---|---|
| `CartService` | `app/Services/CartService.php` (384 lines) | Session+DB hybrid, isAuth() branch, content/add/update/remove/clear/mergeGuestCart/subtotal/discount/total |
| `WishlistService` | `app/Services/WishlistService.php` (80 lines) | Session `wishlist`, ids/content/has/toggle/remove/count |
| `RecentlyViewedService` | `app/Services/RecentlyViewedService.php` (105 lines) | Session `recently_viewed`, max 20, has/add/remove/clear |
| `ProductService` | `app/Services/ProductService.php` (17k) | CRUD + sync specs/variants/images/associations |
| `OrderService` | `app/Services/OrderService.php` (11k) | Create order, shipping, discount, payment token |
| `SettingService` | `app/Services/SettingService.php` (197 lines) | `rememberForever joobjoob_settings_all` + flush |
| `LocaleService` | `app/Services/LocaleService.php` (185 lines) | `rememberForever joobjoob_languages_all` + active/default/dir |
| `HeroSlideService` | `app/Services/HeroSlideService.php` | CRUD hero slides, normalizeTrusts max 5 |
| `BlogService`, `BrandService`, `SpecificationService`, `TrustBadgeService`, `PromoBannerService` | `app/Services/*Service.php` | Module CRUD + Cache::forget |
| `CouponService` | `app/Services/CouponService.php` | apply/calculateForCart, session coupon |
| `StockService` | `app/Services/StockService.php` | enrichCartItemsWithStockStatus, restoreStock |
| `OtpService` | `app/Services/OtpService.php` (218 lines) | normalizeMobile, send, verify, Kavenegar lookup |
| `AuditService`, `NotificationService`, `ShippingCalculator`, `AddressService`, `BankPaymentService` | `app/Services/*.php` | Side services |

- Service-ha be soorat transient resolve mishavand (`app(Service::class)`), faghat 3 ta singleton hastand (bakhsh-e ba'd).
- Scope-ha dar Trait `Filterable`, `HasImageAttributes`, `HasJalaliDate`, `HasMetaData`, `HasSlug` — dar `app/Traits/*.php`.
- Evidence: `app/Services/CartService.php:20 isAuth()`, `app/Services/WishlistService.php:14 sessionKey=wishlist`, `app/Services/RecentlyViewedService.php:14 sessionKey=recently_viewed`

### 2.3 DTO Laye (Data Transfer — Faghat Admin)

- **Masir:** `app/DTOs/*.php` — 9 file: `ProductDTO`, `HeroSlideDTO`, `BlogDTO`, `BlogCategoryDTO`, `BrandDTO`, `ProductCategoryDTO`, `PromoBannerDTO`, `SpecificationDTO`, `TrustBadgeDTO`.
- **Olgou:** `readonly class` + `fromRequest(Request $request): self` + private normalize + getters.
- **Joobjoob estefade:** DTO faghat baraye form-haye pichideye Admin (product ba variants/specs/images, hero ba trusts/linkable). Site controller-ha DTO nadarand — mostaghim `$request->input()`.
- **Mesal — ProductDTO:**

```php
// app/DTOs/ProductDTO.php
readonly class ProductDTO {
  public function __construct(
    private int $productCategoryId, private ?int $brandId, private array $name, // json fa/en
    private string $slug, private ?array $shortDescription, private ?array $description,
    private int $price, private ?int $discountPrice, private int $stock, private ?int $weight,
    private bool $isActive, private array $specs, private array $variants, // ...
    private array $images, private array $deleteImages, private ?int $mainImageId
  ) {}
  public static function fromRequest(ProductRequest $request): self { /* normalizeSpecs, normalizeTextSpecs */ }
  public function getName(): array { return $this->name; }
}
```

- **Mesal — HeroSlideDTO:** `normalizeTrusts()` — faghat item-hayi ke `fa` ya `en` matn darand, be shekl-e `{icon, text:{fa,en}}`, `array_slice(..., 0, HeroSlide::MAX_TRUST_ITEMS)` max 5.
- **Payment DTO:** `app/Payments/PaymentPostDTO.php` + `PaymentVerifyDTO.php` — baraye khorouji-e getBankToken/verifyTransaction.
- Evidence:
  - `app/DTOs/ProductDTO.php:8 readonly class, 41 fromRequest, 112 normalizeSpecs`
  - `app/DTOs/HeroSlideDTO.php:13 readonly class, 36 fromRequest, 71 normalizeTrusts, 96 getLinkableType, 109 getLinkableId`
  - `app/Payments/PaymentPostDTO.php`, `app/Payments/PaymentVerifyDTO.php`

### 2.4 Helper Laye (Service Locator — Global Functions)

Helper-ha tavasot-e `composer.json autoload.files` dar hame ja load mishavand — hich import lazem nist.

| Helper File | Function | Posht-e Sahn-e |
|---|---|---|
| `app/Helpers/settings.php` | `setting(key, default)` → `app(SettingService)->get()` | `Cache::rememberForever joobjoob_settings_all` |
| `app/Helpers/settings.php` | `settingOverride(key, default)` → `getStored()` | Faghat agar admin save karde |
| `app/Helpers/locale.php` | `locale()` → `app(LocaleService)` | Singleton service accessor |
| `app/Helpers/locale.php` | `localizedRoute(name, params, code)` | canonical vs `locale.` prefix |
| `app/Helpers/locale.php` | `localizedUrl(code)` | hreflang / switcher |
| `app/Helpers/format_price.php` | `format_price(amount, locale)` → `NumberHelper::formatPrice` | fa/ar → `٬` (U+066C), en/tr → `,` |
| `app/Helpers/NumberHelper.php` | `NumberHelper::formatPrice`, `persian2LatinDigit` | Digit convert + thousands sep |
| `app/Helpers/date.php`, `jdate.php`, `hijri.php` | `jdate_formate()`, `hijri_date()` | Jalali/Hijri calendar |
| `app/Helpers/Helper.php` | `Helper::isMobile()`, `persian2LatinDigit()`, `uploadImage()` | Static utils + GD WebP |
| `app/Helpers/ImageHandler.php` | `ImageHandler::handle(folder, file, sizes)` | → `ImageUploader::upload` + `ImageResizer::resize` |
| `app/Helpers/Sms.php` | `Sms::sendLookup(template, tokens, mobile)` | Kavenegar REST lookup |

- Evidence:
  - `composer.json:30 files: ["app/Helpers/jdate.php","app/Helpers/hijri.php","app/Helpers/date.php","app/Helpers/settings.php","app/Helpers/locale.php","app/Helpers/format_price.php"]`
  - `app/Helpers/settings.php:10 setting()`, `app/Helpers/locale.php:14 locale()`, `app/Helpers/format_price.php:14 format_price()`
  - `app/Helpers/Helper.php:6 class Helper, 36 uploadFile, 55 uploadImage (GD imagewebp)`
  - `app/Helpers/ImageHandler.php:12 handle(), 31 delete()`

### 2.5 Model Laye (Eloquent)

- **Masir:** `app/Models/*.php` — 33 model.
- **Mored-e estefade:** `fillable` + `casts` + `relations` + `scopes` + `accessors`. Hich Repository nadarad.
- **JSON multilingual:** `title`, `name`, `description`, `badge`, `short_description` be soorat `json` + `casts => array` + helper `trans(field)` dar Product/Category/Blog.
- **SoftDeletes:** 20 model (evidence dar `conventions.md`).
- **Relations nemoune:** `Product::category()`, `images()`, `specifications()`, `variants()`, `properties()`, `related()`, `complement()`.
- Evidence:
  - `app/Models/Product.php:14 HasFactory, SoftDeletes, 16 fillable, 23 casts (name array, price integer), 44 category(), 55 images(), 65 specifications()`
  - `app/Models/HeroSlide.php:20 fillable (linkable_type, linkable_id string), 78 getLinkableUrlAttribute`
  - `app/Models/Order.php:14 SoftDeletes, fillable (user_id, address, items, shipping, payment)`

---

## 3. Singleton Service-ha — Cart / Wishlist / RecentlyViewed

Dar `app/Providers/AppServiceProvider.php::register()` se service be soorat singleton sabt shode-and — ta dar yek request yek instance bishar estefade nashavad.

```php
// app/Providers/AppServiceProvider.php:23-27
public function register(): void {
    $this->app->singleton(RecentlyViewedService::class);
    $this->app->singleton(WishlistService::class);
    $this->app->singleton(CartService::class);
}
```

| Service | Session Key | Sakhtar-e Dakheli | Method-haye Asli | File |
|---|---|---|---|---|
| `CartService` | `cart` (mehman) + `carts` table (login) | `isAuth()` branch → `dbContent()` vs `sessionContent()`; rowId = `md5(product_id+variantId+color+size)` | `content(), add(), update(), remove(), clear(), mergeGuestCart(), subtotal(), discount(), total(), count(), totalWeight(), roundedWeight(), applyCoupon(), toArray()` | `app/Services/CartService.php:14 sessionKey=cart, 20 isAuth(), 28 content(), 249 add()` |
| `WishlistService` | `wishlist` | `list<int> product_id` be tartib-e ezafe shodan | `ids(), content(), has(), toggle(), remove(), count()` — `toggle()` true/false bar migardanad | `app/Services/WishlistService.php:14 sessionKey=wishlist, 52 toggle()` |
| `RecentlyViewedService` | `recently_viewed` | `list<int> product_id`, max 20, jadidtarin aval (unshift) | `ids(), content(limit), add(), remove(), clear(), count(), has()` — `add()` duplicate ra be aval miyavard | `app/Services/RecentlyViewedService.php:14 sessionKey=recently_viewed, 15 maxItems=20, 52 add()` |

- **CartService hybrid:** Agar `auth()->check()` → az `carts` table (`Cart` model ba `user_id, product_id, variant_id, qty, price, original_price, stock, weight, color, size, image`). Vagarna az session array. `mergeGuestCart()` dar login — guest items ra be DB montaghel mikonad ba `min(existing.qty+qty, stock)`.
- **Wishlist/RecentlyViewed faghat session:** Bedoun-e niaz be login — `content()` ba `Product::with(['images','category'])->whereIn(ids)->get()` + `sortBy(array_search)` tartib ra hefz mikonad.
- **Baghiye Service-ha transient:** `app(SettingService::class)`, `app(LocaleService::class)` har bar instance jadid — vali dakhel khodeshan static memory + rememberForever darand.
- Evidence: `app/Providers/AppServiceProvider.php:24-27 singleton`, `app/Services/CartService.php:14-22`, `app/Services/WishlistService.php:12-23`, `app/Services/RecentlyViewedService.php:12-25`

---

## 4. ViewComposer-ha — 4 Composer Baraye Layout-ha

Dar `AppServiceProvider::boot()` 4 ViewComposer sabt shode — mantegh-e amade-sazi az Blade kharej shode ast.

```php
// app/Providers/AppServiceProvider.php:41-50
View::composer('*', LocaleComposer::class);
View::composer('layouts.site.app', SiteLayoutComposer::class);
View::composer('layouts.admin.app', AdminLayoutComposer::class);
View::composer('components.footer', FooterComposer::class);
```

| Composer | File | View Target | Dade-hayi ke Share Mikonad | Vazife |
|---|---|---|---|---|
| `LocaleComposer` | `app/View/Composers/LocaleComposer.php` (30 lines) | `*` (hame-ye view-ha) | `currentLocale`, `direction` (rtl/ltr), `digitFamily` (persian/arabic/latin), `locales` (active list) | Zabaan, jahat, ragham — az `LocaleService` |
| `SiteLayoutComposer` | `app/View/Composers/SiteLayoutComposer.php` (39 lines) | `layouts.site.app` | `announceMessages` (3 ta az setting), `firstAnnounce`, `cartCount`, `wishlistCount`, `unreadNotificationCount` | Header site — cart/wishlist counter + announce bar |
| `AdminLayoutComposer` | `app/View/Composers/AdminLayoutComposer.php` (23 lines) | `layouts.admin.app` | `unreadNotificationCount` (admin) | Header admin — notification badge |
| `FooterComposer` | `app/View/Composers/FooterComposer.php` (22 lines) | `components.footer` | `footerTrustBadges` | Footer — enamad/payment badges via `TrustBadgeService::getActiveForFooter()` |

- **LocaleComposer** digar fallback-e tekrari ra hazf karde — ghablan dar har view `app()->getLocale() ?? 'fa'` tekrar mishod.
- **SiteLayoutComposer** az `CartService::count()` va `WishlistService::count()` estefade mikonad — na az session mostaghim.
- **AdminLayoutComposer** az `NotificationService::getAdminUnreadCount()` estefade mikonad.
- Evidence:
  - `app/Providers/AppServiceProvider.php:42 View::composer('*', LocaleComposer::class)`
  - `app/View/Composers/LocaleComposer.php:16 __construct(LocaleService), 18 compose()`
  - `app/View/Composers/SiteLayoutComposer.php:16 __construct(CartService, WishlistService, NotificationService), 22 compose()`
  - `app/View/Composers/AdminLayoutComposer.php:11 __construct(NotificationService), 15 compose()`
  - `app/View/Composers/FooterComposer.php:13 __construct(TrustBadgeService), 18 compose()`

---

## 5. Middleware Pipeline — SetLocale / SetTheme / AdminPermission / LogAdminRequests

### 5.1 Sabt-e Middleware (bootstrap/app.php)

```php
// bootstrap/app.php:18-35
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'adminPermission' => AdminPermission::class,
        'log.admin'       => LogAdminRequests::class,
    ]);
    $middleware->web([
        SetLocale::class,
        SetTheme::class,
    ]);
    $middleware->redirectGuestsTo(function (Request $request) {
        return str_starts_with($request->path(), 'admin')
            ? route('admin.login')
            : route('login');
    });
})
```

| Middleware | File | Scope | Vazife |
|---|---|---|---|
| `SetLocale` | `app/Http/Middleware/SetLocale.php` (124 lines) | `web` global — ghabl az route resolve | Tashkhis-e zabaan ba 7 marhale priority, set `app()->setLocale`, `view()->share(direction, currentLocale)` |
| `SetTheme` | `app/Http/Middleware/SetTheme.php` (19 lines) | `web` global | Khondan-e cookie `site_theme` → `view()->share('activeTheme', 'light-neumorphism')` |
| `AdminPermission` | `app/Http/Middleware/AdminPermission.php` (57 lines) | `alias adminPermission` — faghat `Route::middleware(['auth','adminPermission'])` dar `routes/admin.php:34` | RBAC: superadmin bypass, free list, `canAccess(routeName)` cache |
| `LogAdminRequests` | `app/Http/Middleware/LogAdminRequests.php` (36 lines) | `alias log.admin` — faghat admin group | Log-e hame request-haye admin be `request_logs` (payload bedoun password) |

### 5.2 SetLocale — 7 Marhale-ye Priority (SEO-aware)

```
1) URL prefix (site only) — /en/... → locale = en, save session+user, 301 if inactive/default
2) ?lang= (site, backward compat) — ?lang=en → 301 to /en/...
3) Tek-zabane mode — locale = default (ignore ?lang)
4) ?lang= (admin only) — if active → save session+user
5) User preference (admin only) — user->locale if active
6) Session locale (admin only) — session('locale') if active
7) Default — locale()->defaultCode(), save session
→ app()->setLocale(locale) + view()->share(direction, currentLocale)
```

- SEO: `/fa/...` (pishfarz pishvandar) → 301 be `/...` (canonical). `/en/...` → 301 be `/...` agar `en` gheir-faal shavad.
- `SetLocale` ghabl az route resolve ejra mishavad — pas `route('locale')` nadarad, az `explode('/', $request->path())` mikhanad.
- Evidence: `app/Http/Middleware/SetLocale.php:28 handle(), 40 urlLocale, 46 canonicalPath(), 114 prefixedPath()`

### 5.3 AdminPermission — RBAC Gate

```
1) !user → 401 json ya redirect admin.login
2) !is_admin && !roles()->exists() → 403 ya redirect home (jalogiri az loop)
3) is_admin (superadmin) → pass (hichvaght ghofl nemishavad)
4) routeName in config('permissions.free') → pass (dashboard, logout, ckeditor.upload)
5) else → abort_unless(user->canAccess(routeName), 403)
   canAccess() = Cache::rememberForever admin.permissions.version + per-user permission cache
```

- Evidence: `app/Http/Middleware/AdminPermission.php:20 handle(), 34 is_admin check, 49 free list, 53 canAccess`
- `app/Models/Traits/HasAdminPermissions.php:27 rememberForever admin.permissions.version`
- `config/permissions.php:11 free = [admin.ckeditor.upload, admin.logout, admin.dashboard.index]`

### 5.4 LogAdminRequests — Audit Trail

```php
// app/Http/Middleware/LogAdminRequests.php:15-32
$response = $next($request);
if (Auth::check()) {
    RequestLog::create([
        'user_id' => Auth::id(), 'method' => $request->method(),
        'url' => $request->fullUrl(), 'ip' => $request->ip(),
        'status_code' => $response->getStatusCode(),
        'payload' => $payload // unset password, _token — cast json dar model
    ]);
}
```

- Evidence: `app/Http/Middleware/LogAdminRequests.php:13 handle(), 24 RequestLog::create`, `app/Models/RequestLog.php`

---

## 6. Payment — Strategy Pattern (Gateway)

### 6.1 Interface + Factory

```php
// app/Payments/PaymentGateway.php:7
interface PaymentGateway {
    public function getBankToken(int $amount, Order $order): PaymentPostDTO;
    public function verifyTransaction(array $callbackData, int $price, ?Order $order = null): PaymentVerifyDTO;
    public function redirect(PaymentPostDTO $postData, Order $order): void;
}

// app/Payments/PaymentGatewayFactory.php:14
class PaymentGatewayFactory {
    public static function create(Bank $bank, string $siteName = ''): PaymentGateway {
        return match ($bank->bank_type) {
            'zarinPal'     => new ZarinPal($bank, $siteName),
            'card_to_card' => new ZarinPal($bank, $siteName), // handled separately in OrderService
            default => throw new InvalidArgumentException("Unsupported bank type: {$bank->bank_type}"),
        };
    }
}
```

| Component | File | Vazife |
|---|---|---|
| `PaymentGateway` | `app/Payments/PaymentGateway.php` (23 lines) | Interface — 3 method: getBankToken, verifyTransaction, redirect |
| `ZarinPal` | `app/Payments/ZarinPal.php` (115 lines) | Implement — merchant az `bank->config MerchantId`, callback route `checkout.finish.zarin-pal` |
| `PaymentGatewayFactory` | `app/Payments/PaymentGatewayFactory.php` (22 lines) | Factory — match bank_type → new ZarinPal |
| `PaymentPostDTO` | `app/Payments/PaymentPostDTO.php` | DTO khorouji-e getBankToken (status + Authority) |
| `PaymentVerifyDTO` | `app/Payments/PaymentVerifyDTO.php` | DTO khorouji-e verifyTransaction (status + RefID) |
| `BankPaymentService` | `app/Services/BankPaymentService.php` | Orchestrator — entekhab-e bank + gateway |
| `OrderService` | `app/Services/OrderService.php` | Farاخاني-e `BankPaymentService::pay()` dar `create()` |

### 6.2 ZarinPal — Toman → Rial

```php
// app/Payments/ZarinPal.php:29 — mohem: toman *10 = rial
$amountRial = intval($amount . '0');
// request:  POST https://payment.zarinpal.com/pg/v4/payment/request.json
// verify:   POST https://payment.zarinpal.com/pg/v4/payment/verify.json
// redirect: https://payment.zarinpal.com/pg/StartPay/{Authority}
```

- Agar `Status !== OK` dar callback → `PaymentVerifyDTO::fromData('failed')`.
- Code 100 ya 101 dar verify → success.
- Evidence: `app/Payments/ZarinPal.php:14 const EP_REQUEST/EP_VERIFY/EP_REDIRECT, 29 amountRial, 38 sendJsonRequest, 56 verifyTransaction`

---

## 7. Observer Pattern — OrderObserver

```php
// app/Providers/AppServiceProvider.php:36
Order::observe(OrderObserver::class);

// app/Observers/OrderObserver.php
class OrderObserver {
    public function __construct(StockService $stockService) {}
    public function created(Order $order): void  { /* OrderHistory::create */ }
    public function updated(Order $order): void  {
        // 1) OrderHistory if order_status or shipping_status_id dirty
        // 2) restoreStock if wasChanged('order_status') && now == 'cancelled' && previous in [paid, deposit_paid, pending_confirmation, wait_for_verification]
    }
}
```

| Event | Amal | File |
|---|---|---|
| `created` | Agar `isDirty(order_status/shipping_status_id)` → `OrderHistory::create(order_id, order_status, shipping_status_id)` | `app/Observers/OrderObserver.php:19 created()` |
| `updated` | Haman OrderHistory + agar `cancelled` shod va ghablan `paid/deposit_paid/pending_confirmation/wait_for_verification` → `StockService::restoreStock(order)` | `app/Observers/OrderObserver.php:30 updated(), 42 wasChanged` |

- **Faghat yek Observer:** `app/Observers/OrderObserver.php` — hich Observer-e digari vojoud nadarad.
- Evidence: `app/Providers/AppServiceProvider.php:36 Order::observe`, `app/Observers/OrderObserver.php:19 created, 30 updated`

---

## 8. DTO Pattern — Readonly + fromRequest

- **Tedad:** 9 DTO dar `app/DTOs/*.php` — hame `readonly class`.
- **Olgou-ye yeksan:**

```php
readonly class XxxDTO {
    public function __construct(public array $title, ...) {}
    public static function fromRequest(XxxRequest $request): self {
        return new self(title: ['fa'=> $request->input('title.fa'), 'en'=> $request->input('title.en')], ...);
    }
    private static function normalize...() {}
    public function get...(): mixed {}
}
```

| DTO | File | fromRequest Input | Normalize | Getters |
|---|---|---|---|---|
| `ProductDTO` | `app/DTOs/ProductDTO.php` (281 lines) | name, slug, price, specs→normalizeSpecs, textSpecs→normalizeTextSpecs, variants, properties, images, deleteImages, mainImageId/mainNewIndex | `normalizeSpecs` [parent_id→[child_id]], `normalizeTextSpecs` [spec_id→value] | 18 getter (getName, getPrice, getSpecs...) |
| `HeroSlideDTO` | `app/DTOs/HeroSlideDTO.php` (114 lines) | title, desc, badge, button_active, link_type/value, price, currency, rating/viewers, trusts, image | `normalizeTrusts` max 5 | `getLinkableType(): ?string` (match → Product::class/page/url/null), `getLinkableId(): ?string` |
| `BlogDTO`, `BlogCategoryDTO`, `BrandDTO`, `ProductCategoryDTO`, `PromoBannerDTO`, `SpecificationDTO`, `TrustBadgeDTO` | `app/DTOs/*.php` | name/title, slug, description, parent_id, icon, image | sade | getter sade |

- **Site DTO:** `PaymentPostDTO`, `PaymentVerifyDTO` dar `app/Payments/` — baraye gateway strategy.
- Evidence: `app/DTOs/ProductDTO.php:8 readonly, 41 fromRequest`, `app/DTOs/HeroSlideDTO.php:13 readonly, 36 fromRequest`

---

## 9. Cheez-hayi Ke Vojoud Nadarand — No Repository / Action / Event / Job / Policy

> **Verify shode ba:** `ls F:/Projects/joobjoob/app/Repositories` → Not found, `ls app/Actions` → Not found, `ls app/Events` → Not found, `ls app/Jobs` → Not found, `ls app/Policies` → Not found

| Pattern | Vaziat dar Joobjoob | Jaye Gozin |
|---|---|---|
| `Repository` | ❌ Hich `app/Repositories/*` nist | Mostaghim `Model::where` dar Service/Controller |
| `Action` (single-use class) | ❌ Hich `app/Actions/*` nist | Logic dar `Service::create/update/delete` |
| `Event/Listener` | ❌ Hich `app/Events/*` ya `app/Listeners/*` nist | Synchronous call dar Service: `AuditService::log()`, `NotificationService::notifyUser()` |
| `Job/Queue` | ❌ Hich `app/Jobs/*` nist — vali `queue=database` config shode | Hich safi dar code dispatch nemishavad — `schedule: orders:cancel-expired everyFiveMinutes` faghat command ast |
| `Policy` (Gate object) | ❌ Hich `app/Policies/*` nist | `Gate::define('admin', fn=>isAdmin())` + `AdminPermission` middleware + `HasAdminPermissions::canAccess()` |

- **In entekhab amdi ast:** Team laze nadide abstraction-e ezafi — hame chiz dar Service + Model + Middleware hal shode.
- Evidence: `ls app/` — faghat `Console, DTOs, Helpers, Http, Models, Observers, Payments, Providers, Rules, Services, Traits, View` — hich kodam az 5 folder-e bala nist.

---

## 10. Helper be Onvan-e Service Locator — Poshte Sahn-e

Helper-ha container ra mostaghim seda mizanand — control inversion-e kamel nist, vali baraye Blade/Config/Table rahat ast.

```
View/Config
  │
  ├─► setting('site_name')          ──► app(SettingService::class)->get('site_name')
  │                                      └─► Cache::rememberForever('joobjoob_settings_all') → Setting::all()->map
  ├─► locale()->active()             ──► app(LocaleService::class)->active()
  │                                      └─► Cache::rememberForever('joobjoob_languages_all') → Language::ordered()->get()
  ├─► localizedRoute('products.show', ['product'=>$p])  ──► route('products.show') or route('locale.products.show', ['locale'=>code])
  ├─► format_price(5000000)          ──► NumberHelper::formatPrice(5000000, app()->getLocale())
  │                                      └─► match('fa','ar' => U+066C '٬', default => ',')
  ├─► jdate_formate($date, 'Y/m/d')  ──► jalali calendar via app/Helpers/jdate.php (MorTez Jabari)
  └─► Helper::uploadImage(file, folder, request) ──► GD: imagecreatefromjpeg/png/webp → imagewebp 90 → public/uploads/{folder}/big/medium/
```

| Helper | File | Service Locator Call | Cache Key |
|---|---|---|---|
| `setting()` | `app/Helpers/settings.php:10` | `app(SettingService::class)->get(key)` | `joobjoob_settings_all` |
| `locale()` | `app/Helpers/locale.php:14` | `app(LocaleService::class)` | `joobjoob_languages_all` |
| `format_price()` | `app/Helpers/format_price.php:14` | `NumberHelper::formatPrice` | — |
| `ImageHandler::handle()` | `app/Helpers/ImageHandler.php:12` | `ImageUploader::upload` + `ImageResizer::resize` | — (filesystem `public/uploads`) |
| `Sms::sendLookup()` | `app/Helpers/Sms.php: sendLookup` | `Http::post(Kavenegar base_url)` | — |

- **Autoload:** `composer.json:30-37` — 6 helper file hamishe load hastand, niaz be `use` nist.
- Evidence:
  - `composer.json:30 files: [jdate.php, hijri.php, date.php, settings.php, locale.php, format_price.php]`
  - `app/Helpers/settings.php:10 function setting()`, `app/Helpers/locale.php:14 function locale(), 23 localizedRoute()`, `app/Helpers/format_price.php:14 format_price()`
  - `app/Services/SettingService.php:17 CACHE_ALL=joobjoob_settings_all, 53 rememberForever`, `app/Services/LocaleService.php:21 CACHE_ALL=joobjoob_languages_all, 41 rememberForever`

---

## 11. Dalil va Masir-e File-ha — Evidence Table

| Mozoue | Masir-e File | Khat-e Kelidi |
|---|---|---|
| Singleton sabt | `app/Providers/AppServiceProvider.php` | `register:24 singleton(CartService), 25 singleton(WishlistService), 26 singleton(RecentlyViewedService)` |
| Observer sabt | `app/Providers/AppServiceProvider.php` | `boot:36 Order::observe(OrderObserver::class)` |
| Gate admin | `app/Providers/AppServiceProvider.php` | `boot:38 Gate::define('admin', fn($u)=>$u->isAdmin())` |
| ViewComposer | `app/Providers/AppServiceProvider.php` | `42 View::composer('*', LocaleComposer), 44 SiteLayoutComposer, 47 AdminLayoutComposer, 50 FooterComposer` |
| Middleware alias | `bootstrap/app.php` | `19 alias adminPermission, 20 log.admin, 24 web [SetLocale, SetTheme]` |
| SetLocale | `app/Http/Middleware/SetLocale.php` | `28 handle(), 40 urlLocale, 46 canonical 301, 114 canonicalPath()` |
| SetTheme | `app/Http/Middleware/SetTheme.php` | `12 handle → cookie site_theme → view()->share(activeTheme)` |
| AdminPermission | `app/Http/Middleware/AdminPermission.php` | `20 handle, 34 is_admin+roles check, 49 free list, 53 canAccess` |
| LogAdminRequests | `app/Http/Middleware/LogAdminRequests.php` | `13 handle → RequestLog::create` |
| CartService | `app/Services/CartService.php` | `14 sessionKey=cart, 20 isAuth, 28 content, 249 add, 357 count` |
| WishlistService | `app/Services/WishlistService.php` | `14 sessionKey=wishlist, 52 toggle` |
| RecentlyViewedService | `app/Services/RecentlyViewedService.php` | `14 recently_viewed, 15 maxItems=20, 52 add` |
| PaymentGateway | `app/Payments/PaymentGateway.php` | `7 interface, 12 getBankToken, 17 verifyTransaction, 22 redirect` |
| ZarinPal | `app/Payments/ZarinPal.php` | `14 EP_REQUEST, 29 amountRial intval(amount.'0'), 38 sendJsonRequest` |
| Factory | `app/Payments/PaymentGatewayFactory.php` | `14 create(Bank) match bank_type` |
| OrderObserver | `app/Observers/OrderObserver.php` | `19 created, 30 updated, 42 wasChanged cancelled → restoreStock` |
| ProductDTO | `app/DTOs/ProductDTO.php` | `8 readonly, 41 fromRequest, 96 getLinkableId string` |
| HeroSlideDTO | `app/DTOs/HeroSlideDTO.php` | `13 readonly, 36 fromRequest, 71 normalizeTrusts, 93 array_slice max 5` |
| LocaleComposer | `app/View/Composers/LocaleComposer.php` | `16 __construct(LocaleService), 18 compose(*)` |
| SiteLayoutComposer | `app/View/Composers/SiteLayoutComposer.php` | `16 CartService WishlistService NotificationService, 22 compose` |
| AdminLayoutComposer | `app/View/Composers/AdminLayoutComposer.php` | `11 NotificationService, 15 compose` |
| FooterComposer | `app/View/Composers/FooterComposer.php` | `13 TrustBadgeService, 18 compose` |
| Helper autoload | `composer.json` | `30 files [jdate, hijri, date, settings, locale, format_price]` |
| setting() | `app/Helpers/settings.php` | `10 setting() → SettingService::get` |
| locale() | `app/Helpers/locale.php` | `14 locale() → LocaleService, 23 localizedRoute()` |
| No Repo/Action/Event/Job/Policy | `app/` ls | `Console, DTOs, Helpers, Http, Models, Observers, Payments, Providers, Rules, Services, Traits, View` — hich kodam nist |

---

## 12. Nemoudar-e Vabastegi — Dependency Graph

```
Controller
  ├─► Service (via app() / __construct DI)
  │     ├─► Model (Eloquent)
  │     ├─► Helper (ImageHandler, Sms, NumberHelper)
  │     └─► Cache (rememberForever — SettingService, LocaleService, TrustBadgeService)
  ├─► DTO (via fromRequest) ──► Model (persist in Service)
  └─► Helper function (setting(), locale(), format_price()) ──► Service ──► Cache/Model

Middleware (SetLocale)
  └─► LocaleService ──► Language model ──► Cache

Observer (OrderObserver)
  ├─► OrderHistory model
  └─► StockService ──► InventoryTransaction model

ViewComposer
  ├─► LocaleService / CartService / WishlistService / TrustBadgeService
  └─► Model/Cache

Payment (BankPaymentService → Factory → ZarinPal)
  ├─► Bank model (config MerchantId)
  ├─► Order model
  └─► Http (Kavenegar/ZarinPal REST) + Payment DTOs
```

---

## 13. Gharardad-haye Na-Negahstare Shode

- **Hich queue dispatch nist:** `queue=database` config shode vali `dispatch()` dar code nist — schedule faghat `CancelExpiredOrders` command ra har 5 daghighe ejra mikonad (`routes/console.php` ya `app/Console/Kernel`).
- **Hich event broadcast nist:** `broadcast=log` vali hich `event(new ...)` nist.
- **Transaction handling:** `ProductService`, `OrderService`, `SettingService` az `DB::transaction` estefade mikonand — ba `Cache::forget` ba'd az commit.
- **File upload:** Hame aks-ha be `public/uploads/{folder}/` miravand via `config('paths.upload')` (default `uploads`) — na `storage/app`.

---

*Payan-e architecture.md — hame mored-ha az code verify shode (Phase 1.5). Agar file ya khat peyda nashod, be ma begou ta eslah konim.*
