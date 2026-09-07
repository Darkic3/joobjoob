# Cross-Module Dependencies — گراف واقعی وابستگی سراسری

> **Status:** ✅ verified-trace — بازسازی کامل از کد واقعی (2026-09-06) — نسخه قبلی ساختگی بود
> **Manba:** `app/Services/*`, `app/Http/Controllers/**`, `app/Payments/*`, `app/Helpers/*`, `app/Providers/AppServiceProvider.php`, `app/Observers/*`, `app/Console/Commands/*`, `routes/console.php`
> **Ghanoon:** Hich class-e ekhtera'i inja nist — har edge ba `file:line` az code-e haghighi poshtibani mishavad. (MellatGateway, Province, CartItem, StockMovement, OtpCode, SmsLog, Shipment, Payment, AuthService, BankService, PaymentCallbackController, CouponController, AddressController — hichkodam dar code vojoud nadarand.)

---

## 1. High-Level Module Graph (mermaid — faghat class-haye haghighi)

```mermaid
graph TD
    subgraph Site
        CHK[Site/CheckoutController] --> CS[CartService]
        CHK -->|app()| OS[OrderService]
        CHK -->|app()| CPS[CouponService]
        CHK -->|app()| STS[StockService]
        CHK -->|static| ADDR[AddressService]
        CHK -->|static| CITS[CityService]
        CHK -->|static| SC[ShippingCalculator]
        CHK -->|direct| ORM1[Order / Bank / Address / ShippingMethod]
        CT[Site/CartController] --> CS
        ST[Site/ProductController] -->|direct| ORM2[Product / Review / ReviewVote]
        ST --> RVS[RecentlyViewedService]
        ST --> TBS[TrustBadgeService]
        HM[Site/HomeController] --> HS[HeroSlideService]
        HM --> TBS
        HM --> PB[PromoBannerService]
        HM --> RVS
        OT[Site/OtpController] --> OTS[OtpService]
        WL[Site/WishlistController] --> WLS[WishlistService]
    end

    subgraph Admin
        PC[Admin/ProductController] --> PS[ProductService]
        OC[Admin/OrderController] --> OS
        OC -->|direct| ORM3[Order / OrderShippingStatus / ShippingMethod]
        NB[Admin/NotificationController] --> NS[NotificationService]
        STG[Admin/SettingController] --> SES[SettingService]
        DBC[Admin/DashboardController] --> DSH[DashboardService]
    end

    subgraph Core
        OS --> BPS[BankPaymentService]
        OS --> STS
        OS -->|static| AS[AuditService]
        OS -->|app()| NS
        BPS --> F[PaymentGatewayFactory]
        F --> ZP[ZarinPal]
        CS -->|app()| STS
        CS -->|app()| CPS
        STS -->|static| AS
        STS --> ORM4[Product / ProductVariant / InventoryTransaction]
        SC --> CHAP[ChaparShipment — curl API]
        OTS --> SMS[Sms helper — Kavenegar]
        OTS --> NS
        OTS --> WDS[WelcomeDiscountService]
        SES --> HELP[Helpers: ImageHandler / Uploader]
    end
```

---

## 2. Textual Edge List — From → Depends On → via

