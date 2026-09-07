# FormRequests & Validation

> **Source of Truth:** `app/Http/Requests/Admin/*Request.php` (14 files) + `app/Http/Requests/Site/*Request.php` (2 files) + `app/Rules/*` (2 files: `UrlRule`, `EmailOrPhone`)
> **Pattern:** `FormRequest::authorize() → prepareForValidation() → rules() → withValidator() → messages()`; DTO consumed only after `validated()` passes.

---

## 1. Purpose — What FormRequests Do in Shopora

- **Validation is the Controller gate.** Every admin write (`store`, `update`) and sensitive site flow (discount code, receipt update) goes through a `FormRequest`. The Controller declares `store(ProductRequest $request)` and **never** calls `Validator::make(...)` inline — validation is declarative on the Request class.

- **Boundary:** `FormRequest` validates *shape* (required/array/exists/unique/image/mimes) and *uniqueness* (`UrlRule`, `Rule::unique`). Business normalization (empty-row filtering, `isFilter` inference, trust slicing) belongs to `DTO::fromRequest`, not `FormRequest`.

- **Consequence for Vibe Coding:** add a `FormRequest` for every new entity. Mirror the hook order below and the validation idioms; never validate inside Service/Controller.

---

## 2. Structure / Signature — Exact Hooks

Every `FormRequest` follows **identical skeleton** (checked across 16 files):

```php
namespace App\Http\Requests\Admin; // or Site

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\HasSlug;              // optional — only sluggable entities
use App\Rules\UrlRule;               // optional — unique slug/url

class XxxRequest extends FormRequest
{
    use HasSlug; // if entity has slug/url auto-generation

    public function authorize(): bool
    {
        return true; // always true — auth/guard is enforced by middleware `auth`, `adminPermission`, not by Request
    }

    protected function prepareForValidation(): void
    {
        // optional — coerce BEFORE rules run. Shopora uses it only for auto-slug:
        //   if (empty($this->slug) && !empty($this->name['fa'])) $this->merge(['slug'=> $this->prepareSlug($this->name['fa'])]);
        //   if (empty($this->slug)) $this->merge(['slug'=> $this->prepareSlug($this->title)]);
        // BlogCategory: $this->merge(['url'=> $this->prepareSlug($this->title,$this->url)]);
    }

    public function rules(): array
    {
        // Two shapes observed: flat array or match($this->method()) split. See §3.
        return [ /* per-field rules */ ];
    }

    public function withValidator($validator): void  // optional — only DiscountRequest implements
    {
        $validator->after(function($validator){ /* conditional errors */ });
    }

    public function messages(): array
    {
        return [ /* 'field.rule' => __('validation.requests.xxx.yyy') or inline Fa */ ];
    }
}
```

**Hook execution order enforced by Laravel:** `authorize()` → `prepareForValidation()` → `rules()` → `after` hooks (`withValidator`) → `messages()` → `validated()`.

---

## 3. Inventory — 16 FormRequests

### 3.1 Admin — 14

