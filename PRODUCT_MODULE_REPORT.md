# گزارش کامل مدل محصول (Product Module) — پروژه common-template

> این گزارش حاصل بررسی کامل کد ماژول `Packages/core-cms/src/Modules/Product` به همراه مصرفکنندههای آن (کنترلر سایت، API ها، صفحه اول) است.
> هدف: انتقال کامل مدل محصول — جدولها، روابط، لاجیکها و کارکردها — به یک پروژه فروشگاهی جدید **غیرماژولار**.

---

## ۱) مرور کلی معماری

ماژول محصول بر پایه الگوی **ماژولار (nwidart/laravel-modules)** ساخته شده و هر لایه آن در پوشههای مجزا قرار دارد:

```
Packages/core-cms/src/Modules/Product/
├── Entities/        → مدلهای Eloquent (Product, Brand, ProductCategory, ProductVariant, ...)
├── Database/Migrations/ → ~۵۰ مایگریشن (شما میتوانید همه را در یک مایگریشن ادغام کنید)
├── Services/        → لایه بیزینس لاجیک (ProductService, VariantService, SpfService, ...)
├── Services/Api/    → لاجیک مخصوص API پنل مدیریت
├── DTO/             → انتقال داده از Request به Service (ProductDTO, VariantDTO, SpfDTO, ...)
├── Http/Controllers/    → کنترلرهای وب پنل
├── Http/Controllers/Api/→ کنترلرهای API (V1 برای سایت، بدون V1 برای پنل)
├── Http/Requests/   → اعتبارسنجی ورودیها
├── Http/Resources/  → شکل خروجی API (ProductCollection, ProductDetailCollection, ...)
├── Filters/         → فیلترهای لیست پنل مدیریت
├── Exports/ Imports → اکسل خروجی/ورودی محصولات
├── Rules/           → قوانین اعتبارسنجی سفارشی
├── Library/         → TgjuSilver (قیمتگذاری داینامیک بر اساس قیمت نقره)
└── Routes/          → web.php (پنل) و api.php (سایت + پنل)
```

**الگوی غالب:** `Controller → DTO → Service → Model` — کنترلرها نازک هستند و همه لاجیک در سرویسهاست. رابط کاربری پنل با Vue (اسکریپتهای جاوااسکریپت) ساخته شده و با اندپوینتهای JSON (بدون میدلور AdminPermission) ارتباط برقرار میکند.

> نکته انتقال: در پروژه مقصد، کافی است namespace ها را از `Rahweb\CmsCore\Modules\Product\...` به `App\Models` / `App\Services` / `App\Http\Requests` و… تغییر دهید. ساختار داخلی کلاسها بدون تغییر قابل کپی است.

---

## ۲) نقشه پایگاه داده (اسکیمای نهایی)

> ستونها از **نتیجه نهایی** تمام مایگریشنها استخراج شدهاند (مایگریشنهای حذف/تغییر اعمال شده).

### ۲.۱ جدول `products`
| ستون | نوع | توضیح |
|---|---|---|
| id | bigint PK | |
| title | string | عنوان |
| description | text | توضیحات |
| url | string | اسلاگ یکتا (مسیر `product/{url}`) |
| image | string | نام فایل تصویر اصلی |
| active | tinyint default 0 | نمایش/عدم نمایش |
| brand_id | int nullable | برند |
| creator_id | int nullable | کاربر سازنده (پنل) |
| price | double | قیمت اصلی (قبل تخفیف) |
| discounted_price | double | قیمت با تخفیف (0 یعنی تخفیف ندارد) |
| final_price | double | **قیمت فروش نهایی** (ستون کلیدی برای لیست/فیلتر/مرتبسازی) |
| weight | double default 100 | وزن (گرم) — برای قیمتگذاری داینامیک |
| stock | int default 0 | موجودی (در صورت داشتن واریانت = جمع موجودی واریانتها) |
| main_variant_specification_id | int nullable | مشخصه اصلی واریانت (پشتیبان) |
| show_in_first_page | tinyint default 0 | نمایش در صفحه اول |
| timer_active | tinyint default 0 | فعال بودن تایمر تخفیف |
| start_timer / end_timer | timestamp nullable | بازه زمانی تخفیف تایمردار |
| price_formula | enum('TgjuSilver') nullable | فرمول قیمت داینامیک |
| deleted_at | timestamp nullable | soft delete |
| created_at / updated_at | timestamp | |

**ایندکسها:** `final_price` و `stock` (برای فیلتر قیمت و مرتبسازی).

### ۲.۲ جدول `product_categories`
| ستون | نوع | توضیح |
|---|---|---|
| id | bigint PK | |
| title, description (longtext), url, image | | |
| active | tinyint default 0 | نمایش در منو |
| show_in_site | boolean default 1 | نمایش در سایت |
| parent_id | int nullable | دسته والد (دستهبندی درختی/نامحدود) |
| sort | int nullable | ترتیب نمایش |
| show_in_first_page | tinyint default 0 | نمایش در صفحه اول |
| have_price_range | int default 0 | آیا بازه قیمتی برای این دسته تعریف شده؟ |
| min_price / max_price | double nullable | بازه قیمت دسته (هم فیلتر و هم شرط نمایش) |
| deleted_at, created_at, updated_at | | |

**ایندکس:** `parent_id`.

### ۲.۳ جدول `brands`
| ستون | نوع | توضیح |
|---|---|---|
| id, title, description (longtext), url, image | | |
| active | tinyint default 0 | |
| show_in_first_page | int default 0 | نمایش در صفحه اول |
| deleted_at, created_at, updated_at | | |

### ۲.۴ جدولهای میانی
- **`product_category_product`** → `product_category_id` + `product_id` (ایندکس روی هر دو)
- **`category_specification`** → `product_category_id` + `specification_id` (مشخصههای مرتبط به دسته؛ هر دو ایندکس)
- **`category_specification_conditions`** → `category_id` + `specification_value_id` (**PK ترکیبی**) — شرطهای «فیلتر اجباری» دسته: محصولاتی که در این دسته نشان داده میشوند باید حداقل یکی از این مقادیر مشخصه را داشته باشند
- **`product_specification`** (جدول محوری ولی با ردیف مجزا) → `id`, `value` (text، nullable), `parent_id`, `product_id`, `specification_id`, `deleted_at`, timestamps (ایندکس روی specification_id و product_id)
- **`product_variant_specification`** → `product_variant_id` + `specification_value_id` (مقادیر مشخصه هر واریانت)
- **`product_main_specifications`** → `product_id` + `main_specification_id` (مشخصههای اصلی گروهبندی واریانت)
- **`product_associations`** (قبلاً `related_product`) → `id`, `product_id`, `related_product_id`, `type` enum('related','complement') default 'related', timestamps
- **`image_product_variant`** → `product_variant_id` + `image_id` (تصاویر اختصاصی هر واریانت)

### ۲.۵ جدول `images`
`id, image, active (tinyint), thumbnail (tinyint — ۱ یعنی تصویر شاخص), product_id, specification_id (nullable), deleted_at, timestamps`

### ۲.۶ جدول `product_variants`
`id, product_id, specification_parent_id, specification_id (nullable — ستون قدیمی), price, discounted_price, final_price (double), stock, price_affective (int default 0), weight (double default 100), deleted_at, timestamps`