| From | Depends On | Via | Evidence |
|---|---|---|---|
| `Site\CheckoutController` | `CartService` | constructor promotion | `Site/CheckoutController.php:23` |
| `Site\CheckoutController` | `OrderService` | `app(OrderService::class)` | `:204, 274, 282, 290, 298` |
| `Site\CheckoutController` | `CouponService` | `app(CouponService::class)` | `:121, 132, 216` |
| `Site\CheckoutController` | `StockService` | `app(StockService::class)` | `:309-310 validateStock` |
| `Site\CheckoutController` | `AddressService` / `CityService` / `ShippingCalculator` | static call | `:54, 44, 59` |
| `Site\CheckoutController` | `Order`, `Bank`, `Address`, `ShippingMethod`, `Discount` | **direct Eloquent** | `:167, 178, 179` + imports `:8-10` |
| `Site\CartController` | `CartService` | constructor | `Site/CartController.php:14` |
| `Site\CartController` | `Product` | **direct Eloquent** (id OR slug) | `:87` |
| `Site\CartController` | `StockService` / `AddressService` / `StateService` | `app()` / static | `:161, 43, 44` |
| `Site\ProductController` | `Product`, `Review`, `ReviewVote`, `Brand`, `ProductCategory` | **direct Eloquent** (index/category/show/reviews) | `Site/ProductController.php:26, 132-200, 312-479` |
| `Site\ProductController` | `RecentlyViewedService`, `TrustBadgeService` | constructor readonly | `:19-22` |
| `Site\ProductController` | `NotificationService` | `app()` dar `storeReview` | `:361` |
| `Site\HomeController` | `HeroSlideService`, `TrustBadgeService`, `PromoBannerService`, `RecentlyViewedService` | constructor | `Site/HomeController.php:23-28` |
| `Site\HomeController` | `Product`, `ProductCategory`, `Review`, `Blog` | **direct Eloquent** | `:33, 42, 69-77` |
| `Site\HomeController` | `ProductCategorySeeder` + 4 Seeder-e digar | `Artisan::call('db:seed')` (faghat local) | `:126-149 seed()` |
| `Site\OtpController` | `OtpService` | constructor | `Site/OtpController.php:13` |
| `Site\OtpController` | `CartService::mergeGuestCart` | `app()` baad az login-e OTP | `:139` |
| `Site\DashboardController` | `Order`, `Address` | **direct Eloquent** | `Site/DashboardController.php:38, 50, 83, 174` |
| `Site\DashboardController` | `AddressService`, `StateService`, `CityService`, `NotificationService` | static / `app()` | `:94, 95, 105, 65` |
| `Site\WishlistController` | `WishlistService` + `Product` | constructor + direct | `Site/WishlistController.php:13, 22` |
| `Site\BlogController` | — | `Blog`, `BlogCategory` — **bedoun service** | `Site/BlogController.php:6-7` |
| `Site\PageController` | `NotificationService` (`app()`) | `ContactRequest` direct | `Site/PageController.php:6-7` |
| `Admin\ProductController` | `ProductService` (:18) | `ProductCategory`, `Brand`, `Specification`, `Product` direct (view data) | `Admin/ProductController.php:20-31, 93-100` |
| `Admin\OrderController` | `OrderService` (constructor :20) | `Order`, `OrderShippingStatus`, `ShippingMethod` **direct** | `Admin/OrderController.php:27, 62-76` |
| `Admin\OrderController` | `NotificationService` | `app()` | `:92` |
| `Admin\DashboardController` | `DashboardService` | constructor | `Admin/DashboardController.php:9` |
| `Admin\LanguageController` | `LocaleService` (`app()` + `flush()`) | `Language` **direct** | `Admin/LanguageController.php:18, 25, 35, 44` |
| `Admin\BlogController` | `BlogService` (:16) | `Blog`, `BlogCategory` **direct** | `Admin/BlogController.php:20-45` |
| `Admin\BrandController` / `HeroSlideController` / `PromoBannerController` / `SpecificationController` / `TrustBadgeController` | XService (constructor) | model-e khod baraye create/edit view | `:16-17` dar har file |
| `Admin\AuditLogController` | — | `AuditLog`, `Order`, `Product` — **bedoun service** | `Admin/AuditLogController.php:18, 57, 68` |
| `Admin\DiscountController` | — | `Discount`, `Product`, `Brand`, `ProductCategory` — **bedoun service** | `Admin/DiscountController.php:20, 70-72, 83` |
| `Admin\ReviewController` | — | `Review` — **bedoun service** | `Admin/ReviewController.php:15, 61` |
| `Admin\RoleController` / `UserController` | — | `Role`, `Permission`, `User` — **bedoun service** | `Admin/RoleController.php:7-9` |
| `Admin\BankController` | — | `Bank` **direct** + `ImageHandler` | `Admin/BankController.php:15, 33, 52` |
| `Admin\ProductCategoryController` | — | `ProductCategory` + `ImageHandler` (DTO dard, service nist) | `Admin/ProductCategoryController.php:6, 9` |
| `Admin\ShippingMethodController` / `ContactController` / `HeroSlideSearchController` | — | `ShippingMethod`/`City` / `ContactRequest` / 4 model — **bedoun service** | imports har file |
| `OrderService` | `BankPaymentService`, `StockService` | constructor DI | `app/Services/OrderService.php:22` |
| `OrderService` | `AuditService`, `NotificationService` | static / `app()` | `OrderService.php:104, 108, 199, 233` |
| `OrderService` | `Order`, `OrderItem`, `OrderShippingStatus`, `Bank` | direct Eloquent | `OrderService.php:45-85, 142` |
| `BankPaymentService` | `PaymentGatewayFactory` | static `create` | `app/Services/BankPaymentService.php:19, 50` |
| `BankPaymentService` | `Bank`, `Order`, `Auth::loginUsingId` | — | `BankPaymentService.php:17, 23, 54` |
| `PaymentGatewayFactory` | `ZarinPal` | `match($bank->bank_type)` — `zarinPal` va `card_to_card` hardo | `app/Payments/PaymentGatewayFactory.php:16-18` |
| `ZarinPal` | `PaymentPostDTO`, `PaymentVerifyDTO`, ZarinPal REST | — | `app/Payments/ZarinPal.php:14-17, 27, 29` |
| `CartService` | `StockService`, `CouponService` | `app()` locator | `app/Services/CartService.php:47, 404, 432` |
| `CartService` | `Cart`, `Product` | direct Eloquent + session `cart` (:15) | `CartService.php:41, 81, 135-179` |
| `StockService` | `AuditService` | static call | `app/Services/StockService.php:126, 158, 185, 209` |
| `StockService` | `Product`, `ProductVariant`, `InventoryTransaction`, `Order` | `DB::transaction :21, 35` | `app/Services/StockService.php` |
| `OtpService` | `Sms` (ctor :27), `User`, `NotificationService`, `WelcomeDiscountService`, `Helper::persian2LatinDigit`, `setting()` | — | `app/Services/OtpService.php:35, 62-163, 100, 104, 123-128` |
| `ShippingCalculator` | `ChaparShipment`, `ShippingMethod`, `Address`, `NumberHelper`, `session('cart')` | static class | `app/Services/ShippingCalculator.php:16, 77, 104` |
| `ChaparShipment` | API `http://api.krch.ir/v1/` (curl, authToken hardcode `''`) | — | `app/Services/ChaparShipment.php:6-7` |
| `CouponService` | `Discount`, `DiscountUsage`, `User`, `Auth`, session `coupon` | — | `app/Services/CouponService.php:22, 67, 95, 137, 181` |
| `CancelExpiredOrders` (command) | `Order` (where `order_status=paying`), `StockService` | — | `app/Console/Commands/CancelExpiredOrders.php:21, 32` |
| `Schedule` | `CancelExpiredOrders` every 5 min `withoutOverlapping` | — | `routes/console.php:11` |
| `OrderObserver` | `StockService::restoreStock` (cancel), `OrderHistory` | — | `app/Observers/OrderObserver.php` + `AppServiceProvider.php:36` |
| `SiteLayoutComposer` | `CartService`, `WishlistService`, `NotificationService` | — | `app/View/Composers/SiteLayoutComposer.php:16` |
| `LocaleComposer` / `SetLocale` middleware / `locale()` helper | `LocaleService` | — | `app/View/Composers/LocaleComposer.php:16` + `app/Helpers/locale.php:14` |
| `setting()` helper | `SettingService` | — | `app/Helpers/settings.php:10` |

