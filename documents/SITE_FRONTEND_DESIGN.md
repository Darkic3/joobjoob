# گزارش طراحی و دیزاین فرانت سایت (Frontend Design Report)

> **هدف داکیومنت:** ثبت دقیق و کامل تمام تکنیک‌ها، متدها، الگوها و تصمیم‌های طراحیِ سمت سایتِ «Shopora».
> این سند هم به‌عنوان مرجع فنی برای توسعه‌دهندگان و هم به‌عنوان راهنمای قابل‌کپی برای پیاده‌سازی همین طراحی در پروژه‌های دیگر (با یک ایجنت) کار می‌کند.

---

## ۱. نمای کلی (Overview)

فرانت سایت بر پایهٔ **نئومورفیسم (Neumorphism / Soft UI)** ساخته شده است: سطوح «برجسته» و «فرو رفته» با سایه‌های دوطرفه (روشن + تیره) از همان رنگ پس‌زمینه، بدون خط و حاشیهٔ واضح.

**استک فنی:**
| لایه | ابزار | نقش |
|---|---|---|
| قالب | Blade (Laravel) | رندر سمت سرور، کامپوننت‌ها |
| استایل | Tailwind CSS + CSS سفارشی | utility classes + دیزاین‌سیستم نئومورفیک |
| باندل | Vite + Laravel Vite Plugin | build، refresh، asset نسخه‌دار |
| تعامل | Alpine.js + Vanilla JS | میکرواینترکشن‌ها، اسلایدر، تایمر |
| اسلایدر | Swiper 11 | هیرو اسلایدر |
| فونت | Vazirmatn (متن) + JetBrains Mono (اعداد) | فارسی/RTL + ارقام یکنواخت |
| آیکون | ایموجی/یونیکد | بدون لایبرری آیکون |
| داده‌ی نمایش | LocaleService / SettingService / Helpers | زبان، ارقام، تاریخ، تنظیمات |

**فایل‌های کلیدی:**
```
resources/css/app.css            ← ترکیب فونت + import ها + استایل Swiper سفارشی + reveal-on-scroll
resources/css/neumorphism.css    ← دیزاین‌سیستم پایه: توکن‌ها + کامپوننت‌های neu-*
resources/css/neumorphism-pro.css← لایهٔ «روان‌شناسی فروش»: بج‌ها، مدال‌ها، انیمیشن‌های زنده
resources/js/ui-effects.js       ← رفتارهای UI (تم، سبد، toast، نوار اعلان، دراپ‌داون، مشاهده‌گر اسکرول)
resources/js/localize-digits.js  ← بومی‌سازی خودکار ارقام بر اساس زبان
resources/js/language.js         ← دراپ‌داون زبان (لینک واقعی SEO)
resources/js/app.js              ← Alpine + Swiper
resources/views/layouts/site/app.blade.php    ← چیدمان اصلی (head/announce/header/main/footer)
resources/views/components/*.blade.php        ← header, footer, product-card, category-card, …
resources/views/site/**/*.blade.php           ← صفحات (home, products.index, products.show)
```

---

## ۲. توکن‌های دیزاین (Design Tokens)

همهٔ رنگ‌ها و مقیاس‌ها با **متغیرهای CSS** در `:root` تعریف شده‌اند و با صفت `data-theme` روی `<html>` سوئیچ می‌شوند. هیچ رنگ هاردکد در Blade نیست (استثناها در کامپوننت‌ها با کامنت مشخص شده‌اند).

### ۲.۱ توکن‌های اصلی (neumorphism.css)
```css
:root, [data-theme="light-neumorphism"] {
    --bg: #e0e5ec;              /* پس‌زمینه */
    --bg-elevated: #e0e5ec;     /* سطح کارت‌ها */
    --shadow-light: #ffffff;    /* سایهٔ روشن (بالا-چپ) */
    --shadow-dark: #a3b1c6;     /* سایهٔ تیره (پایین-راست) */
    --text-primary: #2d3748;
    --text-secondary: #6b7788;
    --accent: #6c63ff;          /* رنگ اصلی/اکشن */
    --accent-contrast: #ffffff;
    --danger: #e53e5c;
    --success: #38a169;
    --radius-sm: 12px;
    --radius-md: 18px;
    --radius-lg: 28px;
    --radius-pill: 999px;
    --gap: 1.25rem;             /* تنها پدینگ استاندارد کارت‌ها */
}
```