### ۲.۷ جدول `specifications` (مشخصهها / ویژگیها)
| ستون | نوع | توضیح |
|---|---|---|
| id, title | | |
| type | string | **'text'** (نوشتاری) یا **'select'** (انتخابی) |
| active | tinyint | |
| is_filter | tinyint | نمایش در فیلترهای دستهبندی/برند |
| is_color | tinyint | پالت رنگی (در فیلتر) |
| color_code | string nullable | کد هگز رنگ مقدار |
| parent_id | int nullable | والد: مشخصههای select فرزند دارند (فرزند = مقدار)، مشخصههای text والد ندارند |
| deleted_at, timestamps | | |

### ۲.۸ جدولهای ساده
- **`videos`**: `id, code (longtext — کد امبد آپارات/یوتیوب), active, product_id, ...`
- **`properties`**: `id, value (string), product_id, ...` (یکسری «ویژگیهای» ساده متن آزاد)
- **`prices`** *(بلااستفاده/قدیمی)*: جدول پلیمورفیک `priceable_id/priceable_type/user_id/price/discounted_price/final_price` — در هیچ سرویسی استفاده نمیشود. **نیازی به کپی نیست.**


## ۳) مدلها و روابط (Entities)

> همه مدلها از `Authenticatable` ارث میبرند (در پروژه مقصد از `Model` معمولی استفاده کنید).

### ۳.۱ ترِیتهای مشترک (از ماژول General/Seo)

- **`GlobalScopesTrait`**: اسکوپ `firstPage()` → `where('show_in_first_page','1')` + اکسسور `getFirstPageNameAttribute`.
- **`Searchable`**: اسکوپ `whereSearch($keyword)` → جستجوی چندکلمهای: کلمات کلیدی را جدا کرده و روی `title` (نرمالشده بدون فاصله) با `LIKE` چک میکند.
- **`Seoable`**: رابطه `morphOne(SeoMeta)` + اکسسورهای `seoTitle`, `seoDescription`, `seoIndex` (از جدول `seo_metas`).
- **`UrlSetterTrait`**: ستتر `setUrlAttribute` که فاصلهها را به `-` تبدیل و کاراکترهای خاص را رد میکند (در ستتر نباید redirect برگردانده شود — باگ؛ در مقصد داخل FormRequest انجام دهید).

### ۳.۲ مدل `Product`

**fillable:** `title, description, url, brand_id, active, image, price, discounted_price, final_price, show_in_first_page, timer_active, end_timer, start_timer, stock, main_variant_specification_id, weight, price_formula, creator_id`

**روابط:**
- `brand()` → hasOne(Brand, 'id', 'brand_id')
- `categories()` → belongsToMany(ProductCategory, 'product_category_product', 'product_id') — دستهبندی چندگانه
- `category()` → همان با `select('id')` (فقط آیدی)
- `related()` / `complement()` → belongsToMany به خودش از طریق `product_associations` با `withPivot('type')` و `wherePivot('type','related'/'complement')` — **محصولات مرتبط و مکمل**
- `relatedIds()` / `complementIds()` → همانها با `select('products.id')`
- `main_specifications()` → belongsToMany(Specification, 'product_main_specifications', 'product_id', 'main_specification_id') — مشخصههای اصلی واریانت
- `specifications()` → belongsToMany(Specification, 'product_specification') **که `value` NULL است** — یعنی مشخصههای انتخابی (select) با ردیف `product_specification`
- `specification_values()` → همین + `whereHas('parent')->with('parent')`
- `specification_active_values()` → فقط والدهای فعال
- `specification_value()` → belongsToMany همان جدول **که `value` NOT NULL است** — مقادیر نوشتاری
- `specification_vals()` → hasMany(ProductSpecification) با `value` NOT NULL
- `specification_active_vals()` → نسخه فعال
- `mainVariantSpecification()` → hasOne(Specification, 'id', 'main_variant_specification_id')
- `variants()` → hasMany(ProductVariant)
- `comments()` → morphMany(Comment, 'commentable')->whereNull('reply_id')->whereStatus(1)
- `tags()` → morphToMany(Tag, 'taggable')
- `faqs()` → morphMany(Faq, 'faqable')
- `images()` → hasMany(Image)->orderBy('thumbnail','DESC')->orderBy('id','ASC')
- `properties()` → hasMany(Property)
- `videos()` → hasMany(Video)
- `creator()` → belongsTo(User, 'creator_id')

**اکسسورها و اسکوپها:**
- `getImage($size='medium')` → از `FileManager::serveFile('uploads/product/{size}/'.$image, fallback)`
- `getPercentAttribute` → درصد تخفیف: اگر `stock!=0` و `discounted_price!=0` → `round((price - final_price)/price * 100)` وگرنه null
- `getActiveNameAttribute`, `getStockProductAttribute`, `getTimerActiveNameAttribute`, `getTimerStatusNameAttribute`, `getFirstPageNameAttribute` → ساختار `['title'=>..., 'badge'=>...]` برای پنل
- `scopeActive` → `whereActive(1)`
- `assignCategory($cat)` → `categories()->sync($cat)`

### ۳.۳ مدل `Brand`
- fillable: `title, description, url, active, image, show_in_first_page`
- `products()` → hasMany(Product) — برای `whereHas('products')` در لیست برندها
- `discounts()` → belongsToMany(Discount, 'discount_product', 'product_category_id', 'discount_id') — **رابطه درست نیست (ستون اشتباه)**؛ عملاً بلااستفاده
- `getItemImageAttribute` → serveFile('uploads/brand/...')

### ۳.۴ مدل `ProductCategory`
- fillable: `title, description, url, active, show_in_site, parent_id, image, show_in_first_page, sort, have_price_range, min_price, max_price`
- `specifications()` → belongsToMany(Specification, 'category_specification')->whereNull('category_specification.deleted_at')
- `specificationConditions()` → belongsToMany(Specification, 'category_specification_conditions', 'category_id', 'specification_value_id') — شرطهای فیلتر اجباری
- `parent()` → hasOne(self, 'id', 'parent_id') — والد
- `children()` → hasMany(self, 'parent_id')->orderBy('sort')->where('show_in_site',1)->with(children) — **درخت نامحدود**
- `childrenInMenu()` → نسخه منو با `whereActive(1)`
- `products()` → belongsToMany(Product, 'product_category_product', 'product_category_id')
- `getAllParents()` → بازگشتی، آرایه والدها تا ریشه
- `getImage($size)` → حالت خاص gif
- `discounts()` → همان رابطه نامعتبر discount_product


### ۳.۵ مدل `Specification` (مشخصه)

- fillable: `title, type, active, parent_id, is_filter, is_color, color_code`
- `images()` → hasMany(Image, 'specification_id') — تصاویر مرتبط به مقدار مشخصه
- `categories()` → belongsToMany(ProductCategory, 'category_specification', 'specification_id', 'product_category_id') — **مشخصههای سراسری هیچ ردیف دستهای ندارند؛ مشخصههای دستهای فقط برای همان دستهها**
- `products()` → belongsToMany(Product, 'product_specification')
- `product_values()` → hasMany(ProductSpecification, 'specification_id')
- `parent()` / `children()` → درخت خودارجاع (مشخصه select: والد = عنوان، فرزندان = مقادیر)
- `variants()` → belongsToMany(ProductVariant, 'product_variant_specification', 'product_variant_id', 'specification_value_id')
- `productVariants()` → belongsToMany با جدول `product_variant_specifications` (جمع — **باگ جدول اشتباه**)
- اکسسورها: `getTypeNameAttribute` ('text'→نوشتاری، بقیه→انتخابی), `getColorNameAttribute`, `getFilterNameAttribute`

### ۳.۶ مدل `ProductSpecification` (جدول `product_specification`)

