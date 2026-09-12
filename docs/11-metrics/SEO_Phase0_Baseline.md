# SEO — Phase 0 Baseline (Preflight / Audit)

> وضعیت ثبت‌شدهٔ پروژه **پیش از هر تغییر SEO**. این سند مرجع مقایسه برای فازهای ۱ تا ۷ است.
> هر عدد این سند با دستور مشخصی که کنارش آمده قابل بازتولید است.

- **تاریخ ثبت:** 2026-09-12
- **شاخه:** `master`
- **محیط ثبت:** توسعهٔ محلی (Windows / Laragon، MySQL 8.0.30 روی `127.0.0.1:3306`)

---

## ۱) وضعیت مهاجرت‌ها

دستور: `php artisan migrate:status`

| مورد | نتیجه |
|---|---|
| کل مهاجرت‌ها | همه **Ran** |
| مهاجرت Pending | فقط `2026_09_11_142655_create_meta_tags_table` |
| جدول `meta_tags` در دیتابیس | **وجود ندارد** (`Schema::hasTable('meta_tags') === false`) |

**نتیجهٔ قطعی:** جدول شکستهٔ `meta_tags` هرگز ساخته نشده است ⇒ مهاجرت آن **Pending** است و می‌توان فایلش را حذف کرد.

**تصمیم اعمال‌شده:** فایل `2026_09_11_142655_create_meta_tags_table.php` حذف و به‌جای آن یک مهاجرت تمیز `create_seo_meta_table` ایجاد می‌شود.
**دلیل:** قاعدهٔ «هیچ مهاجرت اعمال‌شده‌ای ویرایش/حذف نمی‌شود» برای مهاجرت‌های **Ran** است؛ این مهاجرت هرگز اجرا نشده، پس حذفش صفر ریسک دارد و از انباشت جدول بی‌مصرف جلوگیری می‌کند.

---

## ۲) Baseline تست‌ها

دستور: `php artisan test`

### قبل از اصلاحات فاز ۰
```
Tests: 16 failed, 541 passed (1913 assertions) — Duration: 144.02s
```

### بعد از اصلاحات فاز ۰ (مرجع فازهای بعدی)
```
Tests: 9 failed, 549 passed (1937 assertions)
```

### ۲.۱ شکست‌های رفع‌شده در فاز ۰ (۷ تست)

همه در `tests/Feature/SeoLocalizationTest.php` و همه با **یک ریشهٔ مشترک**:

| تست شکسته | علت |
|---|---|
| `hreflang urls point to prefixed versions` | ریشهٔ URL در تست‌ها `http://shopora.test` بود، تست انتظار `http://localhost` داشت |
| `canonical tag present` | همان |
| `localized route helper` | همان |
| `localized url helper uses current route` | همان |
| `redirect preserves query string` | همان |
| `hreflang exact count and x default` | همان |
| `every site route has a locale twin` | روت‌های زیرساختی دوقلوی `locale.*` نداشتند |

**ریشهٔ مشکل اول تا ششم:** `phpunit.xml` مقدار `APP_URL` را تعیین نمی‌کرد، پس مقدار فایل `.env` توسعه‌دهنده (`http://shopora.test`) به تست‌ها نشت می‌کرد و همهٔ assertهای مربوط به canonical/hreflang/host می‌شکستند. ایراد کد نبود؛ ایراد **غیرقطعی بودن محیط تست** بود.

**اصلاح:** افزودن `<env name="APP_URL" value="http://localhost"/>` به `phpunit.xml`.
**دلیل:** تست‌های canonical/hreflang باید مستقل از `.env` ماشین توسعه‌دهنده باشند؛ در غیر این صورت روی هر ماشین نتیجهٔ متفاوت می‌دهند و به‌عنوان تور ایمنی بی‌ارزش می‌شوند.

**ریشهٔ مشکل هفتم:** `SeoLocalizationTest::test_every_site_route_has_a_locale_twin` هر روت غیر از `admin.`/`locale.`/`ignition.` را ملزم به دوقلوی `locale.*` می‌کرد، اما این روت‌ها عمداً بیرون از closure زبان ثبت شده‌اند:
`secret.migrate`, `secret.seed`, `secret.clear-cache`, `secret.fresh`, `sitemap`

**اصلاح:** افزودن allowlist صریح (`INFRASTRUCTURE_ROUTES_WITHOUT_LOCALE_TWIN`) + یک تست نگهبان جدید که تأیید می‌کند هر نام allowlist واقعاً وجود دارد و دوقلوی `locale.*` نگرفته است.
**دلیل:** معافیت باید **آگاهانه و محافظت‌شده** باشد، نه فراموشی. تست نگهبان جلوی دو رگرسیون آینده را می‌گیرد: حذف ناخواستهٔ یک روت زیرساختی، و گرفتن دوقلوی زبان ناخواسته برای آن.

### ۲.۲ شکست‌های باقی‌مانده (۹ تست) — **خارج از محدودهٔ SEO**

