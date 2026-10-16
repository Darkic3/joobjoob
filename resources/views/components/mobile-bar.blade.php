@props(['cartCount' => 0, 'wishlistCount' => 0])

{{--
    نوار پایین موبایل (Sticky Mobile Bar) — فقط زیر lg نمایش داده می‌شود.
    چهار آیتم: خانه / جستجو / علاقه‌مندی‌ها / سبد خرید.
    جستجو یک شیت لغزنده باز می‌کند (بدون Alpine — با JS خالص در ui-effects.js).
--}}
<div class="lg:hidden">
    {{-- شیت جستجو --}}
    <div data-mobile-search
         class="mobile-search-sheet"
         role="dialog" aria-modal="true" aria-label="{{ __('messages.search_placeholder') }}" aria-hidden="true">
        <div class="mobile-search-backdrop" data-mobile-search-close></div>
        <div class="mobile-search-panel">
            <form action="{{ localizedRoute('products.index') }}" method="GET"
                  class="flex items-center gap-2">
                <svg class="w-5 h-5 shrink-0" style="color:var(--eco-ink-3);" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-4.35-4.35M17 10.5A6.5 6.5 0 1 1 4 10.5a6.5 6.5 0 0 1 13 0Z"/>
                </svg>
                <input type="text" name="q" value="{{ request('q') }}"
                       data-mobile-search-input
                       placeholder="{{ __('messages.search_placeholder') }}"
                       class="eco-input flex-1 text-sm"
                       autocomplete="off">
                <button type="submit" class="eco-btn eco-btn--clay eco-btn--sm shrink-0">
                    {{ __('messages.search') }}
                </button>
                <button type="button" data-mobile-search-close aria-label="{{ __('messages.close') }}"
                        class="eco-btn eco-btn--outline eco-btn--icon shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>

    {{-- نوار اصلی --}}
    <nav aria-label="{{ __('messages.mobile_nav') }}"
         class="mobile-bar fixed inset-x-0 bottom-0 z-40">
        <div class="mobile-bar-pill">
            <a href="{{ localizedRoute('home') }}"
               class="mobile-bar-item {{ request()->routeIs('home', 'locale.home') ? 'mobile-bar-active' : '' }}"
               aria-label="{{ __('messages.home') }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m2.25 12 8.954-8.955a1.126 1.126 0 0 1 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/>
                </svg>
                <span>{{ __('messages.home') }}</span>
            </a>

            <button type="button" data-mobile-search-btn
                    class="mobile-bar-item"
                    aria-label="{{ __('messages.search_placeholder') }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                </svg>
                <span>{{ __('messages.search') }}</span>
            </button>

            <a href="{{ localizedRoute('wishlist.index') }}"
               class="mobile-bar-item {{ request()->routeIs('wishlist.index', 'locale.wishlist.index') ? 'mobile-bar-active' : '' }}"
               aria-label="{{ __('messages.wishlist') }}">
                <span class="relative">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.318 6.318a4.5 4.5 0 0 1 6.364 0L12 7.636l1.318-1.318a4.5 4.5 0 1 1 6.364 6.364L12 21l-7.682-7.682a4.5 4.5 0 0 1 0-6.364Z"/>
                    </svg>
                    <span id="wishlist-count-mobile"
                          class="absolute -top-1.5 -end-2.5 rounded-full text-[10px] font-bold w-4 h-4 grid place-items-center"
                          style="background:var(--eco-clay); color:#fff;">{{ $wishlistCount }}</span>
                </span>
                <span>{{ __('messages.wishlist') }}</span>
            </a>

            <a href="{{ localizedRoute('cart.index') }}"
               class="mobile-bar-item {{ request()->routeIs('cart.index', 'locale.cart.index') ? 'mobile-bar-active' : '' }}"
               aria-label="{{ __('messages.cart') }}">
                <span class="relative">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 1.94-4.746 2.437-7.28a1.125 1.125 0 0 0-1.11-1.35H5.106M7.5 14.25 5.106 5.106M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>
                    </svg>
                    <span id="cart-count-mobile"
                          class="absolute -top-1.5 -end-2.5 rounded-full text-[10px] font-bold w-4 h-4 grid place-items-center"
                          style="background:var(--eco-clay); color:#fff;">{{ $cartCount }}</span>
                </span>
                <span>{{ __('messages.cart') }}</span>
            </a>
        </div>
    </nav>
</div>