- fillable: `value, specification_id, product_id, parent_id`
- یک ردیف = **یک مشخصه انتخابی (value=NULL و parent_id=والد)** یا **یک مقدار نوشتاری (value=متن و بدون parent_id)**
- `specification()` → belongsTo(Specification)
- `parent()` → hasOne(self, 'id', 'parent_id') و `children()` → hasMany(self, 'parent_id')->with('children')

### ۳.۷ مدل `ProductVariant`

- fillable: `product_id, price, discounted_price, final_price, price_affective, stock, specification_parent_id, specification_id, weight, price_balancing`
- `$appends = ['percent']` — اکسسور `getPercentAttribute` (منطق آن **وارونه/باگ** است؛ ببینید بخش ۷)
- `specification()` → hasOne(Specification, 'id', 'specification_id') — مقدار single-legacy
- `specifications()` → belongsToMany(Specification, 'product_variant_specification', 'product_variant_id', 'specification_value_id')->with('parent') — **مقادیر انتخابی واریانت (چندتایی)**
- `images()` → belongsToMany(Image, 'image_product_variant', 'product_variant_id', 'image_id')
- `product()` → belongsTo(Product)

### ۳.۸ مدلهای ساده

- **`Image`**: fillable `image, thumbnail, product_id, active, specification_id, product_variant_id`؛ `variants()` → belongsToMany(ProductVariant, 'image_product_variant', 'image_id', 'product_variant_id')؛ `getImage($size)`؛ `products()` نامگذاری اشتباه دارد (هیچ استفادهای ندارد).
- **`Video`**: fillable `code, active, product_id` — کد امبد ویدیو.
- **`Property`**: fillable `value, product_id` — ویژگی متن ساده.
- **`ProductAssociation`**: fillable `product_id, related_product_id, type` — فقط Pivot استفاده میشود (از طریق روابط `related`/`complement`).

### ۳.۹ نقشه کامل روابط (خلاصه)

```
Product ──hasMany─────────────▶ ProductVariant ──belongsToMany──▶ Specification (product_variant_specification)
    │                                │
    │                                └─belongsToMany──▶ Image (image_product_variant)
    ├─belongsTo──▶ Brand
    ├─belongsToMany──▶ ProductCategory (product_category_product)
    ├─belongsToMany──▶ Specification (product_main_specifications)  ← مشخصههای اصلی
    ├─belongsToMany──▶ Specification (product_specification, value NULL) ← مشخصههای انتخابی
    ├─hasMany──▶ ProductSpecification (value NOT NULL) ← مقادیر نوشتاری
    ├─belongsToMany──▶ Product (product_associations, type=related/complement) ← مرتبط/مکمل
    ├─hasMany──▶ Image / Property / Video
    ├─morphMany──▶ Comment / Faq / Taggable
    └─belongsTo──▶ User (creator_id)

ProductCategory ──belongsToMany──▶ Specification (category_specification) ← مشخصههای دسته
    │                └─belongsToMany──▶ Specification (category_specification_conditions) ← شرط فیلتر اجباری
    └─parent/children (self, parent_id) ← درخت نامحدود

Specification ──parent/children (self) ← select: والد=گروه، فرزند=مقدار
```

> **ایده کلیدی:** مشخصههای انتخابی (select) به صورت «ردیف `product_specification` با value=NULL» به محصول وصل میشوند؛ مقدارها در جدول `specifications` به عنوان فرزند والد تعریف شدهاند. مشخصههای نوشتاری (text) مقدار را مستقیم در `product_specification.value` ذخیره میکنند.


## ۴) لاجیکها و کارکردهای کلیدی

### ۴.۱ مدل سهقیمته و درصد تخفیف

هر محصول/واریانت **سه فیلد قیمت** دارد:
- `price` = قیمت اصلی
- `discounted_price` = قیمت بعد از تخفیف (صفر = بدون تخفیف)
- `final_price` = **قیمت فروش نهایی** = اگر `discounted_price != 0` باشد همان است، وگرنه `price`

```php
$final_price = intval($discounted_price) != 0 ? intval($discounted_price) : intval($price);
```

درصد تخفیف (در Resources و اکسسور Product):
```php
if ($originalPrice <= 0) return 0;
return round((($originalPrice - $final_price) / $originalPrice) * 100);
```
همه لیستها فقط از `final_price` برای نمایش قیمت و فیلتر/مرتبسازی استفاده میکنند (و `stock` برای موجودی).

### ۴.۲ محصول بدون واریانت
قیمت و موجودی مستقیماً روی `products` نوشته میشود. تصویر اصلی محصول = اولین عکس گالری (مرتب با `thumbnail DESC, id ASC`) و در `ImageService::create` به صورت خودکار ست میشود.

### ۴.۳ محصول واریانتدار — **مهمترین لاجیک**

وقتی محصول واریانت دارد، فیلدهای قیمت و موجودی **محصول، مقدار تجمیعی (aggregate) است**:

```php
$sum_stock = $product->variants()->orderBy('final_price','ASC')->sum('stock');
$minimum_price_variant = $product->variants()
    ->orderByRaw('CAST(final_price AS UNSIGNED) ASC')
    ->where('stock', '<>', '0')->where('final_price', '<>', '0')
    ->first();
$product->update([
    'price'          => $minimum_price_variant ? $minimum_price_variant['price'] : 0,
    'discounted_price'=> $minimum_price_variant ? $minimum_price_variant['discounted_price'] : 0,
    'final_price'    => $minimum_price_variant ? $minimum_price_variant['final_price'] : 0,
    'stock'          => $sum_stock,
]);
```

**یعنی:** موجودی محصول = جمع موجودی همه واریانتها، و قیمت محصول = قیمت ارزانترین واریانتِ موجود. این کد در `VariantService::create`، `ProductService::update` (هنگام price_formula) و `Imports/ProductsImport` تکرار شده است (پیشنهاد میشود در پروژه مقصد به یک متد واحد تبدیل شود).

### ۴.۴ ساخت و ویرایش واریانتها (`VariantService::create`)

1. محصول را پیدا کن.
2. `main_specifications()->sync(مشخصههای اصلی انتخابشده)` — مشخص میکند کدام مشخصهها گروه واریانت هستند.
3. برای هر ردیف واریانت از فرم:
   - اگر محصول `price_formula` داشته باشد → قیمت از فرمول (با وزن واریانت) گرفته میشود؛ `discounted=0`, `final=price`.
   - وگرنه → `price`/`discounted_price` را با `NumberHelper::persian2LatinDigit` (تبدیل اعداد فارسی به انگلیسی) نرمالسازی و `final_price` را محاسبه کن.
   - اگر `variant_id` خالی → `ProductVariant::create`، وگرنه آپدیت.
   - `newVariant->specifications()->sync(آرایه specification_id ها)` — مقادیر انتخابی واریانت در `product_variant_specification`.
4. **تجمیع** قیمت/موجودی محصول (بند ۴.۳).

اعتبارسنجی در `VariantRequest` + `VariantDTO::fromRequest`: هر واریانت باید `specifications` (آرایه) داشته باشد؛ اگر `discounted_price >= price` ردیف نادیده گرفته میشود و پیام هشدار نمایش داده میشود.

### ۴.۵ قیمتگذاری داینامیک (`price_formula` / `TgjuSilver`)

برخی محصولات قیمتشان بر اساس **وزن × قیمت روز نقره** محاسبه میشود:
```php
// Library/TgjuSilver.php — fetch از API tgju.org
$current_price = (str_replace(',','',$silver['current']['silver_999']['p']) / 100) * (92.5 * 2);
return round((intval($current_price) * intval($this->weight)) / 10);
```
- در `ProductService::create/update` وقتی `price_formula` انتخاب شده، قیمت با وزن محصول محاسبه و نوشته میشود. (در create باگ `updtae` وجود دارد — بند ۷)
- در `VariantService::create` هر واریانت با **وزن خودش** قیمت میگیرد و بعد تجمیع انجام میشود.
- در `ProductsImport` محصولات دارای فرمول **دستکاری نمیشوند**.

