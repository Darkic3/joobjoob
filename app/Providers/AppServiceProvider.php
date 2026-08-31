<?php

namespace App\Providers;

use App\Models\Order;
use App\Observers\OrderObserver;
use App\Services\CartService;
use App\Services\RecentlyViewedService;
use App\Services\WishlistService;
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
        $this->app->singleton(RecentlyViewedService::class);
        $this->app->singleton(WishlistService::class);
        $this->app->singleton(CartService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Observerها
        Order::observe(OrderObserver::class);

        Gate::define('admin', fn ($user) => $user->isAdmin());

        // داده‌های سراسری زبان/جهت/ارقام برای همهٔ ویوها (به‌جای fallback تکراری داخل لایه‌ها)
        View::composer('*', LocaleComposer::class);

        // نوار اعلان چیدمان سایت
        View::composer('layouts.site.app', SiteLayoutComposer::class);

        // نشان‌های اعتماد نوار پایین فوتر (اینماد، درگاه پرداخت و…)
        View::composer('components.footer', FooterComposer::class);
    }
}
