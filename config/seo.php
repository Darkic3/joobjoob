<?php

/*
|--------------------------------------------------------------------------
| منبع واحد سیاست‌های SEO
|--------------------------------------------------------------------------
| این فایل «سیاست ثابت» را نگه می‌دارد: قالب عنوان، لیست روت‌های نوایندکس،
| قواعد پارامتر query و سیاست ترجمهٔ ناقص.
|
| مقادیر قابل ویرایش توسط ادمین در جدول settings زندگی می‌کنند و با
| `setting('seo_...', config('seo....'))` خوانده می‌شوند — یعنی مقدار پیش‌فرض
| هر تنظیم همین‌جاست و ادمین می‌تواند بازنویسی‌اش کند.
|
| ⚠️ چرا لیست‌ها اینجا و نه در دیتابیس: این‌ها قراردادِ معماری‌اند (کدام روت
| خصوصی است؟) و نباید با یک کلیک ادمین کل سایت را از ایندکس خارج کند.
| تغییرشان یک تصمیم کدنویسی است، نه تنظیمات روزمره.
*/

return [

    // ==================== قالب‌ها ====================
    // {title} و {site_name} جایگزین می‌شوند.
    // اگر عنوان از قبل نام سایت را داشته باشد، پسوند دوباره اضافه نمی‌شود.
    'title_template' => '{title} | {site_name}',

    // قالب عنوان برای صفحاتی که عنوان اختصاصی ندارند (مثل فهرست‌ها)
    // {item} = نام موجودیت/صفحه، {site_name} = نام سایت
    'default_robots' => 'index, follow',

    // ==================== مقادیر پیش‌فرض متا ====================
    'default_description' => '',
    'default_keywords' => '',
    'default_og_type' => 'website',
    'default_twitter_card' => 'summary_large_image',

    // تصویر پیش‌فرض OG — مسیر نسبی یا URL کامل. null یعنی «تگ را نزن».
    'default_og_image' => null,

    // ==================== Structured Data ====================
    // ارز اعلام‌شدهٔ قیمت در JSON-LD.
    // قیمت‌های دیتابیس تومانی‌اند؛ IRR = تومان × ۱۰ (معادل رسمی ریال).
    // گزینهٔ دیگر: 'IRT' که قیمت را بدون ضریب اعلام می‌کند.
    'schema_currency' => 'IRR',
    'schema_currency_multiplier' => ['IRR' => 10, 'IRT' => 1],

    // ==================== SEO چندزبانه ====================
    // سیاست برخورد با صفحه‌ای که ترجمهٔ زبان جاری را ندارد.
    // trans() به fa فلاوبک می‌کند ⇒ محتوای فارسی زیر hreflang انگلیسی = محتوای تکراری.
    //   noindex              → صفحه قابل مشاهده ولی noindex, follow و خارج از sitemap
    //   canonical_to_default → canonical به نسخهٔ زبان پیش‌فرض
    //   allow                → رفتار بدون مداخله (همه ایندکس شوند)
    'missing_translation_policy' => 'noindex',

    // فیلدهای ترجمه‌پذیر برای تشخیص «ترجمه دارد/ندارد»
    'translatable_fields' => ['name', 'title'],

    // ==================== سیاست ایندکس‌پذیری ====================

    // روت‌هایی که هرگز نباید ایندکس شوند (بدون پیشوند locale.).
    // دلیل هر گروه:
    //   - فنی/عملیاتی: خروجی ماشینی، بدون ارزش جستجو
    //   - سبد/تسویه: محتوای سشن‌محور و خصوصی
    //   - حساب کاربری: دادهٔ شخصی کاربر (حریم خصوصی)
    //   - احراز هویت: فرم بدون محتوا + جلوگیری از ایندکس شدن صفحات ورود
    //   - AJAX/فرم: پاسخ JSON یا پارشال، نه صفحه
    'noindex_route_names' => [
        // فنی و عملیاتی
        'health',
        'seed',
        'robots',
        'sitemap',
        'sitemap.child',
        'sitemap.chunk',
        'secret.migrate',
        'secret.seed',
        'secret.clear-cache',
        'secret.fresh',

        // سبد خرید
        'cart.index',
        'cart.add',
        'cart.update',
        'cart.remove',
        'cart.coupon',

        // تسویه حساب
        'checkout.index',
        'checkout.address',
        'checkout.cities',
        'checkout.shipments',
        'checkout.set-shipment',
        'checkout.address-price',
        'checkout.payment',
        'checkout.apply-discount',
        'checkout.remove-discount',
        'checkout.validate-stock',
        'checkout.order-price',
        'checkout.create',
        'checkout.success',
        'checkout.failed',
        'checkout.finish.zarin-pal',
        'checkout.finish.saman-bank',
        'checkout.finish.sadad-bank',
        'checkout.finish.zibal-bank',

        // احراز هویت
        'login',
        'login.verify',
        'login.otp',
        'login.otp.check',
        'login.otp.send',
        'login.otp.send-ajax',
        'login.otp.resend',
        'login.otp.verify',
        'logout',

        // حساب کاربری
        'dashboard',
        'dashboard.email.update',
        'dashboard.email.destroy',
        'dashboard.newsletter.toggle',
        'dashboard.orders',
        'dashboard.order-detail',
        'dashboard.order-receipt.update',
        'dashboard.order-factor',
        'dashboard.wishlist',
        'dashboard.addresses',
        'dashboard.addresses.create',
        'dashboard.addresses.update',
        'dashboard.addresses.delete',
        'dashboard.addresses.cities',
        'dashboard.notifications',
        'dashboard.notification-go',
        'dashboard.notification-detail',

        // علاقه‌مندی (سشن‌محور)
        'wishlist.index',
        'wishlist.toggle',

        // API سبک
        'api.notifications.unread-count',
        'api.notifications.mark-read',
        'api.notifications.mark-all-read',

        // فرم‌ها و پاسخ‌های پارشال
        'site.contact.send',
        'newsletter.subscribe',
        'products.reviews.store',
        'products.reviews.reply',
        'products.reviews.vote',
        'products.quick-view',
    ],

    // روت‌هایی که با پیشوند نامه مطابقت دارند (مثل checkout.finish.*)
    'noindex_route_patterns' => [
        'checkout.*',
        'secret.*',
    ],

    // پارامترهای query که وجودشان صفحه را نوایندکس می‌کند.
    // دلیل: نتیجهٔ جستجوی داخلی محتوای یکتا نیست و ارزش ایندکس ندارد.
    // فیلترها/مرتب‌سازی عمداً اینجا نیستند — آن‌ها با canonical به صفحهٔ تمیز
    // ادغام می‌شوند (noindex + canonical با هم متناقض‌اند).
    'noindex_query_params' => ['q', 'search'],

    // پارامترهایی که باید در canonical بمانند (صفحه‌بندی = self-canonical)
    'canonical_keep_query_params' => ['page'],

    // پارامترهایی که در canonical حذف می‌شوند (فیلتر و مرتب‌سازی → صفحهٔ تمیز)
    'canonical_strip_query_params' => [
        'sort', 'category', 'brand', 'min_price', 'max_price',
        'q', 'search', 'view', 'layout', 'page_size',
    ],

    // مسیرهایی که باید از robots.txt مسدود شوند (فاز ۴) — فقط مسیر، بدون دامنه
    'disallow_paths' => [
        '/admin',
        '/cart',
        '/checkout',
        '/account',
        '/login',
        '/wishlist',
        '/health',
        '/seed',
        '/api/',
        '/*?q=',
        '/*?search=',
    ],

    // کوئری‌های sitemap و اولویت/تناوب پیش‌فرض هر نوع صفحه (فاز ۴)
    'sitemap_priority' => [
        'home' => 1.0,
        'products.index' => 0.9,
        'products.category' => 0.8,
        'products.show' => 0.8,
        'blogs.index' => 0.7,
        'blogs.category' => 0.6,
        'blogs.show' => 0.7,
        'site.page' => 0.5,
    ],
    'sitemap_changefreq' => [
        'home' => 'daily',
        'products.index' => 'daily',
        'products.category' => 'weekly',
        'products.show' => 'weekly',
        'blogs.index' => 'weekly',
        'blogs.category' => 'weekly',
        'blogs.show' => 'monthly',
        'site.page' => 'monthly',
    ],

    // تعداد URL در هر فایل sitemap فرزند (فاز ۴)
    'sitemap_chunk_size' => 5000,

    // ساعت اعتبار کش sitemap (فاز ۴)
    'sitemap_cache_hours' => 6,

    // ==================== امنیت هدر ====================
    // نرمال‌سازی دامنه (https و www) — پیش‌فرض خاموش.
    // دلیل: دامنهٔ پروداکشن در .env ثبت نشده؛ روشن‌کردن با مقدار غلط،
    // کل سایت را به دامنهٔ اشتباه ۳۰۱ می‌کند.
    'enforce_canonical_host' => false,

    /*
    |--------------------------------------------------------------------------
    | ⚠️ کلیدهای Legacy — فقط برای کد مرده‌ای که تا فاز ۶ حذف نشده است
    | (MetaService / SeoService / کامپوننت SeoHead قدیمی). در فاز ۶ حذف می‌شوند.
    |--------------------------------------------------------------------------
    */
    'default_title' => env('APP_NAME', 'Joobjoob'),
    'default_image' => null,
    'default_og_site_name' => env('APP_NAME', 'Joobjoob'),
    'default_twitter_site' => null,
    'twitter_site' => null,
    'twitter_creator' => null,
];