> **قاعدهٔ طلایی:** `--gap` تنها منبع پدینگ کارت‌هاست و در Blade هرگز `p-*` روی `.neu-card` گذاشته نمی‌شود (در کامنت CSS صراحتاً نوشته شده). در موبایل (`max-width:768px`) به `0.9rem` کاهش می‌یابد.

### ۲.۲ تم‌های موجود
| تم | `--bg` | `--accent` | توضیح |
|---|---|---|---|
| `light-neumorphism` (پیش‌فرض) | `#e0e5ec` | `#6c63ff` (بنفش) | کلاسیک |
| `dark-neumorphism` | `#2d3748` | `#8b83ff` | سایه‌ها `#3a4759`/`#212a37` |
| `blue-theme` | `#dde9f7` | `#3182ce` | آبی ملایم |
| `green-theme` | `#e2ece3` | `#38a169` | سبز |

### ۲.۳ توکن‌های «پرو» (neumorphism-pro.css)
```css
--gold: #f5a623;  --silver: #c7cdd6;  --bronze: #c98a4b;  /* مدال رتبه */
--urgency: #ff5b5b;  --live-dot: #2ecc71;                 /* فوریت و زنده بودن */
```
هر تم، نسخهٔ تیرهٔ این رنگ‌ها را هم تعریف می‌کند.

---

## ۳. سیستم سطوح نئومورفیک (Elevation System)

دقیقاً ۴ حالت سطح، بر اساس شدت سایه:

| کلاس | سایه | کاربرد |
|---|---|---|
| `.neu-flat` | `8px 8px 16px dark, -8px -8px 16px light` | کارت استاندارد |
| `.neu-flat-sm` | `4px 4px 8px` | عناصر کوچک، toast |
| `.neu-flat-lg` | `12px 12px 24px` | بلاک‌های بزرگ (هیرو، خبرنامه) |
| `.neu-pressed` | `inset 6px 6px 12px` | حالتِ «فشرده» (فعال/تایمر) |

**میکرواینترکشن تعاملی:** `.neu-interactive` روی هاور به سایهٔ بزرگ‌تر + `translateY(-2px)` و روی کلیک به `inset` (فشرده شدن) تبدیل می‌شود — حس فیزیکی «دکمهٔ واقعی».

---

## ۴. کامپوننت‌های پایه (neu-*)

### ۴.۱ کارت (`.neu-card`)
- سایهٔ `flat` + `padding: var(--gap)` + `transition: transform, box-shadow .2s`
- هاور: `translateY(-4px)` + سایهٔ بزرگ‌تر (بلند شدن از سطح)

### ۴.۲ دکمه‌ها (`.neu-btn`)
- **ارتفاع/پدینگ ثابت**: `min-height:46px; padding:0 1.5rem` — یکدستی در کل سایت (جایگزین `py-2/py-3` پراکنده)
- حالت‌ها: `:hover` (بزرگ‌تر + بالا)، `:active` (inset)، `:disabled` (opacity + inset)
- واریانت‌ها: `.neu-btn-sm`, `.neu-btn-primary` (با `color-mix` برای هالهٔ رنگی), `.neu-btn-icon` (دایرهٔ ۴۶px), `.neu-btn-icon-sm`
- دکمهٔ primary سایهٔ رنگی دارد: `box-shadow: 6px 6px 14px color-mix(in srgb, var(--accent) 45%, transparent)`

### ۴.۳ اینپوت (`.neu-input`)
- **فرو رفته (inset)**: برخلاف سطوح، اینپوت‌ها با سایهٔ داخلی ساخته می‌شوند
- فوکوس: سایهٔ شدیدتر + حلقهٔ 2px رنگی با `color-mix` از accent

### ۴.۴ دایرهٔ آیکون (`.neu-icon-circle`)
- `line-height: 1` → ایموجی/حروف همیشه دقیقاً وسط دایره می‌نشینند (نکتهٔ ظریف و مهم)
- اندازه‌ها: `sm` 44px، پیش‌فرض 56px، `lg` 72px

