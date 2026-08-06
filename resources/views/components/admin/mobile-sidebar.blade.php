{{-- Overlay --}}
<div x-show="mobileSidebar" x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     @click="$dispatch('close-mobile')"
     class="fixed inset-0 bg-gray-900/50 z-40 lg:hidden" x-cloak></div>

{{-- Sidebar Drawer --}}
<aside x-show="mobileSidebar" x-cloak
       x-data="{ isRtl: document.documentElement.getAttribute('dir') === 'rtl' }"
       x-transition:enter="transition ease-out duration-300"
       :x-transition:enter-start="isRtl ? 'translate-x-full' : '-translate-x-full'"
       x-transition:enter-end="translate-x-0"
       x-transition:leave="transition ease-in duration-200"
       x-transition:leave-start="translate-x-0"
       :x-transition:leave-end="isRtl ? 'translate-x-full' : '-translate-x-full'"
       class="fixed inset-y-0 start-0 w-72 bg-white dark:bg-gray-800 z-50 lg:hidden flex flex-col shadow-lg">

    <div class="flex items-center justify-between h-16 px-4 border-b border-gray-100 dark:border-gray-700">
        <a href="{{ route('admin.dashboard.index') }}" class="flex items-center gap-2.5">
            <div class="w-9 h-9 bg-primary-600 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-lg">S</span>
            </div>
            <span class="font-bold text-gray-800 dark:text-gray-100">Shopora</span>
        </a>
        <button @click="$dispatch('close-mobile')" class="p-1.5 rounded-lg text-gray-400 dark:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
        <a href="{{ route('admin.dashboard.index') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard.*') ? 'sidebar-link-active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 12a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1v-7z"/>
            </svg>
            <span class="whitespace-nowrap">{{ __('admin.sidebar.dashboard') }}</span>
        </a>

        <div class="pt-4 pb-1">
            <p class="px-3 text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ __('admin.sidebar.content_mgmt') }}</p>
        </div>

        <a href="{{ route('admin.hero-slides.index') }}" class="sidebar-link {{ request()->routeIs('admin.hero-slides.*') ? 'sidebar-link-active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="whitespace-nowrap">{{ __('admin.sidebar.hero_slider') }}</span>
        </a>

        <a href="{{ route('admin.blogs.index') }}" class="sidebar-link {{ request()->routeIs('admin.blogs.*') ? 'sidebar-link-active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
            </svg>
            <span class="whitespace-nowrap">{{ __('admin.sidebar.articles') }}</span>
        </a>

        <a href="{{ route('admin.trust-badges.index') }}" class="sidebar-link {{ request()->routeIs('admin.trust-badges.*') ? 'sidebar-link-active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="whitespace-nowrap">{{ __('admin.sidebar.trust_badges') }}</span>
        </a>

        <div class="pt-4 pb-1">
            <p class="px-3 text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ __('admin.sidebar.settings') }}</p>
        </div>

        <a href="{{ route('admin.roles.index') }}" class="sidebar-link {{ request()->routeIs('admin.roles.*') ? 'sidebar-link-active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span class="whitespace-nowrap">{{ __('admin.sidebar.roles') }}</span>
        </a>

        <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'sidebar-link-active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span class="whitespace-nowrap">{{ __('admin.sidebar.users') }}</span>
        </a>

    </nav>
</aside>
