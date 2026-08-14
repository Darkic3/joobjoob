<?php

namespace App\Providers;

use App\View\Composers\FooterComposer;
use App\View\Composers\LocaleComposer;
use App\View\Composers\SiteLayoutComposer;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('admin', fn ($user) => $user->isAdmin());

        // داده‌های سراسری زبان/جهت/ارقام برای همهٔ ویوها (به‌جای fallback تکراری داخل لایه‌ها)
        View::composer('*', LocaleComposer::class);

        // نوار اعلان چیدمان سایت
        View::composer('layouts.site.app', SiteLayoutComposer::class);

        // نشان‌های اعتماد نوار پایین فوتر (اینماد، درگاه پرداخت و…)
        View::composer('components.footer', FooterComposer::class);
    }
}