### ۴.۵ تاگل تم (`.neu-toggle`)
- دات داخل تاگل با `inset-inline-start` + `transition` حرکت می‌کند (RTL-aware)
- **موقعیت دات فقط با CSS** کنترل می‌شود: `[data-theme="dark-neumorphism"] .neu-toggle-dot { inset-inline-start: 29px; }` — بدون JS دستی

### ۴.۶ صفحه‌بندی (`.neu-page-item`)
- دایره‌های ۴۲px، حالت `.active` با سایهٔ inset + رنگ accent

### ۴.۷ بج‌های ساده
- `.neu-badge` (قرمز) و `.neu-badge-success` (سبز)

### ۴.۸ اسکرول‌بار سفارشی
- کل صفحه: اسکرول‌بار ۱۰px با thumb از `--shadow-dark` (نئومورفیک)
- `.no-scrollbar` برای مخفی‌سازی کامل

---

## ۵. لایهٔ «روان‌شناسی فروش» (Sales Psychology Layer)

این لایه در `neumorphism-pro.css` — مخصوص فروشگاه: بج‌ها، نشان‌ها و انیمیشن‌های «زنده».

| عنصر | کلاس | رفتار |
|---|---|---|
| بج فوریت | `.badge-urgency` | قرمز `--urgency` + سایهٔ رنگی + هالهٔ `pulseGlow` |
| بج جدید | `.badge-new` | accent با متن متضاد |
| بج تخفیف | `.badge-sale` | متن قرمز، پس‌زمینهٔ سطح + سایهٔ inset |
| نشان رتبه | `.neu-rank-badge` | دایرهٔ 34px گوشهٔ کارت؛ رنگ با `--rank-color`/`--rank-text` پاس داده می‌شود (طلا/نقره/برنز) |
| نوار موجودی | `.stock-bar` + `.stock-bar-fill` | شیار inset + گرادیان `urgency→gold` + `transition: width .6s` |
| ستاره امتیاز | `.stars` | `--gold` با `letter-spacing` |
| دکمه‌های سریع | `.product-actions` | گوشهٔ کارت، روی هاور از بیرون اسلاید می‌شود (`--slide` در RTL برعکس) |
| نقطهٔ زنده | `.live-dot` | دایرهٔ سبز + `livePing` (پالس محو شونده) |
| نوار اعلان | `.announce-bar` | گرادیان `accent→gold` — تمام‌عرض بالای هدر |
| اسپینر | `.btn-spinner` | حلقهٔ چرخان با `color-mix` |
| پاپ کانتر | `.count-pop` | انیمیشن `popIn` روی تغییر شمارنده |

**مثال ترکیبی در کارت محصول:** بج «فقط X عدد!» (fear of missing out) + بج «٪-» تخفیف + بج «جدید» (14 روز اخیر) — فقط یکی در هر لحظه، با اولویت: موجودی کم > تخفیف > جدید.

---

## ۶. انیمیشن‌ها و Micro-interactions

### ۶.۱ Keyframes تعریف‌شده
| نام | رفتار | کاربرد |
|---|---|---|
| `float` | حرکت عمودی ±15px در 6s | تصویر محصول هیرو (شناور) |
| `blob` | جابجایی + تغییر مقیاس در 10s | هاله‌های رنگی پس‌زمینهٔ هیرو |
| `pulseGlow` | پالس حلقهٔ رنگی تا شعاع 10px در 2.2s | بج‌های فوریت (تپش) |
| `livePing` | مقیاس 1→2.2 با محو شدن | نقطهٔ «زنده» |
| `toastIn` | ورود با translateY + scale در 0.35s | toastها |
| `spin` | چرخش 360° | اسپینر |
| `popIn` | مقیاس 0.8→1 | پرش شمارنده |

### ۶.۲ انیمیشن پله‌ای هیرو (Staggered Reveal)
محتوای هر اسلاید با تأخیرهای پشت‌سرهم ظاهر می‌شود:
```
badge (0.1s) → title (0.2s) → desc (0.3s) → image (0.3s) → btns (0.4s)
```
با `cubic-bezier(0.2, 0.8, 0.2, 1)` و فقط روی `.swiper-slide-active` — اسلایدهای غیرفعال مخفی می‌مانند.