---

## 3. Service → Model Ownership (neveshtan / khondan — asami-e haghighi)

| Service | Writes (DB) | Reads | Evidence |
|---|---|---|---|
| `CartService` | `carts` (create/update/delete) | `Cart`, `Product with variants`, session `cart` | `CartService.php:41, 81, 135-179, :15` |
| `WishlistService` | — (faghat session `wishlist` :14) | `Product` | `app/Services/WishlistService.php` |
| `RecentlyViewedService` | — (faghat session `recently_viewed` :14) | `Product` | `app/Services/RecentlyViewedService.php` |
| `ProductService` | `products`, `product_images`, `product_specification`, `product_variants`, `product_variant_specification`, `properties` — `DB::transaction :30, 61, 97` | `Product` | `app/Services/ProductService.php` |
| `OrderService` | `orders` (`Order::create :53`), `order_items` (`:85`), orders update (vaziat-e pardakht) | `OrderShippingStatus` (`:45`), `Bank` (`:142`) | `app/Services/OrderService.php` |
| `StockService` | `products/variants.stock` + `inventory_transactions` (`DB::transaction :21, 35`) | `Product`, `ProductVariant`, `Order` | `app/Services/StockService.php` |
| `CouponService` | `discount_usages` (`DiscountUsage::create :181`), session `coupon` (`:67`) | `Discount` (`:22, 137`), `User` | `app/Services/CouponService.php` |
| `OtpService` | `users` (create `:92`), login (`Auth::login :183`) | `User` (`:62, 80, 163`), Kavenegar via `Sms` (`:129`) | `app/Services/OtpService.php` |
| `WelcomeDiscountService` | `discounts` (code-e khosh-oomad baraye user-e jadid) | `User` | `app/Services/WelcomeDiscountService.php` — caller: `OtpService.php:100` |
| `NotificationService` | `notifications` (`Notification::create :45, 73`) | `User where is_admin` (`:68`) | `app/Services/NotificationService.php` |
| `AuditService` | `audit_logs` | `AuditLog`, `Order`, `Product` (context-e log) | `app/Services/AuditService.php` |
| `AddressService` | `addresses` (create/update/delete) | `Address` — **hame static**, instantiable nist | `app/Services/AddressService.php:9-56` |
| `StateService` / `CityService` | — | `State` / `City` — **static**, read-only | `app/Services/StateService.php`, `CityService.php` |
| `BankPaymentService` | `orders` (update vaziat-e pardakht) | `Bank`, `Order` | `app/Services/BankPaymentService.php` |
| `ShippingCalculator` | — | `ShippingMethod`, `Address`, session `cart` (`:104`), Chapar API (`:77`) — **static** | `app/Services/ShippingCalculator.php` |
| `SettingService` | `settings` (`DB::transaction :108`) + upload | `Setting`, cache `shopora_settings_all` (`:53`) | `app/Services/SettingService.php` |
| `LocaleService` | — (neveshtan dar `Admin\LanguageController` anjam mishavad) | `Language`, cache `shopora_languages_all` (`:41`) | `app/Services/LocaleService.php` |
| `BlogService` / `BrandService` / `HeroSlideService` | `blogs` / `brands` / `hero_slides` + `ImageHandler` | model-e khod | `app/Services/{BlogService,BrandService,HeroSlideService}.php` |
| `PromoBannerService` / `TrustBadgeService` / `SpecificationService` | model-e khod (CRUD) | cache `promo_banners_active` / `trust_badges_active` (`Cache::remember 3600`) | `PromoBannerService.php:15,63,113` + `TrustBadgeService.php:15,96,145` |
| `DashboardService` | — | **hich model** — data-e demo hardcode (124/45/18/1240) | `app/Services/DashboardService.php:27-58` |

