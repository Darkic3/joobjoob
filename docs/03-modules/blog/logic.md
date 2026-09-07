# Blog — Logic (Deep Trace)

> **Module:** `blog` — Weblog-e Shopora (maqalat + daste-bandi)
> **Stack:** `Blog` + `BlogCategory` + `BlogService` + `BlogDTO` + `ImageHandler` + `HasSlug` + `UrlRule`
> **Status:** ✅ verified-trace — بازنویسی از کد واقعی (2026-09-06)

---

## 1. Purpose (Hadaf-e Module)

Blog module madiriat-e maqalat-e weblog ast: CRUD-e maqal (ba tasvir-e shakhsi, slug, excerpt, content HTML, daste-bandi, views), CRUD-e daste-bandi-e selselehi (`parent_id`), listing/detail-e site ba marboot (related), bakhsh-e blog-e safheye asli, va namayesh-e tarikh-e mohalli-shode (Jalali/Hijri/Gregorian) ba `localizedDate()`. **Tafavot-e mohem ba products:** field-haye `title/description` dar blog **JSON-e multilingual nistand** — string-e sade hastand (`database/migrations/2026_07_29_182334_create_blogs_table.php:18`); faqat tarikh va UI-e site locale-ain hastand.

**Evidence:**
- `app/Models/Blog.php:9-11` — `class Blog extends Model { use SoftDeletes }`
- `app/Services/BlogService.php:9-11` — `class BlogService { const UPLOAD_FOLDER='blogs' }`
- `app/DTOs/BlogDTO.php:8` — `readonly class BlogDTO` ba 10 field
- `app/Http/Controllers/Admin/BlogController.php:16` — `__construct(protected BlogService $blogService)`
- `app/Http/Controllers/Admin/BlogCategoryController.php` — **bedoon Service va bedoon DTO** — Eloquent-e mostaghim (legacy)

---

## 2. Controllers (Controller-ha)

### 2.1 Admin/BlogController — `app/Http/Controllers/Admin/BlogController.php`

Service-oriented mesl-e products. 6 method: `index/create/store/edit/update/destroy` — **method-e `show` nadarad** (`:14-66`) → `admin.blogs.show` route-e morde ast (be `api-contract.md` negah konid).

#### `index(Request $request): View` — `BlogController.php:18-25`

```php
$categories = BlogCategory::where('is_active', true)->get();              // :20
$blogs = $this->blogService->getPaginatedBlogs($request->all());          // :21
$blogs->appends($request->query());                                       // :22 — hefz-e filter dar pagination
return view('admin.blogs.index', compact('blogs','categories'));          // :24
```

Filter-ha (search/category/status) mostaghim dar form-e `admin/blogs/index.blade.php:28-43` sakhte mishavand; **ba bar aks-e products, filterChip nist** — filter-ha be `getPaginatedBlogs` delegat mishavand.

#### `create(): View` — `BlogController.php:27-32`

`BlogCategory::where('is_active', true)->orderByDesc('id')` (`:29`) → view `admin.blogs.create`. Form ba component-e `<x-admin.image-upload>` tasvir migirad (`admin/blogs/_fields.blade.php:131-136`).

#### `store(BlogRequest $request)` — `BlogController.php:34-41`

```php
$blogDTO = BlogDTO::fromRequest($request, auth()->id()); // :36 — user_id = admin-e nevisande
$this->blogService->store($blogDTO);                     // :37
return redirect()->route('admin.blogs.index')->with('success', __('admin.blogs.created')); // :39-40
```

#### `edit(Blog $blog): View` — `BlogController.php:43-48`

Faghat categories (`:45`) — **hich eager load-i nist** (blog relation-e ezafi nadarad joz `category` ke dar view lazy load mishavad).

#### `update(BlogRequest $request, Blog $blog)` — `BlogController.php:50-57`

