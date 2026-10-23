# Enums & Constants

> **Source of Truth:** No native `enum` declarations in `app/`. Constants live on Models and Services; a handful project-specific types are free-form `string` columns guarded by `in:` validation.
> **Pattern:** `public const` arrays/scalars on Model/Service as single source of truth for validation + labels + helpers. Enum-like behavior is via `const` + `match` + helper methods, not PHP `enum`.

---

## 1. Purpose — Why Constants, Not Enums Here

- **Joobjoob declares no PHP-native `enum`** (`grep -rn "enum " app/` returns only a comment). Instead it uses **`public const` + `protected $casts`** + **`in:` validation** to achieve enum semantics:

  | Type | Storage | Validation | Helpers |
  |------|---------|-----------|---------|
  | `PromoBanner::ICONS / TONES` | string cols `icon`, `tone` | `in:`.implode(',',ICONS) in `PromoBannerRequest` | Used directly; no `label()` |
  | `Review::STATUS_*` / `ReviewVote::TYPE_*` | integer / string cols | `scopeApproved/Pending` | `getIsApprovedLabelAttribute()` + `votes()->where('type',TYPE_LIKE)` |
  | `NotificationService::TYPES` | string `type` on Notification | Service-driven, not validated by FormRequest | icon+color map + fallback `['icon'=>'🔔','color'=>'accent']` |
  | `Order::$order_status` / `Bank::$bank_type` | string cols | `in:cash,percent` pattern for Discount; no `Rule::in` for Bank/Order (value checked in Service) | `Order::getStatusAttribute()` → `match` label+badge |

- **Consequence for Vibe Coding:** to add a new enumerated domain (e.g. `ShipmentStatus`), define `public const VALUES = [...]` on the owning Model or Service, wire `in:`.VALUES in the FormRequest, and add `match`/accessors for labels. Do not introduce `app/Enums/*.php` native enum without team consensus — the codebase expects const-based lookup and `__()` translations for labels.

---

## 2. Complete Inventory — Constants by Owner

### 2.1 Models — domain enumerations

| Owner | Constant(s) | File | Column(s) | Validation |
|-------|-------------|------|-----------|------------|
| `PromoBanner` | `ICONS = ['megaphone','truck','ticket','gift','percent','star','fire','lightning','heart','sparkles']` (10) <br> `TONES = ['accent','gold','emerald','rose','blue','orange','violet']` (7) | `PromoBanner.php:13,21` | `icon: string`, `tone: string` | `PromoBannerRequest: 'icon' => 'required|in:'.implode(',',ICONS)`, `tone => 'required|in:'.implode(',',TONES)` |
| `HeroSlide` | `MAX_TRUST_ITEMS = 5` <br> `MAX_TRUST_ITEMS_SHOWN = 3` | `HeroSlide.php:12,15` | `trusts: json` (sliced), display limit | `HeroSlideDTO::normalizeTrusts` `array_slice(...,MAX_TRUST_ITEMS)` + `rules: 'trusts' => 'array|max:5'` |
| `Review` | `STATUS_PENDING=0`, `STATUS_APPROVED=1`, `STATUS_REJECTED=2` | `Review.php:35-37` | `is_approved: tinyint` | `scopePending/Approved` + admin toggle |
| `ReviewVote` | `TYPE_LIKE='like'`, `TYPE_DISLIKE='dislike'` | `ReviewVote.php:20-21` | `type: string` | not validated by FormRequest — delegated to controller guard |
| `NotificationService` | `TYPES = ['order.created'=>['icon'=>'📦','color'=>'accent'], 'order.paid'=>['icon'=>'✅','color'=>'success'], ... 11 entries]` | `NotificationService.php:13` | `type: string` on Notification | service-internal; fallback `['icon'=>'🔔','color'=>'accent']` if unknown type |
| `Order` (implicit) | `order_status ∈ {'paying','paid','deposit_paid','pending_confirmation','wait_for_verification','unpaid','cancelled'}` — NO `const` array exists; enumeration extracted from `Order::getStatusAttribute()` match | `Order.php:114` | `order_status: string` | not validated via `Rule::in` — guarded in `OrderService::checkout/callback/cancelOrder` + observer |
| `Discount` (implicit) | `type ∈ {'cash','percent'}` <br> `pay_type ∈ {null,'category','brand','product'}` | `Discount.php` + `DiscountRequest` | `type`, `pay_type` | `DiscountRequest: 'type'=>'required|in:cash,percent'`, `pay_type'=>'nullable|in:category,brand,product'` |
| `Bank` (implicit) | `bank_type ∈ {'zarinPal','card_to_card','cash'}` (cash inferred from legacy) | `Bank.php` + `PaymentGatewayFactory` | `bank_type: string` | factory `switch` on `bank_type`; dead `card_to_card=>ZarinPal` branch documented |
| `Specification` (implicit) | `type ∈ {'text','select'}` | `Specification.php` | `type: string` | `SpecificationRequest: 'type'=>'required|in:text,select'`; `is_filter/is_color` forced `false` unless `type==='select'` in `SpecificationDTO` |

