# گزارش طراحی و پیاده‌سازی سیستم مدیریت زبان‌ها (Localization) — بلوپرینت قابل استفاده مجدد

> این سند، **بلوپرینت نهایی** سیستم مدیریت زبان‌های سایت (هم سایت فروشگاهی و هم پنل ادمین) است که ابتدا روی **Shopora** پیاده می‌شود و بعداً برای هر سایت Laravel دیگری با کمترین تغییر قابل انتقال است.
>
> هدف این سند دو چیز است: (۱) به برنامه‌نویس بگوید این سیستم دقیقاً **چطور کار می‌کند** و همه‌ی بخش‌هایی که تحت تأثیر زبان هستند کدامند؛ (۲) به یک ایجنت/توسعه‌دهنده اجازه دهد این فیچر را روی پروژه‌ای دیگر **کپی و پیاده‌سازی** کند.
>
> وضعیت: **کامل — هر ۶ فاز انجام شد ✅ (فاز ۷: پاکسازی).** فازها با ☐ / ✅ علامت می‌خورند و هر فاز با یک «گیت اعتبارسنجی» تمام می‌شود.

---

## فهرست

1. [خلاصه اجرایی](#۱-خلاصه-اجرایی)
2. [چرا این روش؟ (مقایسه سه رویکرد)](#۲-چرا-این-روش-مقایسه-سه-رویکرد)
3. [نمای کلی معماری](#۳-نمای-کلی-معماری)
4. [نقشه کامل سیستم‌های متأثر از زبان (مهم‌ترین بخش)](#۴-نقشه-کامل-سیستمهای-متأثر-از-زبان)
5. [کاتالوگ زبان‌ها — config/languages.php](#۵-کاتالوگ-زبانها--configlanguagesphp)
6. [دیتابیس — جدول languages](#۶-دیتابیس--جدول-languages)
7. [مدل Language](#۷-مدل-language)
8. [سرویس LocaleService (منبع واحد حقیقت)](#۸-سرویس-localeservice-منبع-واحد-حقیقت)
9. [میدلور SetLocale — بازنویسی و زنجیره fallback](#۹-میدلور-setlocale--بازنویسی-و-زنجیره-fallback)
10. [سوییچرهای زبان و حالت تک‌زبانه](#۱۰-سوییچرهای-زبان-و-حالت-تکزبانه)
11. [تاریخ و تقویم (show_date)](#۱۱-تاریخ-و-تقویم-show_date)
12. [اعداد و ارقام (localize-digits.js)](#۱۲-اعداد-و-ارقام-localize-digitsjs)
13. [کامپوننت‌های وابسته به تقویم (پیکر شمسی، تایمر)](#۱۳-کامپوننتهای-وابسته-به-تقویم)
14. [دیتای ترجمه‌پذیر مدل‌ها (JSON columns)](#۱۴-دیتای-ترجمهپذیر-مدلها-json-columns)
15. [صفحه مدیریت زبان‌ها در پنل ادمین + RBAC](#۱۵-صفحه-مدیریت-زبانها-در-پنل-ادمین--rbac)
16. [کامند lang:new و چرخه کامل افزودن زبان](#۱۶-کامند-langnew-و-چرخه-کامل-افزودن-زبان)
17. [سیدر LanguageSeeder](#۱۷-سیدر-languageseeder)
18. [چک‌لیست امنیتی و درستی — باید و نباید](#۱۸-چکلیست-امنیتی-و-درستی--باید-و-نباید)
19. [راهنمای انتقال به سایت دیگر (Porting Guide)](#۱۹-راهنمای-انتقال-به-سایت-دیگر-porting-guide)
20. [نقشه راه پیاده‌سازی — فازبندی ۰→۱۰۰](#۲۰-نقشه-راه-پیادهسازی--فازبندی-۰۱۰۰)

---

## ۱) خلاصه اجرایی

- **اصل طراحی:** «کاتالوگ در کانفیگ، حقیقتِ فعال/پیش‌فرض بودن در دیتابیس». فایل `config/languages.php` می‌داند هر زبانِ شناخته‌شده چه جهتی دارد، چه تقویمی و چه خانواده‌ی رقمی؛ جدول `languages` می‌گوید کدام زبان‌ها **فعال**اند، کدام **پیش‌فرض** است و چه ترتیبی دارند.
- **منبع واحد حقیقت:** سرویس `LocaleService` — همه‌ی مصرف‌کننده‌ها (میدلور، هدرها، login، تاریخ، اعداد، پیکر شمسی، پنل ادمین) فقط از این سرویس می‌خوانند. کلیدهای منسوخ `available_locales`/`rtl_locales` از `config/app.php` **حذف شده‌اند**.
- **قابلیت‌های ادمین:** افزودن زبان جدید (کد دلخواه + نام + جهت)، غیرفعال‌کردن هر زبان، تعیین زبان پیش‌فرض، حالت تک‌زبانه (با غیرفعال‌کردن بقیه یا خودکار با ۱ زبان فعال).
- **زنجیره fallback:** هر مرحله که زبانش نامعتبر/غیرفعال باشد **بی‌صدا رد می‌شود و زنجیره ادامه می‌یابد** (کاربر → session → پیش‌فرض). نتیجه: کاربر هرگز خطا نمی‌بیند و تا حد ممکن به آخرین زبان معتبرش می‌ماند.
- **چهار لایه‌ی زبان:** (۱) رشته‌های UI در `resources/lang/{code}/`، (۲) دیتای ترجمه‌پذیر در ستون‌های JSON مدل‌ها، (۳) تاریخ/تقویم/اعداد، (۴) جهت صفحه (RTL/LTR).
- **حالت تک‌زبانه:** سوییچر مخفی می‌شود و `?lang=` نادیده گرفته می‌شود — قانون ساده: **تعداد زبان‌های فعال ≤ ۱**.
- **کش:** الگوی `SettingService` (یک کوئری در هر ریکوئست + کش یکپارچه + پاک‌سازی روی تغییر) دقیقاً کپی می‌شود.
- **بدون پکیج خارجی.** کاملاً بومی و قابل انتقال.

---

## ۲) چرا این روش؟ (مقایسه سه رویکرد)

| موضوع | رویکرد ۱: فقط کانفیگ | رویکرد ۲: کلیدهای تنظیمات (SettingService) | **رویکرد ۳: جدول languages + LocaleService ✅** |
|---|---|---|---|
| افزودن زبان توسط ادمین | ❌ فقط ویرایش کد | ⚠️ فقط از بین گزینه‌های از‌پیش‌تعریف‌شده | ✅ کد دلخواه (`de`, `ru`, …) + نام + جهت |
| متادیتای هر زبان | در کانفیگ | درهم داخل JSON تنظیمات | ✅ ستون‌های واقعی (نام ترجمه‌پذیر، جهت، فعال، پیش‌فرض، ترتیب) |
| غیرفعال‌کردن / پیش‌فرض / ترتیب | دستی | ضعیف | ✅ یک صفحه‌ی مدیریت تمیز |
| کش و عملکرد | — | خوب | ✅ خوب (همان الگوی SettingService) |
| پیچیدگی | صفر | کم | متوسط (یک جدول + سرویس) |
| قابل انتقال به پروژه دیگر | — | متوسط | ✅ عالی (بلوپرینت همین سند) |
| مسیر آینده (SEO URL، پرچم، آمار) | ❌ | ❌ | ✅ **SEO URL پیاده شد** (پیشوند + hreflang + canonical) — پرچم/آمار آینده |

> ⚠️ **چرا کاتالوگ کانفیگ هم لازم است؟** چون «تقویم/ارقام/جهت پیش‌فرض» یک زبان (مثلاً `de` → میلادی/لاتین/ltr) را نباید ادمین هر بار وارد کند — کاتالوگ برای زبان‌های شناخته‌شده مقدار دارد و برای ناشناخته‌ها یک `_default` منطقی.

---

## ۳) نمای کلی معماری

```
┌───────────────────────────┐
│  config/languages.php     │  ← کاتالوگ: dir / calendar / digits برای هر زبان شناخته‌شده + _default
└────────────┬──────────────┘
             │ fallback
┌────────────▼──────────────┐
│  جدول languages (DB)      │  ← حقیقت: code, name(json), native_name, dir, is_active, is_default, sort_order
└────────────┬──────────────┘
             │ یک کوئری + کش یکپارچه + حافظه‌ی ریکوئست
┌────────────▼──────────────┐
│  LocaleService            │  ← منبع واحد حقیقت runtime
│  active() default() dir() │
│  isActive() isJalali()    │
│  digitFamily() flush()    │
└──────┬─────────┬──────────┘
       │         │
┌──────▼─────┐  ┌▼───────────────────────────────┐
│ SetLocale  │  │ مصرف‌کننده‌ها:                   │
│ میدلور     │  │ هدر سایت / هدر ادمین / login     │
└────────────┘  │ show_date() / localize-digits.js│
                │ jalali-datetime / countdown     │
                │ صفحه مدیریت زبان‌ها (پنل)        │
                └─────────────────────────────────┘
```

**جریان درخواست:** `web` → `SetLocale` (گلوبال) → `SetTheme` → … → کنترلر.
**قانون طلایی:** هیچ کدی `config('app.available_locales')` را نمی‌خواند — این کلید حذف شده است؛ همه فقط از `LocaleService` می‌خوانند.

---

## ۴) نقشه کامل سیستم‌های متأثر از زبان (مهم‌ترین بخش)

این جدول **هر** بخشی را که با زبان در ارتباط است فهرست می‌کند، کاری که می‌کند، محلش و مصرف‌کننده‌اش. هنگام انتقال به پروژه‌ی دیگر، این جدول چک‌لیست «همه‌ی جاهایی که باید تغییر کند» است.

| # | سیستم | چه تأثیری از زبان می‌پذیرد | فایل/محل | نحوه‌ی درست‌سازی (در این معماری) |
|---|---|---|---|---|
| 1 | تعیین زبان فعال | تشخیص زبان از URL / کاربر / session | `app/Http/Middleware/SetLocale.php` | خواندن از `LocaleService::activeCodes()/defaultCode()`؛ رد زبان غیرفعال؛ احترام به حالت تک‌زبانه |
| 2 | جهت صفحه (RTL/LTR) | `dir` روی `<html>` و `direction` در ویوها | `layouts/admin/app.blade.php`، `layouts/site/app.blade.php`، `errors/429`، `admin/auth/login` | `LocaleService::dir($locale)` به‌جای `config('app.rtl_locales')` |
| 3 | رشته‌های UI | ترجمه‌ی متن‌های رابط کاربری | `resources/lang/{code}/*.php` | پوشه برای هر زبان؛ زبان جدید = پوشه جدید (کامند `lang:new`) |
| 4 | دیتای ترجمه‌پذیر مدل‌ها | ستون‌های JSON با کلید کد زبان | ستون‌های `json` مدل‌ها (Product, Brand, …) + متد `trans()`/accessor | fallback `$value[locale] ?? $value['fa'] ?? null` (موجود — بدون تغییر) |
| 5 | تاریخ/تقویم | شمسی برای fa، قمری برای ar، میلادی برای بقیه | `app/Helpers/date.php` → `show_date()` | بر اساس `LocaleService::calendar($locale)` (jalali/hijri/gregorian) — داده‌درایو |
| 6 | اعداد/ارقام | ارقام فارسی/عربی/لاتین + جداکننده‌ی هزارگان | `resources/js/localize-digits.js` | بر اساس `lang` روی `<html>` (موجود)؛ برچسب `data-digits` اختیاری برای override |
| 7 | نام ماه/روز میلادی | نام محلی ماه‌ها در زبان‌های غیرفارسی | `show_date()` → `Carbon->translatedFormat()` | `->locale($locale)` (موجود — برای زبان جدید خودکار است) |
| 8 | پیکر تقویم شمسی | fa → جلالی‌پیکر، بقیه (شامل ar) → `datetime-local` | `resources/views/components/admin/jalali-datetime.blade.php` | `LocaleService::isJalali()` به‌جای `in_array(['fa','ar'])` |
| 9 | تایمر شمارش معکوس | ارقام اولیه‌ی `۰۰` | `resources/views/components/countdown-timer.blade.php` | خانواده‌ی رقمی (`localize-digits.js`) — بدون وابستگی به تقویم |
| 10 | سوییچر زبان سایت | لیست زبان‌های قابل انتخاب | `resources/views/components/header.blade.php` + `resources/js/language.js` | `LocaleService::active()`؛ مخفی وقتی تک‌زبانه |
| 11 | سوییچر زبان ادمین | لیست زبان‌های قابل انتخاب | `resources/views/components/admin/header.blade.php` + صفحه‌ی login | `LocaleService::active()` |
| 12 | زبان کاربر | ترجیح هر کاربر | ستون `users.locale` | بدون تغییر (میدلور می‌خواند؛ مقدار غیرفعال → fallback) |
| 13 | اعتبارسنجی | قواعد ملی مثل موبایل/URL | `app/Rules/*` | معمولاً مستقل از زبان — فقط در صورت نیاز |
| 14 | تنظیمات سایت | متن‌های پویای سایت | `SettingService` + `config/settings.php` | بدون تغییر (خارج از این ماژول) |
| 15 | مدیریت زبان‌ها (پنل) | CRUD زبان‌ها + حالت تک‌زبانه | کنترلر + ویو + روت‌های `admin.languages.*` | جدید — بخش ۱۵ |
| 16 | RBAC | پرمیشن مدیریت زبان‌ها | `config/permissions.php` + `lang/*/permissions.php` + سایدبار | گروه `languages` جدید |
| 17 | کش | اعتبار داده‌های زبان بین ریکوئست‌ها | `LocaleService` (کلید `shopora_languages_all`) | پاک‌سازی روی هر ذخیره/حذف (flush) |
| 18 | تاریخ‌های نمایشی مدل‌ها | `jalali_created_at` و … | `app/Traits/HasJalaliDate.php` | از `show_date()` استفاده می‌کند (موجود) — خودکار درست می‌شود |
| 19 | پیش‌فرض‌های قدیمی | کلیدهای منسوخ کانفیگ | `config/app.php` → `available_locales`/`rtl_locales` | ✅ **حذف شده‌اند** (فاز ۷) — هیچ مصرف‌کننده‌ای باقی نمانده |
| 21 | SEO زبان‌ها | پیشوند URL + hreflang + canonical + og:locale | `routes/site.php` + `SetLocale` + `layouts/site/app.blade.php` | ✅ **پیاده شد** (فاز ۶) — جزئیات در فاز ۶ |
| 20 | نام زبان‌ها در UI | اسم هر زبان به زبان خودش/فعال | جدول `languages.name` + `native_name` | `localizedName()` با fallback |

---

## ۵) کاتالوگ زبان‌ها — `config/languages.php`

```php
<?php

return [
    // پیش‌فرض زبان‌های ناشناخته (خارج از کاتالوگ) — منطقی و امن
    '_default' => [
        'dir'      => 'ltr',
        'calendar' => 'gregorian',   // jalali | hijri | gregorian
        'digits'   => 'latin',       // persian | arabic | latin
    ],

    // کاتالوگ زبان‌های شناخته‌شده
    'fa' => ['native' => 'فارسی',   'dir' => 'rtl',  'calendar' => 'jalali',    'digits' => 'persian'],
    'ar' => ['native' => 'العربية',  'dir' => 'rtl',  'calendar' => 'hijri',     'digits' => 'arabic'],
    'en' => ['native' => 'English',  'dir' => 'ltr',  'calendar' => 'gregorian', 'digits' => 'latin'],
    'tr' => ['native' => 'Türkçe',   'dir' => 'ltr',  'calendar' => 'gregorian', 'digits' => 'latin'],
    // زبان جدید شناخته‌شده را اینجا اضافه کنید (مثلاً de/ru/…)
];
```

> `calendar` و `digits` از کاتالوگ می‌آیند (و برای ناشناخته‌ها `_default`). فقط `dir` در دیتابیس قابل تغییر است — چون تنها چیزی است که ادمین موقع افزودن زبانِ جدید معنا دارد (مثلاً `ur` با اینکه اردو است، RTL است و شاید در کاتالوگ نباشد).

---

## ۶) دیتابیس — جدول `languages`

```php
// database/migrations/2026_08_07_000001_create_languages_table.php
Schema::create('languages', function (Blueprint $table) {
    $table->id();
    $table->string('code', 8)->unique();            // 'fa' | 'en' | 'de' …
    $table->json('name');                            // نام ترجمه‌پذیر: ['fa'=>'فارسی','en'=>'Persian',…]
    $table->string('native_name');                   // نام به زبان خودش (fallback نمایش)
    $table->string('dir', 3)->default('ltr');        // 'rtl' | 'ltr'
    $table->boolean('is_active')->default(true);     // غیرفعال → از سوییچر حذف و در میدلور رد می‌شود
    $table->boolean('is_default')->default(false);   // فقط یک زبان پیش‌فرض
    $table->unsignedSmallInteger('sort_order')->default(0);
    $table->timestamps();

    $table->index(['is_active', 'is_default']);
});
```

**قوانین داده‌ای:**
- `code` یکتاست و فقط حروف a-z و `-` می‌پذیرد (`^[a-z]{2,8}$`).
- دقیقاً یک `is_default = true` (سیدر و کنترلر تضمین می‌کنند).
- حذف فیزیکی زبان توصیه نمی‌شود — فقط غیرفعال کنید (دیتای JSON مدل‌ها سالم می‌ماند).

---

## ۷) مدل `Language`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = ['code', 'name', 'native_name', 'dir', 'is_active', 'is_default', 'sort_order'];

    protected $casts = [
        'name'       => 'array',
        'is_active'  => 'boolean',
        'is_default' => 'boolean',
    ];

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->ordered();
    }

    /** نام زبان به زبانِ فعال سایت (fallback: فا → native_name) */
    public function localizedName(): string
    {
        $locale = app()->getLocale();

        return $this->name[$locale] ?? $this->name['fa'] ?? $this->native_name;
    }

    public function isRtl(): bool
    {
        return $this->dir === 'rtl';
    }

    public function calendar(): string
    {
        return config("languages.{$this->code}.calendar", config('languages._default.calendar'));
    }

    public function digitFamily(): string
    {
        return config("languages.{$this->code}.digits", config('languages._default.digits'));
    }
}
```

---

## ۸) سرویس `LocaleService` (منبع واحد حقیقت)

> الگوی کش دقیقاً مثل `SettingService` بهینه‌شده: **حافظه‌ی ریکوئست** (یک کوئری در هر ریکوئست) + **کش یکپارچه** بین ریکوئست‌ها + `flush()` روی هر تغییر.

```php
<?php

namespace App\Services;

use App\Models\Language;
use Illuminate\Support\Facades\Cache;

class LocaleService
{
    private const CACHE_ALL = 'shopora_languages_all';

    /** @var array<string, Language>|null حافظه‌ی ریکوئست */
    private static ?array $memory = null;

    /** همه‌ی زبان‌ها (فعال + غیرفعال) — یک کوئری + کش یکپارچه */
    public function all(): array
    {
        if (self::$memory !== null) {
            return self::$memory;
        }

        return self::$memory = Cache::rememberForever(self::CACHE_ALL, function () {
            return Language::ordered()->get()->keyBy('code')->all();
        });
    }

    /** فقط زبان‌های فعال */
    public function active(): array
    {
        return array_filter($this->all(), fn (Language $l) => $l->is_active);
    }

    public function codes(): array        { return array_keys($this->all()); }
    public function activeCodes(): array  { return array_keys($this->active()); }

    public function has(string $code): bool     { return isset($this->all()[$code]); }
    public function find(string $code): ?Language { return $this->all()[$code] ?? null; }

    public function isActive(string $code): bool
    {
        $lang = $this->all()[$code] ?? null;

        return $lang !== null && $lang->is_active;
    }

    /** زبان پیش‌فرض: is_default → اولین زبان فعال → کانفیگ app.locale */
    public function default(): ?Language
    {
        foreach ($this->all() as $lang) {
            if ($lang->is_default) {
                return $lang;
            }
        }

        $actives = $this->active();

        return $actives ? reset($actives) : null;
    }

    public function defaultCode(): string
    {
        return $this->default()?->code ?? config('app.locale', 'fa');
    }

    /** جهت صفحه برای یک زبان (از ردیف دیتابیس → کاتالوگ → _default) */
    public function dir(string $code): string
    {
        $lang = $this->all()[$code] ?? null;

        return $lang?->dir ?? config("languages.{$code}.dir", config('languages._default.dir', 'ltr'));
    }

    public function isRtl(string $code): bool { return $this->dir($code) === 'rtl'; }

    /** تقویم: jalali | hijri | gregorian (از کاتالوگ) */
    public function calendar(string $code): string
    {
        return config("languages.{$code}.calendar", config('languages._default.calendar', 'gregorian'));
    }

    public function isJalali(string $code): bool { return $this->calendar($code) === 'jalali'; }
    public function isHijri(string $code): bool  { return $this->calendar($code) === 'hijri'; }

    /** خانواده‌ی رقمی: persian | arabic | latin */
    public function digitFamily(string $code): string
    {
        return config("languages.{$code}.digits", config('languages._default.digits', 'latin'));
    }

    /** حالت تک‌زبانه: کمتر از ۲ زبان فعال → سوییچر مخفی و ?lang نادیده */
    public function singleLanguageMode(): bool
    {
        return count($this->active()) <= 1;
    }

    /** بعد از هر ذخیره/حذف زبان — کش و حافظه را پاک کن */
    public function flush(): void
    {
        self::$memory = null;
        Cache::forget(self::CACHE_ALL);
    }
}
```

**نکات مهم:**
- سرویس از `config('app.available_locales')` استفاده نمی‌کند — جدول languages منبع است (این کلید حذف شده).
- اگر جدول خالی باشد (پیش از سیدر)، `defaultCode()` به `config('app.locale')` برمی‌گردد و `active()` خالی است → سوییچر مخفی. پس **LanguageSeeder اجباری است** (بخش ۱۷).
- برای ثبت سرویس نیازی به bind نیست — کافیست `app(LocaleService::class)` یا یک facade/helper سبک (`locale()`) در `app/Helpers/` برای استفاده در ویوها بسازید.

---

## ۹) میدلور `SetLocale` — بازنویسی و زنجیره fallback

**منطق نهایی (جایگزین نسخه‌ی فعلی که از `config('app.available_locales')` می‌خواند):**

```
۰) اگر حالت تک‌زبانه (تعداد فعال ≤ ۱)  →  locale = defaultCode()   (درخواست ?lang نادیده)
۱) اگر ?lang=xx هست:
     - xx فعال باشد   → locale = xx + ذخیره در session (+ دیتابیس کاربر اگر لاگین است)
     - xx غیرفعال یا ناموجود باشد → ⛔ این مرحله رد می‌شود و زنجیره از مرحله‌ی بعد ادامه می‌یابد
۲) کاربر لاگین با users.locale فعال  → locale = آن
   (اگر کاربر locale غیرفعال داشته باشد → از مرحله‌ی بعدی می‌گذرد)
۳) session.locale فعال  → locale = آن
۴) در غیر این صورت → locale = defaultCode()  (پیش‌فرض از دیتابیس)
۵) app()->setLocale($locale) + view()->share('direction', rtl?) + share('currentLocale', $locale)
```

**چرا «ادامه‌ی زنجیره» به‌جای «اجبار به پیش‌فرض»؟** چون زبان غیرفعال یعنی ادمین آن را «برداشته» — کاربر نباید خطا ببیند و نباید بی‌مورد به زبانی دیگر پرت شود؛ به نزدیک‌ترین زبانِ معتبرِ خودش می‌ماند (مثلاً کاربری که انگلیسی مرور می‌کند با لینک کهنه‌ی `?lang=de` در انگلیسی می‌ماند). اگر سشن/کاربر هم معتبر نبود، نهایتاً به پیش‌فرض می‌افتد.

> ⚠️ **نسخهٔ فعلی (بعد از فاز ۶ — SEO):** کد زیر فقط «زنجیرهٔ پنل ادمین» را نشان می‌دهد. میدلور واقعی علاوه بر آن، **پیشوند URL سایت** (`/en/...`) را با اولویت اول از مسیر می‌خواند، `/fa` و زبان‌های غیرفعال را 301 به نسخهٔ بدون پیشوند می‌کند، و `?lang=` قدیمی روی سایت را 301 به نسخهٔ پیشونددار می‌فرستد. جزئیات کامل در فاز ۶.

**کد (زنجیرهٔ fallback — پنل ادمین):**

```php
<?php

namespace App\Http\Middleware;

use App\Services\LocaleService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locales = app(LocaleService::class);
        $default = $locales->defaultCode();

        // ۰) حالت تک‌زبانه — فقط پیش‌فرض
        if ($locales->singleLanguageMode()) {
            $locale = $default;
        }
        // ۱) پارامتر URL — فقط اگر فعال باشد
        elseif ($request->has('lang') && $locales->isActive($request->get('lang'))) {
            $locale = $request->get('lang');
            session(['locale' => $locale]);
            if ($request->user()) {
                $request->user()->update(['locale' => $locale]);
            }
        }
        // ۲) کاربر لاگین
        elseif ($request->user() && $locales->isActive((string) $request->user()->locale)) {
            $locale = $request->user()->locale;
            session(['locale' => $locale]);
        }
        // ۳) session
        elseif (session()->has('locale') && $locales->isActive((string) session('locale'))) {
            $locale = session('locale');
        }
        // ۴) پیش‌فرض
        else {
            $locale = $default;
            session(['locale' => $locale]);
        }

        app()->setLocale($locale);
        view()->share('direction', $locales->isRtl($locale) ? 'rtl' : 'ltr');
        view()->share('currentLocale', $locale);

        return $next($request);
    }
}
```

---

## ۱۰) سوییچرهای زبان و حالت تک‌زبانه

**سه سوییچر وجود دارد:** هدر سایت (`components/header.blade.php`)، هدر ادمین (`components/admin/header.blade.php`)، صفحه‌ی لاگین ادمین.

**الگوی مشترک (در هر سه):**

```blade
@php
    $locales = app(LocaleService::class);            // به‌جای config('app.available_locales')
    $active  = $locales->active();
@endphp

{{-- حالت تک‌زبانه → کل سوییچر رندر نمی‌شود --}}
@if (count($active) > 1)
    @foreach ($active as $code => $lang)
        {{-- دکمه/لینک با $lang->localizedName() — سایت: data-lang-select، ادمین: ?lang= --}}
    @endforeach
@endif
```

- **سایت (بعد از فاز ۶ — SEO):** دراپ‌داون سوییچر با لینک‌های `localizedUrl($code)` → نسخهٔ پیشونددار (`/en/...`؛ زبان پیش‌فرض بدون پیشوند). لینک‌های داخلی با `localizedRoute()`.
- **ادمین:** لینک `request()->fullUrlWithQuery(['lang' => $code])` (زنجیرهٔ قدیمی حفظ شده).
- **HTML اصلی:** `lang="{{ $currentLocale }}"` و `dir="{{ $direction }}"` — که از share های میدلور می‌آیند، خودکار درست می‌شوند.

---

## ۱۱) تاریخ و تقویم (`show_date`)

**قبل (هاردکد):** `in_array($locale, ['fa', 'ar'])` → شمسی.
**بعد (داده‌محور):** `LocaleService::calendar($locale)` → سه شاخه:
  - `jalali` (fa) → `jdate_format` — ارقام فارسی + تقویم شمسی
  - `hijri` (ar) → `hijri_date` (هِلپر `app/Helpers/hijri.php`) — تقویم اسلامیِ مدنی/جدولی با نام ماه عربی و ارقام عربی
  - `gregorian` → `Carbon::translatedFormat` به زبان محلی

```php
function show_date(string|int|DateTimeInterface|null $timestamp = null, string $format = 'Y/m/d H:i'): string
{
    $locale = app()->getLocale();
    // … (پارس timestamp به همان روش فعلی) …

    $calendar = locale()->calendar($locale);

    if ($calendar === 'jalali' && function_exists('jdate_format')) {
        return jdate_format($format, $ts);   // شمسی با ارقام فارسی
    }
    if ($calendar === 'hijri' && function_exists('hijri_date')) {
        return hijri_date($format, $ts);     // قمری با نام ماه عربی و ارقام عربی
    }

    return Carbon::createFromTimestamp($ts, 'Asia/Tehran')
        ->locale($locale ?: 'en')
        ->translatedFormat($format);
}
```

> نتیجه برای زبان جدید `de` (در کاتالوگ ناشناخته): `_default` → میلادی + لاتین + نام ماه انگلیسی (اگر فایل locale نباشد، Carbon به `en` fallback می‌کند) — بدون هیچ کد اضافه. اگر یک زبانِ قمریِ جدید بخواهید، کافی است `calendar => 'hijri'` در کاتالوگ ثبت کنید.

**الگوریتم قمری (`hijri_date`):** تقویم اسلامیِ مدنی (Islamic civil / tabular) با الگوریتم قطعی و بدون ICU — `1 محرم 1445 = 19 ژوئیهٔ 2023`. نمایش در `Asia/Tehran` مثل jdate.

**نکته‌ی منطقه زمانی:** دیتابیس همیشه میلادی/UTC است؛ نمایش در `Asia/Tehran`. `jdate_format` همیشه تهران است — این هماهنگی عمدی است (از قبل مستند شده).

---

## ۱۲) اعداد و ارقام (`localize-digits.js`)

فایل موجود (`resources/js/localize-digits.js`) قبلاً ساخته شده و از `lang` روی `<html>` می‌خواند:

| `<html lang>` | ارقام | جداکننده‌ی هزارگان |
|---|---|---|
| `data-digits` | ارقام | جداکننده‌ی هزارگان |
|---|---|---|
| `persian` (fa) | ۰۱۲۳۴۵۶۷۸۹ | `٬` |
| `arabic` (ar یا هر زبان عربی‌رقم) | ٠١٢٣٤٥٦٧٨٩ | `٬` |
| `latin` (en, tr, de, …) | 0123456789 | `,` + `٪` → `%` |

- **منبع واحد:** صفت `data-digits` روی `<html>` که سرور از `LocaleService::digitFamily($locale)` می‌سازد — JS فقط از آن می‌خواند (با fallback به `lang` برای صفحات قدیمی/استاتیک).
- روی همه‌ی گره‌های متنی اعمال می‌شود (قیمت، تعداد، موجودی، تاریخ، امتیاز، تایمر، بج سبد، صفحه‌بندی).
- به فرم‌ها (`input/select/textarea`) و کد (`script/style/pre/code`) دست نمی‌زند.
- `MutationObserver` محتوای داینامیک را در هر دو جهت تبدیل می‌کند.
- **زبان جدید با `digits` در کاتالوگ تعیین می‌شود** — بدون تغییر کد JS: `latin` در `_default`، و برای هر خانواده فقط یک ورودی در `config/languages.php` کافی است.

---

## ۱۳) کامپوننت‌های وابسته به تقویم

### ۱۳.۱) `jalali-datetime` (پیکر شمسی)
- **fa (isJalali):** input شمسی + `jalalidatepicker.min.js` + فیلد hidden میلادی → دیتابیس همیشه میلادی.
- **سایر (شامل ar که حالا قمری است):** `datetime-local` مستقیم — مقدار همان میلادیِ دیتابیس است، امن و بدون تبدیل.
- تغییر: به‌جای `in_array(app()->getLocale(), ['fa','ar'])` → `app(LocaleService::class)->isJalali(app()->getLocale())`.

### ۱۳.۲) `countdown-timer`
- مقدار اولیه‌ی `۰۰` بر اساس `isJalali()` سرویس (بعداً خود `localize-digits` ادامه‌ی شمارش را تبدیل می‌کند).

---

## ۱۴) دیتای ترجمه‌پذیر مدل‌ها (JSON columns)

**الگوی موجود (بدون تغییر لازم):** ستون‌های JSON (مثل `Product.name`, `Brand.title`) با accessor یا متد `trans()`:

```php
// الگوی یکتا در هر مدل (مثل Brand::trans)
public function trans(string $field): ?string
{
    $value = $this->$field;
    if (is_array($value) || is_object($value)) {
        return $value[app()->getLocale()] ?? $value['fa'] ?? null;
    }
    return $value;
}
```

**زبان جدید بدون ترجمه:** همه‌ی مقادیر به `fa` fallback می‌شوند تا ادمین/ادیتور آن‌ها را ترجمه کند. این رفتار مطلوب است و نباید شکسته شود.

---

## ۱۵) صفحه مدیریت زبان‌ها در پنل ادمین + RBAC

### ۱۵.۱) روت‌ها (داخل گروه محافظت‌شده)

```php
Route::resource('languages', LanguageController::class)->except('show');
```

### ۱۵.۲) درخت کانفیگ RBAC

```php
// config/permissions.php → گروه جدید
'languages' => [
    'title'  => 'permissions.groups.languages',
    'access' => [
        'index'   => 'permissions.access.languages.index',
        'create'  => 'permissions.access.languages.create',
        'store'   => 'permissions.access.languages.create',
        'edit'    => 'permissions.access.languages.edit',
        'update'  => 'permissions.access.languages.edit',
        'destroy' => 'permissions.access.languages.delete',
    ],
],
```

+ کلیدهای `permissions.php` در هر ۴ زبان + منوی سایدبار (فقط با پرمیشن دیده شود).

### ۱۵.۳) صفحه‌ها

- **index:** جدول همه‌ی زبان‌ها: کد، نام (ترجمه‌پذیر)، نام محلی، جهت (بج RTL/LTR)، **وضعیت فایل‌های ترجمه** (`is_dir(resource_path("lang/{$code}"))` → ✓/⚠)، سوییچ فعال/غیرفعال (AJAX یا فرم)، دکمه‌ی «پیش‌فرض کن» (فقط روی غیرپیش‌فرض)، **ترتیب با درگ‌اندروپ** (دستگیره‌ی کنار هر ردیف + دکمه‌ی «ذخیره ترتیب» → اکشن `reorder` با پرمیشن `admin.languages.reorder` = همان لیبل ویرایش؛ همه‌ی زبان‌ها باید ارسال شوند).
- **create/edit:** کد (فقط هنگام create)، نام در هر زبان (تکرار فیلد برای fa/en/…)، `native_name`، جهت (rtl/ltr)، فعال، پیش‌فرض. **فیلد ترتیب حذف شده** — زبان جدید همیشه به انتهای ترتیب اضافه می‌شود و سورت فقط از ایندکس با درگ‌اندروپ انجام می‌شود.
- **قوانین:** فقط یک `is_default`؛ نمی‌توان آخرین زبان فعال را غیرفعال کرد؛ کد یکتا و معتبر `^[a-z]{2,8}$`.
- **حالت تک‌زبانه:** یک سوییچ/نشانگر بالای صفحه: «با فعال‌بودن فقط ۱ زبان، سوییچر مخفی است».

### ۱۵.۴) کنترلر

```php
public function store(LanguageRequest $request)
{
    Language::create($request->validated());
    app(LocaleService::class)->flush();     // ⚠️ کش را پاک کن
    // …
}

public function update(LanguageRequest $request, Language $language)
{
    // اگر پیش‌فرض شد → بقیه unset شوند
    if ($request->boolean('is_default')) {
        Language::where('is_default', true)->update(['is_default' => false]);
    }
    $language->update($request->validated());
    app(LocaleService::class)->flush();
    // …
}
```

**اعتبارسنجی زبان پیش‌فرض:** اگر آخرین زبان فعال پیش‌فرض است، نمی‌توان آن را غیرفعال کرد (تا همیشه یک پیش‌فرض معتبر بماند).

---

## ۱۶) کامند `lang:new` و چرخه کامل افزودن زبان

### ۱۶.۱) کامند `php artisan lang:new de`

```php
// app/Console/Commands/LangNew.php
// کارها:
//  ۱. ساخت پوشه‌ی resources/lang/de
//  ۲. کپی فایل‌های ترجمه از زبان مبنا (پیش‌فرض fa، با --from=en) 
//  ۳. خروجی: «پوشه ساخته شد — فایل‌ها را ترجمه کنید»
```

> ترجمه‌ی رشته‌های UI را هیچ سیستمی خودکار تولید نمی‌کند؛ این کامند فقط اسکلت را می‌سازد. ترجمه‌ی ماشینی (DeepL/API) در مسیر آینده است.

### ۱۶.۲) چرخه‌ی کامل «ادمین زبان جدید اضافه می‌کند» — قدم‌به‌قدم

| # | قدم | کجا | نتیجه |
|---|---|---|---|
| 1 | فرم افزودن: کد `de` + نام‌ها + جهت ltr | پنل → Languages → Add | ردیف `languages` ساخته شد؛ `LocaleService::flush()` |
| 2 | (اختیاری) `php artisan lang:new de` | ترمینال | پوشه‌ی ترجمه‌ی UI ساخته شد |
| 3 | سوییچرها | خودکار | `de` در هدر سایت/ادمین/لاگین ظاهر شد (چون فعال است) |
| 4 | تاریخ/اعداد | خودکار | `de` در کاتالوگ نبود → `_default`: میلادی + لاتین + ltr |
| 5 | دیتای مدل‌ها | خودکار | همه‌ی مقادیر JSON به `fa` fallback می‌شوند تا ترجمه شوند |
| 6 | (اختیاری) کپی دیتا از زبان مبدأ | اکشن اختیاری | مقادیر fa به کلید de کپی می‌شوند (برای شروع ترجمه) |

### ۱۶.۳) چرخه‌ی «غیرفعال‌کردن زبان»

1. سوییچر → `de` حذف می‌شود (فقط فعال‌ها).
2. درخواست `?lang=de` → fallback بی‌صدا به پیش‌فرض.
3. کاربر با `users.locale = de` → fallback به پیش‌فرض.
4. دیتای موجود دست‌نخورده می‌ماند — فعال‌کردن دوباره همه‌چیز را برمی‌گرداند.

---

## ۱۷) سیدر `LanguageSeeder`

- `updateOrCreate` بر اساس `code` → چند بار اجرا امن است (idempotent).
- ۴ زبان فعلی را با نام ترجمه‌پذیر + native + جهت + ترتیب می‌سازد؛ `fa` پیش‌فرض.
- در انتها تضمین می‌کند **فقط یک `is_default`** هست.
- در `DatabaseSeeder` **اولین** سیدر اجرا شود (زیرا بقیه‌ی سیدرها ممکن است به نام زبان نیاز داشته باشند).

```php
$this->call([
    LanguageSeeder::class,
    AdminOwnerSeeder::class,
    // …
]);
```

---

## ۱۸) چک‌لیست امنیتی و درستی — باید و نباید

### ✅ باید

- [x] همه‌ی مصرف‌کننده‌ها فقط از `LocaleService` — کلیدهای `available_locales`/`rtl_locales` از `config/app.php` حذف شدند
- [x] fallback بی‌صدا برای زبان غیرفعال (هیچ error برای کاربر)
- [x] حالت تک‌زبانه: سوییچر مخفی + `?lang=` نادیده
- [x] دقیقاً یک `is_default` در همه‌ی مسیرها (سیدر + کنترلر)
- [x] کش یکپارچه + `flush()` روی هر تغییر زبان
- [x] اعتبارسنجی کد: یکتا + `^[a-z]{2,8}$`
- [x] ممانعت از غیرفعال‌کردن آخرین زبان فعالِ پیش‌فرض
- [x] پرمیشن RBAC برای همه‌ی اکشن‌های مدیریت زبان
- [x] دیتابیس همیشه میلادی؛ تبدیل فقط در لایه‌ی نمایش/ورودی
- [x] `localize-digits` به فرم‌ها دست نمی‌زند (داده‌ی ارسالی سالم)
- [x] نام زبان در UI از `localizedName()` با fallback

### ❌ نباید

- [x] ❌ خواندن مستقیم `config('app.available_locales')`/`rtl_locales` — کلیدها حذف شده‌اند؛ فقط `LocaleService`
- [ ] ❌ هاردکد `in_array($locale, ['fa','ar'])` برای تشخیص شمسی/اعداد — فقط `LocaleService`
- [ ] ❌ حذف فیزیکی زبان (به‌جایش غیرفعال کنید)
- [ ] ❌ ذخیره‌ی تاریخ شمسی در دیتابیس
- [ ] ❌ دو `is_default` هم‌زمان
- [ ] ❌ فراموش‌کردن `flush()` بعد از ذخیره/حذف زبان
- [ ] ❌ اجازه‌ی مدیریت زبان به کسی که پرمیشن `admin.languages.*` ندارد

---

## ۱۹) راهنمای انتقال به سایت دیگر (Porting Guide)

1. **کپی کد:** میگریشن `create_languages_table`، مدل `Language`، سرویس `LocaleService`، میدلور جدید `SetLocale`، سیدر `LanguageSeeder`، کامند `LangNew`، صفحه‌های مدیریت + کنترلر `LanguageController`.
2. **کاتالوگ:** `config/languages.php` را با زبان‌های همان سایت پر کنید (`_default` را نگه دارید).
3. **ترجمه‌ها:** برای هر زبان پوشه‌ی `resources/lang/{code}` بسازید (کامند `lang:new`).
4. **مصرف‌کننده‌ها:** طبق جدول بخش ۴ همه‌ی جاهایی که `config('app.available_locales')` یا `in_array(['fa','ar'])` می‌خوانند به `LocaleService` مهاجرت کنید (میدلور، هدرها، login، `show_date`، `jalali-datetime`، `countdown-timer`) — سپس کلیدهای منسوخ را از `config/app.php` حذف کنید.
5. **JS اعداد:** `localize-digits.js` را در باندل سایت و ادمین ایمپورت کنید (اگر پروژه جدید Vite دارد).
6. **RBAC:** گروه `languages` را به درخت پرمیشن همان پروژه اضافه کنید + لیبل‌ها.
7. **سیدر:** `LanguageSeeder` را اول اجرا کنید.
8. ⚠️ **میگریشن‌ها را اول اجرا کنید!** مصرف‌کننده‌ها (`locale()->active()` و `locale()->isRtl()`) کوئری دیتابیس می‌زنند — اگر جدول `languages` وجود نداشته باشد، تمام صفحات خطا می‌دهند (برخلاف کانفیگ قدیمی که هرگز خطا نمی‌داد).
9. **تست سریع:** بعد از افزودن زبان غیرفارسی، صفحه را با `?lang=de` باز کنید → تاریخ میلادی، ارقام لاتین، جهت ltr، fallback دیتا به fa.

---

## ۲۰) نقشه راه پیاده‌سازی — فازبندی ۰→۱۰۰

> هر فاز با یک **گیت اعتبارسنجی** تمام می‌شود. وضعیت: ☐ انجام نشده / ✅ انجام شده.

### فاز ۱ — زیرساخت: کاتالوگ + جدول + مدل + سرویس + سیدر (۰ → ۲۵٪) ✅ انجام شد

> اجرا شد: `config/languages.php` (کاتالوگ + `_default`)، میگریشن `create_languages_table` (اجرا شد ✅)، مدل `Language`، سرویس `LocaleService` (کش یکپارچه + حافظهٔ ریکوئست + flush)، هلپر `locale()` (ثبت در composer.json + dump-autoload)، سیدر `LanguageSeeder` (اجرا شد + در اول `DatabaseSeeder`).
> تست زنده tinker: activeCodes=fa,en,ar,tr ✓ | default=fa ✓ | `isJalali('de')`=false و `dir('de')`=ltr (fallback خودکار به `_default`) ✓ | `digitFamily('ar')`=arabic ✓ | `localizedName('en')`=انگلیسی ✓
> 🛠️ اصلاحات از ریویو: (۱) **سیدر واقعاً idempotent شد** — اگر ادمین پیش‌فرض را عوض کرده باشد، اجرای دوبارهٔ سیدر انتخاب او را به fa برنمی‌گرداند (`$hasDefault` + unset)؛ (۲) **`default()` دفاعی شد** — اول پیش‌فرضِ فعال، بعد اولین زبان فعال، بعد هر پیش‌فرض (دادهٔ خراب) → هرگز زبانِ غیرفعالِ پیش‌فرض برنمی‌گردد.
> ⚠️ نکته برای فاز ۵ (تست‌ها): `$memory` استاتیک در کل فرآیند PHPUnit زنده می‌ماند — در `setUp` تست‌ها باید `app(LocaleService::class)->flush()` صدا زده شود (RefreshDatabase آن را پاک نمی‌کند).

| # | کار | فایل | جزئیات کلیدی |
|---|---|---|---|
| 1 | کاتالوگ زبان‌ها | `config/languages.php` | `_default` + fa/ar/en/tr (dir, calendar, digits) |
| 2 | میگریشن `create_languages_table` | `2026_08_07_000001_*.php` | code unique, name json, native_name, dir, is_active, is_default, sort_order + index |
| 3 | مدل `Language` | `app/Models/Language.php` | casts, scopes ordered/active, `localizedName()`, `isRtl()`, `calendar()`, `digitFamily()` |
| 4 | سرویس `LocaleService` | `app/Services/LocaleService.php` | all/active/codes/isActive/default/dir/isJalali/digitFamily/singleLanguageMode/flush + کش یکپارچه |
| 5 | سیدر `LanguageSeeder` | `database/seeders/LanguageSeeder.php` | idempotent + فقط یک default |
| 6 | ثبت سیدر اول | `database/seeders/DatabaseSeeder.php` | اول لیست |
| 7 | هلپر `locale()` (اختیاری) | `app/Helpers/` + composer.json | دسترسی آسان در ویوها |

✅ گیت: `php -l` همه‌ی فایل‌ها + `php artisan migrate` + سیدر + `LocaleService::active()` در tinker → ۴ زبان

### فاز ۲ — مصرف‌کنندگان: میدلور + سوییچرها + login (۲۵ → ۵۰٪) ✅ انجام شد

> اجرا شد: `SetLocale` بازنویسی شد (حالت تک‌زبانه + fallback بی‌صدا با ادامه‌ی زنجیره + حذف `config('app.available_locales')`)، هر سه سوییچر (هدر سایت / هدر ادمین / لاگین) به `locale()->active()` منتقل شدند و در حالت تک‌زبانه مخفی می‌شوند، لیبل‌ها با `localizedName()` (نام به زبانِ فعال)، و layout های ادمین/سایت + صفحه‌ی 429 جهت را از سرویس می‌گیرند.
> تأیید زنده (curl+python): پیش‌فرض fa/rtl ✓ | `?lang=en` → en/ltr ✓ | `?lang=de` با سشن تازه → fa/rtl (fallback) ✓ | غیرفعال‌کردن en + `?lang=en` → fa ✓ | حالت تک‌زبانه (فقط fa) → سوییچر مخفی + `?lang=tr` نادیده ✓ (هم سایت و هم لاگین ادمین) | لاگین واقعی ادمین → هدر پنل ۴ زبان + `?lang=en` → en/ltr ✓ | همه‌ی ویوها با `view:cache` بدون خطا ✓
> 🎨 نکته‌ی طراحی (مطابق داکیومنت): نام زبان‌ها در سوییچر به زبانِ فعال نمایش داده می‌شوند (مثلاً «انگلیسی» در UI فارسی) — اگر نام بومی ترجیح داده می‌شود، کافیست `$lang->localizedName()` با `$lang->native_name` عوض شود.

| # | کار | فایل |
|---|---|---|
| 8 | بازنویسی `SetLocale` (تک‌زبانه + fallback بی‌صدا + حذف `config('app.available_locales')`) | `app/Http/Middleware/SetLocale.php` |
| 9 | سوییچر هدر سایت → `LocaleService::active()` + مخفی در تک‌زبانه | `components/header.blade.php` |
| 10 | سوییچر هدر ادمین + login → `LocaleService::active()` | `components/admin/header.blade.php` + `admin/auth/login.blade.php` |
| 11 | `html lang/dir` از سرویس (بررسی layout ها) | `layouts/site/app.blade.php` + `layouts/admin/app.blade.php` + `errors/429.blade.php` |

✅ گیت: تست دستی fa/en + غیرفعال‌کردن یک زبان → fallback + مخفی‌شدن از سوییچر

### فاز ۳ — تاریخ/اعداد داده‌محور (۵۰ → ۶۵٪) ✅ انجام شد

> اجرا شد: `show_date()` → `LocaleService::isJalali()`، کامپوننت‌های `jalali-datetime` و `countdown-timer` → `isJalali()` — هیچ هاردکد `in_array(['fa','ar'])` در PHP/Blade نمانده است. ضمناً `localize-digits.js` **داده‌محور شد**: صفت `data-digits` (persian/arabic/latin) از `LocaleService::digitFamily()` روی `<html>` در هر ۴ layout (سایت، ادمین، لاگین، 429) ست می‌شود و JS از آن می‌خواند (با fallback به lang) — ارقام عربی هم حالا به‌درستی پشتیبانی می‌شوند.
> تأیید زنده (tinker + مرورگر): `fa` → ۱۴۰۵/۰۵/۱۶ (شمسی) ✓ | `de` (زبان جدیدِ در کاتالوگ نبوده) → 2026/08/07 (میلادی/لاتین/ltr) با صفر کد ✓ | شبیه‌سازی زبان شمسی جدید `ur` (فقط افزودن به کاتالوگ) → isJalali=true + تاریخ شمسی ✓ | مرورگر: `de` → `data-digits="latin"` + سال 2026 در فوتر، `fa` → `data-digits="persian"` + سال ۱۴۰۵ ✓ | باندل Vite بازسازی شد ✓ | `view:cache` بدون خطا ✓

| # | کار | فایل |
|---|---|---|
| 12 | `show_date` → `LocaleService::isJalali()` | `app/Helpers/date.php` |
| 13 | `jalali-datetime` → `isJalali()` | `components/admin/jalali-datetime.blade.php` |
| 14 | `countdown-timer` → `isJalali()` | `components/countdown-timer.blade.php` |
| 15 | `localize-digits` داده‌محور با `data-digits` (persian/arabic/latin روی `<html>` + خواندن در JS) | `resources/js/localize-digits.js` + ۴ layout | ✅ انجام شد در فاز ۳ |

✅ گیت: افزودن زبان `de` → تاریخ میلادی، ارقام لاتین، پیکر میلادی — بدون تغییر کد

### فاز ۴ — مدیریت زبان‌ها در پنل + RBAC (۶۵ → ۸۵٪) ✅ انجام شد

> اجرا شد: `LanguageController` (CRUD کامل + اکشن‌های سریع `toggle` و `make-default` + حفاظت‌ها)، `LanguageRequest` (کد فقط POST + regex `^[a-z]{2,8}$` + نام ترجمه‌پذیر)، روت‌های محافظت‌شده (resource + دو PATCH)، گروه `languages` در درخت RBAC (۸ دسترسی: index/create/store/edit/update/toggle/make-default/destroy) + لیبل‌های ۴ زبان، ویوهای index/create/edit/_form (سوییچ فعال/غیرفعال + پیش‌فرض‌کن + بج وضعیت فایل‌های ترجمه + هشدار تک‌زبانه)، منوی سایدبار (دسکتاپ + موبایل) و ترجمه‌های ادمین.
> **محافظت‌ها:** (۱) آخرین زبان فعال را نمی‌توان غیرفعال/حذف کرد؛ (۲) زبان غیرفعال نمی‌تواند پیش‌فرض شود؛ (۳) زبان پیش‌فرض حذف نمی‌شود؛ (۴) `ensureActiveDefault()` — اگر پیش‌فرض غیرفعال/حذف شد، به اولین زبان فعال منتقل می‌شود؛ (۵) `flush()` در همه‌ی اکشن‌ها.
> **اصلاحات از ریویو (۴ مورد):** (۱) 🐛 **باگ بحرانی آپدیت** — فیلد `code` در فرم ویرایش `disabled` بود و سابمیت نمی‌شد ولی ریکوئست آن را `required` کرده بود → هر ویرایشی شکست می‌خورد؛ با `Rule::requiredIf($this->isMethod('post'))` رفع شد (کنترلر در update کد را unset می‌کند). (۲) **ناهماهنگی پرمیشن دکمه‌ها** — دکمه‌های toggle/make-default به `canAccess('admin.languages.edit')` گیت شده بودند ولی روت‌ها `admin.languages.toggle`/`make-default` را چک می‌کردند؛ هر دکمه به پرمیشن خودش وصل شد. (۳) **پارامتر مرده** `$except` در `ensureActiveDefault()` حذف شد. (۴) **نام‌های خالی** با `array_filter` در `normalizedData()` حذف می‌شوند تا `localizedName()` به fallback بیفتد.
> **تأیید زنده (مرورگر واقعی با CSRF):** لاگین admin → index ۴ ردیف + سوییچ + پیش‌فرض‌کن + دکمهٔ ساخت ✓ | create با payload درست (name[fa]... جدا) → ردیف ساخته شد ✓ | آپدیت بدون فیلد code → موفق (native_name عوض شد و برگشت) ✓ | toggle de → غیرفعال شد ✓ | make-default ru → ru پیش‌فرض شد و fa از پیش‌فرض خارج شد ✓ | destroy ru (پیش‌فرض) → بلاک شد با پیام ✓ | destroy de → حذف شد ✓ | کاربر بدون پرمیشن `admin.languages.index` → **403** + لینک سایدبار مخفی ✓ | دیتابیس به حالت اولیه برگشت (۴ زبان، fa پیش‌فرض) ✓ | pint + `view:cache` بدون خطا ✓
> 🆕 **به‌روزرسانی بعدی (ترتیب درگ‌اندروپ):** فیلد `sort_order` از فرم ساخت/ویرایش حذف شد — زبان جدید همیشه `max(sort_order)+1` می‌گیرد. در ایندکس، ردیف‌ها با دستگیره قابل درگ‌اندروپ‌اند و دکمه‌ی «ذخیره ترتیب» بعد از تغییر ظاهر می‌شود (fetch + JSON به اکشن `admin.languages.reorder`؛ پرمیشن `reorder` با لیبل ویرایش در درخت + سینک با AdminOwnerSeeder؛ لیست باید همه‌ی زبان‌ها را داشته باشد وگرنه 422).

| # | کار | فایل |
|---|---|---|
| 16 | `LanguageController` + `LanguageRequest` | کنترلر + فرم‌ریکوئست |
| 17 | روت‌های `admin.languages.*` (inside protected group) | `routes/admin.php` |
| 18 | گروه `languages` در درخت RBAC + لیبل‌های ۴ زبان | `config/permissions.php` + `lang/*/permissions.php` |
| 19 | ویوها: index + create/edit + فرم | `resources/views/admin/languages/*` |
| 20 | منوی سایدبار + ترجمه‌های ادمین | sidebar × ۲ + `lang/*/admin.php` |
| 21 | `flush()` در همه‌ی اکشن‌ها + محافظت آخرین زبان فعال | کنترلر |

✅ گیت: افزودن/غیرفعال‌کردن/پیش‌فرض‌کردن از پنل + گارد پرمیشن + آپدیت با فرم واقعی مرورگر — همه سبز شد

### فاز ۵ — کامند + تست‌ها + نهایی‌سازی (۸۵ → ۱۰۰٪) ✅ انجام شد

> اجرا شد: کامند `lang:new {code} {--from=fa}` (اعتبارسنجی `^[a-z]{2,8}$` + ساخت پوشه + کپی فایل‌های php از زبان مبنا + هشدار اگر پوشه موجود است + **عدم ساخت پوشهٔ خالی** اگر منبع فایل ترجمه نداشته باشد) + ۴ فایل تست Feature:
> - `LocaleServiceTest` (۱۰ تست): all/active/default/fallback وقتی پیش‌فرض غیرفعال است/جهت از دیتابیس و کاتالوگ/تقویم و ارقام/flush/single-language/localizedName
> - `LanguageCrudTest` (۱۵ تست): ایندکس، ریدایرکت مهمان، create، store + اعتبارسنجی کد/تکراری، **آپدیت بدون فیلد code (رجریشن باگ بحرانی فاز ۴)**، پیش‌فرض‌کردن، **غیرفعال‌کردنِ پیش‌فرض از update → انتقال خودکار پیش‌فرض**، toggle، محافظت آخرین زبان فعال، زبان غیرفعال نمی‌تواند پیش‌فرض شود، حذف پیش‌فرض بلاک می‌شود، حذف زبان عادی موفق، 403 بدون پرمیشن
> - `SetLocaleFallbackTest` (۱۰ تست): زنجیرهٔ fallback کامل از طریق روت واقعی `home` — پیش‌فرض، `?lang`، زبان ناموجود، زبان غیرفعال با حفظ سشن معتبر قبلی، تک‌زبانه نادیده‌گرفتن `?lang`، سشن، اولویت locale کاربر بر سشن، locale غیرفعال کاربر، جهت rtl/ltr، setLocale اپ
> - `LangNewCommandTest` (۶ تست): ساخت/کپی از منبع دلخواه/کد نامعتبر/منبع ناموجود/پوشهٔ موجود/پوشهٔ خالی
> **نکات ریویو اعمال‌شده:** (۱) کامند اگر منبع فایل *.php نداشته باشد، پوشهٔ خالی را برمی‌گرداند و با خطا خارج می‌شود؛ (۲) تستِ «غیرفعال‌کردنِ پیش‌فرض از مسیر update → ensureActiveDefault» اضافه شد؛ (۳) تست خود کامند هم نوشته شد.
> **نتیجهٔ نهایی:** ۹۵ تست کل پروژه سبز (۳۲۱ assertion) — از جمله همهٔ تست‌های قبلی (RBAC، تنظیمات، کاربران، محصولات) بدون هیچ شکستی. pint روی همهٔ فایل‌های جدید تمیز.

| # | کار | فایل |
|---|---|---|
| 22 | کامند `lang:new {code}` | `app/Console/Commands/LangNew.php` |
| 23 | تست‌های Feature: `LocaleServiceTest` + `LanguageCrudTest` + `SetLocaleFallbackTest` + `LangNewCommandTest` | `tests/Feature/*` |
| 24 | pint + تست کامل + اعتبارسنجی مرورگر fa/en/de | — |
| 25 | به‌روزرسانی این سند (تیک چک‌لیست‌ها) | `documents/LOCALIZATION_MODULE_REPORT.md` |

✅ گیت نهایی: همه‌ی تست‌ها سبز + سناریوی کامل افزودن زبان تست شده + سند به‌روز — **فاز ۵ کامل شد ✅**

### فاز ۶ — SEO زبان‌ها: پیشوند URL + hreflang + canonical + og:locale (۱۰۰٪ → 🏁) ✅ انجام شد

> اجرا شد — هدف: هر زبان یک URL واقعی و مستقل داشته باشد (برای گوگل/اشتراک‌گذاری) به‌جای کوئری مشترک `?lang=`:
>
> **۱) روت‌ها (`routes/site.php`):** هر روت سایت **دو بار** ثبت می‌شود — بدون پیشوند (canonical، زبان پیش‌فرض) + کپی پیشونددار با نام `locale.*` و constraint `where(['locale' => $pattern])` که از کاتالوگ کانفیگ + کدهای دیتابیس ساخته می‌شود (طول‌نزولی). ⚠️ پارامتر اختیاری در ابتدای URI (`{locale?}/products`) توسط کامپایلر Laravel الزامی می‌شود → کپی جدا ثبت شد.
> **۲) میدلور `SetLocale`:** (در گروه وب قبل از resolve روت اجرا می‌شود → پیشوند از `$request->path()` خوانده می‌شود) — `{locale}/...` فعال → زبان از پیشوند؛ `/fa/...` (پیش‌فرض پیشونددار) یا زبان غیرفعال → **301** به نسخهٔ بدون پیشوند (کوئری‌استرینگ حفظ می‌شود)؛ کد نامعتبر → 404؛ `?lang=` قدیمی روی سایتِ بدون پیشوند → **301 به نسخهٔ پیشونددار** (سازگاری با عقب برای بوکمارک‌های قدیمی)؛ پنل ادمین همان زنجیرهٔ قبلی را حفظ می‌کند.
> **۳) هلپرها (`app/Helpers/locale.php`):** `localizedRoute(name, params, code)` (زبان پیش‌فرض بدون پیشوند، بقیه → `locale.*`)، `localizedUrl(code)` (همان مسیرِ جاری برای hreflang/سوییچر — نام `locale.*` را به پایه برمی‌گرداند)، `ogLocale(code)` (`fa_IR`/`en_US`/`ar_SA`/`tr_TR` با fallback معتبر `de_DE`).
> **۴) لینک‌های داخلی:** هدر/فوتر/کارت محصول/کارت دسته/صفحه‌های محصول → `localizedRoute()` تا در زبان‌های غیرپیش‌فرض درست بمانند.
> **۵) متا (`layouts/site/app.blade.php`):** `canonical` (self-referencing — روش توصیه‌شدهٔ گوگل)، `hreflang` برای هر زبان فعال + `x-default` → نسخهٔ بدون پیشوند، `og:locale` + `og:locale:alternate`.
> **۶) سوییچر سایت:** دراپ‌داون به لینک‌های پیشونددار تبدیل شد.
>
> **تست‌ها (۲۱ تست جدید/به‌روز):** `SeoLocalizationTest` (۱۶ تست): سرو شدن زبان از پیشوند، 301 پیش‌فرض/غیرفعال (صفحهٔ عمیق + **حفظ کوئری‌استرینگ**)، 404 کد نامعتبر، hreflang (حضور + **شمارش دقیق** ۴ زبان + x-default)، canonical، og:locale، هلپرها، **گارد دوقلوی روت‌ها** (هر روت سایت باید `locale.*` داشته باشد و برعکس — از فراموشی روتِ جدید جلوگیری می‌کند)، `?lang=` قدیمی. + بازنویسی `SetLocaleFallbackTest` (سایت: پیشوند تعیین‌کننده است؛ ادمین: زنجیرهٔ قدیمی).
>
> **یافته‌های ریویو و رفع:** (۱) تست کوئری‌استرینگ باید ترتیب‌-مستقل باشد (Symfony کوئری را نرمال‌سازی/مرتب می‌کند)؛ (۲) شمارش hreflang = زبان‌های فعال + x-default؛ (۳) روت‌های `ignition.*` از گارد مستثنا شدند؛ (۴) صفحه‌های دارای مدل-بایندینگ وقتی رکورد وجود نداشته باشد قبل از میدلور 404 می‌دهند (میدلور در گروه وب بعد از `SubstituteBindings` است — برای URLهای معتبر 301 درست کار می‌کند؛ این رفتار برای slug ناموجود درست است).
>
> **نتیجه:** **۱۱۶ تست کل پروژه سبز (۴۰۳ assertion)** — هیچ تست قبلی نشکست. pint تمیز.

| # | کار | فایل |
|---|---|---|
| 26 | دو ثبت روت (canonical + `locale.*`) با constraint امن | `routes/site.php` |
| 27 | پیشوندخوانی از path + 301 پیش‌فرض/غیرفعال/`?lang=` قدیمی | `app/Http/Middleware/SetLocale.php` |
| 28 | هلپرهای `localizedRoute`/`localizedUrl`/`ogLocale` | `app/Helpers/locale.php` |
| 29 | canonical + hreflang + x-default + og:locale | `layouts/site/app.blade.php` |
| 30 | لینک‌های داخلی و سوییچر → پیشونددار | هدر/فوتر/کارت‌ها/صفحات محصول |
| 31 | `SeoLocalizationTest` (۱۶ تست) + بازنویسی `SetLocaleFallbackTest` | `tests/Feature/*` |

✅ گیت: همهٔ سناریوهای پیشوند/ریدایرکت/متا در مرورگر + تست‌ها سبز + pint تمیز

---

*پایان سند. وضعیت: **کامل — هر ۶ فاز انجام شد ✅** — فاز ۶: SEO زبان‌ها (پیشوند URL + hreflang + canonical + og:locale؛ **۱۱۶ تست سبز / ۴۰۳ assertion**). فاز ۷: پاکسازی — کلیدهای منسوخ `available_locales`/`rtl_locales` از `config/app.php` و فایل مرجع `config/app.locales.reference.php` حذف شدند (هیچ مصرف‌کننده‌ای باقی نمانده). این بلوپرینت آمادهٔ استفادهٔ مجدد روی پروژهٔ دیگر است.*
