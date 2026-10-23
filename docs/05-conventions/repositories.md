# Repositories & Query Builders

> **Source of Truth:** No `app/Repositories/` directory exists. Query building lives in **Models (scopes) + Services (filter builders) + Traits (`Filterable`)**.
> **Pattern:** Direct Eloquent on Model inside Service, composed with local scopes and `when()/whereHas()` chains. No abstraction layer called "Repository".

---

## 1. Purpose — Why No Repository Here?

- **Joobjoob intentionally skips the Repository pattern.** Search of `app/` yields no `Repository` class, no `app/Repositories/` folder, no `*Repository` contract. Data access is performed **directly via Eloquent Models inside Services** (e.g. `Product::query()->with(...)->where(...)->paginate()` in `ProductService::getPaginatedProducts`).

- **Rationale (inferred from code shape):**
  - Eloquent already *is* the repository (active record + query builder). Wrapping `Product::where('is_active',true)` behind `ProductRepository::active()` would duplicate scope semantics without adding testability (Laravel's in-memory SQLite + `RefreshDatabase` tests don't benefit from abstraction).
  - Services own transactions + side effects (audit, notification, image handling) — a pure repository would still need them as callers, producing an extra delegation layer with no behavioral gain.

- **Consequence for Vibe Coding:** do **not** introduce `app/Repositories/` ad-hoc. Put query logic in **Model scopes** for reusable predicates and in **Service builder methods** (`getPaginatedProducts`, `getActiveItems`) for composition. Only introduce a Repository via RFC if you need to swap data source (unlikely — MySQL-only project).

---

## 2. What Exists Instead — The Actual Data-Access Layers

### 2.1 Layer 1 — Model Local Scopes (reusable predicates)

Scopes are the project's "mini-repositories": small `scopeX(Builder $query, ...)` methods on the Model that compose with `Model::active()->valid()->get()`.

### 2.2 Layer 2 — Service Query Builders (composition + pagination)

Services own complex filtering: `when()` chains, `whereHas()`, sorting, eager loading, and `paginate()`.

### 2.3 Layer 3 — Trait `Filterable` (shared filter)

`App\Traits\Filterable::scopeFilter(Builder $query, array $filters)` — a generic `title/url/is_active/show_in_first_page/category_url/start_date/end_date` filter applied to whichever model uses it. Covers admin index search outside the bespoke `getPaginatedProducts`.

### 2.4 Global Scopes — **None**

`grep -rn "GlobalScope|addGlobalScope|booted.*Scope" app/Models` returns only **noIndex** boolean on `MetaData` accessed via `HasMetaData::getSeoIndexAttribute` — no registered `Scope` class is wired via `booted()`. Suppressed records use **explicit** `scopeActive()` calls, not implicit global scope. `SoftDeletes` is used directly (deleted rows hidden by default unless `withTrashed()`).

---

## 3. Complete Scope Inventory

All local scopes as `grep -rn "function scope" app/`:

| Model | Scope | File | Signature & Semantics |
|-------|-------|------|------------------------|
| `Address` | `scopeAuthUser` | `Address.php:19` | `scopeAuthUser(Builder $q): Builder` → `where('user_id', Auth::id())` — per-user address isolation |
| `AuditLog` | `scopeForOrder` | `AuditLog.php:41` | `scopeForOrder(Builder $q, int $orderId)` → `where('auditable_type', Order::class)->where('auditable_id',$orderId)` |
| `AuditLog` | `scopeForProduct` | `AuditLog.php:47` | `scopeForProduct(Builder $q, int $productId)` → `where auditable_type=Product::class` |
| `AuditLog` | `scopeEvent` | `AuditLog.php:53` | `scopeEvent(Builder $q, string $event)` → `where('event',$event)` |
| `AuditLog` | `scopeEvents` | `AuditLog.php:58` | `scopeEvents(Builder $q, array $events)` → `whereIn('event',$events)` |
| `AuditLog` | `scopeByUser` | `AuditLog.php:63` | `scopeByUser(Builder $q, int $userId)` → `where('user_id',$userId)` |
| `Bank` | `scopeActive` | `Bank.php:35` | `scopeActive(Builder $q)` → `where('status', true)` (note: column is `status`, not `is_active`) |
| `Blog` | `scopeActive` | `Blog.php:39` | `where('is_active', true)` |
| `Blog` | `scopeFirstPage` | `Blog.php:44` | `where('show_in_first_page', true)` |
| `BlogCategory` | `scopeActive` | `BlogCategory.php:45` | `where('is_active', true)` |
| `Brand` | `scopeOrdered` | `Brand.php:26` | `orderBy('sort_order')->orderBy('id')` — deterministic display order |
| `Brand` | `scopeActive` | `Brand.php:36` | `where('active', true)` — note: column is `active`, not `is_active` |
| `City` | `scopeActive` | `City.php:39` | `where('is_active', true)` |
| `ContactRequest` | `scopeUnread` | `ContactRequest.php:26` | `scopeUnread(Builder $q): Builder` → `where('is_read', false)` |
| `Discount` | `scopeActive` | `Discount.php:87` | `where('is_active', true)` |
| `Discount` | `scopeValid` | `Discount.php:92` | `scopeValid(Builder $q)` → `active()->where(starts_at<=now || null)->where(expires_at>=now || null)` |
| `HeroSlide` | `scopeActive` | `HeroSlide.php:36` | `scopeActive(Builder $q): Builder` → `where('is_active',true)->orderBy('sort_order')` |
| `Language` | `scopeOrdered` | `Language.php:20` | `scopeOrdered(Builder $q): Builder` → `orderBy('sort_order')->orderBy('id')` |
| `Language` | `scopeActive` | `Language.php:25` | `scopeActive(Builder $q): Builder` → `where('is_active', true)` (note: check migration — Languages use `is_active`) |
| `Notification` | `scopeUnread` | `Notification.php:30` | `scopeUnread(Builder $q): Builder` → `where('is_read', false)` / `whereNull('read_at')` per impl |
| `Notification` | `scopeForUser` | `Notification.php:35` | `scopeForUser(Builder $q, int $userId)` → `where('user_id',$userId)` |
| `Notification` | `scopeForAdmins` | `Notification.php:41` | `scopeForAdmins(Builder $q)` → `whereNull('user_id')->orWhere('is_admin')` (impl check) |
| `Notification` | `scopeOfType` | `Notification.php:47` | `scopeOfType(Builder $q, string $type)` → `where('type',$type)` |
| `Order` | `scopeAuthUser` | `Order.php:36` | `scopeAuthUser(Builder $q)` → `where('user_id',Auth::id())` |
| `OrderShippingStatus` | `scopeDefault` | `OrderShippingStatus.php:22` | `scopeDefault(Builder $q)` → `where('is_default', true)` → `OrderService::create` picks default status |
| `Product` | `scopeActive` | `Product.php:172` | `scopeActive(Builder $q)` → `where('is_active', true)` |
| `Product` | `scopeFeatured` | `Product.php:177` | `scopeFeatured(Builder $q)` → `where('is_featured', true)` |
| `Product` | `scopeBestSeller` | `Product.php:182` | `scopeBestSeller(Builder $q)` → `where('is_best_seller', true)` |
| `ProductVariant` | `scopeAvailable` | `ProductVariant.php:42` | `scopeAvailable(Builder $q)` → `where('stock','>',0)` |
| `PromoBanner` | `scopeActiveOrdered` | `PromoBanner.php:43` | `scopeActiveOrdered(Builder $q)` → `where('is_active',true)->orderBy('sort_order')->orderBy('id')` |
| `Review` | `scopeApproved` | `Review.php:96` | `scopeApproved(Builder $q)` → `where('is_approved', STATUS_APPROVED)` (1) |
| `Review` | `scopeFeatured` | `Review.php:101` | `scopeFeatured(Builder $q)` → `where('is_featured', true)` |
| `Review` | `scopePending` | `Review.php:106` | `scopePending(Builder $q)` → `where('is_approved', STATUS_PENDING)` (0) |
| `ShippingMethod` | `scopeActive` | `ShippingMethod.php:36` | `scopeActive(Builder $q)` → `where('is_active', true)` |
| `Specification` | `scopeActive` | `Specification.php:63` | `scopeActive(Builder $q)` → `where('active', true)` |
| `Specification` | `scopeParents` | `Specification.php:68` | `scopeParents(Builder $q)` → `whereNull('parent_id')` — top-level specs (select groups like "Color", "Size") |
| `State` | `scopeActive` | `State.php:27` | `scopeActive(Builder $q)` → `where('is_active', true)` |
| `TrustBadge` | `scopeActiveOrdered` | `TrustBadge.php:26` | `scopeActiveOrdered(Builder $q)` → `where('is_active',true)->orderBy('sort_order')` |
| `Filterable` (trait) | `scopeFilter` | `Traits/Filterable.php:9` | `scopeFilter(Builder $q, array $filters)` — generic `title/url/is_active/show_in_first_page/category_url/start_date(jalali)/end_date` — uses `when()` |

**Notes on columns:** Brand uses `active`, Bank uses `status`, Specification uses `active` — rest use `is_active`. Scopes reflect the actual column; do not unify without migration.

---

## 4. Structure / Signature — Exact