`BlogDTO::fromRequest($request, auth()->id())` (`:52`) → `$this->blogService->update($blog, $blogDTO)` (`:53`). **Nokte:** har update, `user_id` ra ba admin-e fe'li **jabeja** mikonad — nevisande-e asli az dast miravad (gotcha).

#### `destroy(Blog $blog)` — `BlogController.php:59-65`

`$this->blogService->destroy($blog)` (`:61`) → softDelete + hazf-e file-e tasvir.

---

### 2.2 Admin/BlogCategoryController — `app/Http/Controllers/Admin/BlogCategoryController.php`

**Legacy pattern:** hich Service/DTO-i nist — tamame logic dar controller. `const UPLOAD_FOLDER = 'blog-categories'` (`:12`).

- **`index` (`:14-21`):** `BlogCategory::withCount('blogs')->orderBy('sort_order')->get()` — tedad-e maqalat har daste.
- **`create` (`:23-28`):** `parentCategories = BlogCategory::where('is_active', true)` — tamame daste-ha baraye entekhab-e vald.
- **`store` (`:30-52`):** image ba `ImageHandler::handle(self::UPLOAD_FOLDER, $file)` (`:35-37`) → `BlogCategory::create` ba `'slug' => $validated['url']` (`:42`) — field-e request `url` ast amma column `slug`. Defaults: `is_active ?? true` (`:45`), `show_in_first_page ?? false` (`:46`), `sort_order ?? 0` (`:47`).
- **`edit` (`:54-61`):** parent-ha ba `where('id','!=',$blogCategory->id)` (`:57`) — jelogiri az entekhab-e khod-e daste be onvane vald.
- **`update` (`:63-92`):** **pattern-e amn:** aval upload-e tasvir-e jadid (`:70`), agar movafagh bood → hazf-e tasvir-e ghabli (`:72-74`) → jaygozin (`:75`). Agar upload fail shavad, tasvir-e ghabli saf nemishavad.
- **`destroy` (`:94-109`):** 2 guard: 1) `blogs()->count() > 0` → redirect ba error (`:96-99`), 2) hazf-e file-e tasvir (`:101-103`) → `$blogCategory->delete()` (`:105` — softDelete).

---

### 2.3 Site/BlogController — `app/Http/Controllers/Site/BlogController.php`

Hybrid-e mostaghim (bedoon Service) — query-haye public.

#### `index()` — `Site/BlogController.php:12-22`

```php
$blogs = Blog::with('category')->active()->latest()->paginate(9);  // :14-17
$categories = BlogCategory::active()->withCount('blogs')->get();   // :19
return view('site.blogs.index', compact('blogs','categories'));    // :21
```

Hich filteri nadarad — faghat active + latest. Pagination 9.

#### `show(Request $request)` — `Site/BlogController.php:24-42`

1. `Blog::where('slug', $request->route('blog'))->firstOrFail()` (`:26`) — binding-e dasti ba slug (route `blogs/{blog:slug}`).
2. `abort_unless($blog->is_active, 404)` (`:28`) — guard-e fa'al-boodan.
3. `$blog->load('category')` (`:30`) + `$blog->incrementViews()` (`:31`) — **har request yek DB write** (bedoon dedup/throttle).
4. **Related (`:34-39`):** `Blog::active()->where('id','!=',$blog->id)->when($blog->blog_category_id, fn($q)=>$q->where('blog_category_id',$blog->blog_category_id))->latest()->take(3)->get()` — agar maqal daste nabashad, related az **tamame daste-ha** 3 maqal-e jadid migirad.
5. View `site.blogs.show` ba `blog` + `related` (`:41`).

#### `category(Request $request)` — `Site/BlogController.php:44-62`

```php
$category = BlogCategory::where('slug', $request->route('category'))->firstOrFail(); // :46
abort_unless($category->is_active, 404); // :48
$ids = $this->categoryIdsWithDescendants([$category->id]);  // :51
$blogs = Blog::with('category')->active()->whereIn('blog_category_id', $ids)->latest()->paginate(9); // :52-56
```

