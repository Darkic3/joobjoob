# Traits & Base Classes

> **Source of Truth:** `app/Traits/*` (4 files), `app/Models/Traits/HasAdminPermissions.php`, `app/Http/*`, `app/Models/*`
> **Pattern:** Lean Models/Controllers composed with small Traits; no abstract `BaseModel` / `BaseController` helper layer. Base `Controller` is empty; `Model` is vanilla `Illuminate\Database\Eloquent\Model`.

---

## 1. Purpose — How Joobjoob Uses Traits

- **No Base Model, no Base Controller helpers.** `app/Http/Controllers/Controller.php` is literally empty (`abstract class Controller {}`); `App\Models\*` extend `Illuminate\Database\Eloquent\Model` directly with no shared `BaseModel`. Reuse is achieved **only** via Traits mixed into individual classes.

- **Traits are narrow, composable units:**
  - `HasSlug`, `HasImageAttributes`, `HasJalaliDate`, `HasMetaData`, `Filterable` in `app/Traits/`
  - `HasAdminPermissions` in `app/Models/Traits/`
  - Laravel built-ins `SoftDeletes`, `HasFactory` are used per-model, not abstracted.

- **Consequence for Vibe Coding:** to add a new resource that needs slugging, imaging, filtering or admin perm checks, `use` the relevant Trait — do not subclass a non-existent base. If you need a new cross-cutting concern, create a small Trait under `app/Traits/` (request-scoped) or `app/Models/Traits/` (Eloquent-scoped), mirroring the 10-50 line scale of existing traits.

---

## 2. Complete Inventory

### 2.1 `app/Traits/` — 4 project traits + 1 generic

| Trait | File | Mixed into | Semantics |
|-------|------|------------|-----------|
| `HasSlug` | `app/Traits/HasSlug.php` | `BlogRequest`, `BrandRequest`, `ProductRequest`, `ProductCategoryRequest`, `BlogCategoryRequest` (FormRequests) | `prepareSlug($title, $url=null): string` → `Str::slug($url ?: $title)` |
| `HasImageAttributes` | `app/Traits/HasImageAttributes.php` | Any Model that maps a filename column to a URL (inferred: models with image columns) | `makeImageAttribute(string $column, ?string $folder, string $size='original'): ?string` — resolves `config('paths.upload')/folder/filename`, checks `file_exists`, returns `asset()` or `null` |
| `HasJalaliDate` | `app/Traits/HasJalaliDate.php` | Models exposing `created_at` as Jalali | `getJalaliCreatedAtAttribute(): string|array|null` → `show_date($this->created_at,'d F Y')` locale-aware (jalali/ghamari/gregorian) |
| `HasMetaData` | `app/Traits/HasMetaData.php` | `ProductCategory`, `Product`, `BlogCategory`, `Blog`, etc. | `metaData(): MorphOne` + accessors `seoTitle/seoH1/seoDescription/seoIndex` delegating to `MetaData` row |
| `Filterable` | `app/Traits/Filterable.php` | (mixed into Models that need generic `scopeFilter`) | `scopeFilter(Builder $q, array $filters)` — generic `title/url/is_active/show_in_first_page/category_url/start_date/end_date` where chain |

### 2.2 `app/Models/Traits/` — 1 domain trait

| Trait | File | Mixed into | Semantics |
|-------|------|------------|-----------|
| `HasAdminPermissions` | `app/Models/Traits/HasAdminPermissions.php` | `User` (`use HasAdminPermissions`) | RBAC by route name: `permissionNames(): array` (cached 1h versioned), `canAccess(string $routeName): bool`, `forgetPermissionCache()`, `bumpPermissionsVersion()` — superset gate `is_admin` bypass |

### 2.3 Laravel built-ins used as traits per-model

| Trait | Where | Purpose |
|-------|-------|---------|
| `SoftDeletes` | `Product`, `Order`, `Discount`, `Blog`, `Review`, etc. | Hides `deleted_at != null`; no global scope needed |
| `HasFactory` | `Product`, `Review`, etc. | Test factories |

