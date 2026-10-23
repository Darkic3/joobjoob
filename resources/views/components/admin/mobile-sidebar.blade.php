{{-- دسترسی‌ها و مقصد محصولات در کلاس Admin\MobileSidebar آماده می‌شود --}}
@php
    // جهت صفحه از LocaleComposer سراسری می‌آید. برای چسباندن کشو به لبه، از کلاس‌های
    // فیزیکی (right-0/left-0) استفاده می‌شود؛ چون خاصیت منطقی inset-inline-start
    // (start-0) روی مرورگرهای قدیمی سامسونگ پشتیبانی نمی‌شود و کشو وسط صفحه می‌افتاد.
    // کلاس‌های اسلاید هم سمت سرور ثابت ساخته می‌شوند؛ Alpine بایندینگِ داینامیک
    // `:x-transition:enter-start` را به‌عنوان دایرکتیو ترنزیشن اجرا نمی‌کند.
    $isRtl = ($direction ?? 'ltr') === 'rtl';
    $edgeClass = $isRtl ? 'right-0' : 'left-0';
    $slideClass = $isRtl ? 'translate-x-full' : '-translate-x-full';
@endphp

{{-- کشو خودمحتهای state دارد و مستقیم زیر <body> تله‌پورت می‌شود تا هیچ --}}
{{-- والدی (flex/transform/overflow) روی position:fixed اثر نگذارد --}}
<div x-data="{ mobileSidebar: false }"
     @toggle-mobile.window="mobileSidebar = true"
     @close-mobile.window="mobileSidebar = false">

    <template x-teleport="body">
        <div x-cloak>

    {{-- Overlay --}}
    <div x-show="mobileSidebar" x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         @click="$dispatch('close-mobile')"
         class="fixed inset-0 bg-gray-900/50 z-40 lg:hidden" x-cloak></div>

    {{-- Sidebar Drawer --}}
    <aside x-show="mobileSidebar" x-cloak
           x-transition:enter="transition ease-out duration-300"
           x-transition:enter-start="{{ $slideClass }}"
           x-transition:enter-end="translate-x-0"
           x-transition:leave="transition ease-in duration-200"
           x-transition:leave-start="translate-x-0"
           x-transition:leave-end="{{ $slideClass }}"
           class="fixed inset-y-0 {{ $edgeClass }} w-72 bg-white dark:bg-gray-800 z-50 lg:hidden flex flex-col shadow-lg">

    <div class="flex items-center justify-between h-16 px-4 border-b border-gray-100 dark:border-gray-700">
        <a href="{{ route('admin.dashboard.index') }}" class="flex items-center gap-2.5">
            <div class="w-9 h-9 bg-primary-600 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-lg">S</span>
            </div>
            <span class="font-bold text-gray-800 dark:text-gray-100">Joobjoob</span>
        </a>
        <button @click="$dispatch('close-mobile')" class="p-1.5 rounded-lg text-gray-400 dark:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
        {{-- داشبورد (free) --}}
        <a href="{{ route('admin.dashboard.index') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard.*') ? 'sidebar-link-active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 12a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1v-7z"/>
            </svg>
            <span class="whitespace-nowrap">{{ __('admin.sidebar.dashboard') }}</span>
        </a>

        @if($showContentDivider)
            <div class="pt-4 pb-1">
                <p class="px-3 text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ __('admin.sidebar.content_mgmt') }}</p>
            </div>
        @endif

        @if($can('admin.hero-slides.index'))
            <a href="{{ route('admin.hero-slides.index') }}" class="sidebar-link {{ request()->routeIs('admin.hero-slides.*') ? 'sidebar-link-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="whitespace-nowrap">{{ __('admin.sidebar.hero_slider') }}</span>
            </a>
        @endif

        @if($showProducts)
            <a href="{{ route($productsTarget) }}" class="sidebar-link {{ request()->routeIs('admin.products.*') || request()->routeIs('admin.brands.*') || request()->routeIs('admin.specifications.*') ? 'sidebar-link-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span class="whitespace-nowrap">{{ __('admin.sidebar.products') }}</span>
            </a>
        @endif

        @if($can('admin.product-categories.index'))
            <a href="{{ route('admin.product-categories.index') }}" class="sidebar-link {{ request()->routeIs('admin.product-categories.*') ? 'sidebar-link-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                <span class="whitespace-nowrap">{{ __('admin.sidebar.product_categories') }}</span>
            </a>
        @endif

        @if($showBlog)
            <a href="{{ route($can('admin.blogs.index') ? 'admin.blogs.index' : 'admin.blog-categories.index') }}" class="sidebar-link {{ request()->routeIs('admin.blogs.*') || request()->routeIs('admin.blog-categories.*') ? 'sidebar-link-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                </svg>
                <span class="whitespace-nowrap">{{ __('admin.sidebar.blog') }}</span>
            </a>
        @endif

        @if($can('admin.trust-badges.index'))
            <a href="{{ route('admin.trust-badges.index') }}" class="sidebar-link {{ request()->routeIs('admin.trust-badges.*') ? 'sidebar-link-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="whitespace-nowrap">{{ __('admin.sidebar.trust_badges') }}</span>
            </a>
        @endif

        @if($can('admin.promo-banners.index'))
            <a href="{{ route('admin.promo-banners.index') }}" class="sidebar-link {{ request()->routeIs('admin.promo-banners.*') ? 'sidebar-link-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <span class="whitespace-nowrap">{{ __('admin.sidebar.promo_banners') }}</span>
            </a>
        @endif

        @if($can('admin.reviews.index'))
            <a href="{{ route('admin.reviews.index') }}" class="sidebar-link {{ request()->routeIs('admin.reviews.*') ? 'sidebar-link-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <span class="whitespace-nowrap">{{ __('admin.sidebar.reviews') }}</span>
                @if($pendingReviewsCount > 0)
                    <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[10px] font-bold rounded-full bg-red-500 text-white">
                        {{ $pendingReviewsCount > 99 ? '99+' : $pendingReviewsCount }}
                    </span>
                @endif
            </a>
        @endif

        @if($can('admin.discounts.index'))
            <a href="{{ route('admin.discounts.index') }}" class="sidebar-link {{ request()->routeIs('admin.discounts.*') ? 'sidebar-link-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                <span class="whitespace-nowrap">{{ __('admin.sidebar.discounts') }}</span>
            </a>
        @endif

        @if($can('admin.contact.index'))
            <a href="{{ route('admin.contact.index') }}" class="sidebar-link {{ request()->routeIs('admin.contact.*') ? 'sidebar-link-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.9 5.3a2 2 0 002.2 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <span class="whitespace-nowrap">{{ __('admin.sidebar.contact') }}</span>
                @if($unreadContactsCount > 0)
                    <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[10px] font-bold rounded-full bg-red-500 text-white">
                        {{ $unreadContactsCount > 99 ? '99+' : $unreadContactsCount }}
                    </span>
                @endif
            </a>
        @endif

        @if($showSettingsDivider)
            <div class="pt-4 pb-1">
                <p class="px-3 text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ __('admin.sidebar.settings') }}</p>
            </div>
        @endif

        @if($can('admin.settings.edit'))
            <a href="{{ route('admin.settings.edit') }}" class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'sidebar-link-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span class="whitespace-nowrap">{{ __('admin.sidebar.settings') }}</span>
            </a>
        @endif

        @if($can('admin.audit-logs.index'))
            <a href="{{ route('admin.audit-logs.index') }}" class="sidebar-link {{ request()->routeIs('admin.audit-logs.*') ? 'sidebar-link-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <span class="whitespace-nowrap">{{ __('admin.sidebar.audit_logs') }}</span>
            </a>
        @endif

        @if($can('admin.roles.index'))
            <a href="{{ route('admin.roles.index') }}" class="sidebar-link {{ request()->routeIs('admin.roles.*') ? 'sidebar-link-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span class="whitespace-nowrap">{{ __('admin.sidebar.roles') }}</span>
            </a>
        @endif

        @if($can('admin.users.index'))
            <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'sidebar-link-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span class="whitespace-nowrap">{{ __('admin.sidebar.users') }}</span>
            </a>
        @endif

        <form method="POST" action="{{ route('admin.logout') }}" class="pt-4 mt-4 border-t border-gray-100 dark:border-gray-700">
            @csrf
            <button type="submit" class="sidebar-link w-full text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-700 dark:hover:text-red-300 border border-red-200 dark:border-red-800">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span class="whitespace-nowrap">{{ __('admin.sidebar.logout') }}</span>
            </button>
        </form>

    </nav>
    </aside>

        </div>
    </template>
</div>