View **haman** `site.blogs.index` ba `$activeCategory` ezafi (`:59-61`).

#### `categoryIdsWithDescendants(array $ids): array` — `Site/BlogController.php:67-78`

BFS-e iterative: `while ($found) { $found = BlogCategory::whereIn('parent_id', $found)->pluck('id')->all(); $all = array_merge($all, $found); }` (`:72-75`) + `array_unique` (`:77`). Hameye zireshakhe-ha dar har omgh. Moshabeh-e product-site amma ba `id` shoru mishavad na slug.

---

## 3. BlogService — `app/Services/BlogService.php`

Const `UPLOAD_FOLDER = 'blogs'` (`:11`). **Bedoon DB::transaction** — op-ha tak-radifi hastand.

### `store(BlogDTO $dto): Blog` — `BlogService.php:13-19`

```php
$data = $this->prepareData($dto);                            // :15
$data['image'] = $this->handleImageUpload($dto->getImage()); // :16
return Blog::create($data);                                  // :18
```

### `update(Blog $blog, BlogDTO $dto): bool` — `BlogService.php:21-36`

3 sharayet (olaviat-dar):

1. **Tasvir-e jadid** (`:26-28`): `deleteImage($blog->image)` **aval** → `handleImageUpload` — agar upload fail shavad (`null` bargardanad), tasvir-e ghadimi hazf shode va `data['image']=null` (data loss).
2. **`remove_image=1`** (`:29-33`): tasvir-e fe'li hazf + `data['image']=null` — dokme-hazf-e component-e upload.
3. **Hich kodam** (`:35`): image az `prepareData` miayad ke **shamel `image` nist** (`:58-67`) → `$blog->update($data)` tasvir-ra be hich vajh taghir nemidahad (hefz mishavad).

### `destroy(Blog $blog): bool` — `BlogService.php:38-43`

`deleteImage($blog->image)` (`:40`) + `$blog->delete()` (`:42` — SoftDeletes). **Nokte:** file-e fiziki ghabl az soft-delete hazf mishavad — restore-e maqal, maqal-e bedoon tasvir barmigardanad.

### `getPaginatedBlogs(array $filters, int $perPage = 15)` — `BlogService.php:45-54`

```php
Blog::query()->with('category')                                                     // :47-48
  ->when(!empty($filters['search']), fn($q)=>$q->where('title','like','%'.$filters['search'].'%')) // :49
  ->when(!empty($filters['category']), fn($q)=>$q->where('blog_category_id',$filters['category'])) // :50 — exact
  ->when(isset($filters['status']) && $filters['status'] !== '', fn($q)=>$q->where('is_active',$filters['status'])) // :51
  ->latest()->paginate($perPage);                                                   // :52-53
```

3 filter-e sade; `search` ruy-e `title` (string-e sade, na JSON `->fa`). Admin **hame** maqalat (fa'al va gheyr-e fa'al) ra mibinad.

### `prepareData(BlogDTO $dto): array` — `BlogService.php:56-68`

`title, slug, excerpt, content, blog_category_id, is_active, show_in_first_page, user_id` (`:59-66`) — **`image` dar in list nist** (joda dar store/update handle mishavad).

### Image pipeline — `BlogService.php:73-96`

- `handleImageUpload` (`:73-82`): `ImageHandler::handle(self::UPLOAD_FOLDER, $image)` — **bedoon arg-e `$sizes`** → `ImageHandler.php:19-20` faghat upload (WebP) va **hich resize-i nist** (yek file baraye har tasvir; ba bar aks-e products 3 size).
- `deleteImage` (`:88-96`): `ImageHandler::delete(self::UPLOAD_FOLDER, $imageName)` — `public_path(config('paths.upload','uploads').'/blogs/')` + `@unlink` (`ImageHandler.php:37-44`).

---

## 4. DTO — `app/DTOs/BlogDTO.php`

