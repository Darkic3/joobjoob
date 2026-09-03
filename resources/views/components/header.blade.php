@props(['cartCount' => 0, 'wishlistCount' => 0, 'unreadNotificationCount' => 0])

@php
    $siteName = setting('site_name', __('messages.site_name'));
@endphp

{{-- هدر شناور (Fluid Island) — دسکتاپ: نوار قرصی شیشه‌ای شناور؛ موبایل: لوگو + همبرگر مورف --}}
<header class="site-header-wrap sticky top-3 z-50 px-3 md:px-5">
    <div class="site-header max-w-7xl mx-auto">

        {{-- لوگو: مارک گرادینتی (Double-Bezel) + نام --}}
        <a href="{{ localizedRoute('home') }}" class="site-logo shrink-0" aria-label="{{ $siteName }}">
            <span class="site-logo-mark" aria-hidden="true">{{ mb_substr($siteName, 0, 1) }}</span>
            <span class="site-logo-text truncate">{{ $siteName }}</span>
        </a>

        {{-- ناو دسکتاپ --}}
        <nav class="hidden lg:flex items-center gap-1 ms-3" aria-label="{{ __('messages.navigation') }}">
            <a href="{{ localizedRoute('home') }}"
               class="site-nav-link {{ request()->routeIs('home', 'locale.home') ? 'is-active' : '' }}">
                {{ __('messages.home') }}
            </a>
            <a href="{{ localizedRoute('products.index') }}"
               class="site-nav-link {{ request()->routeIs('products.index', 'locale.products.index', 'products.category', 'locale.products.category') ? 'is-active' : '' }}">
                {{ __('messages.products') }}
            </a>
            <a href="{{ localizedRoute('products.index') }}#deals" class="site-nav-link">
                {{ __('messages.special_offers') }}
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

            @if (count($locales) > 1)
                <div class="relative hidden lg:block">
                    <button type="button" data-lang-dropdown-btn
                            class="site-lang-btn text-xs font-bold"
                            title="{{ __('messages.language') }}" aria-haspopup="true" aria-expanded="false">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M3.6 9h16.8M3.6 15h16.8M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18" stroke-linecap="round"/>
                        </svg>
                        <span>{{ strtoupper($currentLocale) }}</span>
                        <svg class="w-3 h-3 site-lang-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="m6 9 6 6 6-6"/>
                        </svg>
                    </button>
                    <div data-lang-dropdown-menu class="site-lang-menu hidden absolute end-0 top-full mt-2 p-2 w-44 z-50">
                        @foreach($locales as $code => $lang)
                            <a href="{{ localizedUrl($code) }}"
                               class="site-lang-item {{ $currentLocale === $code ? 'is-active' : '' }}">
                                <span class="site-lang-code">{{ strtoupper($code) }}</span>
                                <span class="flex-1">{{ $lang->localizedName() }}</span>
                                @if ($currentLocale === $code)
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.6" d="m5 13 4 4L19 7"/>
                                    </svg>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <button type="button" id="theme-toggle" class="neu-toggle hidden lg:block" title="{{ __('messages.dark_mode') }}">
                <span class="neu-toggle-dot"></span>
            </button>

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
                        <span x-show="count > 0" x-text="count > 99 ? '99+' : count"
                              x-transition
                              class="absolute -top-1 -end-1 min-w-[18px] h-[18px] px-1 text-[10px] font-bold rounded-full grid place-items-center"
                              style="background: var(--danger); color: #fff;"></span>
                    </button>

                    {{-- Dropdown اعلانات --}}
                    <div x-show="open" @click.away="open = false" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute end-0 top-full mt-2 w-80 rounded-2xl overflow-hidden z-50"
                         style="background: var(--bg-elevated); border: 1px solid color-mix(in srgb, var(--text-primary) 10%, transparent); box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
                        <div class="flex items-center justify-between px-4 py-3" style="border-bottom: 1px solid color-mix(in srgb, var(--text-primary) 8%, transparent);">
                            <p class="text-sm font-bold" style="color: var(--text-primary);">{{ __('messages.notifications') }}</p>
                            <button x-show="count > 0" @click="markAllRead()" type="button"
                                    class="text-[11px] font-bold transition-opacity hover:opacity-70"
                                    style="color: var(--accent);">
                                {{ __('messages.mark_all_read') }}
                            </button>
                        </div>
                        <div class="max-h-72 overflow-y-auto">
                            <template x-if="items.length === 0">
                                <div class="px-4 py-8 text-center">
                                    <svg class="w-10 h-10 mx-auto mb-2" style="color: var(--text-secondary); opacity: 0.4;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                                    <p class="text-xs font-bold" style="color: var(--text-secondary);">{{ __('messages.no_notifications') }}</p>
                                </div>
                            </template>
                            <template x-for="item in items" :key="item.id">
                                <a :href="item.link || '#'
                                   @click="!item.is_read && markRead(item.id)"
                                   class="flex items-start gap-3 px-4 py-3 transition-colors hover:opacity-80"
                                   :style="item.is_read ? '' : 'background: color-mix(in srgb, var(--accent) 5%, transparent);'">
                                    <span class="text-lg shrink-0 mt-0.5" x-text="item.icon || '🔔'"></span>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold truncate" style="color: var(--text-primary);" x-text="item.title"></p>
                                        <p class="text-[11px] mt-0.5 line-clamp-2" style="color: var(--text-secondary);" x-text="item.body"></p>
                                        <p class="text-[10px] mt-1" style="color: var(--text-secondary); opacity: 0.6;" x-text="item.created_at"></p>
                                    </div>
                                    <span x-show="!item.is_read" class="w-2 h-2 rounded-full shrink-0 mt-1.5" style="background: var(--accent);"></span>
                                </a>
                            </template>
                        </div>
                        <a href="{{ route('dashboard.notifications') }}"
                           class="block text-center py-2.5 text-xs font-bold transition-opacity hover:opacity-80"
                           style="color: var(--accent); border-top: 1px solid color-mix(in srgb, var(--text-primary) 8%, transparent);">
                            {{ __('messages.view_all_notifications') }}
                        </a>
                    </div>
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
                <span class="site-menu-num">01</span>
                <span class="site-menu-label">{{ __('messages.home') }}</span>
            </a>
            <a href="{{ localizedRoute('products.index') }}"
               class="site-menu-link {{ request()->routeIs('products.*', 'locale.products.*') ? 'is-active' : '' }}">
                <span class="site-menu-num">02</span>
                <span class="site-menu-label">{{ __('messages.products') }}</span>
            </a>
            <a href="{{ localizedRoute('products.index') }}#deals" class="site-menu-link">
                <span class="site-menu-num">03</span>
                <span class="site-menu-label">{{ __('messages.special_offers') }}</span>
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
            @if (count($locales) > 1)
                <div class="site-menu-langs" aria-label="{{ __('messages.language') }}">
                    @foreach($locales as $code => $lang)
                        <a href="{{ localizedUrl($code) }}"
                           class="site-menu-lang {{ $currentLocale === $code ? 'is-active' : '' }}">
                            {{ $lang->localizedName() }}
                        </a>
                    @endforeach
                </div>
            @endif

            <div class="site-menu-theme">
                <span class="site-menu-theme-label">{{ __('messages.dark_mode') }}</span>
                <button type="button" id="theme-toggle-menu" class="neu-toggle"
                        title="{{ __('messages.dark_mode') }}" aria-label="{{ __('messages.dark_mode') }}">
                    <span class="neu-toggle-dot"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function notificationBell() {
    return {
        open: false,
        count: {{ $unreadNotificationCount ?? 0 }},
        items: [],

        toggle() {
            this.open = !this.open;
            if (this.open && this.items.length === 0) {
                this.fetchNotifications();
            }
        },

        async fetchNotifications() {
            try {
                const res = await fetch('{{ route("api.notifications.unread-count") }}', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                const data = await res.json();
                this.count = data.count ?? 0;
            } catch (e) {}

            try {
                const res = await fetch('{{ route("dashboard.notifications") }}?format=json', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const data = await res.json();
                    this.items = (data.data || []).slice(0, 8);
                }
            } catch (e) {}
        },

        async markRead(id) {
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
        }
    };
}
</script>
