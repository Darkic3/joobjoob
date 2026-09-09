<?php

use App\Http\Controllers\HealthController;

Route::get('/health', HealthController::class)->name('health');

include 'admin.php';
include 'site.php';
