# Blog — API Contract (Deep Trace)

> **Source:** `routes/admin.php:38-39`, `routes/site.php:64-66` + `:169-175`, `docs/_generated/routes-dump.md:31-51, 249-257` — trace-e mostaghim, bedoon hads
> **Status:** ✅ verified-trace — بازنویسی از کد واقعی (2026-09-06)

---

## 1. Naghshe-e Kolli-e Route-ha

- **Admin (CRUD):** 2 ta `Route::resource` — `blogs` (`routes/admin.php:38`) va `blog-categories` (`:39`) — **bedoon `except()`** → har kodam 7 route misazad. Zir-e group: `prefix('admin')->name('admin.')` (`:24`) + `middleware([auth, adminPermission, log.admin, refreshRememberMe])` (`:34`).
- **Site (Public):** 3 route dakhel-e closure-e `$siteRoutes` (`routes/site.php:42`) ke **2 bar** ejra mishavad: `$siteRoutes()` bedoon pishvand (`:169`) va dobare ba `prefix('{locale}')->where(['locale' => $localePattern])` (`:172-175`). Natije: har URI-e site 2 name darad (mesl-e `blogs.index` va `locale.blogs.index`).

**Route-haye morde (dead):** `Route::resource` action-e `show` ra misazad vali **hich yek az 2 controller-e Admin method-e `show` nadarand** (`BlogController.php:14-66` 6 method; `BlogCategoryController.php:10-110` 5 method). `GET admin/blogs/{blog}` va `GET admin/blog-categories/{blog_category}` → Exception 500 dar surat-e visit. Moghayese: `hero-slides` va `promo-banners` `except('show')` darand (`routes/admin.php:41,45`) — blog amma na. Dar `config/permissions.php` ham kilid-e `show` nist (`:73-93` faqat index/create/store/edit/update/destroy).

---

## 2. Jadval-e Kamel-e Route-ha (Exhaustive)

### 2.1 Admin — `routes/admin.php:38-39`

> Prefix: `admin` | Name prefix: `admin.` | Middleware-e gorouh: `web, auth, adminPermission, log.admin, refreshRememberMe` (`routes/admin.php:24,34` + routes-dump.md:34-51)

| # | Method | URI | Name | Controller@Method | File:Line | Vaziat |
|---|--------|-----|------|-------------------|-----------|--------|
| B1 | `GET` | `admin/blogs` | `admin.blogs.index` | `Admin\BlogController@index` | `routes/admin.php:38` + `BlogController.php:18` | ✅ fa'al — list + 3 filter (search/category/status) |
| B2 | `GET` | `admin/blogs/create` | `admin.blogs.create` | `Admin\BlogController@create` | `:38` + `BlogController.php:27` | ✅ fa'al |
| B3 | `POST` | `admin/blogs` | `admin.blogs.store` | `Admin\BlogController@store` | `:38` + `BlogController.php:34` | ✅ fa'al — BlogDTO + BlogService::store |
| B4 | `GET` | `admin/blogs/{blog}` | `admin.blogs.show` | `Admin\BlogController@show` | `:38` + routes-dump.md:49 | ⚠️ **morde** — method dar controller vojood nadarad; RBAC ham kilid-e `show` nadarad (`config/permissions.php:73-84`) |
| B5 | `GET` | `admin/blogs/{blog}/edit` | `admin.blogs.edit` | `Admin\BlogController@edit` | `:38` + `BlogController.php:43` | ✅ fa'al |
| B6 | `PUT/PATCH` | `admin/blogs/{blog}` | `admin.blogs.update` | `Admin\BlogController@update` | `:38` + `BlogController.php:50` | ✅ fa'al — BlogDTO + BlogService::update |
| B7 | `DELETE` | `admin/blogs/{blog}` | `admin.blogs.destroy` | `Admin\BlogController@destroy` | `:38` + `BlogController.php:59` | ✅ fa'al — softDelete + hazf-e file |
| C1 | `GET` | `admin/blog-categories` | `admin.blog-categories.index` | `Admin\BlogCategoryController@index` | `routes/admin.php:39` + `BlogCategoryController.php:14` | ✅ fa'al — withCount('blogs'), orderBy sort_order |
| C2 | `GET` | `admin/blog-categories/create` | `admin.blog-categories.create` | `Admin\BlogCategoryController@create` | `:39` + `BlogCategoryController.php:23` | ✅ fa'al |
| C3 | `POST` | `admin/blog-categories` | `admin.blog-categories.store` | `Admin\BlogCategoryController@store` | `:39` + `BlogCategoryController.php:30` | ✅ fa'al — direct Eloquent (bedoon Service/DTO) |
| C4 | `GET` | `admin/blog-categories/{blog_category}` | `admin.blog-categories.show` | `Admin\BlogCategoryController@show` | `:39` + routes-dump.md:38 | ⚠️ **morde** — method vojood nadarad |
| C5 | `GET` | `admin/blog-categories/{blog_category}/edit` | `admin.blog-categories.edit` | `Admin\BlogCategoryController@edit` | `:39` + `BlogCategoryController.php:54` | ✅ fa'al — parent-ha bedoon khod |
| C6 | `PUT/PATCH` | `admin/blog-categories/{blog_category}` | `admin.blog-categories.update` | `Admin\BlogCategoryController@update` | `:39` + `BlogCategoryController.php:63` | ✅ fa'al |
| C7 | `DELETE` | `admin/blog-categories/{blog_category}` | `admin.blog-categories.destroy` | `Admin\BlogCategoryController@destroy` | `:39` + `BlogCategoryController.php:94` | ✅ fa'al — guard-e has_blogs |