### 2.4 Base classes (none with helpers)

| Class | File | Helpers |
|-------|------|---------|
| `App\Http\Controllers\Controller` | `app/Http/Controllers/Controller.php` | **none** — empty abstract |
| `Illuminate\Database\Eloquent\Model` | vendor | — reused directly |

---

## 3. Structure / Signature — Exact

### 3.1 `HasSlug`

```php
// app/Traits/HasSlug.php
namespace App\Traits;

trait HasSlug
{
    protected function prepareSlug($title, $url = null): string
    {
        return \Illuminate\Support\Str::slug($url ?: $title);
    }
}
```
- **Called in `prepareForValidation()`** of FormRequests to coerce `url/slug` before validation runs:
  ```php
  protected function prepareForValidation(): void {
      $this->merge(['url' => $this->prepareSlug($this->title, $this->url)]);
      // Brand: only if empty($this->url) && !empty($this->title['fa'])
  }
  ```

### 3.2 `HasImageAttributes`

```php
// app/Traits/HasImageAttributes.php
trait HasImageAttributes
{
    protected function makeImageAttribute(string $column, ?string $folder = null, string $size = 'original'): ?string
    {
        $baseUpload = config('paths.upload', 'assets/uploads');
        $value = $this->{$column};
        if (empty($value) || $value === ' ') return null;
        $uploadFolder = $folder ?? \Illuminate\Support\Str::kebab(\Illuminate\Support\Str::pluralStudly(class_basename($this)));
        $path = $size==='original' ? "{$baseUpload}/{$uploadFolder}/{$value}" : "{$baseUpload}/{$uploadFolder}/{$size}/{$value}";
        $fullPath = app()->environment('local') ? public_path($path) : base_path('public_html/'.$path);
        return file_exists($fullPath) ? asset($path) : null;
    }
}
```
- Folder defaults to `kebab(pluralStudly(class_basename($this)))` e.g. `Product`→`products`.
- Local checks `public_path`, prod checks `base_path('public_html/...')` (shared-host layout).

### 3.3 `HasJalaliDate`

```php
// app/Traits/HasJalaliDate.php
trait HasJalaliDate
{
    public function getJalaliCreatedAtAttribute(): array|string|null
    {
        if ($this->created_at) return show_date($this->created_at, 'd F Y');
        return null;
    }
    // show_date() picks calendar per app()->getLocale(): fa→Jalali, ar→Hijri, else Gregorian; digits localized.
}
```

### 3.4 `HasMetaData`

```php
// app/Traits/HasMetaData.php
trait HasMetaData
{
    public function metaData(): \Illuminate\Database\Eloquent\Relations\MorphOne
    { return $this->morphOne(\App\Models\MetaData::class, 'meta_datable'); }

    public function getSeoTitleAttribute(): ?string { return $this->metaData?->title; }
    public function getSeoH1Attribute(): ?string    { return $this->metaData?->H1; }
    public function getSeoDescriptionAttribute(): ?string { return $this->metaData?->description; }
    public function getSeoIndexAttribute(): bool    { return $this->metaData?->noIndex ?? false; }
}
// MetaData columns: meta_datable_type, meta_datable_id, title, H1, description, noIndex (boolean), keywords...
```

### 3.5 `Filterable`

```php
// app/Traits/Filterable.php (full — see repositories.md; repeated for traits completeness)
trait Filterable
{
    public function scopeFilter(\Illuminate\Database\Eloquent\Builder $query, array $filters)
    {
        return $query
            ->when($filters['title'] ?? null, fn($q,$v)=> $q->where('title','like',"%$v%"))
            ->when($filters['url'] ?? null,   fn($q,$v)=> $q->where('url','like',"%$v%"))
            ->when(isset($filters['is_active']) && $filters['is_active']!=='', fn($q)=> $q->where('is_active',(bool)$filters['is_active']))
            ->when(isset($filters['show_in_first_page']) && $filters['show_in_first_page']!=='', fn($q)=> $q->where('show_in_first_page',(bool)$filters['show_in_first_page']))
            ->when($filters['category_url'] ?? null, fn($q,$url)=> $q->whereHas('category', fn($sub)=> $sub->where('url',$url)))
            ->when($filters['start_date'] ?? null, fn($q,$date)=> /* jalali_to_gregorian then where('created_at','>=',...) */)
            ->when($filters['end_date'] ?? null,   fn($q,$date)=> /* jalali_to_gregorian then where('created_at','<',...) */);
    }
}
```