### 4.1 Local scope shape

```php
// Every scope lives on the Model and returns Builder for chaining:
namespace App\Models;

class Product extends Model
{
    public function scopeActive($query)              { return $query->where('is_active', true); }
    public function scopeFeatured($query)            { return $query->where('is_featured', true); }
    public function scopeBestSeller($query)          { return $query->where('is_best_seller', true); }
}

class Discount extends Model
{
    public function scopeActive($query)              { return $query->where('is_active', true); }
    public function scopeValid($query)               // composite: active + date window
    {
        $now = now();
        return $query->active()
            ->where(fn($q)=>$q->whereNull('starts_at')->orWhere('starts_at','<=',$now))
            ->where(fn($q)=>$q->whereNull('expires_at')->orWhere('expires_at','>=',$now));
    }
}

class PromoBanner extends Model
{
    public function scopeActiveOrdered($query)       { return $query->where('is_active',true)->orderBy('sort_order')->orderBy('id'); }
}
```

### 4.2 Trait `Filterable`

```php
// app/Traits/Filterable.php
namespace App\Traits;

trait Filterable
{
    public function scopeFilter(Builder $query, array $filters)
    {
        return $query
            ->when($filters['title'] ?? null, fn($q,$v)=> $q->where('title','like',"%$v%"))
            ->when($filters['url'] ?? null,   fn($q,$v)=> $q->where('url','like',"%$v%"))
            ->when(isset($filters['is_active']) && $filters['is_active']!=='', fn($q)=> $q->where('is_active',(bool)$filters['is_active']))
            ->when(isset($filters['show_in_first_page']) && $filters['show_in_first_page']!=='', fn($q)=> $q->where('show_in_first_page',(bool)$filters['show_in_first_page']))
            ->when($filters['category_url'] ?? null, function($q,$url){
                $q->whereHas('category', fn($sub)=> $sub->where('url',$url));
            })
            ->when($filters['start_date'] ?? null, function($q,$date){
                $jDate = explode('/', $date);
                if(count($jDate)===3){ $start = jalali_to_gregorian($jDate[0],$jDate[1],$jDate[2],'-'); $q->where('created_at','>=',$start.' 00:00:00'); }
            })
            ->when($filters['end_date'] ?? null, function($q,$date){
                $jDate = explode('/', $date);
                if(count($jDate)===3){ $end = jalali_to_gregorian($jDate[0],$jDate[1],$jDate[2],'-'); $q->where('created_at','<',$end.' 23:59:59'); }
            });
    }
}
```

### 4.3 Service builder — bespoke paginated query

```php
// app/Services/ProductService.php
public function getPaginatedProducts(array $filters, int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
{
    $query = Product::query()->with(['category','images','brand']);

    if(!empty($filters['search'])){
        $search = $filters['search'];
        $query->where(fn($q)=> $q
            ->where('name->fa','like',"%{$search}%")
            ->orWhere('name->en','like',"%{$search}%")
            ->orWhere('sku','like',"%{$search}%")
            ->orWhere('slug','like',"%{$search}%")
            ->orWhereHas('brand', fn($b)=> $b->where('title->fa','like',"%{$search}%"))
        );
    }

    $query->when(!empty($filters['category']), fn($q)=> $q->where('product_category_id', $filters['category']));
    $query->when(!empty($filters['brand_id']), fn($q)=> $q->where('brand_id', $filters['brand_id']));
    $query->when(($filters['status']??'')==='1', fn($q)=> $q->where('is_active',true));
    $query->when(($filters['status']??'')==='0', fn($q)=> $q->where('is_active',false));
    $query->when(($filters['availability']??'')==='in_stock',  fn($q)=> $q->where('stock','>',0));
    $query->when(($filters['availability']??'')==='out_of_stock', fn($q)=> $q->where('stock','<=',0));
    $query->when(isset($filters['price_min']) && $filters['price_min']!=='', fn($q)=> $q->where('final_price','>=',(int)$filters['price_min']));
    $query->when(isset($filters['price_max']) && $filters['price_max']!=='', fn($q)=> $q->where('final_price','<=',(int)$filters['price_max']));
    $query->when(!empty($filters['featured']),    fn($q)=> $q->where('is_featured',true));
    $query->when(!empty($filters['best_seller']), fn($q)=> $q->where('is_best_seller',true));

    $sort = $filters['sort'] ?? 'newest';
    switch($sort){
        case 'price_asc':  $query->orderBy('final_price','asc'); break;
        case 'price_desc': $query->orderBy('final_price','desc'); break;
        case 'name_asc':   $query->orderBy('name->en'); break;
        case 'name_desc':  $query->orderByDesc('name->en'); break;
        case 'oldest':     $query->orderBy('created_at','asc'); break;
        default:           $query->latest(); break;
    }

    return $query->paginate($perPage);
}
```

