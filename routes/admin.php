<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CkeditorController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\CrawlManagerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HeroSlideController;
use App\Http\Controllers\Admin\HeroSlideSearchController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PromoBannerController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\MonitoringController;
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
    Route::middleware(['auth', 'adminPermission', 'log.admin', 'refreshRememberMe'])->group(function () {

        Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');

        Route::resource('blogs', BlogController::class);
        Route::resource('blog-categories', BlogCategoryController::class);
        // show عمداً حذف شده — کنترلر متد show ندارد و درخت کانفیگ کلید show ندارد (تله 403)
        // هیرو صفحهٔ اصلی — نسخهٔ مینیمال: تک‌ادیتور (بدون لیست اسلایدر)
        Route::get('hero-slides', [HeroSlideController::class, 'index'])->name('hero-slides.index');
        Route::put('hero-slides', [HeroSlideController::class, 'update'])->name('hero-slides.update');
        Route::get('hero-slides/search-model', [HeroSlideSearchController::class, 'searchModel'])->name('hero-slides.search-model');
        Route::resource('trust-badges', TrustBadgeController::class);
        // بنرهای میانی صفحهٔ اصلی — show عمداً حذف شده (کنترلر متد show ندارد)
        Route::resource('promo-banners', PromoBannerController::class)->except('show');
        Route::resource('products', ProductController::class);
        // show عمداً حذف شده — کنترلر متد show ندارد و درخت کانفیگ کلید show ندارد (تله 403)

        // عملیات جمعی محصولات
        Route::post('products/bulk-action', [ProductController::class, 'bulkAction'])->name('products.bulk-action');

        // ========== مدیریت کرالر آپتیمیست ==========
        Route::get('crawl', [CrawlManagerController::class, 'index'])->name('crawl.index');
        Route::post('crawl/run', [CrawlManagerController::class, 'run'])->name('crawl.run');
        Route::get('crawl/status', [CrawlManagerController::class, 'status'])->name('crawl.status');
        Route::get('crawl/{crawlLog}', [CrawlManagerController::class, 'show'])->name('crawl.show');
        Route::delete('crawl/{crawlLog}', [CrawlManagerController::class, 'destroy'])->name('crawl.destroy');
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

        // ذخیره ترتیب درگ‌اندروپ (ایندکس) — با پرمیشن ویرایش
        Route::post('trust-badges/reorder', [TrustBadgeController::class, 'reorder'])->name('trust-badges.reorder');
        Route::post('promo-banners/reorder', [PromoBannerController::class, 'reorder'])->name('promo-banners.reorder');
        Route::post('brands/reorder', [BrandController::class, 'reorder'])->name('brands.reorder');

        // ⚠️ approval و featured و reply باید قبل از resource باشد تا با reviews/{review} تداخل نکند
        Route::patch('reviews/approval', [ReviewController::class, 'updateApproval'])->name('reviews.approval');
        Route::patch('reviews/featured', [ReviewController::class, 'toggleFeatured'])->name('reviews.featured');
        Route::post('reviews/reply', [ReviewController::class, 'reply'])->name('reviews.reply');
        Route::resource('reviews', ReviewController::class)->except('create', 'edit');

        Route::get('contact', [ContactController::class, 'index'])->name('contact.index');
        Route::get('contact/{contact}', [ContactController::class, 'show'])->name('contact.show');
        Route::delete('contact/{contact}', [ContactController::class, 'destroy'])->name('contact.destroy');

        // ========== مدیریت سفارشات ==========
        Route::get('orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{id}', [\App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');
        Route::post('orders/{id}/change-shipping-status', [\App\Http\Controllers\Admin\OrderController::class, 'changeShippingStatus'])->name('orders.change-shipping-status');
        Route::post('orders/change-order-status/{id}', [\App\Http\Controllers\Admin\OrderController::class, 'changeOrderStatus'])->name('orders.change-order-status');
        Route::post('orders/post-code', [\App\Http\Controllers\Admin\OrderController::class, 'postCode'])->name('orders.post-code');
        Route::get('orders/{id}/factor', [\App\Http\Controllers\Admin\OrderController::class, 'factor'])->name('orders.factor');
        Route::get('orders/bulk-factor', [\App\Http\Controllers\Admin\OrderController::class, 'bulkFactor'])->name('orders.bulk-factor');

        // ========== مدیریت روش‌های ارسال ==========
        Route::resource('shipping-methods', \App\Http\Controllers\Admin\ShippingMethodController::class);
        Route::post('shipping-methods/{shippingMethod}/toggle-status', [\App\Http\Controllers\Admin\ShippingMethodController::class, 'toggleStatus'])->name('shipping-methods.toggle-status');

        // ========== مدیریت درگاه‌های پرداخت ==========
        Route::resource('banks', \App\Http\Controllers\Admin\BankController::class);
        Route::post('banks/{bank}/toggle-status', [\App\Http\Controllers\Admin\BankController::class, 'toggleStatus'])->name('banks.toggle-status');

        // ========== مدیریت کدهای تخفیف ==========
        // ⚠️ مسیرهای ثابت باید قبل از مسیرهای پویا ({discount}) تعریف شوند
        Route::get('discounts/users/filter', [\App\Http\Controllers\Admin\DiscountController::class, 'filterUsers'])->name('discounts.users.filter');
        Route::resource('discounts', \App\Http\Controllers\Admin\DiscountController::class)->except('show');
        Route::get('discounts/{discount}', [\App\Http\Controllers\Admin\DiscountController::class, 'show'])->name('discounts.show');
        Route::post('discounts/{discount}/toggle-status', [\App\Http\Controllers\Admin\DiscountController::class, 'toggleStatus'])->name('discounts.toggle-status');

        // ========== اعلانات ==========
        Route::get('notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('notifications.index');
        Route::get('api/notifications/unread-count', [\App\Http\Controllers\Admin\NotificationController::class, 'unreadCount'])->name('api.notifications.unread-count');
        Route::get('notifications/{id}', [\App\Http\Controllers\Admin\NotificationController::class, 'show'])->name('notifications.show');
        Route::get('notifications/{id}/go', [\App\Http\Controllers\Admin\NotificationController::class, 'go'])->name('notifications.go');
        Route::post('api/notifications/{id}/read', [\App\Http\Controllers\Admin\NotificationController::class, 'markRead'])->name('api.notifications.mark-read');
        Route::post('api/notifications/read-all', [\App\Http\Controllers\Admin\NotificationController::class, 'markAllRead'])->name('api.notifications.mark-all-read');

        // ========== لاگ حسابرسی ==========
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('audit-logs/{id}', [AuditLogController::class, 'show'])->name('audit-logs.show');
        Route::get('audit-logs/order/{orderId}', [AuditLogController::class, 'orderHistory'])->name('audit-logs.order-history');
        Route::get('audit-logs/product/{productId}', [AuditLogController::class, 'productHistory'])->name('audit-logs.product-history');

        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

        // ========== مانیتورینگ سیستم ==========
        Route::get('monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
        Route::get('monitoring/errors', [MonitoringController::class, 'errors'])->name('monitoring.errors');
        Route::get('monitoring/errors/{error}', [MonitoringController::class, 'showError'])->name('monitoring.errors.show');
        Route::post('monitoring/errors/{errorId}/resolve', [MonitoringController::class, 'resolveError'])->name('monitoring.errors.resolve');
        Route::post('monitoring/errors/bulk-resolve', [MonitoringController::class, 'bulkResolveErrors'])->name('monitoring.errors.bulk-resolve');
        Route::delete('monitoring/errors/resolved', [MonitoringController::class, 'deleteResolvedErrors'])->name('monitoring.errors.delete-resolved');
        Route::get('monitoring/payments', [MonitoringController::class, 'payments'])->name('monitoring.payments');
        Route::get('monitoring/sms', [MonitoringController::class, 'smsLogs'])->name('monitoring.sms');
        Route::get('monitoring/performance', [MonitoringController::class, 'performance'])->name('monitoring.performance');

        Route::post('ckeditor/upload', [CkeditorController::class, 'upload'])->name('ckeditor.upload');
    });
});