### 3.6 `HasAdminPermissions` — full

```php
// app/Models/Traits/HasAdminPermissions.php
trait HasAdminPermissions
{
    public function roles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    { return $this->belongsToMany(\App\Models\Role::class); }

    public function permissionNames(): array
    {
        $version = \Illuminate\Support\Facades\Cache::rememberForever('admin.permissions.version', fn()=> 1);
        return \Illuminate\Support\Facades\Cache::remember(
            "admin.permissions.v{$version}.{$this->id}", now()->addHour(),
            fn()=> $this->roles()->with('permissions')->get()
                ->flatMap(fn(\App\Models\Role $r)=> $r->permissions)->pluck('name')->unique()->values()->all()
        );
        // note: queries via $this->roles() (fresh), not loaded relation — ensures bump is visible on next miss.
    }

    public function canAccess(string $routeName): bool
    {
        if ($this->is_admin) return true;      // super-admin bypass
        return in_array($routeName, $this->permissionNames(), true);
    }

    public function forgetPermissionCache(): void
    { \Cache::forget("admin.permissions.v{$this->version()}.{$this->id}"); }

    protected function version(): int
    { return (int)\Cache::get('admin.permissions.version', 1); }

    public static function bumpPermissionsVersion(): void
    {
        if (\Cache::has('admin.permissions.version')) \Cache::increment('admin.permissions.version');
        else \Cache::forever('admin.permissions.version', 2);
        // database driver safe: increment on missing key would not bump, so seed at 2 (1 was prior version).
    }
}
```

---

## 4. Usage Examples — From Real Source

### 4.1 `HasSlug` — FormRequest `prepareForValidation`

```php
// app/Http/Requests/Admin/BlogCategoryRequest.php
use App\Traits\HasSlug;
class BlogCategoryRequest extends FormRequest
{
    use HasSlug;
    protected function prepareForValidation(): void {
        $this->merge(['url' => $this->prepareSlug($this->title, $this->url)]);
    }
    public function rules(): array {
        $id = $this->route('blogCategory')?->id ?? $this->route('blog_category')?->id;
        return [
            'parent_id'=>['nullable','exists:blog_categories,id', function($a,$v,$fail) use($id){ if($id && $v==$id) $fail(...); }],
            'title'=>'required|string|max:255',
            'url'=>['required','string','max:255', new UrlRule(BlogCategory::class,$id,'slug')],
            // ...
        ];
    }
}

// Brand: only auto-slug when user left url empty and provided fa title
protected function prepareForValidation(): void {
    if (empty($this->url) && !empty($this->title['fa'])) {
        $this->merge(['url' => $this->prepareSlug($this->title['fa'])]);
    }
}
```

### 4.2 `HasImageAttributes` / `ImageHandler` — URL resolution

```php
// Model accessor delegating to trait helper:
class Product extends Model
{
    use HasImageAttributes; // plus HasFactory, SoftDeletes
    public function getImageUrlAttribute(): ?string
    {
        // primary path via column:
        if ($this->main_image) return asset(config('paths.upload','uploads').'/products/'.$this->main_image);
        // fallback via relation using trait helper:
        return $this->makeImageAttribute('main_image','products');
    }
}

// ImageHandler is NOT a trait — helper class under app/Helpers/ImageHandler.php:
// App\Helpers\ImageHandler::handle('products', $dto->getImage()) → stored filename or null
// App\Helpers\ImageHandler::delete('products', $model->main_image)
```

### 4.3 `HasMetaData` — SEO on ProductCategory / Blog

