# Blog — Dependencies (Deep Trace)

> **Module:** `blog` — Weblog (maqalat + daste-bandi)
> **Status:** ✅ verified-trace — بازنویسی از کد واقعی (2026-09-06)

---

## 1. Graph-e Vabastegi-ha (Dependency Graph)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                          Admin/BlogController                                │
│      index / create / store / edit / update / destroy (6 method, show NIST) │
│      DI: BlogService (constructor promotion :16)                            │
└──────┬───────────────────────────────────────────────────────────────────────┘
       │ :20 index → BlogCategory where is_active (list-e filter)
       │ :21 → BlogService::getPaginatedBlogs($request->all())
       │ :29 create → BlogCategory where is_active orderByDesc id
       │ :36 store → BlogDTO::fromRequest($request, auth()->id()) → service store
       │ :45 edit → BlogCategory (bedoon eager load)
       │ :52 update → BlogDTO::fromRequest + service update
       │ :61 destroy → service destroy
       ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                              BlogService                                     │
│  UPLOAD_FOLDER='blogs' (:11) — bedoon DB::transaction                       │
│  store (:13-19) / update (:21-36) / destroy (:38-43)                        │
│  getPaginatedBlogs (:45-54) — search title like, category exact, status     │
│  prepareData (:56-68) — 8 field, bedoon image                               │
├─────────────────────────────────────────────────────────────────────────────┤
│  store/update/destroy ───► Blog (Eloquent — create/update/delete)           │
│  handleImageUpload (:73-82) ──► ImageHandler::handle('blogs', file)         │
│  │                            └─► bedoon $sizes → ImageResizer SEDA NA-       │
│  │                                MISHAVAD (yek file WebP per tasvir)        │
│  │                            └─► ImageUploader::upload → public/uploads/blogs/*.webp│
│  deleteImage (:88-96) ─────► ImageHandler::delete('blogs', name)            │
│  │                            └─► public_path + @unlink (fail + sizes)       │
└──────┬───────────────────────────────────────────────────────────────────────┘
       │ writes: blogs, (blog_categories by controller) + file system
       │ reads:  blog_categories, users (auth()->id)
       ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                                Models                                        │
│  Blog (SoftDeletes :11, 10 fillable, 3 cast, 2 relation, 2 scope,           │
│        incrementViews, readingMinutes, getImageUrl, localizedDate)          │
│  ├─► category → BlogCategory (FK blog_category_id :28-31)                   │
│  └─► author   → User        (FK user_id :33-36)                             │
│  BlogCategory (SoftDeletes :12, parent/children self, blogs HasMany :39-42) │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                  Admin/BlogCategoryController (bedoon Service/DTO)           │
│  const UPLOAD_FOLDER='blog-categories' (:12) — direct Eloquent              │
│  index withCount blogs orderBy sort_order (:14-21)                          │
│  store/edit/update/destroy — ImageHandler::handle/delete mostaghim          │
│  │   update: aval upload jadid (:70), sepas hazf-e ghadimi (:72-74)          │
│  │   destroy: guard blogs()->count()>0 (:96-99) + hazf-e file (:101-103)     │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                    Site/BlogController (bedoon Service)                      │
│  index → Blog::with(category) active latest paginate 9 (:12-22)             │
│  show  → where slug firstOrFail + abort_unless(is_active) + incrementViews  │
│  │       + related: active, id!=, same category, latest take(3) (:34-39)     │
│  category → BlogCategory slug + categoryIdsWithDescendants BFS (:67-78)      │
│  │          whereIn parent_id loop → whereIn blog_category_id (:52-56)        │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                        Home (Site/HomeController)                            │
│  :77 latestBlogs = Blog::with(category) active latest take(3)               │
│  :89 → view site.home.index (blade :388 image, :403 tarikh, :437 reading)   │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                        Shared / Cross-cutting                                │
│  BlogDTO (readonly, 10 field, fromRequest validated + boolean) ──► BlogService│
│  BlogCategoryDTO (:7-52) ──► ⚠️ DEAD — hichja import/seda nashode            │
│  BlogRequest (HasSlug, UrlRule(Blog), messages :44-59)                       │
│  BlogCategoryRequest (HasSlug, UrlRule(..., 'slug'), self-parent closure)    │
│  HasSlug::prepareSlug (Str::slug $url ?: $title — HasSlug.php:11)            │
│  UrlRule (where slug + whereNull deleted_at + exceptId — UrlRule.php:23-31)  │
│  ImageHandler → ImageUploader (WebP GD) → ImageResizer (blog: unused)        │
│  show_date (app/Helpers/date.php:17-51) → locale()->calendar → jdate/hijri/  │
│  Carbon — used by Blog::localizedDate (:89-92)                               │
│  config/paths.php:upload ('uploads') — dar ImageHandler.php:37 va Blog.php:70│
│  config/permissions.php blogs + blog-categories (:73-93)                     │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Controller → Service → Model (Jadvale Vabastegi)

| Caller | Service | DI / Method-ha | Evidence |
|---|---|---|---|
| `Admin\BlogController` | `BlogService` | constructor promotion `protected BlogService $blogService` → `getPaginatedBlogs`(index), `store`, `update`, `destroy` | `BlogController.php:16,21,37,53,61` |
| `Admin\BlogController` | — (model mostaghim) | `BlogCategory::where('is_active')` dar index/create/edit | `BlogController.php:20,29,45` |
| `Admin\BlogCategoryController` | **nist** | direct `BlogCategory::withCount/create/update/delete` + `ImageHandler` static | `BlogCategoryController.php:16,36,39,70,73,102,105` |
| `Site\BlogController` | **nist** | direct `Blog/BlogCategory` query + `incrementViews()` | `Site/BlogController.php:14,19,26,31,34,46,73` |
| `Site\HomeController` | — (model mostaghim) | `Blog::with('category')->active()->latest()->take(3)` | `HomeController.php:77` |

**Pattern:** Admin-e blog service-oriented (Blog) va direct (BlogCategory); Site kamelan direct — mesl-e products amma sade-tar.

---

## 3. Service → Model (Neveshtan/Khandan)

| Method | Writes (DB) | Reads | Transaction | Evidence |
|---|---|---|---|---|
| `BlogService::store` | `blogs` (create) + file-e `public/uploads/blogs/*.webp` | — | ❌ nist (tak-nevesht) | `BlogService.php:13-19` |
| `BlogService::update` | `blogs` (update) + file delete/upload | `blog->image` | ❌ | `BlogService.php:21-36` |
| `BlogService::destroy` | file delete + `blogs` softDelete | `blog->image` | ❌ | `BlogService.php:38-43` |
| `BlogService::getPaginatedBlogs` | — | `blogs with category` | — | `BlogService.php:45-54` |
| `BlogCategoryController@store/update/destroy` | `blog_categories` + file-e `public/uploads/blog-categories/` | `blog_categories` (parents), `blogs count` | ❌ | `BlogCategoryController.php:30-109` |
| `Site\BlogController@show` | `blogs.views` increment | `blogs`, `blog_categories` | ❌ (yek increment) | `Site/BlogController.php:26-42` |
| `Site\HomeController@index` | — | `blogs + category` take 3 | — | `HomeController.php:77` |

---

## 4. Tightly Coupled (Vabastegi-haye Tangatnag)

| Joda | Vaziat | Khatar |
|---|---|---|
| **BlogService ↔ ImageHandler** | store/update/destroy file I/O dar khate DB op | Agar `ImageUploader` exception bedahad dar `update`, tasvir-e ghadimi **ghabl az** upload hazf shode ast (`BlogService.php:26-28`) — data loss. `@unlink` (`ImageHandler.php:43,51`) fail-ha ra be sokooti rad mikonad. |
| **BlogCategoryController ↔ Eloquent** | Bedoon Service/DTO — logic-e image va guard dar controller | Reuse/test dushvar; maghsad-e migration be `BlogCategoryDTO` ke khodash API-e moghayer darad (kilid-e `slug` vs `url`). |
| **Blog.user_id ↔ User** | FK `cascadeOnDelete` (`blogs migration:17`) | Hazf-e User → **hard DELETE**-e maqalat-e ou dar DB (bypass-e SoftDeletes va image cleanup) → file-haye yatim. |
| **Blog.slug ↔ UrlRule ↔ DB unique** | UrlRule faqat hazf-nashode-ha ra check mikonad (`UrlRule.php:25`), index-e DB shamel soft-delete-ha ast (`blogs migration:19`) | Slug-e maqal-e soft-delete-shode → UrlRule pass, QueryException dar insert. |
| **Blog::localizedDate ↔ show_date ↔ locale()** | `show_date` be `locale()->calendar()` va `jdate_format`/`hijri_date` vabaste ast (`date.php:35-45`) | Porting: bayad helper-haye date + LocaleService ham ghabl shavand. |
| **Site show ↔ views increment** | Write baraye har request-e public (`:31`) | Bot/refresh → views-i gheyr-e vaghe'i; cache/index bar asas-e `views` (`blogs migration:23` index) gheyr-e daqiq mishavad. |

---

## 5. Reusable / Shared (Ghabel-e Estefade-ye Mojadad)

| Component | Scope-e estefade | Evidence |
|---|---|---|
| `ImageHandler::handle/delete` | `BlogService` + `BlogCategoryController` + ProductService/BrandService/ProductCategoryService | `app/Helpers/ImageHandler.php:12-57` |
| `ImageUploader::upload` | WebP conversion (`imagewebp` GD) dar `public/uploads/<folder>/` | `app/Helpers/ImageUploader.php` |
| `HasSlug::prepareSlug` | `BlogRequest` + `BlogCategoryRequest` + ProductRequest/BrandRequest/ProductCategoryRequest | `app/Traits/HasSlug.php:9-12` |
| `UrlRule` | Blog slug (`:34` BlogRequest) + BlogCategory slug ba column-e custom `'slug'` (`:51`) | `app/Rules/UrlRule.php:9-13` (modelClass, exceptId, column) |
| `BlogCategory::scopeActive` + `Blog::scopeActive` | Tamame query-haye site + admin | `Blog.php:39-42`, `BlogCategory.php:45-48` |
| `show_date` | `Blog::localizedDate` + `HasJalaliDate` trait (unused) + gheyr-e module-ha | `app/Helpers/date.php:17` |
| `x-admin.image-upload` | Form-e create/edit-e blog (crop/preview/remove_image) | `admin/blogs/_fields.blade.php:131-136` |
| Traits-e `HasMetaData/HasImageAttributes/HasJalaliDate` | **Hich kodam dar blog (ya hichja) estefade nashode** — orphan-haye app/Traits (kavosh: grep-e kol-e app, tanha definition) | `app/Traits/HasMetaData.php:8`, `HasImageAttributes.php:7`, `HasJalaliDate.php:5` |

---

## 6. External / DB / File (Khareji / DB / File)

| Layer | Resource | Joziat | Evidence |
|---|---|---|---|
| **DB tables** | `blogs` | `id, blog_category_id FK nullable nullOnDelete, user_id FK nullable cascadeOnDelete, title string, slug unique+index, excerpt text nullable, content longText, image string nullable, views unsignedBigInteger default 0 index, is_active bool default true index, show_in_first_page bool default false, timestamps, softDeletes` | `2026_07_29_182334_create_blogs_table.php:14-28` |
| | `blog_categories` | `id, parent_id FK self nullable nullOnDelete, title string, slug unique+index, description text nullable, image string nullable, is_active bool default true index, show_in_first_page bool default false, sort_order unsignedSmallInteger default 0, timestamps, softDeletes` | `2026_07_29_003839_create_blog_categories_table.php:14-29` |
| **File** | `public/<uploads>/blogs/` | Yek file-e WebP per tasvir — **hich resize** (thumb/medium/large nist) | `BlogService.php:81` (bedoon sizes) + `ImageHandler.php:19-20` |
| | `public/<uploads>/blog-categories/` | Haman — yek WebP | `BlogCategoryController.php:36,70` |
| **External** | `https://picsum.photos/seed/blog-{id}/600/400` | Fallback-e tasvir-e site dar index/show/home agar `image_url` null bashad | `site/blogs/index.blade.php:47`, `show.blade.php:60`, `site/home/index.blade.php:388` |
| **Cache** | *(hichi)* | Hich cache dar blog nist | kavosh-e kamel-e BlogService/controllers |
| **Helpers-e yek-pare** | `show_date` + `locale()` + `jdate_format` + `hijri_date` | Baraye `localizedDate` zaroori | `app/Helpers/date.php:17-51` |

**Indexes-e mohem:** `blogs.slug` unique+index (`migration:19`), `blogs.views` index (`:23`), `blogs.is_active` index (`:24`), `blog_categories.slug` unique+index, `blog_categories.is_active` index, `parent_id` FK (`:16`).

---

## 7. Nemoudar-e Vabastegi-e Module-ha (Module Dependency Map)

```
blog (in module)
  ├──► users (User — nevisande-e maqal via user_id + auth()->id; FK cascade)
  │     Evidence: Blog.php:33-36 + BlogController.php:36,52 + blogs migration:17
  ├──► localization (locale()/show_date/jdate/hijri — tarikh-e mohalli-shode)
  │     Evidence: Blog.php:89-92 + app/Helpers/date.php:17-51 + routes/site.php:172-175
  ├──► rbac-admin (AdminPermission + config/permissions.php blogs/blog-categories)
  │     Evidence: routes/admin.php:34 + config/permissions.php:73-93
  ├──► dashboard/menu-e admin (routes-e resource — UNKNOWN: menu builder file peyda nashod dar in trace)
  ├──► media/images (ImageHandler/ImageUploader/WebP — shared stack)
  │     Evidence: BlogService.php:81,95 + BlogCategoryController.php:36,70,73,102
  └──◄ home (Site/HomeController bakhsh-e blog-e safheye asli — take 3)
        Evidence: HomeController.php:77,89
```

---

## 8. Evidence Matrix

| Claim | File:Line |
|---|---|
| Admin DI BlogService + 6 method (show nist) | `app/Http/Controllers/Admin/BlogController.php:14-66` |
| Admin index 3 filter + appends | `BlogController.php:18-25` + `app/Services/BlogService.php:45-54` |
| BlogCategoryController bedoon Service, UPLOAD_FOLDER, pattern-e update-e amn | `BlogCategoryController.php:12, 63-92` |
| Site index/show/category + BFS descendants | `Site/BlogController.php:12-78` |
| BlogService image bedoon resize + remove_image 3-hali | `BlogService.php:73-96, 21-36` + `ImageHandler.php:19-20` |
| BlogDTO 10 field + fromRequest | `app/DTOs/BlogDTO.php:8-37` |
| BlogCategoryDTO dead code (slug vs url mismatch) | `app/DTOs/BlogCategoryDTO.php:23-35` + `BlogCategoryRequest.php:51` |
| BlogRequest HasSlug + UrlRule + 8 rule | `BlogRequest.php:12-42` + `HasSlug.php:9-12` + `UrlRule.php:15-32` |
| Blog model 10 fillable + incrementViews + readingMinutes + localizedDate | `app/Models/Blog.php:13-92` |
| Migrations 2 jadval + FK cascade/nullOnDelete | `2026_07_29_182334...:16-17` + `2026_07_29_003839...:16` |
| Home take(3) + test | `HomeController.php:77` + `tests/Feature/HomeBlogSectionTest.php:58,65` |
| ImageRemovalTest blog remove/keep | `tests/Feature/ImageRemovalTest.php:88-120` |
| Routes admin 2 resource + site 3×2 | `routes/admin.php:38-39` + `routes/site.php:64-66,169-175` + `routes-dump.md:34-51,252-257` |
| Permissions 2 gorouh 6 kilidi (show nist) | `config/permissions.php:73-93` |