---

## 4. Controller Dependency Table — ba sotoon-e "direct model access"

| Controller | Service-haye tazrigh-shode | Direct Model Call | Shekaf? |
|---|---|---|---|
| `Admin\ProductController` | `ProductService` (:18) | `ProductCategory`, `Brand`, `Specification`, `Product` (:20-31, 93-100) | Hybrid |
| `Admin\OrderController` | `OrderService` (:20) | `Order`, `OrderItem`, `OrderShippingStatus`, `ShippingMethod` (:27-131) | Hybrid |
| `Admin\BlogController` | `BlogService` (:16) | `Blog`, `BlogCategory` | Hybrid |
| `Admin\BrandController` / `HeroSlideController` / `PromoBannerController` / `SpecificationController` / `TrustBadgeController` | XService (ctor) | model-e khod baraye create/edit | Hybrid |
| `Admin\LanguageController` | `LocaleService` (app :25, 44) | `Language` (:18, 33, 35, 39) | Hybrid |
| `Admin\NotificationController` | `NotificationService` (:13) | `Notification` | Hybrid |
| `Admin\DashboardController` | `DashboardService` (:9) | — | Service-only |
| `Admin\SettingController` | `SettingService` (:13) | — | Service-only |
| `Admin\AuditLogController` | — | `AuditLog`, `Order`, `Product` | **Direct-only** |
| `Admin\DiscountController` | — | `Discount`, `Product`, `Brand`, `ProductCategory` | **Direct-only** |
| `Admin\ReviewController` | — | `Review` | **Direct-only** |
| `Admin\RoleController` / `UserController` | — | `Role`, `Permission`, `User` | **Direct-only** |
| `Admin\BankController` | — | `Bank` + `ImageHandler` | **Direct-only** |
| `Admin\ProductCategoryController` | — | `ProductCategory` + `ImageHandler` | **Direct-only** |
| `Admin\ShippingMethodController` / `ContactController` / `HeroSlideSearchController` / `CkeditorController` | — | model-haye khod / Helper | **Direct-only** |
| `Site\CheckoutController` | `CartService` (:23) + `OrderService`/`CouponService`/`StockService` (app) + 3 static | `Order`, `Bank`, `Address`, `ShippingMethod`, `Discount` | Hybrid |
| `Site\CartController` | `CartService` (:14) | `Product` (:87) | Hybrid |
| `Site\ProductController` | `RecentlyViewedService`, `TrustBadgeService` (:19-22) | `Product`, `Review`, `ReviewVote`, `Brand`, `ProductCategory` — `ProductService` **nadarad** | **Direct-only** |
| `Site\HomeController` | 4 service (:23-28) | `Product`, `ProductCategory`, `Review`, `Blog` + Seeders | Hybrid |
| `Site\DashboardController` | `NotificationService` (app :65) | `Order`, `Address` | Hybrid |
| `Site\WishlistController` | `WishlistService` (:13) | `Product` (:22) | Hybrid |
| `Site\OtpController` | `OtpService` (:13) + `CartService` (app :139) | — | Service-only |
| `Site\BlogController` | — | `Blog`, `BlogCategory` | **Direct-only** |
| `Site\PageController` | `NotificationService` (app) | `ContactRequest` | Hybrid |
| `Site\SiteAuthController` / `Admin\AdminAuthController` | — | `Auth` facade (login/logout/attempt) | Direct (auth-e Laravel) |