### ۶.۳ ظاهر شدن هنگام اسکرول (`.reveal-on-scroll`)
- **IntersectionObserver** (نه jQuery) با `threshold: 0.1` و `rootMargin: 0`
- پس از دیده‌شدن، `observer.unobserve()` → اجرا فقط یک‌بار (بهینه)
- حالت اولیه: `opacity:0; translateY(30px)` + `will-change` — بدون jank

### ۶.۴ اصول کلی انیمیشن
- فقط `opacity` و `transform` انیمیت می‌شوند (ترکیب GPU-friendly، بدون layout thrash)
- زمان‌بندی استاندارد: ورود/هاور 0.2s–0.3s، ظاهر شدن 0.8s با منحنی‌های نرم

---

## ۷. چیدمان (Layout)

### ۷.۱ کانتینر مرکزی
```
<main class="max-w-7xl mx-auto px-4 md:px-6 py-6">
```
- هدر، main و فوتر همگی `max-w-7xl` (1280px) وسط‌چین هستند — الگوی استاندارد «ستون محتوا»
- نوار اعلان بالای هدر **تمام‌عرض** است (استثنای عمدی)
- هدر `sticky top-0` با `backdrop-blur` و پس‌زمینهٔ نیمه‌شفاف (`color-mix 90%`)

### ۷.۲ الگوهای گرید
| بخش | چیدمان |
|---|---|
| دسته‌بندی‌ها | `flex flex-wrap justify-center` کارت‌های 120px |
| پیشنهادهای داغ | `grid-cols-2 md:grid-cols-4` داخل `neu-flat-lg` |
| نشان‌های اعتماد | `grid-cols-2 md:grid-cols-4` |
| محصولات | `grid-cols-2 md:grid-cols-3` (لیست) / `grid-cols-2 md:grid-cols-4` (بخش‌های صفحه اصلی) |
| نظرات | `md:grid-cols-3` |
| هیرو | `lg:grid-cols-2` (متن + تصویر) با `order` سوئیچ در موبایل |

### ۷.۳ صفحهٔ لیست محصولات
- سایدبار فیلتر: `lg:sticky lg:top-24` (چسبان هنگام اسکرول)
- در موبایل: پنل فیلتر مخفی + دکمهٔ «فیلتر ⚙» با `hidden-mobile` (CSS سفارشی)
- فیلتر برند: **radio های مخفی + چیپ‌های قابل کلیک** (الگوی دسترسی‌پذیر بدون JS اضافه)
- فرم‌های فیلتر و مرتب‌سازی پارامترهای قبلی را با `input hidden` حفظ می‌کنند
- صفحه‌بندی نئومورفیک سفارشی (نه دیفالت Laravel) — دایره‌ها + حالت‌های active/disabled

### ۷.۴ صفحهٔ جزئیات محصول
- گالری: تصویر اصلی + بندانگشتی‌های اسکرول‌افقی (با `onclick` سادهٔ جاوااسکریپت)
- **انتخابگر واریانت** (رنگ/سایز):
  - گزینه‌های رنگ: دایره‌های رنگی با تیک ✓، حالت‌های `ring-2/scale-110`
  - گزینه‌های متنی: چیپ نئومورفیک با حالت `active-variant` (inset)
  - **غیرفعال‌سازی سازگار (compatibility)**: گزینه‌هایی که با انتخاب فعلی ترکیب‌پذیر نیستند `opacity:0.35 + pointer-events:none`
  - یافتن واریانت: مقایسهٔ `spec_ids` مرتب‌شده (الگوریتم `findVariant`)
  - به‌روزرسانی زندهٔ قیمت/تخفیف/موجودی/محدودیت تعداد با JS
- استپر تعداد (qty) با محدودیت `max` از موجودی واریانت انتخاب‌شده
- تب‌ها (توضیحات/مشخصات/نظرات) با نمایش/مخفی‌سازی ساده
- اسکیمای **JSON-LD** برای SEO

---

## ۸. جاوااسکریپت و رفتارها (ui-effects.js)

