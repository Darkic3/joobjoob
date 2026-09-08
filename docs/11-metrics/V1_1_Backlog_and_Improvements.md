# V1.1 Backlog & Professional Improvements (Post-Deploy)

> **Tarikh:** 2026-09-07 — **Mode:** Pure Audit, hich code fix nashod
> **Source:** `documents/01_P0_Critical_Blockers.md` (9 P0) + `documents/02_P1_V1_Essential_Features.md` (7 P1) + Macro-Audit (Cross-Cutting) + `docs/03-modules/*` Micro-Reviews (IDE Agent) + `docs/07-security/threats.md`
> **Hadaf:** Tajmie-e hame yafte-ha baraye V1.1 / V1.2 — faghat fekr, hich patch-e code nist.

---

## 1. Critical Security & Logic Bugs (Bayad Faghat Fikr Shavad)

> **Status legend:** `FIXED` = dar code-e feli hal shode, dar doc sabt shode — digar backlog nist. `OPEN` = baghi baraye V1.1.

| # | Module | Sharh-e 1-khati | File-e marboot | Status |
|---|--------|-----------------|----------------|--------|
| 1.1 | **Orders** | `OrderObserver::created` `isDirty` hamishe false -> hich `OrderHistory` baraye sefaresh-e jadid | `app/Observers/OrderObserver.php:16` | **FIXED 2026-09-07** |
| 1.2 | **Inventory** | Double `restoreStock` (Observer + Service/Command) -> mojoodi 2 bar barmigardad | `app/Observers/OrderObserver.php:31` + `StockService.php` | **FIXED 2026-09-07** (Observer restore hazf) |
| 1.3 | **Payments** | `BankPaymentService::handlePayment` token fail -> `order->delete()` hard delete, data loss | `app/Services/BankPaymentService.php:27` | **FIXED 2026-09-07** -> `unpaid` |
| 1.4 | **Orders** | `OrderService::create` `forceDelete` dar reserve fail + bedone `DB::transaction` | `app/Services/OrderService.php:53,100` | **FIXED 2026-09-07** -> `DB::transaction` + `cancelled` |
| 1.5 | **Orders** | Dead column `payment_receipt` — migration hazf kard ama Service hanuz minevisad (silent ignore via `fillable`) | `app/Services/OrderService.php:78` + `Order.php:16` | **FIXED 2026-09-07** (khat hazf) |
| 1.6 | **Shipping** | `ChaparShipment::$authToken=''` + `http://` -> quote hamishe 0, MITM risk | `app/Services/ChaparShipment.php:7-8` | **OPEN — BROKEN by design (P0-05/T11)** |
| 1.7 | **Orders** | Scheduler `orders:cancel-expired` bedoone `cron schedule:run` -> `paying` leak | `routes/console.php` | **OPEN — infra, docs/03 ok** |
| 1.8 | **Infra** | `.env.example` `DB_CONNECTION=sqlite` vs host `mysql` (JSON `name->fa`) | `.env.example:22` | **OPEN — deploy checklist** |
| 1.9 | **Security** | `Sms.php` `CURLOPT_SSL_VERIFYPEER=0` (HIGH MITM) | `app/Helpers/Sms.php:77` | **FIXED 2026-09-07** -> `true/2` |
| 1.10 | **Security** | `LogAdminRequests` faghat `password/_token` filter -> `receipt_tracking_code`, `payer_card`, `card_number` dar `request_logs.payload` leak | `app/Http/Middleware/LogAdminRequests.php` | **OPEN — T12** |
| 1.11 | **Security** | Coupon race condition — `apply()` 6 check bedoone `lockForUpdate`, 2 concurrent request limit bypass | `app/Services/CouponService.php:54` + `Discount.php:hasReachedLimit` | **OPEN — High** |
| 1.12 | **Security** | Hich `throttle` rooye `products.reviews.store`, `reviews.reply`, `wishlist.toggle`, `cart.add/update`, `checkout.create` | `routes/site.php` + `routes/_generated/routes.json` | **OPEN — Medium** |
| 1.13 | **Inventory** | `StockService::getAvailableStock` ba `withTrashed()->find` per-item N+1 + `validateCartItems` silent `continue` agar `product_id` null (Ghost Stock) | `app/Services/StockService.php:76,43` | **OPEN — Low (perf)** |
| 1.14 | **Payments** | 4 callback-e morde `finish-saman/sadad/zibal` dar `routes/site.php` vali Factory faghat `zarinPal/card_to_card` — hame `Unsupported bank_type` | `routes/site.php` + `PaymentGatewayFactory.php:18` | **OPEN — Low (P1-06)** |
| 1.15 | **Cart** | `mergeGuestCart` dar `SiteAuthController::login` (password) seda zade nemishavad — guest cart lost on password login | `app/Http/Controllers/Site/SiteAuthController.php` vs `OtpController:146` | **OPEN — P1-05** |