### ۴.۶ تایمر تخفیف (بازهای)

- فیلدهای `timer_active`, `start_timer`, `end_timer`.
- اعتبارسنجی: `start_timer` نباید بعد از `end_timer` باشد (در `TimerDTO`/سرویس).
- کوئری محصولات تایمردار (`ProductService::findAll` با `$query['timer']`):
```php
$products->where(fn($q) => $q->whereNotNull('discounted_price')->orWhere('discounted_price','<>',0))
    ->where('timer_active','1')
    ->where('start_timer','<', now_tehran)
    ->where('end_timer','>', now_tehran);
```
- در API، `start_timer`/`end_timer` با `jdate` به فرمت شمسی تبدیل میشوند.

### ۴.۷ مشخصات/ویژگیها/تگها (`SpfService::create`) — تب properties-spf-tag

این سرویس چهار چیز را همزمان ذخیره میکند:
1. **تگها:** حذف تمام `Taggable` های قبلی محصول و بازسازی.
2. **Properties:** upsert (با `property_id` موجود آپدیت، بدون آن ایجاد).
3. **مقادیر نوشتاری مشخصات (text):** upsert روی `ProductSpecification` با `value`.
4. **مشخصههای انتخابی (select):** حذف ردیفهای `value=NULL` قبلی، سپس `firstOrCreate` برای هر `specification_id` با `parent_id`.

مشخصههای قابل نمایش برای یک محصول = **مشخصههای سراسری** (بدون ردیف در `category_specification`) + **مشخصههای دستههای خود محصول**.

### ۴.۸ فیلتر محصولات یک دسته با شرطهای مشخصه (`ProductService::filterByCategoriesAndSpecs`)

این متد ستون فقرات «لیست محصولات دسته» است:
1. مشخصههای شرطی دسته (`specificationConditions`) را بر اساس `parent_id` گروهبندی کن.
2. اگر دسته شرط دارد → محصول باید **از هر گروه حداقل یک مقدار** داشته باشد (`whereHas('specifications', ...)` برای هر گروه).
3. محصولاتی که در **دستههای برگ (فرزندهای نهایی)** هستند هم نشان داده میشوند (`orWhereHas categories`).
4. اگر `have_price_range==1` → `whereBetween('final_price', [min,max])`.

### ۴.۹ لیست/فیلتر/مرتبسازی سایت (`getProductListVue`)

ورودیهای POST به `api/v1/product-cat-vue` و `api/v1/all-product-vue`:
- `category_id` → اعمال `filterByCategoriesAndSpecs` (یا فقط whereHas دسته در نسخه all)
- `brand_id[]` → `whereIn('brand_id', ...)`
- `filter_id[]` → مشخصههای انتخابی: گروهبندی بر اساس `parent_id` و `whereHas` برای هر گروه (تقاطع = AND بین گروهها، OR داخل هر گروه)
- `product_title` → `LIKE %...%`
- `discounted_price==1` → `CAST(discounted_price AS SIGNED) != 0` و `stock>0`
- `stock==1` → `stock>0`
- `price_range` → `whereBetween(DB::raw('final_price * 1'), [min,max])` و `final_price>0`
- `sort_by`: `expensive` (final_price DESC)، `cheapest` (final_price ASC)، `newest` (id DESC) — همه با شرط موجودی/قیمت
- **پیشفرض:** `ORDER BY stock>0 DESC, final_price>0 DESC, id DESC` — محصولات موجود و قیمتدار اول
- صفحهبندی: ۱۲ عدد

### ۴.۱۰ فیلتر لیست پنل مدیریت (`Filters/ProductFilter`)

`title` (با whereSearch)، `url`، `brand_id`، `creator_id`، `category_id` (whereHas)، `stock_status` (موجود/ناموجود)، `show_in_first_page`.

### ۴.۱۱ تصاویر (`ImageService::create`)

- آپلود با `FileUploader` و ساخت سه سایز: `big=1000`, `medium=500`, `small=75` در `uploads/product/{size}/`.
- بعد از آپلود، تصویر اول گالری به عنوان `products.image` ست میشود.
- اگر فرم `variants` داشته باشد → `image->variants()->sync(واریانتها)` و تصاویری که دیگر به واریانتی متصل نیستند از اتصال خارج میشوند.
- `setImageThumbnail`: روی یک تصویر `thumbnail=1` و بقیه ۰.
- در API محصول، `original_images` = تصاویری که به هیچ واریانتی متصل نیستند.

### ۴.۱۲ اکسل خروجی/ورودی (`ProductsExport` / `ProductsImport`)

- **Export:** سطر هر واریانت جدا (شناسه محصول، عنوان، شناسه/عنوان واریانت، موجودی، قیمت، قیمت تخفیف). سقف ۴۰۰۰ ردیف با پیام خطا. هدرها فارسی.
- **Import:** با `شناسه محصول` پیدا میکند؛ اگر `price_formula` نداشته باشد قیمتها را نرمالسازی و `final_price` را بازمحاسبه میکند؛ اگر واریانت دارد با `شناسه متغییر` واریانتها را آپدیت و سپس تجمیع میکند.


## ۵) رابط API (سایت و پنل)

### ۵.۱ اندپوینتهای سایت (بدون احراز هویت)

| متد/مسیر | کارکرد | خروجی کلیدی |
|---|---|---|
| GET `/api/v1/categories` | لیست دستههای ریشه | `product_categories` (درخت) |
| GET `/api/v1/category/{url}` | صفحه دسته (سئو) | دسته + زیردستهها + ۱۲ محصول + برندها + فیلترها + min/max قیمت |
| POST `/api/v1/product-cat-vue/` | لیست/فیلتر/مرتبسازی محصولات دسته (Vue) | `products` (ListProductCollection) + صفحهبندی |
| GET `/api/v1/brands` | لیست برندها | `brands` |
| GET `/api/v1/brand/{url}` | صفحه برند | برند + محصولات + دستهها + فیلترها + محدوده قیمت |
| POST `/api/v1/product-brand-vue/` | لیست محصولات برند با فیلتر | همان ساختار |
| GET `/api/v1/product/{url}` | **صفحه جزئیات محصول** | `product, categories, comments, brand, slogans, specification_values, specifications, properties, faqs, videos, images, related, rate, tags` |
| GET `/api/v1/discounted-products` | محصولات تایمر/تخفیفدار (صفحه تخفیفها) | `products` + `timer_products` |
| POST `/api/v1/all-product-vue/` | لیست همه محصولات با فیلتر | همان ساختار |

### ۵.۲ خروجی جزئیات محصول (`getProductDetail`)

- `rate` = میانگین `rate` کامنتهای تأییدشده محصول
- `specifications` = مشخصههای انتخابی گروهبندیشده بر اساس `parent_id`
- `specification_values` = مقادیر نوشتاری گروهبندیشده بر اساس `specification_id`
- `images` = سه سایز `image_big / image_medium / image_small` برای هر تصویر
- `related` = اگر محصول «مرتبط» تعریف نشده باشد، **۷ محصول از همان دستهها** به صورت خودکار
- `end_timer` در لیستها برای نمایش تایمر روی کارت

### ۵.۳ اندپوینتهای پنل (با `auth:admin_jwt` + `api-permission`)