**Natije:** ~40% controller-ha mostaghim Model query mikonand — in olsou-e asli-e code hast, na estesna. Site-side taqriban hamash hybrid/direct; Admin-side CRUD-haye pichide (product/order/blog/brand/content) service-dar, CRUD-haye sade direct.

---

## 5. Coupling Clusters — khushe-haye vaghe'i

### 5.1 Checkout Session Cluster (bozorg-tarin khushe)

```
CheckoutController ── session keys: checkout_address_id (:57),
                     checkout_shipping_method_id, checkout_shipping_price (:73, 86-88, 170-172)
                     coupon (apply :121, recordUsage :216-221, forget :227)
  ├─ CartService (session 'cart' :15 / table carts) ── subtotal(), toArray(), remove()
  ├─ OrderService::create (:204) ──► reserveStock (StockService :97)
  ├─ CouponService ──► Discount + discount_usages
  └─ StockService::validateCartItems (:309-310) — AJAX ghabl az pardakht
```

Session key-haye asli: `cart`, `coupon`, `checkout_address_id`, `checkout_shipping_method_id`, `checkout_shipping_price`, `otp_mobile`, `wishlist`, `recently_viewed`.
**Khatar:** `ShippingCalculator::getAvailableMethods` mostaghim hardcoded `session('cart', [])` mikhond (`:104`) — taghir-e `CartService::$sessionKey` inja desync misazad.

### 5.2 Payments Cluster

```
CheckoutController::create (:158) / finishZarinPal|Saman|Sadad|Zibal (:271-301)
  → OrderService::checkout (:132) → Bank::find (:142)
      → BankPaymentService::handlePayment (:153) → PaymentGatewayFactory::create (:19)
          → ZarinPal::getBankToken (toman×10 rial :29)
  → OrderService::callback (:165) → BankPaymentService::handleVerify (:179)
      → handleSuccessOrderPayment (:188) / handleFailedPayment (:226 → restoreStock)
```

