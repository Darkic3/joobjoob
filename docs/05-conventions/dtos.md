# DTOs & Data Transfer Objects

> **Source of Truth:** `app/DTOs/*.php` (9 files) + `app/Payments/PaymentPostDTO.php` + `app/Payments/PaymentVerifyDTO.php`
> **Pattern:** `readonly class` + `fromRequest(FormRequest)` static factory + typed getters (`getX()`) or public props
> **Where used:** `app/Http/Controllers/Admin/{Product,Brand,Blog,HeroSlide,PromoBanner,...}Controller.php` → `DTO::fromRequest($request)` → `Service::store/update(DTO)`

---

## 1. Purpose — Why DTOs Here?

**Problem DTO solves:** Admin forms send 15–30 fields (scalars + arrays + `UploadedFile`s + nested structures like `variants`, `specs`, `trusts`). Passing raw `Request` or associative `array` to `ProductService` would leak HTTP concerns into the domain and require ad-hoc key checks (`isset($data['price']) ? (int)$data['price'] : 0`) duplicated across `store`/`update`.

**Joobjoob convention — when to use DTO vs `array`:**

| Use DTO | Use `array` |
|---------|-------------|
| Admin domain with **≥5 fields**, **nested/file data**, or **normalization** (e.g. `ProductDTO`, `HeroSlideDTO`, `BlogCategoryDTO`) | Payments DTOs (`PaymentPostDTO`/`PaymentVerifyDTO` are *internal* gateway results: `status + post_data/verify_data` — intentionally untyped `array` bag for ZarinPal passthrough) |
| Controller → Service boundary where **type safety + IDE autocomplete** matters | Service-internal enrichment (e.g. `CartService::toArray()` returns flat `array` cart items consumed by `OrderService::create(array $cartItems, array $orderData)`) |
| Fields require **canonical normalization** before persistence (e.g. `HeroSlideDTO::normalizeTrusts`, `ProductDTO::normalizeSpecs`) | Checkout/session ephemeral bags (`session('coupon')`, `session('checkout_address_id')`) — no DTO, validated inline |

**Consequence for Vibe Coding:** If you add a new admin resource, **define a `readonly class XxxDTO`** mirroring `PromoBannerDTO/TrustBadgeDTO` shape, expose `fromRequest(XxxRequest)` and typed getters, and consume it only from the Service. Never let `Controller` call `Model::create($request->validated())` directly — route through DTO so normalization stays single-sourced.

---

## 2. Complete Inventory (11 DTOs)

### 2.1 `app/DTOs/*` — Admin domain (9)

| DTO | Request | File | Primary Service |
|-----|---------|------|-----------------|
| `ProductDTO` | `ProductRequest` | `app/DTOs/ProductDTO.php` | `ProductService::store(ProductDTO)` / `update(Product, ProductDTO)` |
| `ProductCategoryDTO` | `ProductCategoryRequest` | `app/DTOs/ProductCategoryDTO.php` | `ProductCategoryService` (inferred controller usage) / direct controller |
| `BrandDTO` | `BrandRequest` | `app/DTOs/BrandDTO.php` | `BrandService::store/update(BrandDTO)` |
| `BlogDTO` | `BlogRequest` | `app/DTOs/BlogDTO.php` | `BlogService::store/update(BlogDTO)` |
| `BlogCategoryDTO` | FormRequest array `validated` | `app/DTOs/BlogCategoryDTO.php` | `BlogCategoryService` (calls `BlogCategoryDTO::fromRequest(array $validated)`) — note: takes `array $validated` not `FormRequest` |
| `HeroSlideDTO` | `HeroSlideRequest` | `app/DTOs/HeroSlideDTO.php` | `HeroSlideService::store/update(HeroSlideDTO)` |
| `PromoBannerDTO` | `PromoBannerRequest` | `app/DTOs/PromoBannerDTO.php` | `PromoBannerService::store/update(PromoBannerDTO)` |
| `SpecificationDTO` | `SpecificationRequest` | `app/DTOs/SpecificationDTO.php` | `SpecificationService::store/update(SpecificationDTO)` |
| `TrustBadgeDTO` | `TrustBadgeRequest` | `app/DTOs/TrustBadgeDTO.php` | `TrustBadgeService::store/update(TrustBadgeDTO)` |

### 2.2 `app/Payments/*` — Gateway result bags (2)

