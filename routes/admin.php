<?php

use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\DashboardController;

//middleware(['adminPermission', 'log.admin'])->
Route::prefix('admin')->name('admin.')->group(function () {
    Route::resource('blogs', BlogController::class);
    Route::resource('blog-categories', BlogCategoryController::class);
});


Route::get('/admin/settings/{group?}', [SettingController::class, 'edit'])->name('admin.settings.edit');
Route::put('/admin/settings/{group}', [SettingController::class, 'update'])->name('admin.settings.update');

Route::get('/admin/theme', [App\Http\Controllers\Admin\ThemeSettingController::class, 'edit'])->name('admin.theme.edit');
Route::put('/admin/theme', [App\Http\Controllers\Admin\ThemeSettingController::class, 'update'])->name('admin.theme.update');

Route::get('dashboard', [DashboardController::class, 'index'])->name('admin.dashboard.index');


//Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
//Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

Route::post('/admin/ckeditor/upload', [App\Http\Controllers\Admin\CkeditorController::class, 'upload'])->name('admin.ckeditor.upload');

Route::get('/admin/theme-settings', [App\Http\Controllers\Admin\ThemeSettingController::class, 'edit'])->name('admin.theme.edit');
Route::put('/admin/theme-settings', [App\Http\Controllers\Admin\ThemeSettingController::class, 'update'])->name('admin.theme.update');