Faghat yek gateway-e amali vojoud darad: `ZarinPal` (`PaymentGatewayFactory.php:16-18` — hatta `card_to_card` ham be ZarinPal map mishavad; mantegh-e card-to-card dakhel-e `OrderService`/`CheckoutController` ast). `Bank.bank_type` ستون-e tahkime factory ast. Callback-ha az `transaction_info->post->Authority` (JSON column) order ra peyda mikonand (`CheckoutController.php:274`).

### 5.3 Catalog Cluster

```
ProductService ──► Product, ProductImage, ProductSpecification, ProductVariant,
                   Property + ImageHandler (GD WebP)
Site\ProductController (index/category/show) ──► Product/Review direct — ProductService bypass
CartService ──► Product::with('variants')->findMany (:81, 190) — ghemat/movjoudi-e rooz
StockService ──► Product/ProductVariant stock + inventory_transactions
RecentlyViewedService / WishlistService / HomeController (deals/featured) ──► Product
HeroSlide.linkable_type → Product::class (HeroSlideDTO)
```

Har taghir-e schema-e `Product` (`final_price`, `stock`, `name` JSON, `is_active`) be 5+ noqte car mikhord: `ProductService`, `Site\ProductController`, `CartService`, `StockService`, `HomeController`.

### 5.4 Order-Lifecycle Side-Effect Cluster

`OrderObserver` (`AppServiceProvider.php:36`) → `OrderHistory::create` + `restoreStock` baraye cancel → `StockService` → `AuditService` + `inventory_transactions`.
`OrderService::cancelOrder` (`:243`), `handleFailedPayment` (`:235`) va `CancelExpiredOrders` (`:32`) ham `restoreStock` mizanand — **3-4 masir-e moghabel baraye restoreStock** (risk-e double-restore — bebin `04-workflows` va gotchas).

---

## 6. Change-Impact Cheat Sheet — agar X ra taghir dadi chi mishkanad

| Taghir | Chi mishkanad |
|---|---|
| `Product` schema (stock / final_price / name) | `ProductService` (sync), `Site\ProductController` (filters), `CartService::dbContent` (:47, 81), `StockService` (decrement/increment), `HomeController` (deals/featured/bestSellers) |
| Session key `cart` (`CartService:15`) | `ShippingCalculator:104` (hardcoded `session('cart')`) + `SiteLayoutComposer` (cartCount) + `OtpController:139` (mergeGuestCart) |
| Session key `coupon` | `CouponService` (:67, 95, 103) + `CartService::discount` (:399) + `CheckoutController` (:200, 214-227) |
| `BankPaymentService::handlePayment/handleVerify` signature | `OrderService::checkout/callback` (:153, 179) + tamami `finish*` callback method-ha |
| `PaymentGatewayFactory::match` | Gateway-e jadid = `Bank.bank_type` jadid + branch-e jadid; interface `PaymentGateway` 3 method: `getBankToken`, `verifyTransaction`, `redirect` |
| `StockService::reserveStock/restoreStock` | `OrderService` (:97, 235, 264), `CheckoutController::validateStock` (:310), `CartService` (:47), `OrderObserver` (cancel), `CancelExpiredOrders` (:32) |
| `CartService` API | `Site\CartController`, `Site\CheckoutController`, `Site\OtpController:139`, `SiteLayoutComposer`, `CouponService::calculateForCart(:129 — type-hint-e CartService!)` |
| `CouponService::apply/recordUsage` | `CheckoutController` (:121, 216) + `CartService::applyCoupon/discount` (:404, 432) |
| `OtpService` | `Site\OtpController` + `WelcomeDiscountService` + `Sms` helper (Kavenegar) + `setting('otp_template')` (:123) |
| Cache `shopora_settings_all` / `shopora_languages_all` | `SettingService`/`LocaleService` + helper-haye `setting()`/`locale()` + flush dar `Admin\SettingController` / `Admin\LanguageController:44` |
| Cache `trust_badges_active` / `promo_banners_active` | `TrustBadgeService` (:15, 96, 145) / `PromoBannerService` (:15, 63, 113) + `FooterComposer` + `Site\HomeController` |
| `AddressService` (static) | `Site\CartController:43`, `CheckoutController:54`, `Site\DashboardController:94, 124, 153` — chon static ast, test/mock sakhtar ast |
| `LocaleService::flush` hazf shavad | `Admin\LanguageController:44` — cache-e `rememberForever` monde va zabane jadid nist mishavad |
| `routes/console.php` schedule | Tanha schedule: `orders:cancel-expired --minutes=15` har 5 daghighe (`withoutOverlapping`) — `CancelExpiredOrders` be cron-e server tavakkol darad |