| DTO | File | Creator | Semantics |
|-----|------|---------|-----------|
| `PaymentPostDTO` | `app/Payments/PaymentPostDTO.php` | `ZarinPal::getBankToken(...)` → `PaymentPostDTO::fromData('success',['Authority'=>...])` | `status` (`'success'|'failed'`) + `post_data: array` (holds `Authority`) |
| `PaymentVerifyDTO` | `app/Payments/PaymentVerifyDTO.php` | `ZarinPal::verifyTransaction(...)` → `PaymentVerifyDTO::fromData('success',['RefID'=>...])` | `status` + `verify_data: array` (holds `RefID`) |

---

## 3. Structure / Signature — Exact

### 3.1 Pattern A — `readonly class` + private fields + `fromRequest` + getters (majority)

**Canonical example — `ProductDTO`:**

```php
// app/DTOs/ProductDTO.php
readonly class ProductDTO
{
    public function __construct(
        private int $productCategoryId,
        private ?int $brandId,
        private array $name,                // ['fa'=>..., 'en'=>...]
        private string $slug,
        private ?array $shortDescription,   // ['fa'=>..., 'en'=>...] | null
        private ?array $description,
        private ?string $sku,
        private int $price,
        private ?int $discountPrice,
        private int $stock,
        private ?int $weight,
        private bool $isActive,
        private bool $isFeatured,
        private bool $isBestSeller,
        private ?string $dealEndsAt,
        private ?string $startTimer,
        private ?string $endTimer,
        private bool $timerActive,
        private array $specs,         // [parent_id => [child_spec_id, ...]]
        private array $textSpecs,     // [specification_id => value]
        private array $variants,      // [['id'=>?int,'spec_ids'=>int[],'price'=>int,'discount_price'=>int,'stock'=>int,'weight'=>int],...]
        private array $properties,    // [['id'=>?int,'title'=>['fa'=>string,'en'=>string],'value'=>string],...]
        private array $relatedIds,    // int[]
        private array $complementIds, // int[]
        private array $images,        // UploadedFile[]
        private array $deleteImages,  // int[] (existing ProductImage ids to delete)
        private ?int $mainImageId,
        private ?int $mainNewIndex,
    ) {}

    public static function fromRequest(ProductRequest $request): self { /* ...normalize & map... */ }

    // Getters — one per field:
    public function getProductCategoryId(): int
    public function getBrandId(): ?int
    public function getName(): array
    public function getSlug(): string
    public function getShortDescription(): ?array
    public function getDescription(): ?array
    public function getSku(): ?string
    public function getPrice(): int
    public function getDiscountPrice(): ?int
    public function getStock(): int
    public function getWeight(): ?int
    public function getIsActive(): bool
    public function getIsFeatured(): bool
    public function getIsBestSeller(): bool
    public function getDealEndsAt(): ?string
    public function getStartTimer(): ?string
    public function getEndTimer(): ?string
    public function getTimerActive(): bool
    public function getSpecs(): array
    public function getTextSpecs(): array
    public function getVariants(): array
    public function getProperties(): array
    public function getRelatedIds(): array
    public function getComplementIds(): array
    public function getImages(): array
    public function getDeleteImages(): array
    public function getMainImageId(): ?int
    public function getMainNewIndex(): ?int

    // private helpers:
    private static function normalizeSpecs(mixed $input): array        // [parent=>children] → int-keyed
    private static function normalizeTextSpecs(mixed $input): array    // [specId=>value] trim+filter
}
```

**Other DTOs follow identical getter naming:**