**Tedad-e vaghe'i-e fa'al:** 12 az 14 — B4 va C4 morde hastand.

**RBAC:** `AdminPermission` check-e per-route; kilid-ha dar `config/permissions.php:73-84` (blogs: index/create/store/edit/update/destroy) va `:85-93` (blog-categories: haman 6) — `store` kilid-e `create` ra dasht, `update` kilid-e `edit` (`:78,80`).

---

### 2.2 Site — Canonical (bedoon pishvand) — `routes/site.php:64-66`

> Middleware: `web` (default) | Tamame route-ha public — hich auth-i nist

| # | Method | URI | Name | Controller@Method | File:Line | Input / Behavior |
|---|--------|-----|------|-------------------|-----------|------------------|
| S1 | `GET` | `blogs` | `blogs.index` | `Site\BlogController@index` | `routes/site.php:64` + `Site/BlogController.php:12` | Query param nist → `with('category') active latest paginate 9` |
| S2 | `GET` | `blogs/{blog:slug}` | `blogs.show` | `Site\BlogController@show` | `routes/site.php:65` + `Site/BlogController.php:24` | `{blog:slug}` → `firstOrFail` + `abort_unless(is_active,404)` + `incrementViews()` + related take(3) |
| S3 | `GET` | `blog-categories/{category:slug}` | `blogs.category` | `Site\BlogController@category` | `routes/site.php:66` + `Site/BlogController.php:44` | `{category:slug}` → `abort_unless(is_active,404)` + `whereIn blog_category_id descendants` paginate 9 → view-e haman index |

### 2.3 Site — Locale Duplicate — `routes/site.php:169-175`

Hameye S1-S3 ba `prefix('{locale}')->where(['locale' => $localePattern])` + name prefix `locale.` dobare sabt mishavand (`$siteRoutes` closure `:42` dar `:175` `group` mishavad). Pattern az katalog-e zabanha sakhte mishavad (`site.php:39` — `preg_quote` + merge ba `locale()->codes()`).

| # | URI | Name |
|---|-----|------|
| L1 | `{locale}/blogs` | `locale.blogs.index` |
| L2 | `{locale}/blogs/{blog:slug}` | `locale.blogs.show` |
| L3 | `{locale}/blog-categories/{category:slug}` | `locale.blogs.category` |

Evidence-e routes-dump: `docs/_generated/routes-dump.md:252-257`.

---

## 3. Validation-e Route-ha

| Route | FormRequest | Rule-haye kilidi |
|---|---|---|
| B3/B6 | `BlogRequest` | title required max:255, slug + `UrlRule(Blog)`, image mimes jpg/jpeg/png/webp max 2048, blog_category_id exists, is_active/show_in_first_page boolean (`BlogRequest.php:33-40`) |
| C3/C6 | `BlogCategoryRequest` | parent_id exists + closure-e self-parent, `url` + `UrlRule(BlogCategory, id, 'slug')`, sort_order integer min:0 (`BlogCategoryRequest.php:40-56`) |
| S1-S3 / L1-L3 | — (bedoon FormRequest) | Faghat implicit/dasti ba slug; guard-e `is_active` dar controller |

**UNKNOWN:** Test-e integration baraye route-haye Site-e blog (joz Home section) peyda nashod — faghat `HomeBlogSectionTest` va `ImageRemovalTest` (admin PUT) blog-ra mipooshanand.