---

## 7. Ghanoon-haye Vaghe'i — layering rules-e asli-e in codebase

1. **"Thin controller" ghanoon nist:** Controller-ha mojaz (va dar ~40% mored) mostaghim `Model::where/create` mizanand — makhsoosan Admin CRUD-haye sade (AuditLog, Discount, Review, Role, User, Bank, ProductCategory, ShippingMethod, Contact) va Site-e product/cart/checkout/home. Service faghat jayi ke transaction, image-upload, ya mantegh-e pichide hast vjoud darad.
2. **In laye-ha vojoud nadarand:** `app/Repositories`, `app/Actions`, `app/Events`, `app/Listeners`, `app/Jobs`, `app/Policies` — hichkodam vojoud nadarand (`ls app/` → `Console, DTOs, Helpers, Http, Models, Observers, Payments, Providers, Rules, Services, Traits, View`).
3. **3 singleton faghat:** `CartService`, `WishlistService`, `RecentlyViewedService` (`app/Providers/AppServiceProvider.php:25-27`). Baghiye transient (`app(Service::class)`); `AddressService`, `StateService`, `CityService`, `ShippingCalculator`, `ChaparShipment`, `AuditService`, `WelcomeDiscountService` aslan **static-method class** hastand va DI nemishavand.
4. **Service-Locator ejra mishavad:** `app(XService::class)` dakhel-e service-ha va controller-ha (`CartService.php:47`, `CheckoutController.php:204`) — constructor-DI faghat baraye service-haye asli va singleton-ha.
5. **Event/Queue nist — Observer hast:** yek Observer (`OrderObserver`, register `AppServiceProvider.php:36`) + yek scheduled command (`routes/console.php:11`). `queue=database` config shode vali hich `dispatch()` dar code nist — hich `app/Events` ya `app/Jobs` nist.
6. **Cross-service hamechi synchronous:** `AuditService` va `NotificationService` ba try/catch dar `OrderService:104-108` seda mishavand ta khataye log/notify block-e order nakonad.
7. **DTO faghat Admin:** `app/DTOs/*` (9 file, readonly + fromRequest) + `app/Payments/PaymentPostDTO|PaymentVerifyDTO` — Site controller-ha `$request->input()` mostaghim migirand.
8. **Helper-ha global:** `setting()`, `locale()`, `format_price()`, `Sms::`, `ImageHandler::` — via `composer.json` autoload.files / static call — service-locator-e hame-ja-pasand; posht-e `setting()`/`locale()` `SettingService`/`LocaleService` ba `rememberForever` nestand.

---

**Khoulase:** 26 service-e real, 2 laye-e payment (interface + 1 gateway-e ZarinPal), 3 singleton, 7 static-service class, ~40% direct-model-access — hame az code ba `file:line` trace shod.

**Naghs-trace-shode:** `Admin\HeroSlideSearchController` (faghat model-imports check shod), helper-haye `VideoHandler`/`VideoUploader`/`MetaManager`/`card.php` (caller-hashan trace nashod), session key-haye digar-e `OtpController` (faghat `otp_mobile` verify shod), `ChaparShipment::agent/bulk` (caller-e mostaghim nadarand — faghat `quote` az `ShippingCalculator:77` verify shod), `DashboardService` data-e demo (riadat sakht nashod ke aya jaygozin-e real khahad shod).
