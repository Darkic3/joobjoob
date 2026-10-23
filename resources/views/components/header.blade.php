@props(['cartCount' => 0, 'wishlistCount' => 0, 'unreadNotificationCount' => 0])

@php
    $siteName = setting('site_name', __('messages.site_name'));
@endphp

{{-- هدر بروتالیست تمام‌عرض (هم‌زبان با هیرو) — دسکتاپ: نوار کرم با بوردر جوهری؛ موبایل: لوگو + همبرگر مورف --}}
<header class="site-header-wrap sticky top-0 z-50">
    <div class="site-header max-w-7xl mx-auto">

        {{-- لوگو: مارک گرادینتی (Double-Bezel) + نام --}}
        <a href="{{ localizedRoute('home') }}" class="site-logo shrink-0" aria-label="{{ $siteName }}">
            <span class="site-logo-mark" aria-hidden="true">{{ mb_substr($siteName, 0, 1) }}</span>
            <span class="site-logo-text truncate">{{ $siteName }}</span>
        </a>

        {{-- ناو دسکتاپ — هر لینک یک کاشی آیکون دارد که در هاور/فعال پررنگ می‌شود --}}
        <nav class="hidden lg:flex items-center gap-1 ms-3" aria-label="{{ __('messages.navigation') }}">
            <a href="{{ localizedRoute('home') }}"
               class="site-nav-link {{ request()->routeIs('home', 'locale.home') ? 'is-active' : '' }}">
                <span class="site-nav-ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m2.25 12 8.954-8.955a1.126 1.126 0 0 1 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/></svg>
                </span>
                <span>{{ __('messages.home') }}</span>
            </a>
            <a href="{{ localizedRoute('products.index') }}"
               class="site-nav-link {{ request()->routeIs('products.index', 'locale.products.index', 'products.category', 'locale.products.category') ? 'is-active' : '' }}">
                <span class="site-nav-ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>
                </span>
                <span>{{ __('messages.products') }}</span>
            </a>
            <a href="{{ localizedRoute('products.index') }}#deals" class="site-nav-link">
                <span class="site-nav-ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/><path d="M6 6h.008v.008H6V6Z"/></svg>
                </span>
                <span>{{ __('messages.special_offers') }}</span>
            </a>
        </nav>

        {{-- جستجو (md به بالا) --}}
        <form action="{{ localizedRoute('products.index') }}" method="GET" role="search"
              class="site-search hidden md:flex flex-1 max-w-md ms-auto me-3">
            <input type="text" name="q" value="{{ request('q') }}"
                   placeholder="{{ __('messages.search_placeholder') }}" class="site-search-input"
                   autocomplete="off">
            <svg class="site-search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10.5A6.5 6.5 0 1 1 4 10.5a6.5 6.5 0 0 1 13 0Z"/>
            </svg>
        </form>

        {{-- اکشن‌ها — در موبایل فقط هامبرگر می‌ماند (زبان/تم داخل منو، سبد/علاقه‌مندی در نوار پایین) --}}
        <div class="flex items-center gap-2 ms-auto lg:ms-0">


            {{-- سبد/علاقه‌مندی/اعلانات/حساب فقط برای اعضا؛ مهمان فقط دکمهٔ ورود/ثبت‌نام --}}
            @auth
                <a href="{{ localizedRoute('wishlist.index') }}" class="neu-btn neu-btn-icon site-action-btn relative hidden lg:inline-flex"
                   title="{{ __('messages.wishlist') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.318 6.318a4.5 4.5 0 0 1 6.364 0L12 7.636l1.318-1.318a4.5 4.5 0 1 1 6.364 6.364L12 21l-7.682-7.682a4.5 4.5 0 0 1 0-6.364Z"/>
                    </svg>
                    <span id="wishlist-count" class="site-badge">{{ $wishlistCount }}</span>
                </a>

                <a href="{{ localizedRoute('cart.index') }}" class="neu-btn neu-btn-icon site-action-btn relative hidden lg:inline-flex"
                   title="{{ __('messages.cart') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 1.94-4.746 2.437-7.28a1.125 1.125 0 0 0-1.11-1.35H5.106M7.5 14.25 5.106 5.106M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>
                </svg>
                <span id="cart-count" class="site-badge">{{ $cartCount }}</span>
            </a>

                {{-- زنگوله اعلانات --}}
                <div x-data="notificationBell()" class="relative hidden lg:block">
                    <button type="button" @click="toggle()"
                            class="neu-btn neu-btn-icon site-action-btn relative"
                            title="{{ __('messages.notifications') }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                        </svg>
                        <span x-show="count > 0" x-text="displayCount()"
                              x-transition
                              class="site-notif-badge">{{ \App\Helpers\NumberHelper::localizeDigits(($unreadNotificationCount ?? 0) > 99 ? '99+' : ($unreadNotificationCount ?? 0), $digitFamily ?? 'latin') }}</span>
                    </button>

                    {{-- Dropdown اعلانات --}}
                    <div x-show="open" @click.away="open = false" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute end-0 top-full mt-2 w-80 site-notif-menu overflow-hidden z-50">
                        <div class="flex items-center justify-between px-4 py-3" style="border-bottom: 1px solid var(--eco-line);">
                            <p class="text-sm font-bold" style="color: var(--eco-ink);">{{ __('messages.notifications') }}</p>
                            <button x-show="count > 0" @click="markAllRead()" type="button"
                                    class="text-[11px] font-bold transition-opacity hover:opacity-70"
                                    style="color: var(--eco-clay-ink);">
                                {{ __('messages.mark_all_read') }}
                            </button>
                        </div>
                        <div class="max-h-72 overflow-y-auto">
                            <template x-if="loading">
                                <div class="px-4 py-8 text-center">
                                    <p class="text-xs font-bold" style="color: var(--eco-ink-2);">{{ __('messages.loading') }}</p>
                                </div>
                            </template>
                            <template x-if="!loading && items.length === 0">
                                <div class="px-4 py-8 text-center">
                                    <svg class="w-10 h-10 mx-auto mb-2" style="color: var(--eco-ink-2); opacity: 0.4;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                                    <p class="text-xs font-bold" style="color: var(--eco-ink-2);">{{ __('messages.no_notifications') }}</p>
                                </div>
                            </template>
                            <template x-for="item in items" :key="item.id">
                                <template x-if="item.link">
                                    <a :href="item.link"
                                       class="flex items-start gap-3 px-4 py-3 transition-colors hover:opacity-80"
                                       :style="item.is_read ? '' : 'background: var(--eco-clay-soft);'"
                                        <span class="text-lg shrink-0 mt-0.5" x-text="item.icon || '🔔'"></span>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-bold truncate" style="color: var(--eco-ink);" x-text="item.title"></p>
                                            <p class="text-[11px] mt-0.5 line-clamp-2" style="color: var(--eco-ink-2);" x-text="item.body"></p>
                                            <p class="text-[10px] mt-1" style="color: var(--eco-ink-2); opacity: 0.6;" x-text="item.created_at"></p>
                                        </div>
                                        <span x-show="!item.is_read" class="w-2 h-2 rounded-full shrink-0 mt-1.5" style="background: var(--eco-clay);"></span>
                                    </a>
                                </template>
                                <template x-if="!item.link">
                                    <div class="flex items-start gap-3 px-4 py-3 transition-colors hover:opacity-80"
                                         :style="item.is_read ? '' : 'background: var(--eco-clay-soft);'"
                                        <span class="text-lg shrink-0 mt-0.5" x-text="item.icon || '🔔'"></span>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-bold truncate" style="color: var(--eco-ink);" x-text="item.title"></p>
                                            <p class="text-[11px] mt-0.5 line-clamp-2" style="color: var(--eco-ink-2);" x-text="item.body"></p>
                                            <p class="text-[10px] mt-1" style="color: var(--eco-ink-2); opacity: 0.6;" x-text="item.created_at"></p>
                                        </div>
                                        <span x-show="!item.is_read" class="w-2 h-2 rounded-full shrink-0 mt-1.5" style="background: var(--eco-clay);"></span>
                                    </div>
                                </template>
                            </template>
                        </div>
                        <a href="{{ route('dashboard.notifications') }}"
                           class="block text-center py-2.5 text-xs font-bold transition-opacity hover:opacity-80"
                           style="color: var(--eco-clay-ink); border-top: 1px solid var(--eco-line);">
                            {{ __('messages.view_all_notifications') }}
                        </a>
                    </div>

                <a href="{{ localizedRoute('dashboard') }}" class="site-account-btn relative hidden lg:inline-flex"
                   title="{{ auth()->user()->name }}" aria-label="{{ __('messages.dashboard') }}">
                    <span class="site-account-avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                </a>
            @else
                <a href="{{ localizedRoute('cart.index') }}" class="neu-btn neu-btn-icon site-action-btn relative hidden lg:inline-flex"
                   title="{{ __('messages.cart') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 1.94-4.746 2.437-7.28a1.125 1.125 0 0 0-1.11-1.35H5.106M7.5 14.25 5.106 5.106M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>
                    </svg>
                    <span id="cart-count-guest" class="site-badge" style="display:none;">{{ $cartCount }}</span>
                </a>
                <a href="{{ localizedRoute('login') }}" class="site-login-btn hidden lg:inline-flex"
                   title="{{ __('messages.login_register') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0 18.9 18.9 0 0 1-3.55 2.55H8.05A18.9 18.9 0 0 1 4.5 20.25Z"/>
                    </svg>
                    <span>{{ __('messages.login_register') }}</span>
                </a>
            @endauth

            {{-- همبرگر مورف — فقط موبایل/تبلت --}}
            <button type="button" class="site-hamburger lg:hidden" data-menu-btn
                    aria-label="{{ __('messages.menu') }}" aria-expanded="false" aria-controls="site-menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</header>