---

## 2. Architectural & Performance Debt (Tech Debt)

| # | Debt | Module | File-ha | Impact |
|---|------|--------|---------|--------|
| 2.1 | **N+1 dar Cart enrich** — `StockService::enrichCartItemsWithStockStatus` per-item `Product::withTrashed()->find` + `getAvailableStock` dar loop (`map`) | `cart` / `inventory` | `app/Services/StockService.php:86`, `CartService.php` | Medium — cart ba 10 item = 10 query |
| 2.2 | **Batch load mitigated dar listing ama Cart baghi** — `ProductService::getPaginatedProducts:110` `with(['category','images','brand'])` dorost; Cart hanuz batch nist | `products` | `app/Services/ProductService.php:110` | Low — listing OK, cart need batch |
| 2.3 | **Hardcoded Order Status strings** — 7 status `'paying','paid',...` dar 15+ file, hich `Enum` | `orders` | `app/Services/OrderService.php:70`, `OrderObserver`, `CancelExpiredOrders` | Medium — typo risk, hich type-safety |
| 2.4 | **Hardcoded Toman->Rial `'0' concat** — `intval($amount.'0')` dar 3 file (ZarinPal, ShippingCalculator, Chapar) — gotcha dar `10-ai-prompts/global-context.md` ama hanuz hardcoded | `payments` / `shipping` | `app/Payments/ZarinPal.php:20`, `ShippingCalculator.php` | Medium — 10x payment bug agar faramoosh shavad |
| 2.5 | **Fat Controllers** — `ProductController:441` line (3 private helper + Service call), `DashboardController:207` inline `NumberHelper` + `AddressService` mapping | `products` / `dashboard` | `app/Http/Controllers/Site/ProductController.php`, `DashboardController.php` | Low — testability kam |
| 2.6 | **Inline validation dar Cart** — `CartController:89,156,216` `$request->validate(['qty'])` bedoone `FormRequest` — dar `05-conventions/validation.md` 22 Request ama Cart esterna | `cart` | `app/Http/Controllers/Site/CartController.php` | Low — inconsistency |
| 2.7 | **Hardcoded throttle + image sizes** — `throttle:5,1` magic dar routes, `IMAGE_SIZES [300,300]` dar `ProductService:19` | `conventions` | `routes/site.php:70`, `app/Services/ProductService.php:19` | Low — config central nist |
| 2.8 | **Duplicated `$siteRoutes` locale prefix** — hame route ha dobar (`/` + `/{locale}/`) — DRY violation | `routes` | `routes/site.php`, `docs/_generated/routes.json` | Low — maintenance 2x |
| 2.9 | **No Event/Job/Policy/Resource layer** — 0 file — hame synchronous, Observer faghat Order | `architecture` | `app/` (gap) | Low — scalability (V1.2) |
| 2.10 | **Migration churn 73 file** — `payment_receipt` -> `receipt_tracking`, `enhance_discounts` — history toolani | `database` | `database/migrations/` | Low — doc only |

---

## 3. Professional Feature Blueprints (Pishnahad-e Piade-sazi)

### 3.1 Smart Notifications Toggle

> **Vaziat-e feli:** Skeleton — `config/settings.php:352` tab `smart_notifications` ba `allow_open_requests` bool, view `layouts/site/app.blade.php:90` check, hich Service/Event nist.

**Blueprint (Professional):**

| Layer | Pishnahad |
|-------|-----------|
| **Migration** | Niaz nist — `settings` table mojood via `SettingService` (key-value). |
| **Model** | `Notification` + `Setting` mojood — kafi. |
| **Service** | `NotificationService::shouldNotify(string $type): bool` — `if $type==='open_requests' return setting('allow_open_requests', false)` else `setting("notify_{$type}", true)`. Centralize dar 2 caller: `OrderService::create` + `DashboardController::updateReceipt`. |
| **Event/Listener** | `OrderCreated` Event -> `SendAdminNotificationListener implements ShouldQueue` — ghabl az `notifyAdmins` `shouldNotify()` check. |
| **Config** | `config/settings.php` fields already tab define — admin toggle auto. |
| **Queue** | `ShouldQueue` + retry 3 + `failed` log. |
| **Validation** | `UpdateSettingsRequest` dynamic — hich FormRequest jadid niaz nist. |

**Test:** Toggle off -> hich row dar `notifications` DB nare.

---

### 3.2 Wishlist / RecentlyViewed Merge (Session -> DB)

> **Vaziat-e feli:** `WishlistService:14 sessionKey='wishlist'` pure session (list<int>), `RecentlyViewedService:14 'recently_viewed'` session, `AppServiceProvider:25 singleton`. Guest wishlist bad az login gom.

**Blueprint (Professional):**

| Layer | Pishnahad |
|-------|-----------|
| **Migration** | `create_wishlists_table` : `user_id FK`, `product_id FK`, `unique(user_id,product_id)`. `recently_viewed` DB niaz nadarad — session kafi (low value). |
| **Model** | `Wishlist` (`fillable user_id, product_id`) `belongsTo User/Product`. |
| **Service** | `WishlistService` dual-mode: `ids()` -> if auth `Wishlist::where(user_id)->pluck(product_id)` else session; `toggle()` -> DB upsert/delete vs session; `mergeGuestWishlist(array $guestIds)` on login `foreach upsert ignore duplicate`. |
| **Event/Listener** | `Illuminate\Auth\Events\Authenticated` -> `MergeWishlistListener` -> `mergeGuestWishlist(session('wishlist',[]))`. |
| **Caller** | `OtpController::verify:146` (already `mergeGuestCart`) + `SiteAuthController::login` (P1-05 gap) — har do. |
| **Throttle** | `wishlist.toggle throttle:30,1` dar `routes/site.php`. |

**Test:** Guest 3 item -> OTP login -> DB 3 row -> wishlist index 3.

---

### 3.3 Coupon Race Condition Fix

> **Vaziat:** `CouponService::apply:22-56` 6 check sequential bedoone lock — 2 concurrent `hasReachedLimit` bypass. `recordUsage:179` ham lock nadarad. Inventory stock lock dorost.

**Blueprint (Professional):**

| Layer | Pishnahad |
|-------|-----------|
| **Lock** | `DB::transaction(function(){ Discount::where('code',$code)->lockForUpdate()->first() ... hasReachedLimit ... session put })` dar `apply`. |
| **Usage** | `CouponService::recordUsage` ham `DB::transaction + lockForUpdate` + `DiscountUsage::create` + optional counter `increment('used_count')` atomic. |
| **Alternative** | `RateLimiter::attempt('coupon:'.$code, 1)` per-code per-second. |
| **Isolation** | `OrderService::create` coupon session check + `discount_id` dar `orders` FK — rollback coupon agar order cancel. |

**Test:** 2 concurrent `curl POST /cart/coupon` ba limit 1 -> yeki `success`, yeki `capacity full`.

---

## 4. Dead Code & Cleanup Candidates

| # | File / Code | Dalil-e dead | Pishnahad |
|---|-------------|--------------|-----------|
| 4.1 | `app/Helpers/VideoHandler.php` + `VideoUploader.php` | Hich caller dar `grep VideoHandler` — hich Controller video upload nadarad | **V1.2:** hazf ya mark `// TODO unused` + note dar `content/gotchas.md` |
| 4.2 | `config/settings.php` `enable_ai` (agar mojood) | Toggle AI ke hanuz feature-e AI code nadarad (0 Job/Event) | **V1.2:** ya AI feature besaz ya config ra hazf |
| 4.3 | `app/Services/ChaparShipment.php::bulk()` + `::agent()` | Faghat `quote()` estefade mishavad (`ShippingCalculator`), 2 method dige unused | **Low:** negah dar — agar Chapar faal shavad lazem mishavad |
| 4.4 | `app/Http/Controllers/Admin/CkeditorController.php` | Single-method, faghat upload image CKEditor — OK vali `Uploader` duplicate | **Low:** merge ba `ImageHandler` |
| 4.5 | `app/Traits/*` unused check | `HasJalaliDate`, `HasMetaData` dar chand Model — OK (na dead) | Clean |
| 4.6 | `routes/site.php` dead callbacks `finish-saman/sadad/zibal` | 4 route vali Factory faghat 2 type — hame `Unsupported` | **P1-06:** ya gateway besaz ya route comment kon |
| 4.7 | `CartService::validateStock()` public | Faghat `CheckoutController::validateStock` call — low usage ama na dead | Negah dar |
| 4.8 | `documents/02_P1` dead code list | `payment_receipt` lang + migration ref | **FIXED** — lang bayad sync shavad |