| رفتار | تکنیک |
|---|---|
| تاگل تم | فقط `data-theme` روی `<html>` عوض می‌شود + `localStorage('site_theme')`؛ بقیهٔ استایل CSS خودکار |
| افزودن به سبد | چرخهٔ `idle → loading(spinner) → ✓ اضافه شد → restore` + پرش شمارندهٔ سبد با `count-pop` (نکته: فعلاً شبیه‌سازی شده) |
| Toast اثبات اجتماعی | چرخش در آرایهٔ `[نام, پیام]`؛ اولین نمایش بعد 3s، سپس هر 12s، 5s روی صفحه |
| نوار اعلان | چرخش پیام‌ها هر 4s با fade (opacity) |
| دراپ‌داون زبان | toggle + بستن با کلیک بیرون |
| چیپ برند | `[data-brand-chip]` با کلاس `active` |
| تایمر معکوس | `startCountdown()` — ارقام با `localizeDigits.isPersian()` بومی می‌شوند |
| مشاهده‌گر اسکرول | IntersectionObserver (بالا) |

**نکتهٔ معماری:** `app.js` فقط Alpine + Swiper است؛ رفتارهای سفارشی در `ui-effects.js` جدا نگه داشته شده‌اند تا باندل‌ها سبک و ماژولار بمانند.

---

## ۹. بومی‌سازی و i18n (مهم‌ترین لایهٔ متقاطع)

### ۹.۱ ارقام (localize-digits.js)
- **منبع واحد**: صفت `data-digits` روی `<html>` که سمت سرور از `LocaleService::digitFamily()` می‌آید
  - `persian` → `۰۱۲۳۴۵۶۷۸۹` + جداکنندهٔ هزارگان `٬`
  - `arabic` → `٠١٢٣٤٥٦٧٨٩` + `٬`
  - `latin` → ASCII + تبدیل `٪` به `%` و `٬` به `,`
- با **TreeWalker** روی همهٔ text-nodeهای قابل مشاهده اجرا می‌شود
- از `SCRIPT/STYLE/TEXTAREA/PRE/CODE` و `input/select/[contenteditable]` رد می‌شود (داده‌های فرم سالم می‌مانند)
- **MutationObserver** با debounce 300ms برای محتوای داینامیک (تایمر، toast، قیمت واریانت)
- نکتهٔ ضد-حلقه: فقط وقتی متن واقعاً تغییر کرده بازنویسی می‌شود
- API عمومی: `window.localizeDigits = { convert, isPersian, walk, digitFamily }`

### ۹.۲ تاریخ (show_date + jdate + hijri)
- دیتابیس همیشه میلادی/UTC — نمایش بر اساس تقویم زبان (`LocaleService::calendar`):
  - تقویم `jalali` (فارسی و زبان‌های شمسی ثبت‌شده) → `jdate_format` با ارقام فارسی
  - تقویم `hijri` (عربی) → `hijri_date` — تقویم اسلامیِ مدنی با نام ماه عربی و ارقام عربی (مثل `٢١ صفر ١٤٤٨`)
  - تقویم `gregorian` → `Carbon::translatedFormat` با نام ماهِ زبان محلی (en → English)
- هر سه در timezone تهران تفسیر می‌شوند
- در جزئیات محصول: `show_date($review->created_at, 'd F Y')`

### ۹.۳ جهت (RTL/LTR)
- `<html dir="rtl|ltr">` از `locale()->isRtl()` 
- کلاس `flip-rtl` برای آینه‌سازی `scaleX(-1)`
- استفادهٔ گسترده از کلاس‌های Tailwind `start/end` و `ms/me` به‌جای `left/right` — چیدمان کاملاً جهت‌آگاه
- فلش‌های Swiper و `product-actions` با `[dir]` بازتعریف می‌شوند

### ۹.۴ نکتهٔ bidi (مشکل رفع‌شده)
- متن ترکیبی «عدد لاتین + کلمهٔ فارسی + عدد فارسی» داخل `dir="ltr"` برعکس نمایش می‌یافت
- راه‌حل: **تگ `<bdi dir="ltr">`** برای ایزوله‌کردن هر عدد: `<bdi dir="ltr">4.8</bdi> از <bdi dir="ltr">۵.۰</bdi>`
- اعداد داخل `dir="ltr"` + فونت `mono` (`JetBrains Mono`) نمایش یکدست دارند

