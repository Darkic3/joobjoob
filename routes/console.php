<?php

use App\Console\Commands\CancelExpiredOrders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// لغو خودکار سفارشات منقضی شده (هر ۵ دقیقه)
Schedule::command('orders:cancel-expired --minutes=15')->everyFiveMinutes()->withoutOverlapping();

// ── Monitoring ──────────────────────────────────────────────────
// بررسی سلامت سیستم (هر ساعت)
Schedule::command('monitoring:check-health')->hourly();

// پاکسازی لاگ‌های قدیمی (روزانه ساعت ۳ صبح)
Schedule::command('monitoring:cleanup')->dailyAt('03:00');