{{-- منوی تمام‌صفحه موبایل — z پایین‌تر از هدر تا همبرگر مورف‌شده روی آن دیده شود --}}
<div class="site-menu lg:hidden" id="site-menu" data-site-menu
     role="dialog" aria-modal="true" aria-label="{{ __('messages.navigation') }}" aria-hidden="true">
    <div class="site-menu-backdrop" data-menu-close></div>
    <div class="site-menu-panel">

        <nav class="site-menu-nav" aria-label="{{ __('messages.navigation') }}">
            <a href="{{ localizedRoute('home') }}"
               class="site-menu-link {{ request()->routeIs('home', 'locale.home') ? 'is-active' : '' }}">
                <span class="site-menu-ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m2.25 12 8.954-8.955a1.126 1.126 0 0 1 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/></svg>
                </span>
                <span class="site-menu-label">{{ __('messages.home') }}</span>
                <span class="site-menu-num">01</span>
            </a>
            <a href="{{ localizedRoute('products.index') }}"
               class="site-menu-link {{ request()->routeIs('products.*', 'locale.products.*') ? 'is-active' : '' }}">
                <span class="site-menu-ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>
                </span>
                <span class="site-menu-label">{{ __('messages.products') }}</span>
                <span class="site-menu-num">02</span>
            </a>
            <a href="{{ localizedRoute('products.index') }}#deals" class="site-menu-link">
                <span class="site-menu-ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/><path d="M6 6h.008v.008H6V6Z"/></svg>
                </span>
                <span class="site-menu-label">{{ __('messages.special_offers') }}</span>
                <span class="site-menu-num">03</span>
            </a>
        </nav>

        {{-- حساب کاربری — مهمان → لاگین، عضو → پنل کاربری --}}
        <div class="site-menu-account">
            @auth
                <a href="{{ localizedRoute('dashboard') }}" class="site-menu-account-link">
                    <span class="site-menu-account-icon">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                    <span class="site-menu-account-body">
                        <span class="site-menu-account-title">{{ auth()->user()->name }}</span>
                        <span class="site-menu-account-sub">{{ __('messages.dashboard') }}</span>
                    </span>
                    <svg class="site-menu-account-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </a>
            @else
                <a href="{{ localizedRoute('login') }}" class="site-menu-account-link">
                    <span class="site-menu-account-icon">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0 18.9 18.9 0 0 1-3.55 2.55H8.05A18.9 18.9 0 0 1 4.5 20.25Z"/>
                        </svg>
                    </span>
                    <span class="site-menu-account-body">
                        <span class="site-menu-account-title">{{ __('messages.login') }}</span>
                        <span class="site-menu-account-sub">{{ __('messages.login_subtitle') }}</span>
                    </span>
                    <svg class="site-menu-account-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </a>
            @endauth
        </div>

        <div class="site-menu-footer">
        </div>
    </div>