- **دسته:** GET/GET show/POST add/PUT edit → `admin/product-category/...`
- **محصول:** GET (لیست) / GET show / POST add / PUT edit / POST `timer/{id}` / PUT `specification/select/{id}` / PUT `specification/text/{id}`
  - `specification/select`: آرایهای از specification_id ها میگیرد، با `getAllowedSpecifications` فیلتر میکند (والد باید با دستههای محصول مشترک باشد یا سراسری)، ردیفهای `product_specification` (value=NULL) را میسازد و ردیفهای حذفشده را پاک میکند
  - `specification/text`: آرایه `[specification_id => value(s)]` میگیرد و ردیفهای value دار را upsert میکند
- **تصویر:** POST `product/image/create/{id}` / POST `add-variants/{id}` / POST `set-thumbnail/{id}`
- **مشخصه:** GET `specification/` (با فیلتر product_id برای مشخصههای مجاز دستهها) / GET `specification/{id}/values`

### ۵.۴ قالب Resource ها (شکل خروجی)

- **کارت محصول (ProductCollection/ListProductCollection):** `id, title, url, vue_url, image, stock, final_price (number_format), price (فقط اگر تخفیف دارد), percent, end_timer`
- **جزئیات (ProductDetailCollection):** + `description, title_seo, description_seo, noindex`
- **دسته (ProductCategoryCollection):** + `product_counts`, `image` با سایز قابل تنظیم
- **برند (BrandCollection):** + `item_image` (اکسسور)
- **مشخصه (SpecificationProductCollection):** `id, title, parent, parent_id`
- **مقدار نوشتاری (SpecificationValueCollection):** `id, value, specification, specification_id`
- همه کلکشنها متد `setHiddenFields()` دارند تا فیلدها را در API حذف کنند.

## ۶) اعتبارسنجیهای مهم

- **ProductRequest (پنل):** `title` و `url` الزامی؛ `UrlRule` یکتایی url (با درنظرگرفتن soft delete)؛ `PriceRule` → قیمت تخفیف نباید بیشتر از قیمت اصلی باشد.
- **API ProductRequest:** کاملترین نسخه — `url` یکتا + regex فارسی (`[\p{Arabic}a-zA-Z0-9\-\/ ]`)، وجود `brand_id/categories/tags/related/complement` در دیتابیس، `seo.title_seo/description_seo` الزامی در صورت ارسال seo، `end_timer >= start_timer`، اعداد نامنفی.
- **ProductCategoryRequest:** `ParentIdRule` (نمیتواند والد خودش باشد) + جلوگیری از سیکل بازگشتی (چک زیرمجموعهها) + `PriceRangeRule` (min < max) + `min/max_price` الزامی در صورت `have_price_range=1`.
- **ProductCategoryRule:** حداکثر ۳ سطح دستهبندی.
- **VariantRequest:** `main_variant_specification_id` الزامی + هر واریانت باید آرایه `specifications` داشته باشد.
- تبدیل اعداد فارسی/عربی به انگلیسی با `NumberHelper::persian2LatinDigit` قبل از ذخیره قیمت/موجودی/وزن.


## ۷) باگها و نکات ظریف (در کپی به اینها دقت کنید)

1. **`ProductVariant::$appends=['percent']`** — متد `getPercentAttribute` در واریانت منطق **وارونه** دارد (وقتی تخفیف دارد null برمیگرداند) و به خاطر `$appends` در همه خروجیهای JSON واریانت وجود دارد. در مقصد، متد `Product::getPercentAttribute` را کپی کنید.
2. **تیپو `$product->updtae([[...]])`** در `ProductService::create` — قیمتگذاری داینامیک هنگام ساخت محصول عملاً اجرا نمیشود (اکسپشن بلعیده میشود). در مقصد این را با `update([...])` درست بنویسید.
3. **`Specification::productVariants()`** از جدول `product_variant_specifications` (جمع) استفاده میکند در حالی که جدول واقعی `product_variant_specification` است — این متد خراب است؛ از `variants()` استفاده کنید.
4. **`Image::products()` / `Video::products()` / `Property::products()`** نامگذاری اشتباه (hasMany به جای belongsTo) و بدون استفاده.
5. **`UrlSetterTrait`** داخل ستتر `redirect()->back()` برمیگرداند که عملاً کار نمیکند؛ منطق اسلاگ را در Request انجام دهید.
6. **`Product::specifications()`** با `whereNull('product_specification.value')` مشخصههای انتخابی را برمیگرداند؛ هنگام استفاده از `with('specifications')` حواستان به شروط جدول محوری باشد.
7. **جدول `prices`** قدیمی/بلااستفاده است — کپی نکنید.
8. **دو مکانیزم همزمان برای مشخصه اصلی واریانت:** ستون `main_variant_specification_id` روی محصول (قدیمی) و جدول `product_main_specifications` (جدید). در کد جدید از Pivot استفاده میشود.
9. مرتبسازیها با `orderByRaw('final_price * 1 ...')` انجام میشوند چون ستون DOUBLE است و MySQL میخواهد عددی مقایسه کند.
10. فیلتر `discounted_price` با `CAST(discounted_price AS SIGNED) != 0` انجام میشود چون ممکن است رشته ذخیره شده باشد.
11. `ProductsExport` با حجم بالا محدودیت ۴۰۰۰ دارد (TODO در کد). `ProductsImport` فقط بهروزرسانی میکند (ساخت نمیکند).
12. نام جداول pivot در BelongsToMany باید دقیقاً با مایگریشنها یکسان باشد (`product_variant_specification` مفرد، `product_main_specifications` جمع!).

## ۸) راهنمای انتقال به پروژه غیرماژولار (گامبهگام)

### گام ۱ — مایگریشنها
همه جدولها را در یک مایگریشن (یا چند مایگریشن منطقی) بسازید؛ از اسکیمای بخش ۲ استفاده کنید. جدولهای پیشنهادی به ترتیب:
`brands → product_categories → products → specifications → category_specification → category_specification_conditions → images → videos → properties → product_variants → product_specification → product_variant_specification → product_main_specifications → product_associations → image_product_variant → product_category_product`

### گام ۲ — مدلها (App\Models)
- ۱۰ مدل بخش ۳ را با همان روابط و اکسسورها کپی کنید؛ namespace ها را تغییر دهید.
- ترِیتهای `Searchable`, `GlobalScopesTrait`, `Seoable`, `UrlSetterTrait` را یا به صورت Traits در `app/Traits` بسازید یا متدها را مستقیم در مدلها بیاورید.
- `Seoable` به جدول `seo_metas` پلیمورفیک وابسته است — اگر سئو میخواهید همان را پیاده کنید.

### گام ۳ — سرویسها (App\Services)
- `ProductService` (create/update/timer/destroy/findAll/findOne/prices/getSpfs/getVariants/filterByCategoriesAndSpecs)
- `VariantService` (create/delete + تجمیع قیمت)
- `SpfService` (properties/tags/specifications)
- `SpecificationService` (CRUD + findAll + قالببندی مقادیر نوشتاری)
- `BrandService`, `ProductCategoryService`, `ImageService`, `VideoFaqService`
- `Api/ProductService` (برای API پنل: store/update/syncRelations/specificationSelect/specificationText/postVariants)

### گام ۴ — DTO ها
الگوی `DTO::fromRequest($request)` را نگه دارید؛ تبدیل اعداد فارسی و محاسبه `final_price` در همانجا انجام میشود (این الگو کد کنترلر را تمیز نگه میدارد).

