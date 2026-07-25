@props(['sidebarOpen' => true])

<aside :class="sidebarOpen ? 'w-64' : 'w-20'"
       class="hidden lg:flex flex-col bg-white border-s border-gray-200 transition-all duration-300 ease-in-out fixed inset-y-0 right-0 z-30 shadow-sm">

    {{-- Sidebar Header - اصلاح شده --}}
    <div class="flex items-center h-16 border-b border-gray-100 transition-all duration-300"
         :class="sidebarOpen ? 'justify-between px-4' : 'justify-center px-2'">

        {{-- لوگو --}}
        <a href="{{ route('admin.dashboard.index') }}" class="flex items-center gap-2.5 overflow-hidden">
            <div class="flex-shrink-0 w-9 h-9 bg-gradient-to-br from-primary-600 to-primary-700 rounded-xl flex items-center justify-center shadow-md">
                <span class="text-blue-700 font-bold text-lg">S</span>
            </div>
            <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200"
                  x-transition:enter-start="opacity-0 -translate-x-2" x-transition:enter-end="opacity-100 translate-x-0"
                  class="font-bold text-gray-800 whitespace-nowrap">
                Shopora
            </span>
        </a>

        {{-- دکمه بستن - فقط وقتی سایدبار باز است نمایش داده می‌شود --}}
        <button x-show="sidebarOpen" @click="$dispatch('toggle-sidebar')"
                class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors flex-shrink-0">
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
            <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">داشبورد</span>
        </a>

        {{-- Divider: تعاملات --}}
        <div x-show="sidebarOpen" class="pt-4 pb-1">
            <p class="px-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">تعاملات</p>
        </div>

        {{-- درخواست‌های استخدام --}}
        <a href="#" class="sidebar-link {{ request()->routeIs('admin.recruitment.*') ? 'sidebar-link-active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
            <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">درخواست‌های استخدام</span>
            @if(isset($unreadRecruitmentCount) && $unreadRecruitmentCount > 0)
                <span x-show="sidebarOpen" class="mr-auto bg-blue-100 text-blue-700 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $unreadRecruitmentCount }}</span>
            @endif
        </a>

        {{-- درخواست مخاطبین --}}
        <a href="#" class="sidebar-link {{ request()->routeIs('admin.contact.*') ? 'sidebar-link-active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">درخواست مخاطبین</span>
            @if(isset($unreadContactCount) && $unreadContactCount > 0)
                <span x-show="sidebarOpen" class="mr-auto bg-rose-100 text-rose-700 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $unreadContactCount }}</span>
            @endif
        </a>

        {{-- نظرات کاربران --}}
        <a href="#" class="sidebar-link {{ request()->routeIs('admin.comments.*') ? 'sidebar-link-active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">نظرات کاربران</span>
            @if(isset($unreadCommentCount) && $unreadCommentCount > 0)
                <span x-show="sidebarOpen" class="mr-auto bg-amber-100 text-amber-700 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $unreadCommentCount }}</span>
            @endif
        </a>

        {{-- Divider: مدیریت محتوا --}}
        <div x-show="sidebarOpen" class="pt-4 pb-1">
            <p class="px-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">مدیریت محتوا</p>
        </div>

        {{-- وبلاگ و مقالات --}}
        <div x-data="{ open: {{ request()->routeIs('admin.blogs.*') || request()->routeIs('admin.blog-category.*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="sidebar-link w-full {{ request()->routeIs('admin.blogs.*') || request()->routeIs('admin.blog-category.*') ? 'sidebar-link-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span x-show="sidebarOpen" x-transition class="whitespace-nowrap flex-1 text-right">وبلاگ و مقالات</span>
                <svg x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -rotate-90" x-transition:enter-end="opacity-100 rotate-0"
                     :class="open ? '-rotate-90' : ''"
                     class="w-4 h-4 transition-transform duration-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <div x-show="open" x-collapse x-cloak class="sidebar-submenu">
                <a href="#" class="block px-3 py-2 text-sm rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('admin.blogs.*') ? 'text-primary-700 bg-primary-50 font-medium' : '' }}">لیست مقالات</a>
                <a href="#" class="block px-3 py-2 text-sm rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('admin.blog-category.*') ? 'text-primary-700 bg-primary-50 font-medium' : '' }}">دسته‌بندی مقالات</a>
            </div>
        </div>

        {{-- سایر آیتم‌های منو را با همین الگو اضافه کنید --}}

        {{-- خروج از حساب --}}
        <div class="pt-4">
            <a href="#" class="sidebar-link text-red-600 hover:bg-red-50 hover:text-red-700 border border-red-200">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">خروج از حساب</span>
            </a>
        </div>
    </nav>
</aside>