</div>

<script>
function notificationBell() {
    return {
        open: false,
        count: {{ $unreadNotificationCount ?? 0 }},
        items: [],
        loading: false,

        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.fetchNotifications();
            }
        },

        // نمایش هماهنگ با زبان از همان اول (بدون چشمک انگلیسی→فارسی)؛
        // localize-digits گره‌های تازه‌اضافه‌شده را پیش از paint همان فریم تبدیل می‌کند.
        displayCount() {
            const raw = this.count > 99 ? '99+' : String(this.count);
            return (window.localizeDigits && window.localizeDigits.convert)
                ? window.localizeDigits.convert(raw)
                : raw;
        },

        async fetchNotifications() {
            this.loading = true;
            try {
                const res = await fetch('{{ route("api.notifications.unread-count") }}', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const data = await res.json();
                    this.count = data.count ?? 0;
                }
            } catch (e) {}

            try {
                const res = await fetch('{{ route("dashboard.notifications") }}?format=json', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const data = await res.json();
                    const loc = (window.localizeDigits && window.localizeDigits.convert)
                        ? window.localizeDigits.convert
                        : (s) => s;
                    // عنوان/متن/تاریخ همان موقعِ نگاشت بومی می‌شوند (id و link دست نمی‌خورند)
                    this.items = (data.data || []).slice(0, 8).map((item) => ({
                        ...item,
                        title: item.title ? loc(item.title) : item.title,
                        body: item.body ? loc(item.body) : item.body,
                        created_at: item.created_at ? loc(item.created_at) : item.created_at,
                    }));
                    if (typeof data.count === 'number') this.count = data.count;
                }
            } catch (e) {}
            this.loading = false;
        },

        async markRead(id) {
            if (! id) return;
            try {
                await fetch('{{ url("/api/notifications") }}/' + id + '/read', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' }
                });
                const item = this.items.find(i => i.id === id);
                if (item) item.is_read = true;
                if (this.count > 0) this.count--;
            } catch (e) {}
        },

        async markAllRead() {
            try {
                await fetch('{{ url("/api/notifications/read-all") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' }
                });
                this.items.forEach(i => i.is_read = true);
                this.count = 0;
            } catch (e) {}
        },

    };
}
</script>