### گام ۵ — کنترلرها و روتها
- روتهای پنل (بخش ۵.۳ + web.php ماژول) و روتهای سایت (بخش ۵.۱) را معادلسازی کنید.
- صفحه جزئیات وب را مثل `Shop/ProductController::detail` بسازید: `getSpfs` (گروهبندی واریانتها بر اساس مشخصه اصلی) + `getVariants` (واریانتهای مرتبشده با specification_ids) + تبها (توضیحات/مشخصات/ویدیو/سوالات) + `min_variant`.
- اسلایدر واریانت در Vue با داده `main_specifications[].children[].variants[].specification_ids` کار میکند — اگر قالب جدیدتان Vue نیست، همان ساختار را به جاوااسکریپت بدهید.

### گام ۶ — اعتبارسنجیها و قواعد
بخش ۶ را کپی کنید (UrlRule, PriceRule, ParentIdRule, PriceRangeRule, ProductCategoryRule).

### گام ۷ — جریانهای جانبی
- فیلتر پنل (`Filters/*`)
- اکسل (Export/Import)
- صفحه اول: `ProductService::findAll(['first_page'=>true])` و `['timer'=>true]` برای «پیشنهاد ویژه»
- سئو: `Seoable` + sitemap محصولات

## ۹) چکلیست نهایی

- [ ] اسکیمای دیتابیس (بخش ۲) با ایندکسهای `final_price`, `stock`, `parent_id` و کلیدهای pivot
- [ ] ۱۰ مدل با روابط کامل (بخش ۳)
- [ ] منطق سهقیمته + درصد تخفیف (بخش ۴.۱)
- [ ] تجمیع قیمت/موجودی واریانتها (بخش ۴.۳)
- [ ] ساخت/ویرایش واریانت با مشخصههای چندتایی (بخش ۴.۴)
- [ ] قیمتگذاری داینامیک وزنمحور (بخش ۴.۵) — اختیاری
- [ ] تایمر تخفیف (بخش ۴.۶)
- [ ] مشخصات انتخابی/نوشتاری + تگ + ویژگی (بخش ۴.۷)
- [ ] شرطهای فیلتر اجباری دسته + بازه قیمت دسته (بخش ۴.۸)
- [ ] فیلتر/مرتبسازی سایت (بخش ۴.۹)
- [ ] تصاویر سهسایز + تامبنیل + تصاویر واریانت (بخش ۴.۱۱)
- [ ] اکسل ورودی/خروجی (بخش ۴.۱۲)
- [ ] API سایت + پنل (بخش ۵)
- [ ] اعتبارسنجیها (بخش ۶)
- [ ] رفع/آگاهبودن از باگ‌های بخش ۷

---

## ۱۰) صفحه جزئیات محصول (فرانت‌اند سایت)

> این بخش از بررسی کامل `resources/views/pages/product-detail/` (فایل index + ۳۰ فایل partial)، `app/Http/Controllers/Shop/ProductController.php` و فایل‌های JS/CSS صفحه استخراج شده است.

### ۱۰.۱ ساختار فایل‌ها

```
resources/views/pages/product-detail/
├── index.blade.php                     → قالب اصلی صفحه (extends layouts.main.master)
└── _partials/
    ├── breadcrumb.blade.php             → مسیر خانه / همه محصولات / عنوان
    ├── product-image.blade.php          → گالری تصاویر (دسکتاپ: مودال / موبایل: کاروسل)
    ├── info-product.blade.php           → ستون اطلاعات (H1، امتیاز، انتخابگر، قیمت، دکمه خرید)
    ├── rate.blade.php                   → امتیاز ستاره + تعداد نظرات + برند
    ├── components/variant-selector.blade.php → حلقه روی مشخصه‌های اصلی (رنگ/سایز)
    ├── components/colors.blade.php      → انتخابگر پالت رنگی (دکمه با color_code)
    ├── components/text.blade.php        → انتخابگر متنی (دکمه‌های سایز/مقدار)
    ├── components/price.blade.php       → نمایش قیمت (قدیم + جدید + درصد + موجودی)
    ├── components/cart-btn.blade.php    → دکمه «افزودن به سبد خرید»
    ├── components/counter.blade.php     → کنترل تعداد (منفی/مثبت)
    ├── add-to-cart-desktop.blade.php    → بلوک خرید دسکتاپ (قیمت + دکمه + شمارنده)
    ├── add-to-cart-mobile.blade.php     → بلوک خرید موبایل + Offcanvas انتخاب واریانت
    ├── attributes.blade.php             → لیست ویژگی‌ها (با «مشاهده بیشتر»)
    ├── selected-specification.blade.php → (استاتیک/نمونه) — عملاً استفاده نشده
    ├── slogan.blade.php / slogan-two.blade.php → شعارهای کنار صفحه اول
    ├── tabs.blade.php + tabs/*.blade.php → تب‌های توضیحات/مشخصات/ویدیو/سوالات/نظرات
    ├── tags.blade.php                   → برچسب‌ها
    ├── related.blade.php / complement.blade.php → اسلایدر محصولات مرتبط و مکمل (Swiper)
    ├── schema.blade.php                 → JSON-LD (Product/Offer/Breadcrumb/FAQPage)
    ├── vue.blade.php                    → تمام لاجیک Vue 2 صفحه (~۵۰۰ خط)
    ├── scripts.blade.php / styles.blade.php → اسکریپت‌ها و استایل‌های اختصاصی
```

### ۱۰.۲ ورودی‌های کنترلر (`Shop/ProductController::detail`)

کنترلر با `ProductService::findOne($url)` محصول را می‌گیرد و به قالب می‌دهد:
`product, brand, categories, comments, slogans, specifications (گروه‌بندی‌شده بر parent_id), specification_values, properties, rate, faqs, videos, images (سه‌سایز), related_products, complement_products, tags, activeTab, tabs, main_specifications (getSpfs), min_variant, all_specification_children_ids, variants (getVariants), original_images`

- **`tabs`** یک آرایه boolean است که تب‌ها را شرطی می‌کند: `home-tab` (آیا توضیحات دارد)، `profile-tab` (آیا مشخصات/تگ دارد)، `video-tab`، `faq-tab`، و تب «نظرات» همیشه فعال است. اولین تب فعال به صورت خودکار `show active` می‌شود.
- **`main_specifications`** ساختار درختی `[main_id, main_title, main_is_color, children[title, color_code, variants[]]]` است — قلب انتخابگر واریانت.
- **`min_variant`** = ارزان‌ترین واریانت موجود؛ در `created()` Vue به صورت خودکار انتخاب و قیمت صفحه روی آن تنظیم می‌شود.

### ۱۰.۳ ساختار چیدمان (Layout)

```
product-info → row
  ├── col-lg-5 col-md-6        → product-image (گالری)
  ├── col-xl-7 col-md-6 (یا col-xl-4) → info-product (H1، امتیاز، واریانت، قیمت، خرید)
  └── col-lg-3 (v-if=filteredMainSpecs.length>0) → attributes (ویژگی‌ها)
slogan (اگر properties یا variants دارد)
tabs
related (Swiper)
complement (Swiper)
```

- عرض ستون اطلاعات به صورت **داینامیک** است: اگر `filteredMainSpecs.length > 0` (محصول واریانت‌دار) ستون attributes باز می‌شود و اطلاعات `col-xl-4` می‌شود؛ وگرنه `col-xl-7`.
- در `index.blade.php` اگر `$product->mainVariant` و واریانت‌ها موجود باشند، یک **جدول مخفی** (`d-none`) از واریانت‌ها رندر می‌شود (برای خزنده‌ها/سئو).

### ۱۰.۴ ریسپانسیو (موبایل/دسکتاپ)

