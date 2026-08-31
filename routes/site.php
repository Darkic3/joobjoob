<?php

// ── SEO زبان: دو مجموعه روت ────────────────────────────────────────────
//   ۱) بدون پیشوند (canonical) — زبان پیش‌فرض؛ نام روت: home, products.index, …
//   ۲) با پیشوند {locale} — سایر زبان‌ها؛ نام روت: locale.home, locale.products.index, …
//
//   /            → زبان پیش‌فرض (fa)
//   /en          → زبان en (SetLocale آن را اعمال می‌کند)
//   /fa          → 301 به / (پیش‌فرض پیشوند نمی‌گیرد — در SetLocale)
//   /xx (نامعتبر) → 404 (در constraint نیست)
//
// ⚠️ چرا کپی جدا؟ پارامتر اختیاری در ابتدای URI ({locale?}/products) توسط
//   کامپایلر Laravel الزامی می‌شود — پس نسخه‌ی پیشونددار با {locale} اجباری ثبت می‌شود.

use App\Http\Controllers\Site\BlogController;
use App\Http\Controllers\Site\CheckoutController;
use App\Http\Controllers\Site\CartController;
use App\Http\Controllers\Site\DashboardController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\OtpController;
use App\Http\Controllers\Site\PageController;
use App\Http\Controllers\Site\ProductController;
use App\Http\Controllers\Site\SiteAuthController;
use App\Http\Controllers\Site\WishlistController;

// الگوی کد زبان برای constraint — امن: فقط کدهای واقعی (کاتالوگ + دیتابیس)
$catalogCodes = array_diff(array_keys(config('languages', [])), ['_default']);

try {
    $codes = array_merge($catalogCodes, locale()->codes());
} catch (Throwable $e) {
    // جدول languages هنوز موجود نیست (پیش از میگریشن) → فقط کاتالوگ
    $codes = $catalogCodes;
}

$codes = array_values(array_unique($codes));
usort($codes, fn (string $a, string $b) => strlen($b) <=> strlen($a));
$localePattern = '^(?:'.implode('|', array_map(fn (string $c) => preg_quote($c, '/'), $codes)).')$';

// بدنه‌ی روت‌های سایت — یک بار برای بدون پیشوند، یک بار برای پیشونددار
$siteRoutes = function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/seed', [HomeController::class, 'seed'])->name('seed');

    // محصولات
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');
    Route::post('/products/{product:slug}/reviews', [ProductController::class, 'storeReview'])
        ->name('products.reviews.store');
    Route::post('/products/{product:slug}/reviews/{review}/reply', [ProductController::class, 'storeReply'])
        ->name('products.reviews.reply');
    Route::get('/products/{product:slug}/quick-view', [ProductController::class, 'quickView'])->name('products.quick-view');
    Route::get('/product-categories/{category:slug}', [ProductController::class, 'category'])->name('products.category');

    // علاقه‌مندی‌ها (سشن — بدون نیاز به لاگین)
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/toggle/{product:slug}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

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

    // افزودن به سبد — مهمان هم مجاز است (سبد session-based است)
    Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');

    // نمایش، ویرایش و حذف اقلام سبد — بدون نیاز به لاگین
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::patch('/update/{rowId}', [CartController::class, 'update'])->name('update');
        Route::delete('/remove/{rowId}', [CartController::class, 'remove'])->name('remove');
    });

    // کد تخفیف — نیازمند ورود
    Route::middleware('auth')->post('/cart/coupon', [CartController::class, 'applyCoupon'])->name('cart.coupon');

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
        Route::post('/apply-discount', [CheckoutController::class, 'applyDiscount'])->name('apply-discount');
        Route::post('/remove-discount', [CheckoutController::class, 'removeDiscount'])->name('remove-discount');
        Route::get('/order-price', [CheckoutController::class, 'orderPrice'])->name('order-price');

        // مرحله ۴: ایجاد سفارش
        Route::post('/create', [CheckoutController::class, 'create'])->name('create');

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

    // حساب کاربری — لاگین/خروج + پنل کاربری (نیازمند ورود)
    Route::get('/login', [SiteAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [SiteAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [SiteAuthController::class, 'logout'])->middleware('auth')->name('logout');

    // ورود با کد یکبارمصرف (OTP) — پیامک کاوه‌نگار
    Route::get('/login/otp', [OtpController::class, 'show'])->name('login.otp');
    Route::post('/login/check-phone', [OtpController::class, 'checkPhone'])
        ->name('login.otp.check');
    Route::post('/login/send', [OtpController::class, 'send'])
        ->middleware('throttle:5,1')
        ->name('login.otp.send');
    Route::get('/login/verify', [OtpController::class, 'showVerify'])->name('login.verify');
    Route::post('/login/otp/resend', [OtpController::class, 'resend'])
        ->middleware('throttle:3,1')
        ->name('login.otp.resend');
    Route::post('/login/otp/verify', [OtpController::class, 'verify'])
        ->middleware('throttle:5,1')
        ->name('login.otp.verify');

    Route::middleware('auth')->group(function () {
        Route::get('/account', [DashboardController::class, 'index'])->name('dashboard');
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
    });
};

// ۱) نسخه‌ی بدون پیشوند — canonical، زبان پیش‌فرض
$siteRoutes();

// ۲) نسخه‌ی پیشونددار — سایر زبان‌ها (locale.home, locale.products.index, …)
Route::prefix('{locale}')
    ->where(['locale' => $localePattern])
    ->name('locale.')
    ->group($siteRoutes);