```php
// app/Models/ProductCategory.php (inferred)
use App\Traits\HasMetaData;
class ProductCategory extends Model
{
    use HasMetaData, SoftDeletes;
    protected $casts = ['name'=>'array','description'=>'array'];
    public function trans(string $field): string { /* app()->getLocale() fallback fa */ }
}

// Usage in Service when saving:
$category->metaData()->updateOrCreate([], ['title'=>$dto->getMetaTitle(),'description'=>$dto->getMetaDescription()]);
$seoTitle = $category->seo_title; // → metaData->title
$noIndex  = $category->seo_index;  // → metaData->noIndex

// Used downstream in SEO/Meta rendering (layouts)
```

### 4.4 `HasAdminPermissions` — gate + cache

```php
// app/Http/Middleware/AdminPermission.php (inferred — checks permissionNames)
public function handle(Request $req, Closure $next, string $permission = null) {
    if (!auth()->user()->canAccess($req->route()->getName())) abort(403);
    return $next($req);
}

// app/Http/Controllers/Admin/RoleController.php — after permission sync:
$role->permissions()->sync($validated['permissions']);
\App\Models\User::bumpPermissionsVersion(); // invalidates admin.permissions.v{N}.{userId} for all users
// or per-user
auth()->user()->forgetPermissionCache();

// Super-admin bypass:
if (auth()->user()->is_admin) { /* canAccess() always true */ }
```

### 4.5 `HasJalaliDate` / `HasImageAttributes` combined

```php
// On a Model needing both:
class Blog extends Model { use SoftDeletes, HasJalaliDate; /* getJalaliCreatedAtAttribute */ }
// Blade:
{{ $blog->jalali_created_at }}  // already via show_date respecting locale

// On a filterable Model:
class Brand extends Model { use HasImageAttributes, Filterable; /* scopeFilter */ }
Brand::filter($request->only(['title','is_active']))->paginate();
```

---

## 5. Invariants & Rules for Trait Authors

- **No Base class injection:** keep `Controller` empty. If you need a controller helper (e.g. `json()` or `paginated()`), add a Trait under `app/Traits/` or `app/Http/Traits/`, not a Base method — so only controllers that need it opt in.
- **Trait methods are `protected` helpers or `scope*` / `getXAttribute` accessors.** Do not add public domain verbs to traits that belong in Services (e.g. `store`, `checkout`). Traits must be pure helpers/predicates/relations.
- **Eloquent Traits (`HasMetaData`, `HasAdminPermissions`, `Filterable`, `HasJalaliDate`) live by Eloquent conventions** — use `morphOne`, `belongsToMany`, `Builder $query`, `$this->metaData?->title` nullsafe. Place them under `app/Models/Traits/` if they touch relations/scopes; under `app/Traits/` if generic.
- **`HasAdminPermissions` cache contract:** `version` key is monotonic; `bumpPermissionsVersion()` must run after any `Role`/`Permission` mutation. Never call `Cache::forget('admin.permissions.version')` — bump, don't delete, to keep `rememberForever` seed.
- **`HasImageAttributes::makeImageAttribute` hits disk (`file_exists`).** Do not call inside tight loops without caching result; prefer Service-level `ImageHandler` already outside loops.
- **`HasSlug::prepareSlug` is deterministic `Str::slug`.** Uniqueness is enforced by `UrlRule`, not by the trait — do not append suffixes inside the trait; let the DB validation error surface.

---

## 6. Adding a New Trait / Base — Checklist

- [ ] Place under `app/Traits/Xxx.php` (generic/request) or `app/Models/Traits/Xxx.php` (Eloquent). Keep <100 lines.
- [ ] No constructor/state beyond a `protected function helper()` — traits must not hold mutable instance property beyond auth-style cache keys.
- [ ] If Eloquent-related, type-hint `Builder` and return `Builder`; name scope `scopeCamelCase`.
- [ ] Document on-which Models/Controllers it is mixed in; grep before adding duplicate logic.
- [ ] Do not turn `Controller` into a base — opt-in via `use` stays the pattern.