```php
// BrandDTO — readonly, private fields
readonly class BrandDTO {
    public function __construct(private array $title, private string $url, private ?array $description, private ?UploadedFile $image, private bool $removeImage, private bool $isActive, private bool $showInFirstPage) {}
    public static function fromRequest(BrandRequest $request): self
    // getters: getTitle(): array, getUrl(): string, getDescription(): ?array, getImage(): ?UploadedFile, getRemoveImage(): bool, getIsActive(): bool, getShowInFirstPage(): bool
}

// BlogDTO
readonly class BlogDTO {
    public function __construct(private string $title, private string $slug, private ?string $excerpt, private string $content, private ?int $blogCategoryId, private bool $isActive, private bool $showInFirstPage, private ?UploadedFile $image, private bool $removeImage, private ?int $userId) {}
    public static function fromRequest(BlogRequest $request, ?int $userId = null): self
    // getters: getTitle, getSlug, getExcerpt, getContent, getBlogCategoryId, getIsActive, getShowInFirstPage, getImage, getRemoveImage, getUserId
}

// ProductCategoryDTO
readonly class ProductCategoryDTO {
    public function __construct(private ?int $parentId, private array $name, private string $slug, private ?string $icon, private ?UploadedFile $image, private ?array $description, private ?array $metaTitle, private ?array $metaDescription, private int $sortOrder, private bool $isActive) {}
    public static function fromRequest(ProductCategoryRequest $request): self
    // getters: getParentId, getName, getSlug, getIcon, getImage, getDescription, getMetaTitle, getMetaDescription, getSortOrder, getIsActive
}

// SpecificationDTO
readonly class SpecificationDTO {
    public function __construct(private array $title, private string $type, private ?int $parentId, private bool $isActive, private bool $isFilter, private bool $isColor, private ?string $colorCode) {}
    public static function fromRequest(SpecificationRequest $request): self
    // getters: getTitle, getType, getParentId, getIsActive, getIsFilter, getIsColor, getColorCode
    // note: isFilter/isColor forced false unless type==='select'
}

// PromoBannerDTO
readonly class PromoBannerDTO {
    public function __construct(private string $icon, private array $title, private ?array $subtitle, private ?string $url, private string $style, private string $tone, private bool $isActive) {}
    public static function fromRequest(PromoBannerRequest $request): self
    // getters: getIcon, getTitle, getSubtitle, getUrl, getStyle, getTone, getIsActive
}

// TrustBadgeDTO
readonly class TrustBadgeDTO {
    public function __construct(private string $icon, private array $title, private ?array $description, private bool $isActive, private string $placement = 'site') {}
    public static function fromRequest(TrustBadgeRequest $request): self
    // getters: getIcon, getTitle, getDescription, getIsActive, getPlacement
}

// BlogCategoryDTO — takes array $validated not FormRequest, exposes toArray()
readonly class BlogCategoryDTO {
    public function __construct(public ?int $parent_id, public string $title, public string $slug, public ?string $description, public ?UploadedFile $image, public bool $is_active, public bool $show_in_first_page, public int $sort_order) {}
    public static function fromRequest(array $validated): self
    public function toArray(): array  // → ['parent_id','title','slug','description','is_active','show_in_first_page','sort_order'] (no file)
}
```

### 3.2 Pattern B — `readonly class` + **public** props (HeroSlideDTO only)

```php
// app/DTOs/HeroSlideDTO.php — stands out: public props, no getters for core fields
readonly class HeroSlideDTO {
    public function __construct(
        public array $title,          // ['fa'=>..., 'en'=>...]
        public array $desc,
        public array $badge,
        public bool $button_active,
        public array $btn_text,
        public ?string $link_type,    // null|'custom'|'page'|'product'|'product_category'|'blog'|'blog_category'
        public ?string $link_value,
        public ?int $price,
        public ?int $old_price,
        public string $currency,      // 'IRT'|'USD'|'EUR'
        public bool $rating_active,
        public ?string $rating,
        public bool $viewers_active,
        public ?int $viewers,
        public array $trusts,         // [['icon'=>string,'text'=>['fa'=>string,'en'=>string]],...] max 5
        public ?UploadedFile $image,
        public bool $removeImage,
        public bool $is_active,
    ) {}
    public static function fromRequest(HeroSlideRequest $request): self
    private static function normalizeTrusts(array $raw): array
    public function getLinkableType(): ?string  // maps link_type → model FQCN or null
    public function getLinkableId(): mixed
}
```

### 3.3 Pattern C — Payments: mutable bag + `fromData` (not `fromRequest`)

```php
// app/Payments/PaymentPostDTO.php
class PaymentPostDTO {
    protected string $status;     // 'success' | 'failed'
    protected array $post_data;   // ['Authority'=>string] on success, [] on failed
    public function getStatus(): string
    public function getPostData(): array
    public static function fromData(string $status, array $postData = []): self
}

// app/Payments/PaymentVerifyDTO.php — identical shape
class PaymentVerifyDTO {
    protected string $status;
    protected array $verify_data; // ['RefID'=>string] on success
    public function getStatus(): string
    public function getVerifyData(): array
    public static function fromData(string $status, array $verifyData = []): self
}
```

**Key difference:** Payments DTOs are **NOT `readonly`**, NOT constructed via `fromRequest`, and are **gateway-internal** — they model ZarinPal HTTP result, not HTTP input.

---

## 4. Validation Rules — Source in DTO vs FormRequest

DTOs themselves contain **NO `rules()`** — validation lives strictly in `FormRequest` (`app/Http/Requests/Admin/*Request.php`). DTO assumes already-validated data:

| Layer | Responsibility | Example |
|-------|---------------|---------|
| `FormRequest::rules()` | declarative validation (`required|array`, `exists:brands,id`, `image|max:2048`) | `ProductRequest` | 
| `FormRequest::prepareForValidation()` | coercion that affects validation (auto-slug) | `ProductRequest::prepareForValidation()` → `prepareSlug($this->name['fa'])` |
| `DTO::fromRequest()` | **business normalization** after validation | `ProductDTO::fromRequest` filters empty `variants` rows (`price<=0 && stock<=0` skipped), `HeroSlideDTO::normalizeTrusts` slices to `HeroSlide::MAX_TRUST_ITEMS`, `SpecificationDTO` forces `isFilter/isColor=false` unless `type==='select'` |
| `DTO::fromRequest()` | type coercion (`(int)`, `boolean()`, `integer()`) | `$request->boolean('is_active')`, `$request->integer('viewers', 0)` |
| `DTO::fromRequest()` | conditional nulling (`button_active? link_type : null`) | `HeroSlideDTO` → `$linkType = $buttonActive ? $request->input('link_type') : null` |

---

## 5. Usage Examples — From Real Source

### 5.1 Controller → DTO → Service (Product — canonical)

```php
// app/Http/Controllers/Admin/ProductController.php
use App\DTOs\ProductDTO;
use App\Services\ProductService;

class ProductController extends Controller
{
    public function __construct(protected ProductService $productService) {}

    public function store(ProductRequest $request)  // validated first
    {
        $productDTO = ProductDTO::fromRequest($request);  // DTO built from validated request
        $this->productService->store($productDTO);        // Service receives DTO, never raw Request
        return redirect()->route('admin.products.index');
    }

    public function update(ProductRequest $request, Product $product)
    {
        $productDTO = ProductDTO::fromRequest($request);
        $this->productService->update($product, $productDTO);
        return redirect()->route('admin.products.index');
    }
}
```

```php
// app/Services/ProductService.php
use App\DTOs\ProductDTO;

class ProductService
{
    protected const UPLOAD_FOLDER = 'products';
    protected const IMAGE_SIZES = ['thumb'=>[300,300],'medium'=>[800,800],'large'=>[1200,1200]];

    public function store(ProductDTO $dto): Product
    {
        return DB::transaction(function () use ($dto) {
            $data = $this->prepareData($dto);  // maps DTO getters → $fillable array
            $product = Product::create($data);
            $newImages = $this->appendImages($product, $dto->getImages());
            $this->setMainImage($product, $dto->getMainImageId(), $dto->getMainNewIndex(), $newImages);
            $this->syncSpecifications($product, $dto);
            $this->syncVariants($product, $dto);   // DTO carries variants[] already normalized
            return $product;
        });
    }

    private function prepareData(ProductDTO $dto): array
    {
        return [
            'product_category_id' => $dto->getProductCategoryId(),
            'brand_id'            => $dto->getBrandId(),
            'name'                => $dto->getName(),
            'slug'                => $dto->getSlug(),
            'price'               => $dto->getPrice(),
            'discount_price'      => $dto->getDiscountPrice(),
            'final_price'         => $dto->getDiscountPrice() ?? $dto->getPrice(),
            // ...
        ];
    }
}
```

### 5.2 Brand / Blog (simpler DTOs)

```php
// app/Http/Controllers/Admin/BrandController.php
$dto = BrandDTO::fromRequest($request);   // title:[fa,en], url, description, image, active
$this->brandService->store($dto);

// app/Http/Controllers/Admin/BlogController.php
$dto = BlogDTO::fromRequest($request, auth()->id()); // userId injected as 2nd arg
$this->blogService->store($dto);
```

### 5.3 BlogCategory — array-based `fromRequest`

```php
// app/Http/Controllers/Admin/BlogCategoryController.php (inferred)
$dto = BlogCategoryDTO::fromRequest($request->validated()); // passes array, not FormRequest
$dto->toArray(); // only persistable fields, file handled separately via $dto->image
```

### 5.4 HeroSlide — public props + conditional nulling

```php
// app/Http/Controllers/Admin/HeroSlideController.php
public function store(HeroSlideRequest $request)
{
    $dto = HeroSlideDTO::fromRequest($request);
    // dto->button_active===false → dto->link_type === null (cleared inside fromRequest)
    // dto->trusts already sliced to 5 elements
    $this->service->store($dto);
}

// app/Services/HeroSlideService.php consumes dto->title, dto->trusts directly (public props)
```