### ۹.۵ ترجمه‌ها
- کلیدهای `__('messages.*')` در `resources/lang/{fa,en,ar,tr}/messages.php`
- تنظیمات پویا از `setting('key', 'default')` — متن‌های مدیریتی قابل تغییر از پنل

### ۹.۶ مسیرها و SEO زبان
- `localizedRoute('products.show', [...])` — زبان پیش‌فرض بدون پیشوند، بقیه با `/en/...`
- `localizedUrl()` برای hreflang / سوییچر — `ogLocale()` برای `og:locale`

---

## ۱۰. اسلایدر هیرو (Swiper)

- نصب: CDN (css + js) در layout + import در `app.js`
- کانفیگ: `loop + autoplay(5s) + fade crossFade + speed 1500 + pagination + navigation`
- `rtl: true` — اما **فلش‌ها به بیرون از کادر منتقل شده‌اند** (`-60px`) و در موبایل داخل کادر می‌آیند
- در RTL: فلش بعدی چپ، قبلی راست؛ در LTR برعکس (با `[dir]` + `!important`)
- استایل نئومورفیک روی navigation/pagination در `app.css`
- pagination فعال: پهن‌تر (`24px`, `radius-pill`)
- انیمیشن‌های پله‌ای محتوا (بخش ۶.۲)
- نکته: یک `app.js` و یک `<script>` داخل `hero.blade.php` هر دو Swiper را mount می‌کنند — نسخهٔ داخل view با کانفیگ کامل (پیروز است). در بازنویسی بعدی باید یکی شوند.

---

## ۱۱. کارت محصول — آناتومی کامل (نمونهٔ مرجع)

```
[neu-card neu-interactive group relative]
 ├─ neu-rank-badge          (رتبه ۱-۳: طلا/نقره/برنز — فقط پرفروش‌ها)
 ├─ product-actions         (♡ علاقه‌مندی + 👁 مشاهده سریع — هاور اسلاید می‌شود)
 ├─ تصویر (aspect-ratio 1/1, lazy, group-hover:scale-110 500ms)
 │   └─ بج: موجودی کم | تخفیف | جدید (اولویت‌بندی‌شده)
 ├─ نام (line-clamp-2 + min-h)
 ├─ ستاره‌ها + تعداد نظرات
 └─ قیمت: final (accent, mono) + تومان + old (line-through)
     ├─ stock-bar (درصد فروش — فقط پیشنهادهای داغ)
     └─ دکمهٔ افزودن به سبد (idle→loading→success)
```

---

## ۱۲. الگوهای خوب / بهترین‌عمل‌های رعایت‌شده

1. **توکن‌محوری**: بدون رنگ هاردکد؛ همه‌چیز از CSS vars
2. **قاعدهٔ منفرد `--gap`**: پدینگ کارت فقط یک‌جا
3. **`color-mix()`**: سایه‌های رنگی و پس‌زمینه‌های شفاف بدون هاردکد
4. **RTL-native**: `start/end/ms/me/inset-inline-*` به‌جای جهت‌های مطلق
5. **انیمیشن GPU-safe**: فقط opacity/transform
6. **یک‌بار اجرا**: `observer.unobserve` + MutationObserver با debounce
7. **دسترسی‌پذیری**: چیپ‌های radio مخفی، `alt` تصاویر، `title` دکمه‌ها، `loading="lazy"`
8. **پرفورمنس**: `Cache::remember` در trust-badges، `lazy` تصاویر، `will-change`
9. **SEO**: hreflang/canonical/og:locale + JSON-LD روی محصول

---

## ۱۳. بدهی فنی و نقاط ضعف فعلی (برای بهبود)

| # | مشکل | کجا | راه‌حل پیشنهادی |
|---|---|---|---|
| 1 | دوبار mount شدن Swiper | `app.js` + `hero.blade.php` | یکی کردن، نگه‌داشتن کانفیگ کامل |
| 2 | افزودن به سبد شبیه‌سازی‌شده (setTimeout) | `ui-effects.js` | اتصال واقعی به `cart.add` با fetch |
| 3 | «تومان» و «نفر در حال مشاهده» هاردکد فارسی در کامپوننت‌ها | product-card, hero | انتقال به `__('messages.*')` |
| 4 | `add-to-cart` دو بار ثبت listener شده | `ui-effects.js` | ادغام در یک ثبت |
| 5 | `hero.blade.php` `rtl: true` ثابت | hero | بر اساس `locale()->isRtl()` |
| 6 | `.reveal-on-scroll` در موبایل ممکن است محتوا را دیر نشان دهد | app.css | threshold پایین‌تر در موبایل یا non-JS fallback |
| 7 | ایموجی‌ها به‌عنوان آیکون — در سیستم‌عامل‌های مختلف یکسان نیستند | همه‌جا | در فاز بعدی: لایبرری آیکون SVG (مثل Lucide/Iconify) |
| 8 | `!important` زیاد در استایل Swiper | neumorphism.css | بازنویسی با specificity درست |