- **دایرکتیو سرویس‌ساید `@mobile`** (در `AppServiceProvider` با `Blade::if('mobile', …)` و regex روی `HTTP_USER_AGENT`) بین دو نسخه جدا می‌کند:
  - **دسکتاپ:** breadcrumb بالای اطلاعات، بلوک `add-to-cart-desktop` (قیمت + دکمه + شمارنده در یک ردیف)، گالری با **مودال fullscreen** (Bootstrap Carousel `#proImgIndicatorsDesk`).
  - **موبایل:** breadcrumb بالای گالری، بلوک `add-to-cart-mobile` که شامل **Offcanvas پایین صفحه** (`#offcanvasVariant` — «انتخاب کن بعد به سبد اضافه کن») برای انتخاب واریانت، و نوار چسبان خرید (`mobile-btn-cart`) با قیمت + شمارنده + دکمه.
  - گالری موبایل = Bootstrap Carousel ساده (`#proImgIndicatorsMob`) که بعد از رندر Vue با `mobileCarouselReady` سوار می‌شود.
- قالب‌های CSS: `public/assets/site/css/product/detail.css` + `magiczoomplus.css` (Zoom روی عکس دسکتاپ).


### ۱۰.۵ لاجیک Vue (`vue.blade.php`)

یک نمونه **Vue 2** روی `#app` سوار است. داده‌های اولیه از بک‌اند با `@json()` به `data` تزریق می‌شوند: `variants`, `images`, `imagesOriginal`, `mainSpecs`, `min_variant`, `all_specification_children_ids`.

**متدهای کلیدی:**

| متد | کارکرد |
|---|---|
| `autoSelectSingleSpecs()` | اگر مشخصه اصلی فقط یک فرزند داشته باشد، خودکار آن را انتخاب می‌کند |
| `calculateAvailableVariants()` | واریانت‌های سازگار با انتخاب‌های فعلی را فیلتر می‌کند (همه مشخصه‌های انتخابی باید در `specification_ids` واریانت باشند) |
| `selectSpecs(childSpec)` | انتخاب/لغو انتخاب یک مقدار؛ دوباره کلیک = حذف انتخاب |
| `runAutoSelectCycle()` | چرخه انتخاب خودکار تا ۵ تکرار (برای انتخاب‌های وابسته به هم) |
| `isSpecAvailable(childSpec)` | بررسی سازگاری یک مقدار با انتخاب‌های فعلی (برای غیرفعال‌کردن گزینه‌های ناموجود) |
| `selectVariant(variant)` | وقتی واریانت یکتا انتخاب شد، قیمت/درصد/موجودی به‌روز می‌شود |
| `handleImage(variant)` | گالری را با تصاویر واریانت + تصاویر عمومی (original) ترکیب می‌کند |
| `addToBasket()` | POST به `basket.add` با `product_id`, `product_variant_id`, `quantity`؛ مدیریت پاسخ با SweetAlert2 |
| `resetAllSelections()` | پاک‌کردن همه انتخاب‌ها و بازگشت به حالت اولیه |
| `scrollToVariants()` | در موبایل اگر دکمه خرید قبل از انتخاب واریانت زده شود، به گالری اسکرول + پیام |
| `updatePrice(newVariant)` / `updateSwipers()` | به‌روزرسانی قیمت و ری‌اینییت کاروسل |

**Computed ها:** `filteredMainSpecs` (محدودکردن مقادیر قابل انتخاب به ترکیب‌پذیرها)، `displayPrice`/`displayFinalPrice` (با `toLocaleString`)، `discountPercent` (در فرانت از `price`/`final_price`)، `isAvailable` (موجودی + منطق «تماس بگیرید» وقتی stock>0 ولی قیمت 0).

**Watch ها:** `selectedVariant` → تغییر گالری و قیمت؛ `selectedOption` (نسخه قدیمی).

> **نکته انتقال:** اگر قالب جدیدتان Vue ندارد، منطق `calculateAvailableVariants`/`filteredMainSpecs` را با جاوااسکریپت خالص روی همین ساختار داده (`mainSpecs[].children[].variants[].specification_ids`) پیاده کنید.

### ۱۰.۶ انتخابگر واریانت (رنگ/سایز)

- `variant-selector` روی `filteredMainSpecs` حلقه می‌زند؛ برای هر مشخصه اصلی اگر `main_is_color == '1'` قالب `colors` وگرنه قالب `text` رندر می‌شود.
- **رنگ (`colors.blade.php`):** دکمه‌های دایره‌ای با `background: child.color_code`؛ رنگ‌های سفید (`#fff`, `#ffffff`, `#feffff`, `rgb(255,255,255)`) با کلاس `bg-fff` حاشیه می‌گیرند؛ انتخاب فعلی با `bi-check-lg` سفید تیک می‌خورد؛ عنوان انتخابشده (`رنگ X`) با رنگ خود `color_code` نمایش داده می‌شود؛ `data-bs-toggle=tooltip` برای نمایش نام.
- **متن (`text.blade.php`):** دکمه‌های مستطیلی (`link-size`) برای مقادیر (مثل سایز).
- در هر دو: کلاس `disabled` + `loadingSpec` هنگام تغییر انتخاب (overlay اسپینر در CSS).
- وقتی همه مشخصه‌ها انتخاب شدند و دقیقاً یک واریانت سازگار وجود دارد → `selectedVariant` ست می‌شود و فقط بعد از آن دکمه خرید «افزودن» فعال می‌شود.
- دکمه **«پاک کردن انتخابها»** (فقط وقتی چیزی انتخاب شده و تعداد مشخصه‌ها > ۱) → `resetAllSelections()`.

### ۱۰.۷ بلوک قیمت، موجودی و خرید

- **سه حالت قیمت** (در هر دو نسخه موبایل/دسکتاپ):
  1. قیمت‌دار و موجود (`final_price != 0` و `stock != 0`) → قیمت + دکمه خرید + شمارنده
  2. قیمت صفر ولی موجود (`final_price == 0`) → دکمه **«تماس بگیرید»** با شماره تلفن از `$settings['main_phone_number']`
  3. ناموجود (`stock == 0`) → پیام «ناموجود» (بج دِنجر)
- قیمت قبلی با `<del>` و درصد تخفیف به صورت بج (`badge-discount-percent`) فقط وقتی `discountPercent != 0`.
- **موجودی:** آیکون سبز `available.svg` «موجود در انبار» / زرد «تماس بگیرید» / قرمز `unavailable.svg` «ناموجود» — وضعیت از computed `isAvailable` می‌آید.
- **شمارنده (`counter.blade.php`):** دکمه‌های `-`/`+` روی input خواندنی `#number`؛ مقدار در `quantity` نگه داشته و همراه سبد ارسال می‌شود.
- **افزودن به سبد:** `axios.post(route('basket.add'))` → اگر موفق: SweetAlert با دکمه‌های «تکمیل سفارش و پرداخت» / «ادامه خرید» و به‌روزرسانی آیکون سبد (`menu_nav.getBasketItemCount()`). اگر ناموفق: پیام toast یا modal طبق `response.data.swal`.

### ۱۰.۸ گالری تصاویر

- **دسکتاپ:** یک تصویر اصلی (کلیک → مودال fullscreen با Bootstrap Carousel `#proImgIndicatorsDesk`) + تا ۴ تصویر کوچک (هر کدام مودال را روی همان اسلاید باز می‌کند با `setActiveItem(key)`) + اگر بیش از ۵ تصویر باشد دکمه **«X+ مشاهده»**. در مودال: اسلایدر + فلش‌ها + indicator های کوچک پایین. Zoom با `magiczoomplus.js`.
- **موبایل:** کاروسل Bootstrap ساده (`#proImgIndicatorsMob`) با indicator ها و فلش‌ها؛ فقط وقتی `mobileCarouselReady=true` ساخته می‌شود.
- **تصاویر وابسته به واریانت:** با `handleImage(variant)` تصاویر واریانت اول + بقیه تصاویر عمومی (که به واریانت دیگری وابسته نیستند) نمایش داده می‌شوند و `main_image` عوض می‌شود.

