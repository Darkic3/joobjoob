<aside :class="sidebarOpen ? 'w-64' : 'w-20'"
       class="hidden lg:flex flex-col bg-white dark:bg-gray-800 border-s border-gray-200 dark:border-gray-700 transition-all duration-300 ease-in-out fixed inset-y-0 right-0 z-30 shadow-sm">

    {{-- Sidebar Header --}}
    <div class="flex items-center h-16 border-b border-gray-100 dark:border-gray-700 transition-all duration-300"
         :class="sidebarOpen ? 'justify-between px-4' : 'justify-center px-2'">

        <a href="{{ route('admin.dashboard.index') }}" class="flex items-center gap-2.5 overflow-hidden">
            <div class="flex-shrink-0 w-9 h-9 bg-gradient-to-br from-primary-600 to-primary-700 rounded-xl flex items-center justify-center shadow-md">
                <span class="text-white font-bold text-lg">S</span>
            </div>
            <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200"
                  x-transition:enter-start="opacity-0 -translate-x-2" x-transition:enter-end="opacity-100 translate-x-0"
                  class="font-bold text-gray-800 dark:text-gray-100 whitespace-nowrap">
                Shopora
            </span>
        </a>

        <button x-show="sidebarOpen" @click="$dispatch('toggle-sidebar')"
                class="p-1.5 rounded-lg text-gray-400 dark:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-600 dark:hover:text-gray-300 transition-colors flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
            </svg>
        </button>
    </div>

    {{-- Sidebar Navigation --}}
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">

        {{-- داشبورد --}}
        <a href="{{ route('admin.dashboard.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.dashboard.*') ? 'sidebar-link-active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 12a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1v-7z"/>
            </svg>
            <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">{{ __('admin.sidebar.dashboard') }}</span>
        </a>

        {{-- Divider: مدیریت محتوا --}}
        <div x-show="sidebarOpen" class="pt-4 pb-1">
            <p class="px-3 text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ __('admin.sidebar.content_mgmt') }}</p>
        </div>

        {{-- Hero-Slide --}}
        <div x-data="{ open: {{ request()->routeIs('admin.hero-slides.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="sidebar-link w-full {{ request()->routeIs('admin.hero-slides.*') ? 'sidebar-link-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span x-show="sidebarOpen" x-transition class="whitespace-nowrap flex-1 text-right">{{ __('admin.sidebar.hero_slider') }}</span>
                <svg x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -rotate-90" x-transition:enter-end="opacity-100 rotate-0"
                     :class="open ? '-rotate-90' : ''"
                     class="w-4 h-4 transition-transform duration-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>

            {{-- Subs --}}
            <div x-show="open" x-collapse x-cloak class="sidebar-submenu">
                <a href="{{ route('admin.hero-slides.index') }}"
                   class="block px-3 py-2 text-sm rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white {{ request()->routeIs('admin.hero-slides.index') ? 'text-primary-700 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 font-medium' : '' }}">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                {{ __('admin.sidebar.slides_list') }}
            </span>
                </a>
                <a href="{{ route('admin.hero-slides.create') }}"
                   class="block px-3 py-2 text-sm rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white {{ request()->routeIs('admin.hero-slides.create') ? 'text-primary-700 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 font-medium' : '' }}">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ __('admin.sidebar.add_slide') }}
            </span>
                </a>
            </div>
        </div>

        {{-- ========================================== --}}
        {{-- مدیریت محصولات --}}
        {{-- ========================================== --}}
        <div x-data="{ open: {{ request()->routeIs('admin.products.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="sidebar-link w-full {{ request()->routeIs('admin.products.*') ? 'sidebar-link-active' : '' }}">
                {{-- آیکون جعبه/محصول --}}
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span x-show="sidebarOpen" x-transition class="whitespace-nowrap flex-1 text-right">{{ __('admin.sidebar.products') }}</span>
                <svg x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -rotate-90" x-transition:enter-end="opacity-100 rotate-0"
                     :class="open ? '-rotate-90' : ''"
                     class="w-4 h-4 transition-transform duration-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>

            {{-- زیرمنوهای محصول --}}
            <div x-show="open" x-collapse x-cloak class="sidebar-submenu">
                <a href="{{ route('admin.products.index') }}"
                   class="block px-3 py-2 text-sm rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white {{ request()->routeIs('admin.products.index') ? 'text-primary-700 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 font-medium' : '' }}">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                {{ __('admin.sidebar.products_list') }}
            </span>
                </a>
                <a href="{{ route('admin.products.create') }}"
                   class="block px-3 py-2 text-sm rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white {{ request()->routeIs('admin.products.create') ? 'text-primary-700 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 font-medium' : '' }}">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ __('admin.sidebar.add_product') }}
            </span>
                </a>
                <a href="{{ route('admin.brands.index') }}"
                   class="block px-3 py-2 text-sm rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white {{ request()->routeIs('admin.brands.*') ? 'text-primary-700 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 font-medium' : '' }}">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                {{ __('admin.sidebar.brands') }}
            </span>
                </a>
                <a href="{{ route('admin.specifications.index') }}"
                   class="block px-3 py-2 text-sm rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white {{ request()->routeIs('admin.specifications.*') ? 'text-primary-700 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 font-medium' : '' }}">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                {{ __('admin.sidebar.specs') }}
            </span>
                </a>
            </div>
        </div>

        {{-- ========================================== --}}
        {{-- مدیریت دسته‌بندی محصولات --}}
        {{-- ========================================== --}}
        <div x-data="{ open: {{ request()->routeIs('admin.product-categories.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="sidebar-link w-full {{ request()->routeIs('admin.product-categories.*') ? 'sidebar-link-active' : '' }}">
                {{-- آیکون تگ/دسته‌بندی --}}
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                <span x-show="sidebarOpen" x-transition class="whitespace-nowrap flex-1 text-right">{{ __('admin.sidebar.product_categories') }}</span>
                <svg x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -rotate-90" x-transition:enter-end="opacity-100 rotate-0"
                     :class="open ? '-rotate-90' : ''"
                     class="w-4 h-4 transition-transform duration-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>

            {{-- زیرمنوهای دسته‌بندی --}}
            <div x-show="open" x-collapse x-cloak class="sidebar-submenu">
                <a href="{{ route('admin.product-categories.index') }}"
                   class="block px-3 py-2 text-sm rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white {{ request()->routeIs('admin.product-categories.index') ? 'text-primary-700 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 font-medium' : '' }}">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                {{ __('admin.sidebar.categories_list') }}
            </span>
                </a>
                <a href="{{ route('admin.product-categories.create') }}"
                   class="block px-3 py-2 text-sm rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white {{ request()->routeIs('admin.product-categories.create') ? 'text-primary-700 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 font-medium' : '' }}">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ __('admin.sidebar.add_category') }}
            </span>
                </a>
            </div>
        </div>

        {{-- Blog --}}
        <div x-data="{ open: {{ request()->routeIs('admin.blogs.*') || request()->routeIs('admin.blog-categories.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="sidebar-link w-full {{ request()->routeIs('admin.blogs.*') || request()->routeIs('admin.blog-categories.*') ? 'sidebar-link-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                </svg>
                <span x-show="sidebarOpen" x-transition class="whitespace-nowrap flex-1 text-right">{{ __('admin.sidebar.blog') }}</span>
                <svg x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -rotate-90" x-transition:enter-end="opacity-100 rotate-0"
                     :class="open ? '-rotate-90' : ''"
                     class="w-4 h-4 transition-transform duration-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>

            {{-- Subs --}}
            <div x-show="open" x-collapse x-cloak class="sidebar-submenu">
                <a href="{{ route('admin.blogs.index') }}"
                   class="block px-3 py-2 text-sm rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white {{ request()->routeIs('admin.blogs.*') ? 'text-primary-700 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 font-medium' : '' }}">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        {{ __('admin.sidebar.articles') }}
                    </span>
                </a>
                <a href="{{ route('admin.blog-categories.index') }}"
                   class="block px-3 py-2 text-sm rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white {{ request()->routeIs('admin.blog-categories.*') ? 'text-primary-700 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 font-medium' : '' }}">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        {{ __('admin.sidebar.blog_categories') }}
                    </span>
                </a>
            </div>
        </div>

        {{-- Trust Badges --}}
        <div x-data="{ open: {{ request()->routeIs('admin.trust-badges.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="sidebar-link w-full {{ request()->routeIs('admin.trust-badges.*') ? 'sidebar-link-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span x-show="sidebarOpen" x-transition class="whitespace-nowrap flex-1 text-right">{{ __('admin.sidebar.trust_badges') }}</span>
                <svg x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -rotate-90" x-transition:enter-end="opacity-100 rotate-0"
                     :class="open ? '-rotate-90' : ''"
                     class="w-4 h-4 transition-transform duration-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>

            {{-- Subs --}}
            <div x-show="open" x-collapse x-cloak class="sidebar-submenu">
                <a href="{{ route('admin.trust-badges.index') }}"
                   class="block px-3 py-2 text-sm rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white {{ request()->routeIs('admin.trust-badges.*') ? 'text-primary-700 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 font-medium' : '' }}">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                        {{ __('admin.sidebar.manage_badges') }}
                    </span>
                </a>
            </div>
        </div>

        {{-- Divider: تنظیمات --}}
        <div x-show="sidebarOpen" class="pt-4 pb-1">
            <p class="px-3 text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ __('admin.sidebar.settings') }}</p>
        </div>

        {{-- تنظیمات سایت --}}
        <a href="{{ route('admin.settings.edit', 'general') }}"
           class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'sidebar-link-active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">{{ __('admin.sidebar.settings') }}</span>
        </a>

        {{-- خروج از حساب --}}
        <div class="pt-4 mt-4 border-t border-gray-100 dark:border-gray-700">
            <a href="#" class="sidebar-link text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-700 dark:hover:text-red-300 border border-red-200 dark:border-red-800">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">{{ __('admin.sidebar.logout') }}</span>
            </a>
        </div>
    </nav>
</aside>