---

## 5. Usage Examples — From Real Source

### 5.1 Scope chaining on Model

```php
// Anywhere: scopes compose via Eloquent fluent API
Product::active()->featured()->get();
Product::active()->bestSeller()->orderBy('final_price')->paginate(12);
Blog::active()->firstPage()->latest()->take(6)->get();
Brand::active()->ordered()->get(); // ordered = sort_order asc
Discount::valid()->where('code', $code)->first(); // valid = active + date window
HeroSlide::active()->get(); // active already orders by sort_order
PromoBanner::activeOrdered()->get();
Review::approved()->featured()->with('votes')->get();
Specification::active()->parents()->get(); // top-level groups only
Order::authUser()->where('order_status','paid')->paginate(10);
AuditLog::forOrder($orderId)->events(['order.created','order.paid'])->latest()->get();
```

### 5.2 Service builder consumed by Controller

```php
// app/Http/Controllers/Admin/ProductController.php
public function index(Request $request, ProductService $service)
{
    $products = $service->getPaginatedProducts($request->all(), 15);
    return view('admin.products.index', compact('products'));
}
// Blade filters: search, category, brand_id, status (1/0), availability (in_stock/out_of_stock), price_min/max, featured, best_seller, sort

// Equivalent for other catalog resources — each Service mirrors getPaginated pattern:
// $brands = $brandService->getPaginated($request->all());
// $blogs  = $blogService->getPaginated($request->all());
```

### 5.3 `Filterable` used generically

```php
// On any model that `use Filterable;` (currently Brand-ish models):
Model::filter($request->only(['title','url','is_active','show_in_first_page','category_url','start_date','end_date']))
     ->paginate(15);
// Note: start_date/end_date are Jalali strings "YYYY/MM/DD" — converted via jalali_to_gregorian helper before where.
```

### 5.4 `Discount::valid()` as query + in-memory guard

```php
// Query path
$discount = Discount::valid()->where('code', $inputCode)->first();

// In-memory path (helper on instance)
if (!$discount->isCurrentlyValid()) abort(400, 'کد منقضی شده');
if ($discount->hasReachedLimit(auth()->id())) abort(400, 'سقف استفاده پر شده');
if ($discount->basket_minimum_price > $cart->subtotal()) abort(400, 'حداقل سبد رعایت نشده');
```

---

## 6. Invariants & Rules for Query Authors

- **No repository abstraction:** call `Model::scope` + `Service::getPaginated*` directly. Do not wrap `Product::active()` behind `ProductRepository::active()`.
- **No global scopes:** Joobjoob never auto-filters `where('is_active',true)` at booted time — callers must opt in via `scopeActive()`. Exception is `SoftDeletes` which *does* globally hide `deleted_at != null` unless `withTrashed()`.
- **Scope naming mirrors column where possible, but watch variants:** `Brand` `active` maps column `active`, `Bank` `status`, `Specification` `active` — do not rename scope without checking column. New models: prefer column `is_active` + scope `scopeActive` for consistency.
- **Services own `when()` chains:** scopes are small predicates; any filter with branching, JSON `->` where, `whereHas`, or aggregated search belongs in Service builder, not Model.
- **Eager loading is mandatory on Service builders:** `with(['category','images','brand'])` prevents N+1 on admin index pages. Always eager-load relations consumed by the view.
- **Jalali dates:** `Filterable` expects `start_date`/`end_date` as `"YYYY/MM/DD"` Jalali and calls `jalali_to_gregorian`. Pass Gregorian directly only if you bypass `Filterable` and write raw `where('created_at',...)`.
- **`scopeAuthUser` is auth-scoped — never call without auth guard:** `Order::authUser()` and `Address::authUser()` depend on `Auth::id()`. In CLI/jobs, guard `Auth::check()` or pass explicit `user_id` where.

---

## 7. Adding a New Scope / Builder — Checklist

- [ ] If reusable predicate (<5 lines, single `where`): add `public function scopeX(Builder $q): Builder` on Model.
- [ ] If composite filter (search + multiple whens + sorting + pagination): add `public function getPaginated(array $filters, int $perPage=15)` on the owning Service, mirroring `ProductService::getPaginatedProducts` shape (defaults, `when()` chains, `switch(sort)` fallthrough to `latest()`).
- [ ] Do not add `GlobalScope` class unless you need implicit filtering across *every* query — justify in PR description.
- [ ] Index the column filtered by scope (`is_active`, `slug`, `code`, `final_price`) — verify `database/migrations`.