| تست | علت ریشه‌ای |
|---|---|
| `Tests\Unit\CouponServiceTest` (۹ تست) | `Method Mockery_2_Illuminate_Auth_AuthManager::check() does not exist on this mock object` در `app/Services/CartService.php:22` |

**مالکیت:** نقص mock در ناحیهٔ سبد خرید/کدتخفیف — هیچ ارتباطی با SEO ندارد.
**تصمیم:** در فازهای SEO دست نمی‌خورد. تعمیرش باید در یک کار مستقل انجام شود (اصلاح mock یا جایگزینی `AuthManager` با `auth()` واقعی در تست)، چون دست‌زدن به `CartService` در جریان کار SEO، ریسک رگرسیون در سبد خرید را وارد می‌کند.

**قاعدهٔ ادامهٔ کار:** از این پس «سبز» یعنی **۹ شکست CouponServiceTest بدون تغییر بماند** و بقیه سبز باشند. هر عدد دیگری = رگرسیون.

---

## ۳) موجودی داده (Inventory)

دستور: `php artisan tinker` + شمارش مدل‌ها

| موجودیت | تعداد | یادداشت |
|---|---|---|
| `products` | 11 | همه `is_active = true` |
| `product_categories` | 7 | — |
| ↳ دارای `meta_title` | **0** | هیچ‌کس از فیلد متای دسته‌بندی استفاده نکرده |
| ↳ دارای `meta_description` | **0** | همان |
| `blogs` | 3 | بدون هیچ ستون SEO |
| `blog_categories` | 3 | بدون هیچ ستون SEO |
| `brands` | 3 | بدون هیچ ستون SEO و **بدون روت صفحهٔ برند** |
| `reviews` | 14 | منبع `aggregateRating` در فاز ۳ |

**نتیجهٔ مهم:** مهاجرت دادهٔ `product_categories.meta_*` → `seo_meta` عملاً **صفر ردیف** منتقل می‌کند ⇒ ریسک از‌دست‌رفتن متای دسته‌بندی صفر است و حذف ستون‌ها در فاز ۶ بی‌خطر خواهد بود.

### زبان‌ها

دستور: `Language::orderBy('sort_order')->get()`

| کد | فعال | پیش‌فرض |
|---|---|---|
| `fa` | ✅ | ✅ |
| `en` | ✅ | — |
| `ar` | ✅ | — |
| `tr` | ✅ | — |

⇒ **۴ زبان فعال** و `singleLanguageMode() === false` ⇒ hreflang و sitemap برای ۴ زبان + `x-default`.

`resources/lang/es` وجود دارد اما `es` نه در `config/languages.php` است نه در جدول `languages` ⇒ `/es/...` الان ۴۰۴ است.
**تصمیم:** خارج از محدودهٔ SEO این دوره (طبق انتخاب کاربر) — هیچ اثری در hreflang/sitemap نخواهد داشت.

---

## ۴) نقشهٔ روت‌ها (بازتولید در فاز ۰)

دستور: `php artisan route:list --json > docs/_generated/routes.json`

| متریک | قبل (dump کهنه) | بعد (این ثبت) |
|---|---|---|
| کل روت‌ها | 276 | **293** |
| روت‌های `locale.*` | 64 | **70** |

> ⚠️ `docs/_generated/routes.json` بازتولید شد. فایل `docs/_generated/routes-dump.md` هم کهنه است اما **اسکریپت تولیدکنندهٔ آن در مخزن وجود ندارد** (جست‌وجو نتیجه نداد) ⇒ بازتولید آن یک کار مستقل مستندسازی است؛ `routes.json` منبع ماشینی معتبر است.

### ۴.۱ روت‌های عمومی سایت (غیر admin، غیر locale)

**کاندید ایندکس (۸ نام روت):**
`home` · `products.index` · `products.show` · `products.category` · `blogs.index` · `blogs.show` · `blogs.category` · `site.page`

**باید noindex شوند:**

| گروه | روت‌ها |
|---|---|
| فنی/عملیاتی | `health`, `seed`, `secret.migrate`, `secret.seed`, `secret.clear-cache`, `secret.fresh`, `sitemap` |
| سبد و تسویه | `cart.index`, `cart.add`, `cart.update`, `cart.remove`, `cart.coupon`, `checkout.*` (شامل `index`, `address`, `cities`, `shipments`, `set-shipment`, `address-price`, `payment`, `apply-discount`, `remove-discount`, `validate-stock`, `order-price`, `create`, `success`, `failed`, `finish.*`) |
| حساب کاربری | `dashboard`, `dashboard.*` (شامل `order-detail`, `order-factor`, `orders`, `addresses.*`, `notifications`, `notification-detail`, `notification-go`, `order-receipt.update`, `email.*`, `newsletter.toggle`, `wishlist`) |
| احراز هویت | `login`, `login.verify`, `login.otp`, `login.otp.check`, `login.otp.send`, `login.otp.send-ajax`, `login.otp.resend`, `login.otp.verify`, `logout` |
| علاقه‌مندی | `wishlist.index`, `wishlist.toggle` |
| فرم‌ها و AJAX | `site.contact.send`, `newsletter.subscribe`, `products.reviews.store`, `products.reviews.reply`, `products.reviews.vote`, `products.quick-view`, `api.notifications.unread-count`, `api.notifications.mark-read`, `api.notifications.mark-all-read` |