### 2.2 Payments & Gateway

| Owner | Constant | File | Purpose |
|-------|----------|------|---------|
| `ZarinPal` | `EP_REQUEST = 'https://payment.zarinpal.com/pg/v4/payment/request.json'` | `ZarinPal.php:14` | POST request token |
| `ZarinPal` | `EP_VERIFY = 'https://payment.zarinpal.com/pg/v4/payment/verify.json'` | `ZarinPal.php:15` | POST verify |
| `ZarinPal` | `EP_REDIRECT = 'https://payment.zarinpal.com/pg/StartPay/'` | `ZarinPal.php:16` | Browser redirect `EP_REDIRECT . Authority` |
| `ZarinPal` | `CALLBACK_ROUTE_NAME = 'checkout.finish.zarin-pal'` | `ZarinPal.php:17` | `route(CALLBACK_ROUTE_NAME)` used for `callback_url` |
| `ZarinPal` | `USER_AGENT = 'ZarinPal Rest Api v1'` | `ZarinPal.php:18` | `Http` header |
| `PaymentPostDTO/VerifyDTO` | `status ∈ {'success','failed'}` | `Payments/*DTO.php` | not const array — checked via `getStatus()==='failed'` in `BankPaymentService` |

### 2.3 Services — business params as constants

| Service | Constant | File | Semantics |
|---------|----------|------|-----------|
| `ProductService` | `UPLOAD_FOLDER='products'`, `IMAGE_SIZES=[thumb=>[300,300],medium=>[800,800],large=>[1200,1200]]` | `ProductService.php:13,19` | `ImageHandler::handle(UPLOAD_FOLDER, file)` + `ImageResizer` cover-crop |
| `BlogService` | `UPLOAD_FOLDER='blogs'` | `BlogService.php:11` | — |
| `BrandService` | `UPLOAD_FOLDER='brands'`, `IMAGE_SIZES` | `BrandService.php` | — |
| `HeroSlideService` | `UPLOAD_FOLDER='hero-slides'` | `HeroSlideService.php:11` | — |
| `PromoBannerService` | `CACHE_KEY='promo_banners_active'`, `SITE_MAX_BANNERS=4` | `PromoBannerService.php:15,20` | `Cache::remember/get` + `booted: Cache::forget(CACHE_KEY)` |
| `TrustBadgeService` | `CACHE_KEY='trust_badges_active'`, `SITE_MAX_BADGES=6`, `FOOTER_MAX_BADGES=6` | `TrustBadgeService.php:15,20,25` | Same caching pattern |
| `WelcomeDiscountService` | `DISCOUNT_PERCENT=10`, `DISCOUNT_COUNT=1`, `MAX_USAGE_PER_USER=1`, `EXPIRY_DAYS=30`, `CODE_PREFIX='WELCOME-'`, `CODE_LENGTH=6` | `WelcomeDiscountService.php:21-26` | `WELCOME-XXXXXX` percent coupon on signup |
| `LocaleService` | `CACHE_ALL='joobjoob_languages_all'` | `LocaleService.php:21` | `Cache::rememberForever(CACHE_ALL, fn()=>Language::all())` |
| `SettingService` | `CACHE_ALL='joobjoob_settings_all'` | `SettingService.php:17` | `setting()` helper caching |
| `OtpService` | `CODE_LENGTH=4` | `OtpService.php:25` | — |
| `Audit` | `event ∈ {'order.created','order.status_changed','order.cancelled','order.stock_reserved','product.price_changed',...}` — const-like strings in `AuditService` | `AuditService.php` | `scopeEvent/Events` |
| `Notification` | `TYPES` map (11 entries) | `NotificationService.php:13` | icon+color per type |

### 2.4 Helpers & View

