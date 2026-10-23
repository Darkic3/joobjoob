# Actions & Services Pattern

> **Source of Truth:** `app/Services/*.php` (26 files) — **there is no `app/Actions/` directory**
> **Pattern:** Thin `Controller` → `FormRequest::validated` / `DTO::fromRequest` → **Service** (stateful via DI/`app()->make`) → `DB::transaction` + side effects (audit, notify, stock)
> **No Action layer exists** in this project. Historical intent "CreateOrderAction" was realized as `OrderService::create()` + `OrderService::checkout()` — the Actions terminology is not adopted. Do not invent `app/Actions/` classes.

---

## 1. Purpose — What This Project Means by "Service" and Why There Is No "Action"

- **Service = use-case orchestrator bound to a domain aggregate.** Each service maps to one resource or cross-cutting concern and exposes domain verbs (`store`, `update`, `destroy`, `toggleStatus`, `reserveStock`, `apply`, `checkout`). Services are **stateful only via DI** (constructor-injected deps) or **stateless** with static helpers; they hold no per-request state beyond injected collaborators.

- **Action (as bookish pattern) ≠ exists here.** There is **no `app/Actions/` namespace, no `__invoke`, no `execute()`**. The team considered per-use-case single-method Actions (e.g. `CreateOrderAction::__invoke(OrderData): Order`) but shipped `OrderService` with `create()`+`checkout()`+`callback()` instead. `Actions` therefore must be documented as **absent** so future AI does not hallucinate them.

- **Controller is intentionally thin.** Controllers never contain business rules; they resolve a `FormRequest`, map to a `DTO` (or `array` for cart/checkout session flows), delegate to a Service, then issue the `redirect`/`view`/`json`. Any controller doing more than `$dto = XxxDTO::fromRequest($request); $service->store($dto)` is a smell in this codebase.

---

## 2. Service vs Action — Decision Table (Joobjoob Convention)

| Criterion | **Service** (what Joobjoob uses) | **Action** (not used — do not create) |
|-----------|----------------------------------|----------------------------------------|
| Granularity | One class per **aggregate** (`Product`, `Order`, `Cart`, `Blog`) owning multiple verbs | One class per **single use case** (`CreateProductAction`, `AttachSpecificationsAction`) |
| State | May hold dependencies (`ProductService` holds none; `OrderService` holds `BankPaymentService` + `StockService` via constructor) | Stateless single `__invoke` with all deps injected |
| Entry | Named verbs: `store(DTO)`, `update(Model,DTO)`, `destroy`, `getPaginatedProducts`, `checkout(Order)`, `apply(string $code, int $subtotal)` | Single `__invoke` or `execute` (convention varies by package) |
| Transaction | Service owns `DB::transaction(...)` wrapping multi-table writes | Same, but even more isolated |
| Reuse | Callers inject the same Service (e.g. `OrderService` is injected into `CheckoutController` and into `OrderObserver` indirectly) | Callers would compose multiple small Actions |
| Existence in repo | **26 services** under `app/Services/` | **0 actions** — no `app/Actions/`, no `grep` hit for `class *Action` (checked 2026-09-04) |

**Guidance for Vibe Coding:** continue the Service pattern. If a use-case feels too large, **split methods inside the same Service**, not into a new `Action`. Only introduce `app/Actions/` via RFC if the team agrees to migrate — never ad-hoc.

---

## 3. Full Inventory — 26 Services

### 3.1 Catalog & Content