| Request | File | Traits/Rules | Slug? | Notable |
|---------|------|--------------|-------|---------|
| `BankRequest` | `Admin/BankRequest.php` | — | — | `match(method())` split but both branches **identical** (dead branching) |
| `BlogRequest` | `Admin/BlogRequest.php` | `HasSlug`, `UrlRule` | `slug` | `prepareForValidation` only if `empty(slug)` |
| `BlogCategoryRequest` | `Admin/BlogCategoryRequest.php` | `HasSlug`, `UrlRule` | `url` (column `slug`) | inline closure self-parent guard; `messages()` via `__('validation.requests.blog_category.*')` |
| `BrandRequest` | `Admin/BrandRequest.php` | `HasSlug`, `UrlRule` | `url` | `title` as `required|array` with `title.fa`/`title.en` sub-rules; auto-slug only if `empty(url) && !empty(title['fa'])` |
| `DiscountRequest` | `Admin/DiscountRequest.php` | — | — | `match(method())` for `code` uniqueness (`NULL,id,deleted_at,NULL`); **only** Request with `withValidator` |
| `HeroSlideRequest` | `Admin/HeroSlideRequest.php` | — | — | `required_if:button_active,1` on `btn_text.fa`/`link_type`/`link_value`; `trusts.*` array max 5 |
| `LanguageRequest` | `Admin/LanguageRequest.php` | `Rule::requiredIf` | — | `code` `regex:/^[a-z]{2,8}$/` + `Rule::unique()->ignore(route('language'))` |
| `ProductRequest` | `Admin/ProductRequest.php` | `HasSlug`, `UrlRule` | `slug` | large array: `specs`, `text_specs`, `variants.*`, `properties`, `related_ids`, `images.*` |
| `ProductCategoryRequest` | `Admin/ProductCategoryRequest.php` | `HasSlug`, `UrlRule` | `slug` | `name` bilingual required |
| `PromoBannerRequest` | `Admin/PromoBannerRequest.php` | `PromoBanner::ICONS/TONES` | — | `icon in:`.ICONS implosion; `tone in:`.TONES; `title.fa required` |
| `RoleRequest` | `Admin/RoleRequest.php` | `Rule::unique` | — | `permissions.*` `exists:permissions,name` |
| `SpecificationRequest` | `Admin/SpecificationRequest.php` | — | — | `title` array bilingual; `type in:text,select` |
| `TrustBadgeRequest` | `Admin/TrustBadgeRequest.php` | — | — | `placement in:site,footer,both` |
| `UpdateSettingsRequest` | `Admin/UpdateSettingsRequest.php` | `config('settings.fields')` | — | **dynamic** rules: iterates `config('settings.fields')` filtered by `tab === route('group')`; strips file rules when no file uploaded |
| `UserRequest` | `Admin/UserRequest.php` | `Rule::unique` | — | `password required` on create, `nullable` on update; `roles.* exists:roles,id` |

### 3.2 Site — 2

| Request | File | Notable |
|---------|------|---------|
| `CardToCardCheckoutRequest` | `Site/CardToCardCheckoutRequest.php` | Conditional rules: if `bank_id` resolves to `bank_type==='card_to_card'` then `receipt_tracking_code required\|numeric\|min:6\|max:16`, `receipt_payer_card regex` |
| `UpdateReceiptRequest` | `Site/UpdateReceiptRequest.php` | Always `receipt_tracking_code required`, `receipt_payer_card regex` |

### 3.3 Custom Rules — 2

| Rule | File | Implements | Semantics |
|------|------|------------|-----------|
| `UrlRule` | `app/Rules/UrlRule.php` | `Illuminate\Contracts\Validation\Rule` (`passes($attr,$value): bool`, `message(): string`) | `where($column,$value)->whereNull('deleted_at')->where('id','!=',$exceptId)->count()===0` — wired as `new UrlRule(Model::class, $id, $column='slug')` — respects `SoftDeletes` |
| `EmailOrPhone` | `app/Rules/EmailOrPhone.php` | `Rule` (legacy string API — `message()` returns inline Fa) | `preg_match('/^09[0-9]{9}$/') || filter_var(FILTER_VALIDATE_EMAIL)` — message `'ورودی باید ... ایمیل یا شماره همراه ...'` |

---

## 4. Rules Detail — Per Request

### 4.1 Common idioms

