<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HeroSlideController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SpecificationController;
use App\Http\Controllers\Admin\TrustBadgeController;
use App\Http\Controllers\Admin\UserController;

Route::prefix('admin')->name('admin.')->group(function () {

    // ── لاگین (عمومی) ──────────────────────────────────────────────
    Route::get('login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AdminAuthController::class, 'postLogin'])
        ->name('login.submit')
        ->middleware('throttle:5,1');

    // ── همه بخش‌های محافظت‌شده پنل ──────────────────────────────────
    // ⚠️ هیچ روت ادمینی نباید خارج از این گروه تعریف شود (پیش‌فرض = باز)
    Route::middleware(['auth', 'adminPermission', 'log.admin'])->group(function () {

        Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');

        Route::resource('blogs', BlogController::class);
        Route::resource('blog-categories', BlogCategoryController::class);
        Route::resource('hero-slides', HeroSlideController::class);
        Route::resource('trust-badges', TrustBadgeController::class);
        Route::resource('products', ProductController::class);
        // show عمداً حذف شده — کنترلر متد show ندارد و درخت کانفیگ کلید show ندارد (تله 403)
        Route::resource('product-categories', ProductCategoryController::class)->except('show');
        Route::resource('brands', BrandController::class);
        Route::resource('specifications', SpecificationController::class);
        // show عمداً حذف شده — کنترلر متد show ندارد و درخت کانفیگ هم کلید show ندارد
        // (اگر روت show می‌ماند، نقش main هم به آن دسترسی نداشت → تله 403)
        Route::resource('roles', RoleController::class)->except('show');
        // مدیریت کاربران: index/edit/update + create/store (ساخت کاربر از پنل ادمین)
        // حذف کاربران به عهده‌ی فلوهای آینده (deactivate) است
        Route::resource('users', UserController::class)->only(['index', 'create', 'store', 'edit', 'update']);

        Route::get('settings/{group?}', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings/{group}', [SettingController::class, 'update'])->name('settings.update');

        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

        Route::post('ckeditor/upload', [App\Http\Controllers\Admin\CkeditorController::class, 'upload'])->name('ckeditor.upload');
    });
});