| Service | File | Key public methods | Notes |
|---------|------|--------------------|-------|
| `ProductService` | `ProductService.php` (~780 lines) | `store(ProductDTO): Product` · `update(Product, ProductDTO): bool` · `destroy(Product): bool` · `getPaginatedProducts(array $filters, int $perPage=15): LengthAwarePaginator` · `prepareData`, `appendImages`, `setMainImage`, `syncSpecifications`, `syncVariants`, `syncProperties`, `syncAssociations` | Transaction-wrapped; `UPLOAD_FOLDER='products'`, `IMAGE_SIZES=[thumb 300, medium 800, large 1200]` |
| `BrandService` | `BrandService.php` | `store(BrandDTO)`, `update(Brand, BrandDTO)`, `destroy`, `getPaginated`, `reorder` | Mirrors `ProductService` but single image |
| `BlogService` | `BlogService.php` | `store(BlogDTO)`, `update(Blog, BlogDTO)`, `destroy`, `getPaginated` | `UPLOAD_FOLDER='blogs'` |
| `SpecificationService` | `SpecificationService.php` | `store(SpecificationDTO)`, `update`, `destroy`, `getTree` | `isFilter/isColor` derived |
| `HeroSlideService` | `HeroSlideService.php` | `store(HeroSlideDTO)`, `update(HeroSlide, HeroSlideDTO)`, `destroy`, `reorder`, `getActive()` | `UPLOAD_FOLDER='hero-slides'`, linkable morph resolve |
| `PromoBannerService` | `PromoBannerService.php` | `store(PromoBannerDTO)`, `update`, `destroy`, `reorder`, `getActive/Cached()` | `CACHE_KEY='promo_banners_active'`, `SITE_MAX_BANNERS=4`, `Cache::forget` on `saved/deleted` |
| `TrustBadgeService` | `TrustBadgeService.php` | `store(TrustBadgeDTO)`, `update`, `destroy`, `reorder`, `getActive()` | `CACHE_KEY='trust_badges_active'`, `SITE_MAX_BADGES=6`, `FOOTER_MAX_BADGES=6` |
| `ProductCategory` (no dedicated Service — uses DTO directly in Controller) | — | `ProductCategoryDTO::fromRequest` → controller persists | Intentionally service-less; lightweight tree |

### 3.2 Commerce & Checkout

| Service | Key methods | Notes |
|---------|-------------|-------|
| `CartService` | `content(): Collection` · `add(Product,int qty,?int variant): string rowId` · `update(string rowId,int qty): void` · `remove`, `clear`, `subtotal():int`, `discount():int`, `total():int`, `roundedWeight():int`, `toArray(): array`, `applyCoupon/remove` passthrough | Singleton in `AppServiceProvider::register`; **dual store**: DB for auth (`carts` table) vs `session('cart')` for guest; enriches with `StockService::enrichCartItemsWithStockStatus` |
| `OrderService` | `create(array $cartItems, array $orderData, ?string desc): Order` · `checkout(Order): RedirectResponse` · `callback(string field, mixed val, array data): RedirectResponse` · `handleSuccessOrderPayment`, `handleFailedPayment`, `findById`, `findByField` · `cancelOrder` | Constructor DI: `BankPaymentService` + `StockService`; calls `AuditService` + `NotificationService` via `try/catch`; `handleFreeOrder` for `discount_id && price<=0` |
| `BankPaymentService` | `handlePayment(Bank, Order, int price): RedirectResponse` · `handleVerify(Order, array data, int price): bool` | Factory `PaymentGatewayFactory::create(Bank)` → `ZarinPal`; `Auth::loginUsingId($order->user_id)` before redirect |
| `StockService` | `reserveStock(Order): void` · `restoreStock(Order): void` · `validateCartItems(array): ['available','insufficient']` · `enrichCartItemsWithStockStatus(Collection): Collection` · `decrementForOrder` | `DB::transaction` + `lockForUpdate`? (check impl); throws `RuntimeException` on insufficient |
| `CouponService` | `apply(string $code, int $subtotal): array` · `remove(): void` · `recordUsage(int discountId, int userId, int orderId): void` · `isApplicable(Discount,int subtotal): bool` | Guards `isCurrentlyValid`, `hasReachedLimit`, `basket_minimum_price`, `first_purchase`, `pay_type` |
| `ShippingCalculator` | `getAvailableMethods(Address,int weight,int subtotal): Collection` · `computeCost(ShippingMethod,int weight,int subtotal): int` | Pure calculation; `freight_balance`, zone/weight rules |
| `AddressService` / `CityService` / `StateService` | `findOne/ActiveByState`, `findActive()` | Thin query wrappers; `StateService` single method |

### 3.3 Cross-Cutting & Ops

