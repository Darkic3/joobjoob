<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\Site\BlogController;
use App\Http\Controllers\Site\CheckoutController;
use App\Http\Controllers\Site\CartController;
use App\Http\Controllers\Site\DashboardController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\OtpController;
use App\Http\Controllers\Site\PageController;
use App\Http\Controllers\Site\ProductController;
use App\Http\Controllers\Site\NotificationController;
use App\Http\Controllers\Site\SiteAuthController;
use App\Http\Controllers\Site\WishlistController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SitemapController;
use App\Services\Seo\SitemapBuilder;


Route::get('/migrate-{secret}', function (string $secret) {
    $allowedSecret = '123';

    if ($secret !== $allowedSecret) {
        abort(403, 'Unauthorized Access');
    }

    try {
        Artisan::call('migrate', ['--force' => true]);
        $output = Artisan::output();

        return response()->make('<pre style="background:#f4f4f4; padding:20px; border-radius:8px; font-family:monospace;">' . $output . '</pre>', 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    } catch (\Exception $e) {
        return response()->make('<pre style="background:#ffebee; color:#c62828; padding:20px; border-radius:8px; font-family:monospace;">Migration Failed: ' . $e->getMessage() . '</pre>', 500, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
})->name('secret.migrate');
// ==============================================================================

Route::get('/seed-{secret}', function (string $secret) {
    $allowedSecret = '123';

    if ($secret !== $allowedSecret) {
        abort(403, 'Unauthorized Access');
    }

    try {
        Artisan::call('db:seed', ['--force' => true]);
        $output = Artisan::output();

        return response()->make(
            '<h3 style="color: #2e7d32;">✅ Database Seeding Successful</h3>' .
            '<pre style="background:#f4f4f4; padding:20px; border-radius:8px; font-family:monospace;">' . htmlspecialchars($output) . '</pre>',
            200,
            ['Content-Type' => 'text/html; charset=UTF-8']
        );
    } catch (\Exception $e) {
        return response()->make(
            '<h3 style="color: #c62828;">❌ Database Seeding Failed</h3>' .
            '<pre style="background:#ffebee; color:#c62828; padding:20px; border-radius:8px; font-family:monospace;">' . htmlspecialchars($e->getMessage()) . '</pre>',
            500,
            ['Content-Type' => 'text/html; charset=UTF-8']
        );
    }
})->name('secret.seed');
// ==============================================================================


// ==============================================================================
// ⚠️ Cache
// ==============================================================================
Route::get('/clear-all-cache-{secret}', function (string $secret) {
    $allowedSecret = '1';

    if ($secret !== $allowedSecret) {
        abort(403, 'دسترسی غیرمجاز');
    }

    try {
        $results = [];

        // اجرای دستورات آرتیسان و بررسی کد خروجی (0 به معنی موفقیت است)
        $results[] = "1. route:clear    => " . (Artisan::call('route:clear') === 0 ? '✅ موفق' : '❌ خطا');
        $results[] = "2. config:clear   => " . (Artisan::call('config:clear') === 0 ? '✅ موفق' : '❌ خطا');
        $results[] = "3. view:clear     => " . (Artisan::call('view:clear') === 0 ? '✅ موفق' : '❌ خطا');
        $results[] = "4. cache:clear    => " . (Artisan::call('cache:clear') === 0 ? '✅ موفق' : '❌ خطا');
        $results[] = "5. optimize:clear => " . (Artisan::call('optimize:clear') === 0 ? '✅ موفق' : '❌ خطا');

        $output = implode("\n", $results);

        return response()->make(
            '<h3 style="color: #2e7d32; font-family: Tahoma, sans-serif;">✅ پاک‌سازی کش با موفقیت انجام شد!</h3>' .
            '<pre style="background:#f4f4f4; padding:20px; border-radius:8px; font-family:monospace; direction: ltr; text-align: left; border: 1px solid #ccc;">' . htmlspecialchars($output) . '</pre>' .
            '<p style="color: #d32f2f; font-weight: bold; font-family: Tahoma, sans-serif; margin-top: 20px;">⚠️ هشدار امنیتی: لطفاً بلافاصله پس از استفاده، این کد را از فایل routes/web.php حذف یا کامنت کنید.</p>',
            200,
            ['Content-Type' => 'text/html; charset=UTF-8']
        );
    } catch (\Exception $e) {
        return response()->make(
            '<h3 style="color: #c62828; font-family: Tahoma, sans-serif;">❌ خطا در پاک‌سازی کش</h3>' .
            '<pre style="background:#ffebee; color:#c62828; padding:20px; border-radius:8px; font-family:monospace; direction: ltr; text-align: left;">' . htmlspecialchars($e->getMessage()) . '</pre>',
            500,
            ['Content-Type' => 'text/html; charset=UTF-8']
        );
    }
})->name('secret.clear-cache');


// ==============================================================================
// ⚠️  migrate:fresh
// ==============================================================================
Route::get('/fresh-{secret}', function (string $secret) {
    $allowedSecret = '123'; // ⚠️ حتماً تغییر دهید

    if ($secret !== $allowedSecret) {
        abort(403, 'Unauthorized Access');
    }

    try {
        // اجرای migrate:fresh که اول همه جداول را دراپ می‌کند و بعد می‌سازد
        Artisan::call('migrate:fresh', ['--force' => true]);
        $output = Artisan::output();

        return response()->make(
            '<h3 style="color: #e65100;">⚠️ migrate:fresh Executed (All tables dropped and recreated)</h3>' .
            '<pre style="background:#fff3e0; padding:20px; border-radius:8px; font-family:monospace; border: 1px solid #ffb74d;">' . htmlspecialchars($output) . '</pre>',
            200,
            ['Content-Type' => 'text/html; charset=UTF-8']
        );
    } catch (\Exception $e) {
        return response()->make(
            '<pre style="background:#ffebee; color:#c62828; padding:20px; border-radius:8px; font-family:monospace;">Migration Fresh Failed: ' . htmlspecialchars($e->getMessage()) . '</pre>',
            500,
            ['Content-Type' => 'text/html; charset=UTF-8']
        );
    }
})->name('secret.fresh');
// ==============================================================================


// ── SEO زبان: دو مجموعه روت ──────────────────────────────────────────────────
//   ۱) بدون پیشوند (canonical) — زبان پیش‌فرض؛ نام روت: home, products.index, …
//   ۲) با پیشوند {locale} — سایر زبان‌ها؛ نام روت: locale.home, locale.products.index, …
//
//   /            → زبان پیش‌فرض (fa)
//   /en          → زبان en (SetLocale آن را اعمال می‌کند)
//   /fa          → 301 به / (پیش‌فرض پیشوند نمی‌گیرد — در SetLocale)
//   /xx (نامعتبر) → 404 (در constraint نیست)

// الگوی کد زبان برای constraint — امن: فقط کدهای واقعی (کاتالوگ + دیتابیس)
$catalogCodes = array_diff(array_keys(config('languages', [])), ['_default']);

try {
    $codes = array_merge($catalogCodes, locale()->codes());
} catch (\Throwable $e) {
    // جدول languages هنوز موجود نیست (پیش از میگریشن) → فقط کاتالوگ
    $codes = $catalogCodes;
}

$codes = array_values(array_unique($codes));
usort($codes, fn (string $a, string $b) => strlen($b) <=> strlen($a));
$localePattern = '^(?:' . implode('|', array_map(fn (string $c) => preg_quote($c, '/'), $codes)) . ')$';

// بدنه‌ی روت‌های سایت — یک بار برای بدون پیشوند، یک بار برای پیشونددار
$siteRoutes = function () {

    // مانیتورینگ — endpoint عمومی سبک
    Route::get('/health', [HealthController::class, 'index'])->name('health');

    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/seed', [HomeController::class, 'seed'])->name('seed');

    // محصولات
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');
    Route::post('/products/{product:slug}/reviews', [ProductController::class, 'storeReview'])
        ->middleware('throttle:reviews')
        ->name('products.reviews.store');
    Route::post('/products/{product:slug}/reviews/{review}/reply', [ProductController::class, 'storeReply'])
        ->middleware('throttle:reviews')
        ->name('products.reviews.reply');
    Route::post('/products/{product:slug}/reviews/{review}/vote', [ProductController::class, 'voteReview'])
        ->middleware(['auth', 'throttle:reviews'])
        ->name('products.reviews.vote');
    Route::get('/products/{product:slug}/quick-view', [ProductController::class, 'quickView'])->name('products.quick-view');
    Route::get('/product-categories/{category:slug}', [ProductController::class, 'category'])->name('products.category');

    // علاقه‌مندی‌ها (سشن — بدون نیاز به لاگین)
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/toggle/{product:slug}', [WishlistController::class, 'toggle'])
        ->middleware('throttle:wishlist')
        ->name('wishlist.toggle');

    // وبلاگ
    Route::get('/blogs', [BlogController::class, 'index'])->name('blogs.index');
    Route::get('/blogs/{blog:slug}', [BlogController::class, 'show'])->name('blogs.show');
    Route::get('/blog-categories/{category:slug}', [BlogController::class, 'category'])->name('blogs.category');

    // صفحات استاتیک
    Route::get('/page/{page}', [PageController::class, 'show'])->name('site.page');

    // فرم تماس — throttle: حداکثر ۵ ارسال در دقیقه برای هر IP
    Route::post('/contact', [PageController::class, 'sendContact'])
        ->middleware('throttle:5,1')
        ->name('site.contact.send');

    // خبرنامه — throttle: حداکثر ۳ ارسال در دقیقه
    Route::post('/newsletter/subscribe', [PageController::class, 'subscribeNewsletter'])
        ->middleware('throttle:3,1')
        ->name('newsletter.subscribe');

    // افزودن به سبد — مهمان هم مجاز است (سبد session-based است)
    Route::post('/cart/add/{product}', [CartController::class, 'add'])
        ->middleware('throttle:cart_actions')
        ->name('cart.add');

    // نمایش، ویرایش و حذف اقلام سبد — بدون نیاز به لاگین
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::patch('/update/{rowId}', [CartController::class, 'update'])->name('update');
        Route::delete('/remove/{rowId}', [CartController::class, 'remove'])->name('remove');
    });

    // کد تخفیف — نیازمند ورود
    Route::middleware(['auth', 'throttle:discount'])->post('/cart/coupon', [CartController::class, 'applyCoupon'])->name('cart.coupon');

    // ==================== تسویه حساب (نیازمند ورود) ====================
    Route::middleware('auth')->prefix('checkout')->name('checkout.')->group(function () {
        // مرحله ۱: سبد خرید
        Route::get('/', [CheckoutController::class, 'index'])->name('index');

        // مرحله ۲: انتخاب آدرس
        Route::get('/address', [CheckoutController::class, 'address'])->name('address');
        Route::get('/cities', [CheckoutController::class, 'cities'])->name('cities');
        Route::post('/shipments', [CheckoutController::class, 'shipments'])->name('shipments');
        Route::post('/set-shipment', [CheckoutController::class, 'setShipment'])->name('set-shipment');
        Route::get('/address-price', [CheckoutController::class, 'addressPrice'])->name('address-price');

        // مرحله ۳: پرداخت
        Route::get('/payment', [CheckoutController::class, 'payment'])->name('payment');
        Route::post('/apply-discount', [CheckoutController::class, 'applyDiscount'])
            ->middleware('throttle:discount')
            ->name('apply-discount');
        Route::post('/remove-discount', [CheckoutController::class, 'removeDiscount'])->name('remove-discount');
        Route::post('/validate-stock', [CheckoutController::class, 'validateStock'])->name('validate-stock');
        Route::get('/order-price', [CheckoutController::class, 'orderPrice'])->name('order-price');

        // مرحله ۴: ایجاد سفارش
        Route::post('/create', [CheckoutController::class, 'create'])
            ->middleware('throttle:checkout')
            ->name('create');

        // صفحات نتیجه
        Route::get('/success/{order}', [CheckoutController::class, 'success'])->name('success');
        Route::get('/failed', [CheckoutController::class, 'failed'])->name('failed');
    });

    // Callback درگاه‌های پرداخت (بدون نیاز به لاگین)
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::any('/finish', [CheckoutController::class, 'finishZarinPal'])->name('finish.zarin-pal');
        Route::any('/finish-saman', [CheckoutController::class, 'finishSaman'])->name('finish.saman-bank');
        Route::any('/finish-sadad', [CheckoutController::class, 'finishSadad'])->name('finish.sadad-bank');
        Route::any('/finish-zibal', [CheckoutController::class, 'finishZibal'])->name('finish.zibal-bank');
    });

    // حساب کاربری — لاگین/خروج + پنل کاربری
    Route::get('/login', [SiteAuthController::class, 'showLogin'])->name('login');
    Route::post('/logout', [SiteAuthController::class, 'logout'])->middleware('auth')->name('logout');

    // ورود با کد یکبارمصرف (OTP)
    Route::get('/login/otp', [OtpController::class, 'show'])->name('login.otp');
    Route::post('/login/check-phone', [OtpController::class, 'checkPhone'])->name('login.otp.check');
    Route::post('/login/send', [OtpController::class, 'send'])
        ->middleware('throttle:5,1')
        ->name('login.otp.send');
    Route::post('/login/send-ajax', [OtpController::class, 'sendAjax'])
        ->middleware('throttle:5,1')
        ->name('login.otp.send-ajax');
    Route::get('/login/verify', [OtpController::class, 'showVerify'])->name('login.verify');
    Route::post('/login/otp/resend', [OtpController::class, 'resend'])
        ->middleware('throttle:3,1')
        ->name('login.otp.resend');
    Route::post('/login/otp/verify', [OtpController::class, 'verify'])
        ->middleware('throttle:5,1')
        ->name('login.otp.verify');

    // روت‌های محافظت شده با لاگین
    Route::middleware('auth')->group(function () {
        Route::get('/account', [DashboardController::class, 'index'])->name('dashboard');
        Route::put('/account/email', [DashboardController::class, 'updateEmail'])->name('dashboard.email.update');
        Route::delete('/account/email', [DashboardController::class, 'destroyEmail'])->name('dashboard.email.destroy');
        Route::post('/account/newsletter/toggle', [DashboardController::class, 'toggleNewsletter'])->name('dashboard.newsletter.toggle');
        Route::get('/account/orders', [DashboardController::class, 'orders'])->name('dashboard.orders');
        Route::get('/account/orders/{id}', [DashboardController::class, 'orderDetail'])->name('dashboard.order-detail');
        Route::patch('/account/orders/{id}/receipt', [DashboardController::class, 'updateReceipt'])->name('dashboard.order-receipt.update');
        Route::get('/account/orders/{id}/factor', [DashboardController::class, 'orderFactor'])->name('dashboard.order-factor');
        Route::get('/account/wishlist', [DashboardController::class, 'wishlist'])->name('dashboard.wishlist');
        Route::get('/account/addresses', [DashboardController::class, 'addresses'])->name('dashboard.addresses');
        Route::post('/account/addresses', [DashboardController::class, 'createAddress'])->name('dashboard.addresses.create');
        Route::put('/account/addresses/{id}', [DashboardController::class, 'updateAddress'])->name('dashboard.addresses.update');
        Route::delete('/account/addresses/{id}', [DashboardController::class, 'deleteAddress'])->name('dashboard.addresses.delete');
        Route::get('/account/addresses/cities', [DashboardController::class, 'cities'])->name('dashboard.addresses.cities');

        // اعلانات
        Route::get('/account/notifications', [NotificationController::class, 'index'])->name('dashboard.notifications');
        Route::get('/api/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('api.notifications.unread-count');
        Route::get('/account/notifications/{id}/go', [NotificationController::class, 'go'])->name('dashboard.notification-go');
        Route::get('/account/notifications/{id}', [NotificationController::class, 'show'])->name('dashboard.notification-detail');
        Route::post('/api/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('api.notifications.mark-read');
        Route::post('/api/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('api.notifications.mark-all-read');
    });
};

// ── robots.txt و sitemap‌ها — بیرون از closure زبان‌دار ─────────────────────
// این‌ها محتوای صفحه‌ای نیستند و نباید دوقلوی locale.* بگیرند؛ نسخهٔ چندزبانه‌شان
// با xhtml:link داخل همان فایل اعلام می‌شود.

Route::get('/robots.txt', [RobotsController::class, 'index'])->name('robots');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// constraint صریح روی {type} لازم است، وگرنه الگوی /sitemap-{type}.xml
// مقدار «products-2» را هم می‌بلعد و مسیر صفحه‌بندی هرگز match نمی‌شود.
$sitemapTypePattern = implode('|', array_map(
    fn (string $type) => preg_quote($type, '/'),
    SitemapBuilder::TYPES
));

Route::get('/sitemap-{type}-{page}.xml', [SitemapController::class, 'chunk'])
    ->where('type', $sitemapTypePattern)
    ->whereNumber('page')
    ->name('sitemap.chunk');

Route::get('/sitemap-{type}.xml', [SitemapController::class, 'show'])
    ->where('type', $sitemapTypePattern)
    ->name('sitemap.child');

// ۱) نسخه‌ی بدون پیشوند — canonical، زبان پیش‌فرض
$siteRoutes();

// ۲) نسخه‌ی پیشونددار — سایر زبان‌ها (locale.home, locale.products.index, …)
Route::prefix('{locale}')
    ->where(['locale' => $localePattern])
    ->name('locale.')
    ->group($siteRoutes);
