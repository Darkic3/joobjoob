<?php


// صفحه اول
use App\Http\Controllers\Admin\ThemeSettingController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;

Route::get('/', [HomeController::class, 'index'])->name('home');

// محصولات
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

// سبد خرید
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add/{product}', [CartController::class, 'add'])->name('add');
    Route::patch('/update/{rowId}', [CartController::class, 'update'])->name('update');
    Route::delete('/remove/{rowId}', [CartController::class, 'remove'])->name('remove');
    Route::post('/coupon', [CartController::class, 'applyCoupon'])->name('coupon');
});

// // پرداخت (نیازمند لاگین) — فعلاً غیرفعال
// Route::middleware('auth')->group(function () {
//     Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
//     Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
//     Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');
//
//     // پنل کاربری
//     Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
//     Route::get('/dashboard/orders', [DashboardController::class, 'orders'])->name('dashboard.orders');
//     Route::get('/dashboard/wishlist', [DashboardController::class, 'wishlist'])->name('dashboard.wishlist');
//     Route::get('/dashboard/addresses', [DashboardController::class, 'addresses'])->name('dashboard.addresses');
// });
//
// // پنل ادمین: مدیریت تم پیش‌فرض سایت — فعلاً غیرفعال
// Route::prefix('admin')->name('admin.')->middleware(['auth', 'can:admin'])->group(function () {
//     Route::get('/theme-settings', [ThemeSettingController::class, 'edit'])->name('theme.edit');
//     Route::put('/theme-settings', [ThemeSettingController::class, 'update'])->name('theme.update');
// });

// ذخیره تم انتخابی کاربر در دیتابیس (AJAX از theme.js فراخوانی می‌شود)
Route::post('/set-theme', function () {
    $slug = request('theme');
    $cookie = cookie('theme', $slug, 60 * 24 * 365);

    if (auth()->check()) {
        auth()->user()->update(['theme' => $slug]);
    }

    return response()->json(['status' => 'ok'])->withCookie($cookie);
})->name('theme.set');
