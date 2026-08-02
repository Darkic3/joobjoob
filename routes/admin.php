<?php

use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\HeroSlideController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TrustBadgeController;
use App\Http\Controllers\ProductController;

//middleware(['adminPermission', 'log.admin'])->
Route::prefix('admin')->name('admin.')->group(function () {
    Route::resource('blogs', BlogController::class);
    Route::resource('blog-categories', BlogCategoryController::class);
    Route::resource('hero-slides', HeroSlideController::class);
    Route::resource('trust-badges', TrustBadgeController::class);
    Route::resource('products', ProductController::class);
});


Route::get('/admin/settings/{group?}', [SettingController::class, 'edit'])->name('admin.settings.edit');
Route::put('/admin/settings/{group}', [SettingController::class, 'update'])->name('admin.settings.update');

Route::get('dashboard', [DashboardController::class, 'index'])->name('admin.dashboard.index');

Route::post('/admin/ckeditor/upload', [App\Http\Controllers\Admin\CkeditorController::class, 'upload'])->name('admin.ckeditor.upload');