```php
// Bilingual name: array wrapper + per-locale sub-rules
'name' => 'required|array',
'name.fa' => 'required|string|max:255',
'name.en' => 'nullable|string|max:255', // or required depending on entity

// Auto-slug pre-validation
protected function prepareForValidation(): void {
    if (empty($this->slug) && !empty($this->name['fa'])) $this->merge(['slug'=> $this->prepareSlug($this->name['fa'])]);
    // Brand: only when url empty; BlogCategory: always $this->merge(['url'=> prepareSlug($this->title,$this->url)]);
}

// Uniqueness with soft-deletes awareness — two forms observed:
// via UrlRule (new style):
'slug' => ['required','string','max:255', new UrlRule(Product::class, $id)]
// via Rule::unique string (Discount/Role/Language/User):
'code' => "required|string|max:50|alpha_dash|unique:discounts,code,{$id},id,deleted_at,NULL"

// Image
'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048' // HeroSlide max:20480

// Existence
'product_category_id' => 'required|exists:product_categories,id'
'blog_category_id'    => 'nullable|exists:blog_categories,id'
'permissions.*'       => 'string|exists:permissions,name'

// Boolean flags always plain 'boolean' — cast in DTO via $request->boolean(...)
//   is_active, show_in_first_page, timer_active, is_featured, is_best_seller, button_active, rating_active ...
```

### 4.2 ProductRequest — largest form (abridged)

```php
// app/Http/Requests/Admin/ProductRequest.php
public function rules(): array {
    $id = $this->route('product')?->id;
    return [
        'product_category_id' => 'required|exists:product_categories,id',
        'brand_id'            => 'nullable|exists:brands,id',
        'name'                => 'required|array',
        'name.fa'             => 'required|string|max:255',
        'name.en'             => 'required|string|max:255',
        'slug'                => ['required','string','max:255', new UrlRule(Product::class,$id)],
        'short_description'   => 'nullable|array',
        'description'         => 'nullable|array',
        'sku'                 => 'nullable|string|max:100|unique:products,sku,'.$id,
        'price'               => 'nullable|integer|min:0',
        'discount_price'      => 'nullable|integer|min:0',
        'stock'               => 'nullable|integer|min:0',
        'weight'              => 'nullable|integer|min:0',
        'is_active'           => 'boolean',
        'is_featured'         => 'boolean',
        'is_best_seller'      => 'boolean',
        'deal_ends_at'        => 'nullable|date|after:now',
        'timer_active'        => 'boolean',
        'start_timer'         => 'nullable|date',
        'end_timer'           => 'nullable|date|after:start_timer',
        'specs'               => 'nullable|array',
        'specs.*'             => 'array',
        'specs.*.*'           => 'integer|exists:specifications,id',
        'text_specs'          => 'nullable|array',
        'text_specs.*'        => 'nullable|string|max:500',
        'variants'            => 'nullable|array',
        'variants.*.id'             => 'nullable|integer|exists:product_variants,id',
        'variants.*.spec_ids'       => 'nullable|array',
        'variants.*.spec_ids.*'     => 'integer|exists:specifications,id',
        'variants.*.price'          => 'nullable|integer|min:0',
        'variants.*.discount_price' => 'nullable|integer|min:0',
        'variants.*.stock'          => 'nullable|integer|min:0',
        'variants.*.weight'         => 'nullable|integer|min:0',
        'properties'          => 'nullable|array',
        'properties.*.title'  => 'nullable|string|max:255',
        'properties.*.value'  => 'nullable|string|max:500',
        'related_ids'         => 'nullable|array',
        'related_ids.*'       => 'integer|exists:products,id',
        'complement_ids'      => 'nullable|array',
        'complement_ids.*'    => 'integer|exists:products,id',
        'images'              => 'nullable|array',
        'images.*'            => 'image|mimes:jpg,jpeg,png,webp|max:3072',
        'delete_images'       => 'nullable|array',
        'delete_images.*'     => 'integer',
        'main_image_id'       => 'nullable|integer',
        'main_new_index'      => 'nullable|integer',
    ];
}
```

### 4.3 DiscountRequest + withValidator (conditional)

