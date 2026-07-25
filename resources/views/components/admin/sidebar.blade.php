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
            <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">داشبورد</span>
        </a>

        {{-- Divider --}}
        <div x-show="sidebarOpen" class="pt-4 pb-1">
            <p class="px-3 text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">تعاملات</p>
        </div>

        {{-- ... بقیه آیتم‌های منو با همین الگو ... --}}

        {{-- خروج از حساب --}}
        <div class="pt-4">
            <a href="#" class="sidebar-link text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-700 dark:hover:text-red-300 border border-red-200 dark:border-red-800">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">خروج از حساب</span>
            </a>
        </div>
    </nav>
</aside>
