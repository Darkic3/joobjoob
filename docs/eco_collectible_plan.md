# پلن بازطراحی فرانت — تم Modern Eco-Collectible

> [!IMPORTANT]
> دامنه: فقط سایت عمومی (layouts/site، components، site/*، checkout، errors). پنل ادمین **دست نمی‌خورد** — از قبل `app-admin.css` جداگانه دارد و به `app.css` وابسته نیست.

## ۱. وضعیت فعلی (یافته‌های بررسی)

| مورد | وضعیت |
|---|---|
| سیستم استایل | Tailwind 3.4 + توکن‌های CSS در [neumorphism.css](file:///f:/Projects/joobjoob/resources/css/neumorphism.css) |
| لایهٔ بزرگ | [neumorphism-pro.css](file:///f:/Projects/joobjoob/resources/css/neumorphism-pro.css) — ۴۱۲۰ خط، ۱۶۰KB (هیرو، دسته‌ها، deals، هدر، فوتر، محصول، سبد…) |
| مکانیزم تم | `data-theme` روی `<html>` از [SetTheme.php](file:///f:/Projects/joobjoob/app/Http/Middleware/SetTheme.php) و `config/site-features.php` (قفل روی `light-neumorphism`) |
| ناهمگونی | [product-card](file:///f:/Projects/joobjoob/resources/views/components/product-card.blade.php) و `products/index` با رنگ‌های خام Tailwind (indigo/gray/red) نوشته شده‌اند؛ بقیه با `neu-*` و صدها `style=""` اینلاین (سبد خرید: ۱۹۹ مورد) |
| فونت | Vazirmatn + JetBrains Mono (لود شده) + Estedad (محلی، فقط هیرو) |
| داده | فیلد SKU/متریال نداریم؛ ولی `product_specification` وجود دارد (منبع تگ متریال) |

## ۲. سیستم طراحی هدف (Design Tokens)

**پالت**

| توکن | مقدار | کاربرد |
|---|---|---|
| `--eco-canvas` | `#F7F5F0` | پس‌زمینهٔ صفحه |
| `--eco-canvas-2` | `#FAF8F5` | سکشن‌های متناوب / هدر |
| `--eco-surface` | `#FFFFFF` | کارت‌ها |
| `--eco-line` | `#E6E2D8` | بوردر ۱px کارت‌ها و جداکننده‌ها |
| `--eco-ink` | `#242422` | متن اصلی |
| `--eco-ink-2` | `#6B6860` | متن ثانویه (کنتراست AA روی بژ) |
| `--eco-sage` | `#7A8B7B` | تگ متریال / وضعیت موفق |
| `--eco-clay` | `#C27D60` | قیمت، دکمهٔ افزودن |
| `--eco-sand` | `#D4A373` | تگ وضعیت (پک اختصاصی، محدود) |
| `--eco-danger` | `#B5543F` | خطا/تخفیف (تراکوتای تیره‌تر، نه قرمز خالص) |

**تایپوگرافی:** بدنه `Vazirmatn` · تیتر نمایشی `Estedad` با وزن متوسط (محدود) · شناسه/قیمت/اعداد کلکسیونی `JetBrains Mono` با letter-spacing باز و حروف بزرگ.

**فرم و سایه:** کارت `radius 12px` + بوردر ۱px · دکمه `radius 8px` · تگ‌ها کپسولی · سایه‌ها تخت و بسیار محو (`0 1px 2px rgba(36,36,34,.04)`، در hover حداکثر `0 8px 24px -12px`). حذف کامل سایه‌های دوطرفهٔ نئومورفیسم.

**امضای بصری (Signature):** «برچسب انبار» — هر کارت گوشهٔ بالا یک شناسهٔ مونو مثل `#ECO-0042` و یک خط‌چین ظریف شبیه تگ کاغذی/لیبل انبار دارد؛ همین زبان در صفحهٔ محصول، سبد و فاکتور تکرار می‌شود.

**حرکت:** آرام و کم: fade/translate کوتاه، hover با بالا آمدن ۲px و تغییر بوردر. حذف افکت‌های آتش/ember/glow/چرخش بج‌ها. احترام کامل به `prefers-reduced-motion`.

## ۳. فازبندی

### فاز ۰ — زیرساخت تم (پایه، بدون تغییر ظاهری مخرب)
- افزودن تم `eco-collectible` به سیستم `data-theme` و تنظیم پیش‌فرض در `config/site-features.php`
- فایل جدید `resources/css/eco/tokens.css` + نگاشت توکن‌های قدیمی (`--bg`, `--accent`, `--shadow-*` …) به پالت جدید تا کل سایت **فوراً** رنگ جدید بگیرد
- افزودن پالت `eco` به `tailwind.config.js` (`bg-eco-canvas`, `text-eco-clay` …)
- ساختار فایل CSS ماژولار: `eco/base.css`, `eco/components.css`, `eco/sections/*.css`
- **خروجی:** سایت با رنگ‌های جدید ولی هنوز ساختار قدیمی

### فاز ۱ — کامپوننت‌های پایه (Primitives)
- `eco-card`, `eco-btn` (clay / ink / ghost / outline)، `eco-tag` (sage / sand / clay)، `eco-id` (شناسهٔ مونو)، `eco-input`/`select`/`checkbox`، `eco-badge`، صفحه‌بندی، breadcrumbs، toast، مدال
- جایگزینی سایه‌های نئومورفیک در `neu-*` (لایهٔ سازگاری تا حذف تدریجی)
- حالت‌های focus-visible، disabled، loading

### فاز ۲ — پوستهٔ سایت (Shell)
- نوار اعلان، [header](file:///f:/Projects/joobjoob/resources/views/components/header.blade.php)، منوی موبایل، [mobile-bar](file:///f:/Projects/joobjoob/resources/views/components/mobile-bar.blade.php)، [footer](file:///f:/Projects/joobjoob/resources/views/components/footer.blade.php)، back-to-top، layout‌های `app` و `guest`

### فاز ۳ — کارت محصول (قلب تم)
- بازنویسی [product-card](file:///f:/Projects/joobjoob/resources/views/components/product-card.blade.php): شناسهٔ کلکسیونی، تگ متریال از مشخصات، تگ وضعیت، قاب تصویر با پس‌زمینهٔ بژ و سایهٔ محو زیر محصول، قیمت تراکوتا با مونو، دکمهٔ `rounded-lg`
- منطق PHP کلاس کامپوننت حفظ می‌شود؛ فقط پراپرتی‌های جدید (`collectibleId`, `materialTag`, `statusTag`) اضافه می‌شوند
- category-card، quick-view، promo-banner-card

### فاز ۴ — صفحهٔ اصلی
- هیرو، دسته‌بندی‌ها (جایگزینی brutal/Neo-Tokyo)، پیشنهاد ویژه (حذف آتش → «سری محدود» با تایمر آرام)، trust badges، socials، بلاگ‌ها

### فاز ۵ — لیست و جزئیات محصول
- `products/index` (فیلترها، سورت، گرید)، `product-categories/show`
- `products/show` (۱۱۴KB): گالری، انتخاب واریانت، جدول مشخصات به سبک «برگهٔ مشخصات انبار»، نظرات، محصولات مرتبط

### فاز ۶ — سبد و پرداخت
- `cart/index` (۱۵۰KB، ۱۹۹ استایل اینلاین → کلاس‌ها)، ajax-cart، checkout (address/payment/success/failed)

### فاز ۷ — حساب کاربری و احراز هویت
- login / otp / verify، داشبورد، سفارش‌ها، جزئیات سفارش، فاکتور، آدرس‌ها، علاقه‌مندی‌ها، اعلان‌ها

### فاز ۸ — صفحات فرعی و پاکسازی
- بلاگ، صفحات ثابت (about/contact/faq/legal)، 404/429/500
- حذف CSS مرده از `neumorphism-pro.css` (هدف: کاهش چشمگیر حجم CSS)
- ممیزی نهایی: کنتراست، کیبورد، RTL، موبایل، Lighthouse

## ۴. روش کار و تأیید در هر فاز
1. اجرای تغییرات فاز
2. `npm run build` برای اطمینان از سالم بودن
3. اسکرین‌شات دسکتاپ + موبایل از صفحات تحت تأثیر
4. گزارش کوتاه → **تأیید شما** → فاز بعد

> [!WARNING]
> فایل‌های `neumorphism*.css` تا فاز ۸ حذف نمی‌شوند تا هیچ صفحه‌ای وسط کار خراب نماند. تست‌های PHPUnit مربوط به ویو (در صورت وجود assertion روی کلاس‌ها) بعد از هر فاز اجرا می‌شوند.