---

## ۱۴. نقشهٔ راه حرفه‌ای‌تر شدن (به تفکیک اولویت)

### P0 — فوری (باگ/یکدستی)
- حذف هاردکدهای فارسی (ردیف 3 جدول بالا) و i18n کامل کارت محصول/هیرو
- یک‌سان‌سازی Swiper + `rtl` داینامیک

### P1 — تجربهٔ کاربری و اعتماد (بیشترین اثر بر نرخ تبدیل)
- **اتصال واقعی سبد خرید** (افزودن با fetch + شمارندهٔ واقعی + صفحهٔ سبد)
- **حالت‌های واقعی محصول**: ناموجود/تماس بگیرید در کارت + صفحهٔ جزئیات
- **لایو چت/دکمهٔ پشتیبانی شناور** (نئومورفیک، گوشهٔ پایین)
- **بازگشت به بالا** (back-to-top) نئومورفیک
- **هدر چسبان فشرده‌شونده** (shrink on scroll)

### P2 — بصری و برند
- جایگزینی ایموجی‌ها با **SVG icon set** (هم‌رنگ accent)
- **تصاویر واقعی** به‌جای picsum (fallback صرفاً برای dev)
- **انیمیشن‌های اسکلت (skeleton)** هنگام لود گرید محصولات
- **Hover روی کارت**: بزرگ‌نمایی تصویر + دکمهٔ quick view واقعی (مودال)
- دکمهٔ «اشتراک‌گذاری» روی محصول (لینک‌های سوشال با SVG)

### P3 — حرکت و پویایی
- **حالت‌های ورود صفحات** (page transition) با View Transitions API
- **کانفتی/موفقیت** بعد از افزودن به سبد (لایهٔ تایید مثبت)
- **نوار پیشرفت اسکرول** بالای صفحه (progress bar نئومورفیک)
- **پیشنهاد شخصی‌سازی‌شده** («بر اساس سبد شما») با data-driven

### P4 — تکنیکال
- استخراج Alpine components (`Alpine.data('productCard')`) برای خوانایی
- بارگذاری `ui-effects.js` فقط در سایت (فعلاً به‌صورت جدا باندل شده)
- **پشتیبانی از `prefers-reduced-motion`** برای دسترس‌پذیری (حذف انیمیشن‌ها برای کاربران حساس)
- بررسی FCP/LCP و Lazy-loading مراحل بعدی

---

## ۱۵. جمع‌بندی

طراحی سایت یک **دی‌زاین‌سیستم نئومورفیک کامل** با چهار لایهٔ مشخص است:
1. **توکن‌ها و تم‌ها** (متغیرهای CSS، ۴ تم)
2. **سطوح و کامپوننت‌های پایه** (`neu-flat/pressed/card/btn/input/...`)
3. **لایهٔ روان‌شناسی فروش** (بج‌ها، مدال‌ها، هاله‌های زنده، stock-bar)
4. **لایهٔ متقاطع بومی‌سازی** (ارقام، تاریخ، جهت، ترجمه — همه داده‌محور از LocaleService)

پایهٔ طراحی مدرن و منسجم است؛ بیشترین فرصتِ بهبود در **اعتبارِ واقعی داده‌ها** (سبد/موجودی واقعی)، **یکدستی i18n** (حذف هاردکدها) و **لایهٔ آیکون و انیمیشن‌های تکمیلی** است. برای کپی در پروژهٔ دیگر کافی است این ۸ فایل پایه (۳ CSS + ۲ JS + layout + ۲ کامپوننت مرجع) منتقل و توکن‌ها سفارشی شوند.