| Owner | Constant | File |
|-------|----------|------|
| `NumberHelper` | `ARABIC_THOUSANDS_SEP="\u{066C}"` | `Helpers/NumberHelper.php:55` |
| `PageController` | `PAGES=['contact','about','faq','terms','privacy']` | `Http/Controllers/Site/PageController.php:16` |
| `ProductCard` | `RANK_STYLES = [...]` | `View/Components/ProductCard.php:42` |
| `CategoryCard` | `ICON_MAP = [...]` | `View/Components/CategoryCard.php:22` |

---

## 3. Structure / Signature — Exact

### 3.1 Model const arrays + casts + accessors

```php
// app/Models/PromoBanner.php
class PromoBanner extends Model
{
    public const ICONS = ['megaphone','truck','ticket','gift','percent','star','fire','lightning','heart','sparkles'];
    public const TONES = ['accent','gold','emerald','rose','blue','orange','violet'];

    protected $fillable = ['icon','title','subtitle','url','style','tone','sort_order','is_active'];
    protected $casts = ['title'=>'array','subtitle'=>'array','is_active'=>'boolean'];

    public function scopeActiveOrdered($query){ return $query->where('is_active',true)->orderBy('sort_order')->orderBy('id'); }
    public function trans(string $field): string { /* app()->getLocale() with fa fallback */ }
    protected static function booted(){ static::saved(fn()=> Cache::forget('promo_banners_active')); static::deleted(fn()=> Cache::forget('promo_banners_active')); }
}

// app/Models/Review.php
class Review extends Model
{
    use HasFactory, SoftDeletes;
    const STATUS_PENDING=0, STATUS_APPROVED=1, STATUS_REJECTED=2;

    public function scopeApproved($q){ return $q->where('is_approved', self::STATUS_APPROVED); }
    public function scopePending($q){ return $q->where('is_approved', self::STATUS_PENDING); }
    public function getIsApprovedLabelAttribute(): string {
        return match($this->is_approved){
            self::STATUS_APPROVED => __('admin.reviews.status_approved'),
            self::STATUS_REJECTED => __('admin.reviews.status_rejected'),
            default              => __('admin.reviews.status_pending'),
        };
    }
    public function getLikesCountAttribute(): int { return $this->votes()->where('type', ReviewVote::TYPE_LIKE)->count(); }
    public function getAvatarLetterAttribute(): string { return mb_substr($this->display_name, 0, 1); }
}

// app/Models/ReviewVote.php
class ReviewVote extends Model {
    const TYPE_LIKE='like', TYPE_DISLIKE='dislike';
}

// app/Models/Specification.php
class Specification extends Model {
    protected $fillable=['title','type','parent_id','active','is_filter','is_color','color_code'];
    protected $casts=['title'=>'json','active'=>'boolean','is_filter'=>'boolean','is_color'=>'boolean'];
    public function getTypeNameAttribute(): string { return $this->type==='text' ? 'نوشتاری' : 'انتخابی'; }
}

// app/Models/Order.php — implicit enumeration via accessor (NO const array)
class Order extends Model {
    // fillable includes order_status, payment_price, receipt_tracking_code, receipt_payer_card, transaction_info (casts array)
    public function getStatusAttribute(): ?array {
        return match($this->order_status){
            'paying'              => ['title'=>'در حال پرداخت','badge'=>'warning'],
            'paid'                => ['title'=>'پرداخت شده','badge'=>'success'],
            'deposit_paid'        => ['title'=>'بیعانه پرداخت شده','badge'=>'success'],
            'pending_confirmation'=> ['title'=>'در انتظار تأیید پرداخت','badge'=>'warning'],
            'wait_for_verification'=>['title'=>'در انتظار تایید فیش ها','badge'=>'info'],
            'unpaid'              => ['title'=>'پرداخت نشده','badge'=>'danger'],
            'cancelled'           => ['title'=>'لغو شده','badge'=>'danger'],
            default => null,
        };
    }
    public function getBankTrackingCodeAttribute(): string {
        $tx = $this->transaction_info ?? []; $bt=$this->bank?->bank_type;
        if($bt==='card_to_card' && $this->receipt_tracking_code) return $this->receipt_tracking_code;
        return match($bt){ 'zarinPal'=>$tx['verify']['RefID']??'', 'sep'=>$tx['verify']['TraceNo']??'', default=>'' };
    }
    public function getIsCardToCardAttribute(): bool { return in_array($this->bank?->bank_type??'', ['card_to_card','card-to-card']); }
    public function getShippingNameAttribute(): string {
        if($this->freight_balance===1) return 'پس کرایه';
        if(intval($this->shipping_price)===0) return 'ارسال رایگان';
        return number_format($this->shipping_price).' تومان';
    }
}

// app/Models/Discount.php — validity + type checks
class Discount extends Model {
    use SoftDeletes;
    protected $casts=['is_active'=>'boolean','starts_at'=>'datetime','expires_at'=>'datetime','count'=>'integer','max_usage_per_user'=>'integer'];
    public function getAmountNameAttribute(): string { return $this->type==='cash' ? number_format($this->amount).' تومان' : $this->amount.' درصد'; }
    public function isCurrentlyValid(): bool { if(!$this->is_active) return false; $now=now(); if($this->starts_at && $this->starts_at->isAfter($now)) return false; if($this->expires_at && $this->expires_at->isBefore($now)) return false; return true; }
    public function hasReachedLimit(?int $userId=null): bool { if($this->count<=0) return true; if($this->usageRecords()->count() >= $this->count) return true; if($userId && $this->max_usage_per_user>0 && $this->usageRecords()->where('user_id',$userId)->count() >= $this->max_usage_per_user) return true; return false; }
    public function scopeValid($q){ $now=now(); return $q->active()->where(fn($qq)=> $qq->whereNull('starts_at')->orWhere('starts_at','<=',$now))->where(fn($qq)=> $qq->whereNull('expires_at')->orWhere('expires_at','>=',$now)); }
}

// app/Services/NotificationService.php — TYPES map
class NotificationService {
    public const TYPES = [
        'order.created'=>['icon'=>'📦','color'=>'accent'], 'order.paid'=>['icon'=>'✅','color'=>'success'],
        'order.shipped'=>['icon'=>'🚚','color'=>'info'],  'order.cancelled'=>['icon'=>'❌','color'=>'danger'],
        'discount.welcome'=>['icon'=>'🎁','color'=>'success'], 'discount.expiring'=>['icon'=>'⏰','color'=>'warning'],
        'order.new'=>['icon'=>'📦','color'=>'accent'], 'order.paid_admin'=>['icon'=>'✅','color'=>'success'],
        'order.card_to_card'=>['icon'=>'💳','color'=>'warning'], 'review.new'=>['icon'=>'💬','color'=>'info'],
        'contact.new'=>['icon'=>'✉️','color'=>'accent'],
    ];
    public function notifyUser(User $user, string $type, string $title, ?string $body=null, ?string $link=null, ?array $data=null): Notification
    public function notifyAdmins(string $type, string $title, ?string $body=null, ?string $link=null, ?array $data=null): int
    public function iconFor(string $type): string { return (self::TYPES[$type]??[])['icon'] ?? '🔔'; }
    public function colorFor(string $type): string{ return (self::TYPES[$type]??[])['color'] ?? 'accent'; }
}
```