| Service | Key methods | Notes |
|---------|-------------|-------|
| `AuditService` | `log(string event, $auditable, ?array old, ?array new, ?array meta, ?string desc, ?int userId): AuditLog` · `orderCreated`, `orderStatusChanged`, `orderStockReserved/Restored`, `productCreated/Updated`, `productPriceChanged`, `productStockChanged` | **Static** methods only; wraps `AuditLog::create`; `event` names like `order.created`, `order.status_changed` |
| `NotificationService` | `notifyUser(User,string type,string title,string body,string? actionUrl,array data): Notification` · `notifyAdmins(type,title,body,url,data)` · `markRead/markAllRead`, `unreadCount` | `TYPES` const enumerates like `order.created`, `order.new`, `order.paid`; persists to `notifications` table |
| `DashboardService` | (admin dashboard aggregations) | Groups order/product stats |
| `LocaleService` | `codes(): array` · `current(): Language` · `resolve(string header): Language` | `CACHE_ALL='joobjoob_languages_all'`, `Cache::rememberForever`; `codes()` includes DB + catalog `config('languages')` |
| `SettingService` | `get(string key, mixed default)` · `all(): array` · `set(string key, mixed val): void` · `clearCache()` | `CACHE_ALL='joobjoob_settings_all'`; `Cache::rememberForever`; `setting()` helper delegates here |
| `RecentlyViewedService` / `WishlistService` | `push/track`, `get/sync` | Singletons; session+DB variants |
| `OtpService` | `send(string mobile): array` · `verify(string mobile, string code): bool` · `resend` | `CODE_LENGTH=4`, throttled routes; `Sms` helper |
| `WelcomeDiscountService` | `maybeCreateFor(User): ?Discount` · `buildCode()` | `DISCOUNT_PERCENT=10`, `DISCOUNT_COUNT=1`, `MAX_USAGE_PER_USER=1`, `EXPIRY_DAYS=30`, `CODE_PREFIX='WELCOME-'`, `CODE_LENGTH=6` |
| `ChaparShipment` | (Chapar carrier integration — currently BROKEN: hard-coded empty `authToken`) | `BROKEN` tag in docs; not active |

---

## 4. Structure / Signature — Canonical

### 4.1 Constructor DI shape

```php
// DI for collaborators — no per-request state stored
namespace App\Services;

class OrderService
{
    public function __construct(
        protected BankPaymentService $bankPaymentService,
        protected StockService $stockService,
    ) {}
}

class ProductService
{
    protected const UPLOAD_FOLDER = 'products';
    protected const IMAGE_SIZES = ['thumb'=>[300,300],'medium'=>[800,800],'large'=>[1200,1200]];
    // no constructor — stateless helpers private
}
```

```php
// Static-only service — no instance state at all
class AuditService
{
    public static function log(string $event, $auditable, ?array $old=null, ?array $new=null, ?array $meta=null, ?string $desc=null, ?int $userId=null): AuditLog
    public static function orderCreated(Order $order): AuditLog
    public static function orderStatusChanged(Order $order, string $oldStatus, string $newStatus, ?string $changedBy=null): AuditLog
}
```

### 4.2 Verb signatures (catalog standard)

```php
// All catalog Services converge on these signatures — Controller depends on this shape:
interface CatalogService { // implicit in codebase, not formal interface
    public function store(SomeDTO $dto): Model;
    public function update(Model $model, SomeDTO $dto): bool;
    public function destroy(Model $model): bool;
    public function getPaginatedProducts(array $filters, int $perPage = 15): LengthAwarePaginator; // or getPaginated
}

// Commerce — checkout specific:
class OrderService
{
    public static function findById(int $id): ?Order
    public static function findByField(string $field, mixed $value): ?Order  // e.g. 'transaction_info->post->Authority'
    public function create(array $cartItems, array $orderData, ?string $userDescription = null): Order
    public function checkout(Order $order): RedirectResponse
    public function callback(string $field, string $value, array $data): RedirectResponse
    public function handleSuccessOrderPayment(Order $order): RedirectResponse
    public function handleFailedPayment(Order $order): void
    public function cancelOrder(Order $order): void
}

class BankPaymentService
{
    public function handlePayment(Bank $bank, Order $order, int $price): RedirectResponse
    public function handleVerify(Order $order, array $callbackData, int $price): bool
}

class CartService // singleton
{
    public function content(): Collection
    public function add(Product $product, int $qty, ?int $variantId=null, ?string $color=null, ?string $size=null): string
    public function update(string $rowId, int $qty): void
    public function remove(string $rowId): void
    public function clear(): void
    public function subtotal(): int
    public function discount(): int
    public function total(): int
    public function roundedWeight(): int
    public function toArray(): array
}
```

