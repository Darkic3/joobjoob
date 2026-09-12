<?php

namespace App\Providers;

use App\Events\OrderCreated;
use App\Events\OrderPaid;
use App\Listeners\RecordAuditListener;
use App\Listeners\RecordCouponUsageListener;
use App\Listeners\SendOrderNotificationListener;
use App\Models\Order;
use App\Observers\OrderObserver;
use App\Services\CartService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use App\Services\MetaService;
use App\Services\RecentlyViewedService;
use App\Services\Seo\SeoManager;
use App\Services\Seo\SeoResolver;
use App\Services\WishlistService;
use App\View\Composers\AdminLayoutComposer;
use App\View\Composers\FooterComposer;
use App\View\Composers\LocaleComposer;
use App\View\Composers\SiteLayoutComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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
        $this->app->singleton(MetaService::class);

//        TODO: ahora_fix ino hamin bmoone ye bayad fix she ?
        if (! function_exists('seo')) {
            require_once base_path('app/Helpers/seo.php');
        }

        // SEO — singleton تا `seo()` در کنترلر و کامپوننت <x-seo-head /> به یک نمونه برسند.
        // state واقعی روی Request نگه داشته می‌شود، پس بین درخواست‌ها نشت نمی‌کند.
        $this->app->singleton(SeoManager::class);
        $this->app->singleton(SeoResolver::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Observerها
        Order::observe(OrderObserver::class);

        // ── V1.1 Event-Driven wiring (Ready-to-Merge — order create listeners) ──
        // In 3 listener dar V1 faqhat sabt mishavand, amma OrderService hanuz mostaghim
        // Audit/Notification/Coupon ra sedaa mizand (dual-write). Dar migration-e V1.1
        // bakhsh-e synchronous-e OrderService hazf va `event(new OrderCreated($order))`
        // jaygozin mishavad — guide: docs/11-metrics/V2_Event_Driven_Migration_Guide.md
        Event::listen(OrderCreated::class, SendOrderNotificationListener::class);
        Event::listen(OrderCreated::class, RecordAuditListener::class);
        Event::listen(OrderCreated::class, RecordCouponUsageListener::class);
        Event::listen(OrderPaid::class, RecordAuditListener::class);

        Gate::define('admin', fn($user) => $user->isAdmin());

        // ── Rate Limiters ──────────────────────────────────────────────
        // Reviews: 5/min per IP — prevent spam reviews
        RateLimiter::for('reviews', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Wishlist toggle: 30/min per user/IP — prevent abuse
        RateLimiter::for('wishlist', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        // Cart actions: 30/min per user/IP — prevent cart spam
        RateLimiter::for('cart_actions', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        // Checkout: 10/min per user — prevent order spam
        RateLimiter::for('checkout', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        // Discount apply: 10/min per user — prevent code enumeration
        RateLimiter::for('discount', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        // داده‌های سراسری زبان/جهت/ارقام برای همهٔ ویوها (به‌جای fallback تکراری داخل لایه‌ها)
        View::composer('*', LocaleComposer::class);

        // نوار اعلان چیدمان سایت
        View::composer('layouts.site.app', SiteLayoutComposer::class);

        // داده‌های اعلانات پنل ادمین
        View::composer('layouts.admin.app', AdminLayoutComposer::class);

        // نشان‌های اعتماد نوار پایین فوتر (اینماد، درگاه پرداخت و…)
        View::composer('components.footer', FooterComposer::class);

        // Blade Directive for Meta Tags
        Blade::directive('meta', function () {
            return "<?php echo app(\App\Services\MetaService::class)->render(); ?>";
        });
    }
}
