<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CkeditorController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HeroSlideController;
use App\Http\Controllers\Admin\HeroSlideSearchController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PromoBannerController;
use App\Http\Controllers\Admin\ReviewController;
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
        // show عمداً حذف شده — کنترلر متد show ندارد و درخت کانفیگ کلید show ندارد (تله 403)
        Route::resource('hero-slides', HeroSlideController::class)->except('show');
        Route::get('hero-slides/search-model', [HeroSlideSearchController::class, 'searchModel'])->name('hero-slides.search-model');
        Route::resource('trust-badges', TrustBadgeController::class);
        // بنرهای میانی صفحهٔ اصلی — show عمداً حذف شده (کنترلر متد show ندارد)
        Route::resource('promo-banners', PromoBannerController::class)->except('show');
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

        // مدیریت زبان‌ها — اکشن‌های سریع ایندکس با پرمیشن ویرایش (toggle / make-default)
        Route::resource('languages', LanguageController::class)->except('show');
        Route::patch('languages/{language}/toggle', [LanguageController::class, 'toggle'])->name('languages.toggle');
        Route::patch('languages/{language}/make-default', [LanguageController::class, 'makeDefault'])->name('languages.make-default');
        // ذخیره ترتیب درگ‌اندروپ (ایندکس) — با پرمیشن ویرایش
        Route::post('languages/reorder', [LanguageController::class, 'reorder'])->name('languages.reorder');
        Route::post('hero-slides/reorder', [HeroSlideController::class, 'reorder'])->name('hero-slides.reorder');
        Route::post('trust-badges/reorder', [TrustBadgeController::class, 'reorder'])->name('trust-badges.reorder');
        Route::post('promo-banners/reorder', [PromoBannerController::class, 'reorder'])->name('promo-banners.reorder');
        Route::post('brands/reorder', [BrandController::class, 'reorder'])->name('brands.reorder');

        // ⚠️ approval و featured باید قبل از resource باشد تا با reviews/{review} تداخل نکند
        Route::patch('reviews/approval', [ReviewController::class, 'updateApproval'])->name('reviews.approval');
        Route::patch('reviews/featured', [ReviewController::class, 'toggleFeatured'])->name('reviews.featured');
        Route::resource('reviews', ReviewController::class)->except('create', 'edit');

        Route::get('contact', [ContactController::class, 'index'])->name('contact.index');
        Route::get('contact/{contact}', [ContactController::class, 'show'])->name('contact.show');
        Route::delete('contact/{contact}', [ContactController::class, 'destroy'])->name('contact.destroy');

        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

        Route::post('ckeditor/upload', [CkeditorController::class, 'upload'])->name('ckeditor.upload');
    });
});