### 4.3 Side-effect helpers are `protected`/`private` and named `sync*` / `append*` / `prepare*`

```php
// Inside ProductService — never called by Controller, only by store()/update()
private function prepareData(ProductDTO $dto): array
private function appendImages(Product $product, array $files): array
private function setMainImage(Product $product, ?int $mainId, ?int $mainNewIndex, array $newImages): void
private function syncSpecifications(Product $product, ProductDTO $dto): void
private function syncVariants(Product $product, ProductDTO $dto): void
private function syncProperties(Product $product, ProductDTO $dto): void
private function syncAssociations(Product $product, ProductDTO $dto): void
```

### 4.4 `__invoke` / `execute` — **not present**

`grep -rn "__invoke|function execute" app/` returns only `App\Helpers\Sms::execute(string $url)` and `Sms::executePost(string $url, $postData)` — HTTP helpers, not domain actions. There is no `fn __invoke(...): ...` anywhere else. Do not add it.

---

## 5. Usage Examples — From Real Source

### 5.1 Product — full flow

```php
// app/Http/Controllers/Admin/ProductController.php
use App\DTOs\ProductDTO;
use App\Services\ProductService;

class ProductController extends Controller
{
    public function __construct(protected ProductService $productService) {}

    public function store(ProductRequest $request)
    {
        $productDTO = ProductDTO::fromRequest($request); // validated → normalized
        $this->productService->store($productDTO);       // DB::transaction + images + specs
        return redirect()->route('admin.products.index');
    }

    public function update(ProductRequest $request, Product $product)
    {
        $productDTO = ProductDTO::fromRequest($request);
        $this->productService->update($product, $productDTO);
        return redirect()->route('admin.products.index');
    }

    public function destroy(Product $product)
    {
        $this->productService->destroy($product); // delete DB rows + ImageHandler::delete folder
        return back();
    }
}
```

### 5.2 HeroSlide / Brand — same verb trio

```php
// app/Http/Controllers/Admin/HeroSlideController.php
public function store(HeroSlideRequest $request)
{
    $dto = HeroSlideDTO::fromRequest($request);
    $this->service->store($dto); // HeroSlideService::store(HeroSlideDTO): HeroSlide
}

// app/Http/Controllers/Admin/BrandController.php
$dto = BrandDTO::fromRequest($request);
$this->brandService->store($dto);  // BrandService::store(BrandDTO): Brand
```

### 5.3 Cart + Checkout — singleton wired in `AppServiceProvider`

```php
// app/Providers/AppServiceProvider.php
public function register(): void
{
    $this->app->singleton(CartService::class);
    $this->app->singleton(WishlistService::class);
    $this->app->singleton(RecentlyViewedService::class);
}

// Anywhere via DI or `app(CartService::class)`:
$cart = app(CartService::class); // same instance per request
$cart->add($product, 1, $variantId);
$cart->subtotal(); // int (rial/toman)
$items = $cart->toArray(); // passed to OrderService::create($items, $orderData)

// app/Http/Controllers/Site/CheckoutController.php
public function create(CardToCardCheckoutRequest $request)
{
    $addressId = session('checkout_address_id');
    $shippingMethodId = session('checkout_shipping_method_id');
    $orderData = [
        'address_id'=>$address->id, 'state_id'=>$address->state_id,
        'bank_id'=>$request->input('bank_id'),
        'shipping_price'=>session('checkout_shipping_price', 0),
        'total_price'=>$this->cart->subtotal(), 'payment_price'=>$this->cart->total() + $shippingMethodPrice,
        // ...
    ];
    $order = $this->orderService->create($this->cart->toArray(), $orderData, $request->input('user_description'));
    if ($couponSession = session('coupon')) $this->couponService->recordUsage(...);
    $this->cart->clear(); session()->forget('coupon');
    return $this->orderService->checkout($order); // → BankPaymentService→ZarinPal or card_to_card pending_confirmation
}
```

### 5.4 Order callback — service-to-service via DTO bag