`readonly class BlogDTO` (`:8`) — 10 field-e readonly dar constructor (`:10-21`):

```php
title:string, slug:string, excerpt:?string, content:string, blogCategoryId:?int,
isActive:bool, showInFirstPage:bool, image:?UploadedFile, removeImage:bool=false, userId:?int=null
```

`fromRequest(BlogRequest $request, ?int $userId = null)` (`:23-37`):

- `validated(...)` baraye title/slug/excerpt/content/blog_category_id (`:26-30`)
- `$request->boolean('is_active' / 'show_in_first_page' / 'remove_image')` (`:31-34`)
- `$request->file('image')` (`:33`)
- `userId` az parametr — `auth()->id()` az controller (`BlogController.php:36,52`)

Hich filtering/normalization-i nist (ba bar aks-e ProductDTO) — field-ha kam hastand va validation dar Request anjam mishavad.

**BlogCategoryDTO — code-e morde:** `app/DTOs/BlogCategoryDTO.php:7-52` vojood darad (`public ?int $parent_id` + `fromRequest(array $validated)` ke kilid-e `slug` ra mikhahanad `:28` vali `BlogCategoryRequest` field-e `url` ra validate mikonad `BlogCategoryRequest.php:51`) va **hichja seda zade nemishavad** — `BlogCategoryController` import nashode ast (`BlogCategoryController.php:1-10`).

---

## 5. Validation (Request-ha)

### 5.1 BlogRequest — `app/Http/Requests/Admin/BlogRequest.php`

- `use HasSlug` (`:12`) — trait-e `app/Traits/HasSlug.php`.
- `prepareForValidation` (`:19-26`): agar `slug` khali → `merge(['slug' => prepareSlug($this->title, null)])` — `Str::slug(title)` (`HasSlug.php:11`).
- `rules` (`:28-42`): `$id = $this->route('blog')?->id` (`:30`) baraye UrlRule ignore:

| Field | Rule | Line |
|---|---|---|
| `title` | `required\|string\|max:255` | `:33` |
| `slug` | `required\|string\|max:255` + `new UrlRule(Blog::class, $id)` | `:34` |
| `excerpt` | `nullable\|string` | `:35` |
| `content` | `required\|string` | `:36` |
| `image` | `nullable\|image\|mimes:jpg,jpeg,png,webp\|max:2048` | `:37` |
| `blog_category_id` | `nullable\|exists:blog_categories,id` | `:38` |
| `is_active` / `show_in_first_page` | `boolean` | `:39-40` |

- `messages` (`:44-59`) — hame az `validation.requests.blog.*`.

### 5.2 BlogCategoryRequest — `app/Http/Requests/Admin/BlogCategoryRequest.php`

- `prepareForValidation` (`:23-28`): **hamishe** `url = prepareSlug(title, url)` merge mishavad (`Str::slug($url ?: $title)` — `HasSlug.php:11`).
- `rules` (`:35-58`): `$id = $this->route('blogCategory')?->id ?? $this->route('blog_category')?->id` (`:37`) — resource param-e asli `blog_category` ast (`routes-dump.md:37`) pas fallback-e dovom estefade mishavad.

| Field | Rule | Line |
|---|---|---|
| `parent_id` | `nullable\|exists:blog_categories,id` + Closure-e self-parent: `if ($id && $value == $id) $fail(...)` | `:40-49` |
| `title` | `required\|string\|max:255` | `:50` |
| `url` | `required\|string\|max:255` + `new UrlRule(BlogCategory::class, $id, 'slug')` — arg-e **sevom** column-e `slug` | `:51` |
| `description` | `nullable\|string` | `:52` |
| `image` | mesl-e BlogRequest | `:53` |
| `is_active` / `show_in_first_page` | `boolean` | `:54-55` |
| `sort_order` | `nullable\|integer\|min:0` | `:56` |

### 5.3 UrlRule — `app/Rules/UrlRule.php:15-32`