```php
// app/Http/Requests/Admin/DiscountRequest.php
public function rules(): array {
    $discountId = $this->route('discount')?->id;
    return match($this->method()){
        'PUT','PATCH' => [ /* ... 'code' => "required|string|max:50|alpha_dash|unique:discounts,code,{$discountId},id,deleted_at,NULL", ... */ ],
        default       => [ /* ... 'code' => "required|string|max:50|alpha_dash|unique:discounts,code,NULL,id,deleted_at,NULL", ... */ ],
    };
}

public function withValidator($validator): void {
    $validator->after(function($validator){
        $type = $this->input('type'); $amount = $this->input('amount');
        $payType = $this->input('pay_type'); $ids = $this->input('discountable_ids');
        if ($type==='percent' && $amount>100) $validator->errors()->add('amount','درصد تخفیف نمیتواند بیشتر از ۱۰۰ باشد.');
        if (!empty($payType) && empty($ids))  $validator->errors()->add('discountable_ids','وقتی نوع محدودیت انتخاب شده، حداقل یک مورد باید انتخاب شود.');
        if (!empty($payType) && !empty($ids)) {
            $validClass = match($payType){ 'category'=>\App\Models\ProductCategory::class,'brand'=>\App\Models\Brand::class,'product'=>\App\Models\Product::class,default=>null };
            if($validClass && $validClass::whereIn('id',$ids)->count() !== count($ids))
                $validator->errors()->add('discountable_ids','برخی از آیتمهای انتخابی معتبر نیستند.');
        }
    });
}
```

### 4.4 UpdateSettingsRequest — dynamic per-group

```php
// app/Http/Requests/Admin/UpdateSettingsRequest.php
public function rules(): array {
    $group = $this->route('group'); // tab key: 'general'|'payment'|'seo'...
    $fields = collect(config('settings.fields', []))->filter(fn($f)=> ($f['tab']??'general')===$group)->all();
    $rules=[];
    foreach($fields as $key=>$field){
        $validation = $field['validation'] ?? 'nullable';
        $isFile = in_array($field['type']??'text', ['file','video'], true);
        if($isFile && $this->hasFile("settings.{$key}")) $rules["settings.{$key}"] = $validation;
        else {
            $validationString = str_replace(['required','image','mimes:jpeg,png,jpg,webp','max:2048'],'',$validation);
            $rules["settings.{$key}"] = trim($validationString,'|') ?: 'nullable';
        }
    }
    return $rules;
}
```

### 4.5 Site requests — conditional + regex

```php
// app/Http/Requests/Site/CardToCardCheckoutRequest.php
public function rules(): array {
    $bank = $this->input('bank_id') ? Bank::find($this->input('bank_id')) : null;
    $isCardToCard = $bank && $bank->bank_type==='card_to_card';
    $rules = ['bank_id'=>'required|exists:banks,id'];
    if($isCardToCard){
        $rules['receipt_tracking_code']='required|numeric|min:6|max:16';
        $rules['receipt_payer_card']='nullable|string|regex:/^(\d{4}-?){3}\d{4}$/';
    }
    return $rules;
}

// app/Http/Requests/Site/UpdateReceiptRequest.php — always requires tracking code
'receipt_tracking_code'=>'required|numeric|min:6|max:16',
'receipt_payer_card'=>'nullable|string|regex:/^(\d{4}-?){3}\d{4}$/'
```

### 4.6 Messages — i18n key pattern

```php
// Admin messages consistently use __('validation.requests.xxx.yyy') — see lang files under resources/lang/fa/validation.php + admin.php
public function messages(): array {
    return [
        'title.required'=>__('validation.requests.blog_category.title_required'),
        'url.required'  =>__('validation.requests.blog_category.url_required'),
        // Discount uses inline Fa instead:
        'title.required'=>'عنوان تخفیف الزامی است.',
        'code.alpha_dash'=>'کد تخفیف فقط شامل حروف، اعداد، خط تیره و زیرخط باشد.',
        // Site uses inline Fa as well:
        'bank_id.required'=>'لطفاً روش پرداخت را انتخاب کنید.',
    ];
}
```

---

## 5. Custom Rules — Detail