### 3.2 No `enum` — how to read enum-like checks

```php
// Validation — declarative via const arrays:
'icon' => 'required|string|in:'.implode(',', PromoBanner::ICONS),
'tone' => 'required|in:'.implode(',', PromoBanner::TONES),
'type' => 'required|in:text,select',            // Specification — literal, not const array
'type' => 'required|in:cash,percent',            // Discount

// Service/Code — imperative via match/switch:
$title = match($this->type){ 'text'=>'نوشتاری', 'select'=>'انتخابی', default=>'' };
$label = PromoBanner::ICONS; // used as allowlist
if(!in_array($request->input('icon'), PromoBanner::ICONS, true)) { /* shouldn't happen — FormRequest already enforces */ }

// Order status badge — accessor returns array [title,badge]:
$order->status // null | ['title'=>'پرداخت شده','badge'=>'success']
```

---

## 4. Usage Examples — From Real Source

### 4.1 PromoBanner — ICONS/TONES round-trip

```php
// Validation
$request->validate(['icon'=>'required|in:'.implode(',', PromoBanner::ICONS), 'tone'=>'required|in:'.implode(',', PromoBanner::TONES)]);
// Persist
$banner = PromoBanner::create(['icon'=>$dto->getIcon(), 'tone'=>$dto->getTone(), 'is_active'=>$dto->getIsActive()]);
// Display — tone controls CSS class .promo-banner--{tone}
<div class="promo-banner--{{ $banner->tone }}">...</div>
```

### 4.2 Review status + votes