---

## 5. Priority Matrix (Baad az Deploy-e V1.0)

| Priority | Item | Hoze | Effort | Mohelet |
|----------|------|------|--------|---------|
| **High (V1.1 — 2 hafte)** | 1.11 Coupon race `lockForUpdate` | Security | 2h | Fowri — bypass limit = mali |
| **High** | 1.10 RequestLog payload leak filter | Security | 30min | Fowri — PII leak |
| **High** | 1.12 Throttle reviews/wishlist/cart | Security | 1h | Fowri — spam |
| **High** | 1.15 Guest Cart/Wishlist merge (password login) | Cart/Wishlist | 1h | Fowri — data loss |
| **Medium** | 2.1 N+1 Cart batch load | Perf | 1h | V1.1 |
| **Medium** | 3.1 Smart Notifications `shouldNotify()` | Feature | 3h | V1.1 |
| **Medium** | 2.3 OrderStatus Enum (7 case) | Arch | 2h | V1.1 — type safety |
| **Medium** | 1.6 Chapar disable ya HTTPS + token | Shipping | 1h | V1.1 — ya disable |
| **Low (V1.2)** | 3.2 Wishlist DB merge + RecentlyViewed keep session | Feature | 4h | V1.2 |
| **Low** | 2.5 Fat Controller -> Action/Service | Arch | 4h | V1.2 |
| **Low** | 2.8 Duplicated locale routes DRY | Routes | 2h | V1.2 |
| **Low** | 4.1/4.3 VideoHandler / Chapar bulk cleanup | Debt | 1h | V1.2 |
| **Low** | 2.4 MoneyHelper tomanToRial | Arch | 30min | V1.2 |
| **Low** | 1.14 Dead gateway callbacks | Payments | 30min | V1.2 (P1-06) |
| **Low** | 2.9 Event/Job/Policy layer | Arch | — | V1.2+ (scalability) |

> **Tartib-e pishnahadi V1.1:** 1.11 -> 1.10 -> 1.12 -> 1.15 -> 2.1 -> 3.1 -> 2.3. Chapar (1.6) agar faal nist, faghat doc kafi.

---

## Appendix — Cross-Reference

| Source | File |
|--------|------|
| P0 blockers kamel | `documents/01_P0_Critical_Blockers.md` |
| P1 features | `documents/02_P1_V1_Essential_Features.md` |
| Deploy checklist | `documents/03_Deployment_Checklist_and_Roadmap.md` |
| Threat matrix | `docs/07-security/threats.md` (T1-T12) |
| Validation inventory | `docs/05-conventions/validation.md` (22 Request) |
| Integration status | `docs/06-integrations/{chapar,kavenegar,storage}.md` |
| Codebase metrics | `docs/11-metrics/codebase-metrics.md` |
| Prior commit | `e7d9a82 docs(workflows,conventions,security,ai): sync docs ...` |