### ۱۰.۹ تب‌ها و بخش‌های پایین صفحه

- **تب توضیحات:** HTML خام `description` (خروجی به صورت `{!! !!}`)؛ جدول‌های داخل آن با اسکریپت به `table-responsive` تبدیل می‌شوند؛ حالت خالی با تصویر empty-state.
- **تب مشخصات:** جدول دوستونه — سطر مشخصه‌های انتخابی (والد به عنوان عنوان، مقادیر با کاما) + سطر مقادیر نوشتاری (مشخصه/مقدار) + سطر تگ‌ها.
- **تب ویدیو:** هر ویدیو با `code` امبد (آپارات/یوتیوب) رندر می‌شود (`{!! $video['code'] !!}`).
- **تب سوالات متداول:** آکاردئون Bootstrap با question/answer.
- **تب نظرات:** `layouts.common.comment._partials.comment-base` — فرم نظر + لیست کامنت‌ها + ریپلای (مودال) — با `commentable_id` و `commentable_type` محصول.
- **دکمه واتساپ:** زیر تب‌ها اگر `settings.show_whatsapp_button == 1` — لینک `wa.me/98...` با پیام از پیش‌تنظیم و لینک محصول.
- **اسلایدرهای مرتبط/مکمل:** `related.blade.php` و `complement.blade.php` با **Swiper** (`swiper-team`) و داخل هر اسلاید کامپوننت `layouts.common.product.product-box` (کارت محصول با درصد تخفیف SVG، تصویر medium، قیمت/قدیم/ناموجود/تماس بگیرید).

### ۱۰.۱۰ سئو و اسکیما

- **Meta tags** (از `index.blade.php`): `robots` (index/noindex طبق `seoIndex`)، `title_seo`، `description_seo`، `image_seo` + تگ‌های اختصاصی `product_id`, `product_name`, `product_price`, `product_old_price`, `availability`.
- **JSON-LD (schema.blade.php):**
  1. `Product` + `Offer` (priceCurrency=IRR، price=final_price، availability=InStock، url) + `aggregateRating` (استاتیک!) + آرایه `review` از کامنت‌ها (datePublished به صورت تایم‌استمپ) + `brand`
  2. `BreadcrumbList` (خانه / همه محصولات / عنوان)
  3. `FAQPage` (در صورت وجود سوالات) — با `json_encode` و `JSON_UNESCAPED_UNICODE`
- **محدودیت‌های سئو:** `aggregateRating` مقدار ثابت ۵.۰/۶ است (باید از داده واقعی محاسبه شود)، `availability` همیشه InStock است (باید از `stock` بیاید) — هنگام کپی این‌ها را اصلاح کنید.


### ۱۰.۱۱ قابلیت‌هایی که **وجود ندارند** (در صورت نیاز خودتان پیاده کنید)

در کل کدبیس جستجو شد: **«موجود شد خبرم کن»، «حراج شد خبرم کن» و هر نوع Notify/اعلان محصول در این صفحه پیاده‌سازی نشده است** (جستجوی `خبرم کن`/`موجود شد`/`حراج` صفر نتیجه). قابلیت‌های موجود فعلاً فقط این سه حالت هستند:

1. **ناموجود** (`stock == 0`) → نمایش پیام «ناموجود» (بدون فرم اطلاع‌رسانی)
2. **تماس بگیرید** (`stock > 0` ولی `final_price == 0`)
3. **موجود در انبار** (قابل خرید)

اگر می‌خواهید «خبرم کن» داشته باشید باید از صفر بسازید؛ الگوی پیشنهادی: جدول `product_notifications` (product_id, user_id یا mobile, type enum['back_in_stock','price_drop'], status, created_at) + فرم در همان جای «ناموجود» + Job/Cron که بعد از آپدیت stock/قیمت، به‌روزرسانی‌ها را چک کند (یا Observer روی Product/ProductVariant) + ارسال پیامک/SMS از طریق درگاه پیامکی.

### ۱۰.۱۲ اسکریپت‌ها و اجزای متفرقه

- **`detail.js`** (asset عمومی): تنظیمات MagicZoom (تکست فارسی)، دو Swiper برای اسلایدرهای `swiper-selector` و `swiper-team` (مرتبط/مکمل با breakpoint های ریسپانسیو)، منطق «مشاهده بیشتر» ویژگی‌ها (`#feature-list` — ۵ مورد اول نمایش داده می‌شود)، و Swiper های گالری `mySwiper-mobile-detail`, `mySwiper-images`, `mySwiper-images-2` (با thumbs).
- **`scripts.blade.php`:** اسکرول نرم به گالری (`#scrollBtn` → `#targetSection` با offset منفی)، لود `sweetalert2`, `magiczoomplus`, `detail.js`، تبدیل جدول‌های داخل توضیحات به responsive.
- **متفرقه/کد مرده:** اسکریپت چرخش متن `#dynamic-text-list` در add-to-cart-mobile (المنت آن وجود ندارد)، `selected-specification.blade.php` (استاتیک)، بخش‌های کامنت‌شده در price/cart-btn.

### ۱۰.۱۳ نکات انتقال برای پروژه مقصد

1. **ساختار داده Vue را همان‌طور بفرستید:** `main_specifications` (با `getSpfs`)، `variants` (با `getVariants`)، `images` سه‌سایز، `original_images`، `min_variant` — بقیه صفحه با همین JSON کار می‌کند.
2. **قوانین تجربه کاربری انتخابگر:** انتخاب خودکار تکی‌ها، قفل‌کردن گزینه‌های ناسازگار، دکمه پاک‌کردن انتخاب‌ها، و نمایش واریانتِ یکتا فقط وقتی همه انتخاب‌ها کامل شوند.
3. **سه حالت قیمت/موجودی** را با منطق `isAvailable`/`contactRequired` حفظ کنید.
4. **دایرکتیو `@mobile`** را معادل‌سازی کنید (یا از CSS breakpoint به‌جای UA استفاده کنید — پیشنهاد بهتر).
5. **اسکیمای سئو** را با مقادیر واقعی (امتیاز واقعی، availability واقعی) بازنویسی کنید.
6. **دکمه واتساپ** و **تب نظرات** را از ماژول‌های Comment/Setting جدا و مستقیم پیاده کنید.
7. `addToBasket` به روت `basket.add` وابسته است — در پروژه مقصد همان قرارداد (product_id, product_variant_id, quantity) را بسازید.

---

## ۱۱) جمع‌بندی — چه چیزی را کپی کنیم

- **بک‌اند:** اسکیمای بخش ۲ + مدل‌های بخش ۳ + سرویس‌های بخش ۴ (مخصوصاً تجمیع واریانت ۴.۳ و شرط‌های دسته ۴.۸) + API های بخش ۵ + اعتبارسنجی‌های بخش ۶ + اجتناب از باگ‌های بخش ۷.
- **فرانت‌اند:** ساختار صفحه جزئیات بخش ۱۰ (گالری، انتخابگر واریانت، سه حالت قیمت، تب‌ها، اسکیمای سئو، ریسپانسیو موبایل/دسکتاپ).
- **اختیاری/جدید:** قیمت‌گذاری داینامیک (TgjuSilver)، اکسل ورودی/خروجی، و قابلیت‌های «موجود شد / حراج شد خبرم کن» (که در پروژه مبدا وجود ندارند).