```php
Review::approved()->featured()->with('votes')->get();
$review->is_approved_label // via match: 'تایید شده' / 'رد شده' / 'در انتظار'
$review->likes_count        // votes()->where('type', ReviewVote::TYPE_LIKE)->count()
$review->dislikes_count
$review->is_reply           // parent_id !== null
```

### 4.3 Order status + discount validity

```php
// Badge in Blade
<span class="badge bg-{{ $order->status['badge'] }}">{{ $order->status['title'] }}</span>

// Discount flow
$discount = Discount::valid()->where('code',$code)->first();
if(!$discount->isCurrentlyValid()) throw ValidationException::withMessages(['code'=>'کد منقضی شده']);
if($discount->hasReachedLimit(auth()->id())) throw ValidationException::withMessages(['code'=>'سقف استفاده پر شده']);
$discount->amount_name // "10 درصد" or "50,000 تومان"

// Notification
app(NotificationService::class)->notifyUser($order->user, 'order.paid', 'پرداخت تایید شد', "...", route('dashboard.order-detail',$order->id), ['order_id'=>$order->id]);
app(NotificationService::class)->notifyAdmins('order.new', 'سفارش جدید', "سفارش #{$order->id} ...", route('admin.orders.show',$order->id), [...]);
```

### 4.4 Caps enforced by DTO + validation

```php
// HeroSlide trusts — validation caps array, DTO slices hard
$request->validate(['trusts'=>'nullable|array|max:5', 'trusts.*.fa'=>'nullable|string|max:100']);
// HeroSlideDTO::fromRequest -> normalizeTrusts slices to HeroSlide::MAX_TRUST_ITEMS (5)
// Blade only shows HeroSlide::MAX_TRUST_ITEMS_SHOWN (3)
```

---

## 5. Invariants & Rules for Enum/Constant Authors

- **No native `enum`:** use `public const` (array) or scalar `const` on the Model/Service. Validation consumes them via `'in:'.implode(',', Model::VALUES)`. Keep const and FormRequest in sync — validation referencing a stale list is a bug.
- **`Order::$order_status` has no `VALUES` array yet — document expansions there.** When adding a status, update `Order::getStatusAttribute()` match **and** every `match`/`in_array` in `OrderService`/`OrderObserver`/`Policies` that switches on it.
- **Bank `$bank_type` is free-form string** (`zarinPal`, `card_to_card`) — guarded by `PaymentGatewayFactory` switch, not `Rule::in`. Introducing a new gateway requires updating the factory switch, `BankRequest` rules (currently `'bank_type'=>'required|string|max:100'`), and `Order::getBankTrackingCodeAttribute` match.
- **Discount `type` is the only `in:cash,percent` validated enum** — `pay_type` is nullable with literal `in:category,brand,product`. Do not normalize them into `const` arrays without updating `DiscountRequest` + `Discount` accessor + `CouponService`.
- **`Specification::type` (`text`/`select`) drives behavior:** `SpecificationDTO` forces `isFilter/isColor=false` unless `type==='select'`; `getTypeNameAttribute` maps to `نوشتاری`/`انتخابی`. When adding spec type, update DTO + Request + accessor together.
- **Notification `TYPES` must stay single-sourced on `NotificationService::TYPES`** — icon/color fallback is `TYPES[$type] ?? ['icon'=>'🔔','color'=>'accent']`. Adding a new notification type: append to `TYPES` with icon+color, then call `notifyUser/notifyAdmins(type,...)` with same key.
- **Service numeric constants are business params, not enum values** (`SITE_MAX_BANNERS`, `MAX_TRUST_ITEMS`, `EXPIRY_DAYS`). Changing them is a product decision — update corresponding DTO slicing / validation `max:` / Blade caps together.

---

## 6. Adding a New Enumerated Domain — Checklist

- [ ] Define `public const VALUES = [...]` (and optionally `LABELS = [...]`) on owning Model (or Service if service-owned like `NotificationService::TYPES`).
- [ ] Wire `rules: 'field' => 'required|string|in:'.implode(',', Model::VALUES)` in the matching `FormRequest`.
- [ ] Add scope/helper: `scopeOfType`, `getLabelAttribute(): string` via `match($this->field){ case=>__() }` (like `Review::getIsApprovedLabelAttribute`).
- [ ] Add accessor/badge method if status is rendered: `getStatusAttribute(): ?array` returning `[title,badge]`.
- [ ] No need to create `app/Enums/*.php` — unless team migrates; then port all consumers at once.
