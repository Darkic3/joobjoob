{{-- Overlay --}}
<div x-show="mobileSidebar" x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     @click="$dispatch('close-mobile')"
     class="fixed inset-0 bg-gray-900/50 z-40 lg:hidden" x-cloak></div>

{{-- Sidebar Drawer --}}
<aside x-show="mobileSidebar" x-transition:enter="transition ease-out duration-300"
       x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
       x-transition:leave="transition ease-in duration-200"
       x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
       class="fixed inset-y-0 right-0 w-72 bg-white z-50 lg:hidden flex flex-col shadow-lg" x-cloak>

    <div class="flex items-center justify-between h-16 px-4 border-b border-gray-100">
        <a href="{{ route('admin.dashboard.index') }}" class="flex items-center gap-2.5">
            <div class="w-9 h-9 bg-primary-600 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-lg">S</span>
            </div>
            <span class="font-bold text-gray-800">Shopora</span>
        </a>
        <button @click="$dispatch('close-mobile')" class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100">
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
            <span class="whitespace-nowrap">داشبورد</span>
        </a>
        {{-- بقیه آیتم‌های منوی موبایل --}}
    </nav>
</aside>