```php
// app/Rules/UrlRule.php
class UrlRule implements Rule {
    public function __construct(protected string $modelClass, protected ?int $exceptId=null, protected string $column='slug'){}
    public function passes($attribute,$value): bool {
        if(empty($value)) return true;
        $model = new $this->modelClass;
        $q = $model->newQuery()->where($this->column,$value)->whereNull('deleted_at');
        if($this->exceptId) $q->where('id','!=',$this->exceptId);
        return $q->count()===0;
    }
    public function message(): string { return 'این آدرس قبلاً استفاده شده است.'; }
}

// app/Rules/EmailOrPhone.php (legacy Rule interface — no constructor args)
class EmailOrPhone implements Rule {
    public function passes($attribute,$value){
        if(preg_match('/^09[0-9]{9}$/', $value)) return true;
        if(filter_var($value, FILTER_VALIDATE_EMAIL)) return true;
        return false;
    }
    public function message(){ return 'ورودی باید به صورت یک ایمیل یا شماره همراه معتبر باشد.'; }
}
```

**Usage in requests:**
```php
'slug' => ['required','string','max:255', new UrlRule(Product::class, $id)]
'slug' => ['required','string','max:255', new UrlRule(BlogCategory::class, $id, 'slug')]
'login'=> ['required', new EmailOrPhone] // inferred — auth flow
```

---

## 6. Invariants & Rules for Validation Authors

- **`authorize()` is always `return true`** — do not duplicate `Gate`/`auth` checks there; rely on route middleware (`auth`, `adminPermission`, `guest`). Changing `authorize()` to `false` would shadow middleware and produce opaque 403.
- **`prepareForValidation()` only coerces before-`rules()` shape** (slug generation, `jalali_to_gregorian` formatting if needed). Never mutate `options` or array flags there — that belongs in DTO. Keep `prepareForValidation` to <5 lines.
- **Bilingual fields are always `array` wrapper + per-locale `*.fa`/`*.en` sub-rules** (`required|string|max:255` on `fa`; `nullable` on `en`). Do not validate `name` as `string` directly.
- **Images are always `nullable|image|mimes:...|max:...`** with limit 2048 (or 20480 for hero) — consistent across admin.
- **`boolean` rules map to `$request->boolean('field', false)` in DTO** — FormRequest casts won't coerce; DTO must handle missing boolean as `false`.
- **Uniqueness with soft-deletes must include `,deleted_at,NULL`** (see DiscountRequest) or use `UrlRule` (which does `whereNull('deleted_at')`). Plain `unique:table,column` is **wrong** here.
- **`UpdateSettingsRequest` is the only dynamic request** — it builds rules at **request time** from `config('settings.fields')` grouped by `route('group')`. When adding a setting, update `config/settings.php`, not the Request.
- **`withValidator` is only on `DiscountRequest`** — keep conditional validation there (`percent => amount<=100`, `pay_type => ids required`). For other conditional validations, prefer `required_if` declarative rule over `withValidator` hook (see `HeroSlideRequest: required_if:button_active,1`).

---

## 7. Adding a New FormRequest — Checklist

- [ ] Create `app/Http/Requests/Admin/XxxRequest.php` implementing `authorize():true`, optional `prepareForValidation()` (slug), `rules(): array`, `messages(): array`.
- [ ] Type-hint it on the Controller method: `store(XxxRequest $request)` — never `Request $request` + manual `Validator::make`.
- [ ] For bilingual `title/name`: add `field=>required|array` + `field.fa` + `field.en` sub-rules.
- [ ] For unique slug/url: add `new UrlRule(ModelClass::class, $this->route('model')?->id, 'slug')` and `use HasSlug` + `prepareForValidation` slug.
- [ ] For soft-delete uniqueness: ensure `UrlRule` path or explicit `,deleted_at,NULL` is present.
- [ ] For conditional fields: use `required_if:flag,1` inline; reach for `withValidator` only if cross-field logic exceeds declarative rules.
- [ ] Wire DTO as `XxxDTO::fromRequest($request)` immediately after validation in Controller.
