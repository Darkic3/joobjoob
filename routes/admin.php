<?php

use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\DashboardController;

Route::middleware(['adminPermission', 'log.admin'])->prefix('admin')->name('admin.')->group(function () {});

Route::get('/admin/settings/{group?}', [SettingController::class, 'edit'])->name('admin.settings.edit');
Route::post('/admin/settings/{group}', [SettingController::class, 'update'])->name('admin.settings.update');

Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');


Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