```php
// app/Http/Controllers/Site/CheckoutController.php
public function finishZarinPal(Request $request)
{
    $input = $request->all(); // ['Authority'=>..., 'Status'=>'OK', ...]
    return $this->orderService->callback('transaction_info->post->Authority', $input['Authority'] ?? '', $input);
}
// app/Services/OrderService.php
public function callback(string $field, string $value, array $data): RedirectResponse
{
    $order = self::findByField($field, $value); // where('transaction_info->post->Authority', $value)
    if (in_array($order->order_status, ['paid','deposit_paid']))
        return redirect()->route('checkout.success', $order->id); // duplicate guard
    $isSuccess = $this->bankPaymentService->handleVerify($order, $data, $price);
    return $isSuccess ? $this->handleSuccessOrderPayment($order) : $this->handleFailedPayment($order);
}

// app/Services/BankPaymentService.php
public function handlePayment(Bank $bank, Order $order, int $price): RedirectResponse
{
    $gateway = PaymentGatewayFactory::create($bank, config('app.name','Shop'));
    $response = $gateway->getBankToken($price, $order); // → PaymentPostDTO
    Auth::loginUsingId($order->user_id);
    if ($response->getStatus()==='failed') { $order->delete(); return redirect(route('checkout.failed')); }
    $order->update(['transaction_info'=>['post'=>$response->getPostData(),'verify'=>[]]]);
    ob_start(); $gateway->redirect($response, $order); $content=ob_get_clean(); return response($content);
}
```

### 5.5 Audit / Notification — static + try/catch

```php
// app/Services/OrderService.php — every status change is wrapped so audit never breaks checkout
try { AuditService::orderCreated($order); } catch (\Throwable $e) { Log::error('AuditLog failed ...'); }
try { AuditService::orderStatusChanged($order, $old, $new, 'سیستم - پرداخت موفق'); } catch (\Throwable $e) { Log::error(...); }
try {
    $notif = app(NotificationService::class);
    $notif->notifyUser($order->user, 'order.paid', 'پرداخت تایید شد', "...", route('dashboard.order-detail',$order->id), ['order_id'=>$order->id]);
    $notif->notifyAdmins('order.paid_admin', ...);
} catch (\Throwable $e) { Log::error('Notification failed ...'); }
```

---

## 6. Invariants & Rules for Service Authors

- **Singletons are only `CartService`, `WishlistService`, `RecentlyViewedService`** (registered in `AppServiceProvider::register`). All other Services are **transient** — instantiated per injection, never cached with state.
- **Transactions:** every multi-table write sits in `DB::transaction(...)`. `ProductService::store/update/destroy`, `StockService::reserveStock`, `Order creation` all follow this. Do not partially-wrap — the entire verb must be atomic.
- **No `__invoke`/`execute`:** keep verbs explicit (`store`, `update`, `checkout`, `handlePayment`). If you need a one-liner facade, add a method on the existing Service — do not spin an `Action` class.
- **Never throw audit/notification errors up:** all `AuditService`/`NotificationService` calls are `try/catch (\Throwable)` so checkout/search never fails because audit table is down.
- **DTO boundary:** Services that create/update from HTTP **must accept a DTO**, not `Request` or `array`. Exception is `OrderService::create(array $cartItems, array $orderData)` — it accepts cart/session arrays because cart is already normalized by `CartService::toArray()`.
- **Price math stays in Service, not Controller:** `final_price = discount_price ?? price`, `payment_price = subtotal - discount + shipping` — always computed in Service or `CartService::total()`, never in Blade.
- **Cache invalidation lives in Model `booted()` + Service `reorder`:** `PromoBanner::booted()`/`TrustBadge::booted()` call `Cache::forget(cacheKey)` on `saved/deleted`; `reorder` also clears. Future cached entities must follow same.

---

## 7. Adding a New Service — Checklist

- [ ] Place under `app/Services/XxxService.php` namespace `App\Services`.
- [ ] Expose verbs `store(XxxDTO): Model`, `update(Model,XxxDTO): bool`, `destroy(Model): bool` if catalog; or domain verbs (`send`, `verify`, `apply`, `reserve`) if cross-cutting.
- [ ] Wrap multi-table writes in `DB::transaction`.
- [ ] Wrap `AuditService`/`NotificationService` calls in `try/catch`.
- [ ] Register as singleton **only** if it holds request-scoped mutable state (cart/wishlist); otherwise leave transient.
- [ ] Never add `app/Actions/` — keep logic in Services unless architectural RFC is approved.