---

## ۵) تأیید وابستگی کد Legacy (پیش‌نیاز فاز ۶)

جست‌وجوی کل مخزن، شاهدها:

| فایل | وضعیت | شاهد |
|---|---|---|
| `app/Services/MetaService.php` | یتیم | فقط `AppServiceProvider.php:39` (singleton) و `:102` (دایرکتیو `@meta`)؛ `@meta` در **صفر** ویو |
| `app/Services/SeoService.php` | شکسته/یتیم | فقط کامپوننت `SeoHead` و trait یتیم |
| `app/View/Components/SeoHead.php` | شکسته | `getSeoData()` در سرویس **وجود ندارد** + عدم تطابق سازندهٔ ۳‌آرگومانی با ۲‌آرگومانی ⇒ اگر رندر شود fatal |
| `resources/views/components/seo-head.blade.php` | بی‌استفاده | صفر ارجاع در ویوها |
| `app/Models/MetaTag.php` و `app/Models/Meta.php` | یتیم | دو مدل روی یک جدول؛ فقط ارجاع از کد مرده |
| `app/Traits/HasSeoFeatures.php` | یتیم | صفر `use` |
| `app/Traits/HasMetaData.php` | یتیم + خطرناک | صفر `use` **و** ارجاع به `App\Models\MetaData` که وجود ندارد |
| `app/Http/Requests/Admin/MetaTagRequest.php` | یتیم | صفر مصرف‌کننده |
| `resources/views/admin/seo/*` | یتیم | ارجاع به روت‌های ناموجود `admin.seo.edit` / `admin.seo.index-all` |
| `resources/views/admin/meta-date/` | یتیم | صفر ارجاع |
| `resources/views/admin/partials/meta-tags-form.blade.php` | یتیم | صفر `@include`؛ تایپوی `@endror` |
| `resources/views/layouts/site/_partials/head.blade.php` | یتیم | `@include` آن در `app.blade.php:33` کامنت شده |
| `app/Services/Schema/*` | فقط از کد مرده | منبع مفید؛ به `App\Services\Seo\Schema` منتقل و بازاستفاده می‌شود |

---

## ۶) تأیید زیرساخت

| مورد | مقدار ثبت‌شده | اثر بر SEO |
|---|---|---|
| `APP_URL` (`.env`) | `http://shopora.test` | دامنهٔ پروداکشن نامعلوم ⇒ همهٔ URLهای مطلق از **ریشهٔ درخواست** ساخته می‌شوند |
| `APP_ENV` (`.env`) | `production` | مراقبت: `APP_DEBUG=false` روی محیط توسعه |
| `CACHE_STORE` | `database` | `Cache::tags()` پشتیبانی نمی‌شود ⇒ فقط کلید نامدار + `Cache::forget` |
| `CACHE_PREFIX` | خالی | کلیدهای کش بدون پیشوند |
| `SESSION_DRIVER` / `QUEUE_CONNECTION` | `database` / `database` | — |
| `public/.htaccess` | Apache، front controller با `!-f` + ۳۰۱ اسلش انتهایی | روت داینامیک `/robots.txt` پس از حذف فایل استاتیک کار می‌کند؛ نرمال‌سازی اسلش در میدل‌ور لازم نیست |
| `phpunit.xml` | دیتابیس تست جدا `shopora_test`، `CACHE_STORE=array` | تست‌ها دیتای واقعی `shopora` را لمس نمی‌کنند |
| `.github/workflows/deploy.yml` | با هر push روی `master` دیپلوی FTP روی cPanel، **بدون هیچ تست** | تنها تور ایمنی، تست محلی قبل از push است |

---

## ۷) ریسک‌های ثبت‌شده

| ریسک | شدت | مهار |
|---|---|---|
| انتشار مستقیم به پروداکشن با هر push و بدون گیت تست | **بالا** | اجرای تست محلی قبل از هر push؛ گیت CI دومرحله‌ای (non-blocking در فاز ۱، blocking در فاز ۷) |
| نبود دامنهٔ قطعی در `.env` | متوسط | URLها از ریشهٔ درخواست؛ `seo_enforce_canonical_host` پیش‌فرض خاموش |
| روت‌های عمومی `seed` / `migrate-123` / `fresh-123` / `clear-all-cache-1` با راز قابل حدس | **بالا (امنیتی، خارج از SEO)** | در SEO فقط noindex می‌شوند؛ امنی‌سازی نیازمند کار مستقل |
| `resources/lang/es` نیمه‌ساخته | پایین | خارج از محدوده؛ صفر اثر در hreflang/sitemap |
| `docs/_generated/routes-dump.md` کهنه و بدون اسکریپت تولید | پایین | `routes.json` ماشینی معتبر است؛ بازتولید dump کار مستقل |