`where($column,$value)->whereNull('deleted_at')` + `where('id','!=',$exceptId)` → `count() === 0`. Uniqueness faqat **miyan-e radif-haye hazf-nashode** — vali index-e unique-e DB shamel soft-delete-ha ham hast (be `gotchas.md` negah konid). Message-e Persian dar `:36`.

---

## 6. Models & Relations

### 6.1 Blog — `app/Models/Blog.php`

- **Fillable (`:13-16`):** `blog_category_id, user_id, title, slug, excerpt, content, image, views, is_active, show_in_first_page` — 10 field.
- **Casts (`:18-25`):** `views => integer`, `is_active/show_in_first_page => boolean` — **hich cast-e JSON/array nist**.
- **Relations:** `category` BelongsTo BlogCategory ba FK `blog_category_id` (`:28-31`), `author` BelongsTo User ba FK `user_id` (`:33-36`).
- **Scopes:** `scopeActive` (`:39-42`) `where is_active true`, `scopeFirstPage` (`:44-47`) `where show_in_first_page true`.
- **`incrementViews()` (`:50-53`):** `$this->increment('views')` — write be DB har show.
- **`readingMinutes(): int` (`:59-65`):** `strip_tags(content)` → `preg_split('/\s+/u')` → `max(1, ceil(words/200))` — huriestic-e 200 kalame/daghighe.
- **`getImageUrlAttribute()` (`:67-72`):** `asset(config('paths.upload','uploads').'/blogs/'.$image)` ya `null` — fallback-e view-ha **picsum.photos** (`site/blogs/index.blade.php:47`), na image-not-found.
- **`localizedDate(string $format = 'd F Y'): string` (`:89-92`):** `show_date($this->created_at, $format)` — `show_date` (`app/Helpers/date.php:17-51`) bar asas-e `locale()->calendar()`: `jalali → jdate_format` (`:38-40`), `hijri → hijri_date` (`:43-45`), gheir → `Carbon::translatedFormat` (`:48-50`). Docblock-e `Blog.php:83-84` be `LocaleService::isJalali` eshare darad. Moshabeh-e trait-e `HasJalaliDate` (`app/Traits/HasJalaliDate.php:5-14`) amma **in trait estefade nashode** (kavosh-e code: trait dar hichja `use` nashode ast) — logic dar khod-e model tekrar shode (legacy duplication).

### 6.2 BlogCategory — `app/Models/BlogCategory.php`

- **Fillable (`:14-17`):** `parent_id, title, slug, description, image, is_active, show_in_first_page, sort_order`.
- **Casts (`:19-26`):** `is_active/show_in_first_page => boolean`, `sort_order => integer`.
- **Relations:** `parent` BelongsTo self ba `parent_id` (`:29-32`), `children` HasMany self (`:34-37`), `blogs` HasMany Blog (`:39-42`).
- **Scope:** `scopeActive` (`:45-48`).
- **`getImageUrlAttribute()` (`:51-56`):** `asset(uploads/blog-categories/...)`.

---

## 7. Home Section (Bakhsh-e Blog-e Safheye Asli)

- `app/Http/Controllers/Site/HomeController.php:77` — `$latestBlogs = Blog::with('category')->active()->latest()->take(3)->get();`
- Dar compact-e view `site.home.index` (`:89`).
- View: `site/home/index.blade.php:388` (tasvir + fallback picsum), `:403-407` (`localizedDate`), `:437` (`readingMinutes` ba `__('messages.reading_time')`).
- Test: `tests/Feature/HomeBlogSectionTest.php:58` (`test_home_page_hides_section_when_no_blogs`), `:65` (`test_home_page_shows_only_the_latest_three_blogs`).

---

## 8. Seeders & Tests