### 5.5 Promo / Trust / Specification

```php
// PromoBannerController / TrustBadgeController follow identical one-liner:
$dto = PromoBannerDTO::fromRequest($request); $this->promoBannerService->store($dto);
$dto = TrustBadgeDTO::fromRequest($request);  $this->trustBadgeService->store($dto);
$dto = SpecificationDTO::fromRequest($request); // isFilter forced false unless type==='select'
```

### 5.6 Payments — gateway-internal DTOs

```php
// app/Payments/ZarinPal.php
public function getBankToken(int $amount, Order $order): PaymentPostDTO
{
    $payload = ['merchant_id'=>$this->merchant,'amount'=>intval($amount.'0'), ...];
    $resp = $this->sendJsonRequest(self::EP_REQUEST, $payload);
    if (!$resp['ok']) return PaymentPostDTO::fromData('failed');
    if ((int)($resp['json']['data']['code']??-1)===100)
        return PaymentPostDTO::fromData('success', ['Authority'=>$resp['json']['data']['authority']]);
    return PaymentPostDTO::fromData('failed');
}

// app/Services/BankPaymentService.php — consumed via getter
$response = $gateway->getBankToken($price, $order);
if ($response->getStatus() === 'failed') { $order->delete(); /* ... */ }
$order->update(['transaction_info'=>['post'=>$response->getPostData(),'verify'=>[]]]);

// verify path
$response = $gateway->verifyTransaction($callbackData, $price, $order);
if ($response->getStatus()==='success') $ref = $response->getVerifyData()['RefID'];
```

---

## 6. Invariants & Rules for DTO Authors

- **DTOs are immutable:** all 9 admin DTOs are `readonly class`; no setters, no `withX()` cloners. Payments DTOs are mutable bags but treated as value objects — never mutated after `fromData`.
- **Construction boundary:** admin DTOs **only** via `fromRequest(FormRequest)` (or `fromRequest(array $validated)` for `BlogCategoryDTO`). Never `new ProductDTO(...)` in controllers; always go through factory so normalization is not bypassed.
- **Validation boundary:** `FormRequest` validates, DTO normalizes. Do not duplicate `rules()` logic inside `fromRequest` (e.g. required checks). DTO may still guard (`if price<=0 && stock<=0 skip variant`) — that is business filtering, not input validation.
- **`type` narrowing:** `SpecificationDTO` narrows: `isFilter/isColor` are `false` unless `type==='select'`; `HeroSlideDTO` narrows: `link_type/link_value/rating/viewers` become `null` when owning flag is `false`. Replicate this pattern for future flag-gated fields.
- **File handling:** DTO carries `?UploadedFile $image` raw; **Service persists the file**, not DTO. DTO never calls `store()` — service calls `ImageHandler::handle('products', $dto->getImage())`. `ProductDTO::images` is `UploadedFile[]` for bulk.
- **No service logic in DTO:** DTO helpers are pure normalizers (`normalizeSpecs`, `normalizeTrusts`, `normalizeTextSpecs`) — no DB, no `config()`, no HTTP. Only exception is `HeroSlideDTO::getLinkableType()` which maps string → FQCN (`'product'=>Product::class`) — pure mapping, no query.
- **Payments DTOs are separate pattern:** do not extend them with `readonly` or `fromRequest`; they model **outbound** ZarinPal result and stay as `status+array` bag for forward-compatibility with additional gateways.
- **Naming:** `XxxDTO` in `App\DTOs` namespace; payments bags in `App\Payments` namespace as `PaymentPostDTO/VerifyDTO` (not moved to `DTOs` — intentional colocation with `ZarinPal`).

---

## 7. Adding a New DTO — Checklist

- [ ] Create `app/DTOs/XxxDTO.php` as `readonly class XxxDTO` with typed constructor (private fields + getters, or public props if you mirror `HeroSlideDTO`).
- [ ] Implement `public static function fromRequest(XxxRequest $request): self` — map `validated()`, `boolean()`, `integer()`, `file()`; add normalization helpers for arrays.
- [ ] Ensure `FormRequest` stays source of truth for `rules()`; DTO only normalizes.
- [ ] Update Service to accept `XxxDTO` not `array|Request` and wire `Controller → DTO::fromRequest → Service::store(DTO)`.
- [ ] Add getters for every field; if array holds `UploadedFile[]`, expose as `array` getter (not spread).
