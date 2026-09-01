{{-- دسترسی‌ها و وضعیت باز بودن منوها در کلاس Admin\Sidebar آماده می‌شود --}}
<aside :class="sidebarOpen ? 'w-64' : 'w-20'"
       class="hidden lg:flex flex-col bg-white dark:bg-gray-800 border-s border-gray-200 dark:border-gray-700 transition-all duration-300 ease-in-out fixed inset-y-0 start-0 z-30 shadow-sm">

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

        {{-- داشبورد (free — هر ادمین لاگین‌شده) --}}
        <a href="{{ route('admin.dashboard.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.dashboard.*') ? 'sidebar-link-active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 12a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1v-7z"/>
            </svg>
            <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">{{ __('admin.sidebar.dashboard') }}</span>
        </a>

        {{-- مدیریت محتوا — فقط اگر حداقل یک بخش محتوا در دسترس باشد --}}
        @if($showContentDivider)
            <div x-show="sidebarOpen" class="pt-4 pb-1">
                <p class="px-3 text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ __('admin.sidebar.content_mgmt') }}</p>
            </div>
        @endif

        @if($can('admin.hero-slides.index'))
            {{-- اسلایدر اصلی --}}
            <a href="{{ route('admin.hero-slides.index') }}"
               class="sidebar-link {{ request()->routeIs('admin.hero-slides.*') ? 'sidebar-link-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">{{ __('admin.sidebar.hero_slider') }}</span>
            </a>
        @endif

        @if($showProducts)
            {{-- محصولات — گروه دارای زیرمنو (مدیریت محصولات / برندها / مشخصات) --}}
            <div x-data="{ open: {{ $productsOpen ? 'true' : 'false' }} }" @sidebar-closed.window="open = false">
                <button @click="if (!sidebarOpen) { sidebarOpen = true; open = true; } else { open = !open; }"
                        class="sidebar-link w-full {{ $productsOpen ? 'sidebar-link-active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <span x-show="sidebarOpen" x-transition class="sidebar-label">{{ __('admin.sidebar.products') }}</span>
                    <svg x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                         :class="open ? '-rotate-90' : 'ltr:rotate-180'"
                         class="w-4 h-4 transition-transform duration-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>

                <div x-show="open" x-collapse x-cloak class="sidebar-submenu">
                    @if($can('admin.products.index'))
                        <a href="{{ route('admin.products.index') }}"
                           class="block px-3 py-2 text-sm rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white {{ request()->routeIs('admin.products.*') ? 'text-primary-700 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 font-medium' : '' }}">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                {{ __('admin.sidebar.products_list') }}
                            </span>
                        </a>
                    @endif
                    @if($can('admin.brands.index'))
                        <a href="{{ route('admin.brands.index') }}"
                           class="block px-3 py-2 text-sm rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white {{ request()->routeIs('admin.brands.*') ? 'text-primary-700 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 font-medium' : '' }}">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                {{ __('admin.sidebar.brands') }}
                            </span>
                        </a>
                    @endif
                    @if($can('admin.specifications.index'))
                        <a href="{{ route('admin.specifications.index') }}"
                           class="block px-3 py-2 text-sm rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white {{ request()->routeIs('admin.specifications.*') ? 'text-primary-700 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 font-medium' : '' }}">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                                {{ __('admin.sidebar.specs') }}
                            </span>
                        </a>
                    @endif
                </div>
            </div>
        @endif

        @if($can('admin.product-categories.index'))
            {{-- دسته‌بندی محصولات --}}
            <a href="{{ route('admin.product-categories.index') }}"
               class="sidebar-link {{ request()->routeIs('admin.product-categories.*') ? 'sidebar-link-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">{{ __('admin.sidebar.product_categories') }}</span>
            </a>
        @endif

        @if($showBlog)
            {{-- وبلاگ — گروه دارای زیرمنو (مقالات / دسته‌بندی‌ها) --}}
            <div x-data="{ open: {{ $blogOpen ? 'true' : 'false' }} }" @sidebar-closed.window="open = false">
                <button @click="if (!sidebarOpen) { sidebarOpen = true; open = true; } else { open = !open; }"
                        class="sidebar-link w-full {{ $blogOpen ? 'sidebar-link-active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                    </svg>
                    <span x-show="sidebarOpen" x-transition class="sidebar-label">{{ __('admin.sidebar.blog') }}</span>
                    <svg x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                         :class="open ? '-rotate-90' : 'ltr:rotate-180'"
                         class="w-4 h-4 transition-transform duration-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>

                <div x-show="open" x-collapse x-cloak class="sidebar-submenu">
                    @if($can('admin.blogs.index'))
                        <a href="{{ route('admin.blogs.index') }}"
                           class="block px-3 py-2 text-sm rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white {{ request()->routeIs('admin.blogs.*') ? 'text-primary-700 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 font-medium' : '' }}">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                {{ __('admin.sidebar.articles') }}
                            </span>
                        </a>
                    @endif
                    @if($can('admin.blog-categories.index'))
                        <a href="{{ route('admin.blog-categories.index') }}"
                           class="block px-3 py-2 text-sm rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white {{ request()->routeIs('admin.blog-categories.*') ? 'text-primary-700 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 font-medium' : '' }}">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                                {{ __('admin.sidebar.blog_categories') }}
                            </span>
                        </a>
                    @endif
                </div>
            </div>
        @endif

        @if($can('admin.trust-badges.index'))
            {{-- نشان‌های اعتماد --}}
            <a href="{{ route('admin.trust-badges.index') }}"
               class="sidebar-link {{ request()->routeIs('admin.trust-badges.*') ? 'sidebar-link-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">{{ __('admin.sidebar.trust_badges') }}</span>
            </a>
        @endif

        @if($can('admin.promo-banners.index'))
            {{-- بنرهای میانی صفحهٔ اصلی --}}
            <a href="{{ route('admin.promo-banners.index') }}"
               class="sidebar-link {{ request()->routeIs('admin.promo-banners.*') ? 'sidebar-link-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">{{ __('admin.sidebar.promo_banners') }}</span>
            </a>
        @endif

        @if($can('admin.reviews.index'))
            {{-- نظرات محصولات --}}
            <a href="{{ route('admin.reviews.index') }}"
               class="sidebar-link {{ request()->routeIs('admin.reviews.*') ? 'sidebar-link-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">{{ __('admin.sidebar.reviews') }}</span>
                @if($pendingReviewsCount > 0)
                    <span x-show="sidebarOpen" x-transition
                          class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[10px] font-bold rounded-full bg-red-500 text-white">
                        {{ $pendingReviewsCount > 99 ? '99+' : $pendingReviewsCount }}
                    </span>
                @endif
            </a>
        @endif

        {{-- سفارشات --}}
        @if($can('admin.orders.index'))
            <a href="{{ route('admin.orders.index') }}"
               class="sidebar-link {{ request()->routeIs('admin.orders.*') ? 'sidebar-link-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">{{ __('admin.sidebar.orders') }}</span>
                @if($newOrdersCount > 0)
                    <span x-show="sidebarOpen" x-transition
                          class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[10px] font-bold rounded-full bg-red-500 text-white">
                        {{ $newOrdersCount > 99 ? '99+' : $newOrdersCount }}
                    </span>
                @endif
            </a>
        @endif

        @if($can('admin.contact.index'))
            {{-- پیام‌های تماس --}}
            <a href="{{ route('admin.contact.index') }}"
               class="sidebar-link {{ request()->routeIs('admin.contact.*') ? 'sidebar-link-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.9 5.3a2 2 0 002.2 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">{{ __('admin.sidebar.contact') }}</span>
                @if($unreadContactsCount > 0)
                    <span x-show="sidebarOpen" x-transition
                          class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[10px] font-bold rounded-full bg-red-500 text-white">
                        {{ $unreadContactsCount > 99 ? '99+' : $unreadContactsCount }}
                    </span>
                @endif
            </a>
        @endif

        @if($showSettingsDivider)
            <div x-show="sidebarOpen" class="pt-4 pb-1">
                <p class="px-3 text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ __('admin.sidebar.settings') }}</p>
            </div>
        @endif

        @if($can('admin.shipping-methods.index'))
            {{-- روش‌های ارسال --}}
            <a href="{{ route('admin.shipping-methods.index') }}"
               class="sidebar-link {{ request()->routeIs('admin.shipping-methods.*') ? 'sidebar-link-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25m-2.25 0h-2.25m0 0v-.958c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.958m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                </svg>
                <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">{{ __('admin.sidebar.shipping_methods') }}</span>
            </a>
        @endif

        @if($can('admin.banks.index'))
            {{-- درگاه‌های پرداخت --}}
            <a href="{{ route('admin.banks.index') }}"
               class="sidebar-link {{ request()->routeIs('admin.banks.*') ? 'sidebar-link-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/>
                </svg>
                <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">{{ __('admin.sidebar.banks') }}</span>
            </a>
        @endif

        @if($can('admin.settings.edit'))
            {{-- تنظیمات سایت --}}
            <a href="{{ route('admin.settings.edit') }}"
               class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'sidebar-link-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">{{ __('admin.sidebar.settings') }}</span>
            </a>
        @endif

        @if($can('admin.audit-logs.index'))
            {{--loguهای حسابرسی --}}
            <a href="{{ route('admin.audit-logs.index') }}"
               class="sidebar-link {{ request()->routeIs('admin.audit-logs.*') ? 'sidebar-link-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">{{ __('admin.sidebar.audit_logs') }}</span>
            </a>
        @endif

        @if($can('admin.roles.index'))
            {{-- نقش‌ها و دسترسی‌ها --}}
            <a href="{{ route('admin.roles.index') }}"
               class="sidebar-link {{ request()->routeIs('admin.roles.*') ? 'sidebar-link-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">{{ __('admin.sidebar.roles') }}</span>
            </a>
        @endif

        @if($can('admin.languages.index'))
            {{-- زبان‌ها --}}
            <a href="{{ route('admin.languages.index') }}"
               class="sidebar-link {{ request()->routeIs('admin.languages.*') ? 'sidebar-link-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21a9 9 0 100-18 9 9 0 000 18zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7zM8 9h8m-8 3h5m-5 3h2"/>
                </svg>
                <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">{{ __('admin.sidebar.languages') }}</span>
            </a>
        @endif

        @if($can('admin.users.index'))
            {{-- کاربران --}}
            <a href="{{ route('admin.users.index') }}"
               class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'sidebar-link-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">{{ __('admin.sidebar.users') }}</span>
            </a>
        @endif

        {{-- خروج از حساب — فرم POST (csrf) به جای لینک --}}
        <form method="POST" action="{{ route('admin.logout') }}" class="pt-4 mt-4 border-t border-gray-100 dark:border-gray-700">
            @csrf
            <button type="submit" class="sidebar-link w-full text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-700 dark:hover:text-red-300 border border-red-200 dark:border-red-800">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">{{ __('admin.sidebar.logout') }}</span>
            </button>
        </form>
    </nav>
</aside>