- **BlogCategorySeeder** (`database/seeders/BlogCategorySeeder.php:12-21`): 3 daste (`buying-guide`, `store-news`, `fashion-tips`) ba `updateOrCreate(['slug'=>...])` (`:19`).
- **BlogSeeder** (`database/seeders/BlogSeeder.php:12-63`): daste-ha ba slug peyda mishavand (`:14-15`), admin-e `admin@shopora.test` (`:16`) — agar nabood `return` (`:18-20`). 3 maqal-e namone ba `updateOrCreate` ba slug (`:59`).
- **Tests:** `tests/Feature/ImageRemovalTest.php:88-102` (`remove_image=1` → image null), `:105-120` (bedoon image/bedoon remove → image-e ghadimi hefz mishavad). `HomeBlogSectionTest` (bakhsh 7).

---

## 9. Views

| Masir | Content |
|---|---|
| `resources/views/admin/blogs/` | `index` (form-e filter search/category/status `:28-43`), `create`, `edit`, `_fields` (`x-admin.image-upload` `:131-136`) |
| `resources/views/admin/blog-categories/` | `index`, `create`, `edit`, `_fields` |
| `resources/views/site/blogs/` | `index.blade.php` (`:47` image + picsum fallback, `:72` localizedDate), `show.blade.php` (`:20-21` image, `:33` tarikh, `:53-57` related) |

---

## 10. Business Rules (Ghavaed-e Kasbokar)

| # | Rule | Evidence | Tozih |
|---|------|----------|-------|
| 1 | `Slug auto + unique` | `BlogRequest.php:21-25` + `HasSlug.php:11` + `UrlRule.php:23-31` | Slug-e khali az title misazad; uniqueness dar radif-haye hazf-nashode. |
| 2 | `Image WebP bedoon resize` | `BlogService.php:81` (bedoon sizes) + `ImageHandler.php:19-20` | Yek file-e WebP per tasvir — thumb/medium/large nadarad. |
| 3 | `remove_image 3-hali` | `BlogService.php:26-33` | Jadid > remove > hefz. Test shode dar `ImageRemovalTest.php:88-120`. |
| 4 | `Guard-e hazf-e daste` | `BlogCategoryController.php:96-99` | Daste ba maqal (hazf-nashode) hazf nemishavad — error redirect. |
| 5 | `is_active guard` | `Site/BlogController.php:28,48` | Maqal/daste-ye gheyr-e fa'al dar site 404. |
| 6 | `Views increment` | `Site/BlogController.php:31` + `Blog.php:50-53` | Har show +1 — bedoon dedup/throttle. |
| 7 | `Tarikh-e mohalli` | `Blog.php:89-92` + `app/Helpers/date.php:17-51` | Jalali/Hijri/Gregorian bar asas-e locale — point-e yek-e namayesh. |
| 8 | `title string-e sade` | migration `blogs:18` + `BlogService.php:49` | Search-e `like` ruy-e `title` — JSON multilingual nist. |
| 9 | `user_id nevisande` | `BlogController.php:36,52` | Dar store va update `auth()->id()` — update nevisande-ra avaz mikonad. |
| 10 | `SoftDeletes` | `Blog.php:11` + `BlogCategory.php:12` + FK `nullOnDelete` (`blogs migration:16`) | Hazf-e daste → maqalatash bi-daste nemishavand (FK null mishavad). |

---

## 11. Open Questions (Soalat-e Baz)

- `admin.blogs.show` / `admin.blog-categories.show` morde hastand — aya `except('show')` bayad ezafe shavad (mesl-e `hero-slides` `routes/admin.php:41`)?
- `BlogCategoryDTO` morde ast — hazf ya estefade?
- `scopeFirstPage` (`Blog.php:44-47`) dar hich controller-i estefade nashode — koda? (kavosh: faghat definition peyda shod)
- Rate-limit baraye `incrementViews` vojood nadarad — aya amdan ast?

UNKNOWN: nashan-e daghigh-e inke `views` dar admin index namayesh dade mishavad ya na (view-e index kamel trace nashode); aya `scopeFirstPage` ghablan dar HomeController-e ghadimi boode (tarikhche-e git check nashode).
