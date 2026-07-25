{{-- خط @props را حذف کردیم --}}

<aside :class="sidebarOpen ? 'w-64' : 'w-20'"
       class="hidden lg:flex flex-col bg-white border-s border-gray-200 transition-all duration-300 ease-in-out fixed inset-y-0 right-0 z-30">

    <div class="flex items-center justify-between h-16 px-4 border-b border-gray-100">
        <a href="{{ route('admin.dashboard.index') }}" class="flex items-center gap-2.5 overflow-hidden">
            <div class="flex-shrink-0 w-9 h-9 bg-primary-600 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-lg">S</span>
            </div>
            <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200"
                  x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                  class="font-bold text-gray-800 whitespace-nowrap">
                Shopora
            </span>
        </a>
        <button @click="$dispatch('toggle-sidebar')"
                class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors hidden lg:flex">
            <svg x-show="sidebarOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
            </svg>
            <svg x-show="!sidebarOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
            </svg>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
        <a href="{{ route('admin.dashboard.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.dashboard.*') ? 'sidebar-link-active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 12a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1v-7z"/>
            </svg>
            <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">داشبورد</span>
        </a>

        {{-- بقیه کدهای منوی شما دقیقاً همین‌جا با حفظ کامنت‌ها قرار می‌گیرد --}}
        {{-- ... --}}

    </nav>
</aside>
