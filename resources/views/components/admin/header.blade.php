@php $user = auth()->user(); @endphp

<header class="sticky top-0 z-20 bg-white border-b border-gray-200 px-4 sm:px-6">
    <div class="flex items-center justify-between h-16">
        {{-- Right Side: Mobile Menu Toggle + Breadcrumb --}}
        <div class="flex items-center gap-3">
            <button @click="$dispatch('toggle-mobile')"
                    class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 lg:hidden">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            @hasSection('breadcrumb')
                @yield('breadcrumb')
            @else
                <h1 class="text-lg font-semibold text-gray-800">@yield('tab-name', 'پنل مدیریت')</h1>
            @endif
        </div>

        {{-- Left Side: Actions --}}
        <div class="flex items-center gap-2">
            <a href="/" target="_blank"
               class="hidden sm:flex items-center gap-2 px-3 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                <span>مشاهده سایت</span>
            </a>

            {{-- Notifications --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="relative p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    @if(isset($unreadNotificationCount) && $unreadNotificationCount > 0)
                        <span class="absolute top-1.5 left-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
                    @endif
                </button>
                <div x-show="open" @click.away="open = false" x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                     class="absolute left-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50">
                    <div class="px-4 py-2 border-b border-gray-100">
                        <p class="text-sm font-semibold text-gray-800">اعلان‌ها</p>
                    </div>
                    <div class="max-h-64 overflow-y-auto">
                        <p class="px-4 py-6 text-sm text-gray-400 text-center">اعلان جدیدی وجود ندارد</p>
                    </div>
                </div>
            </div>

            {{-- Profile Dropdown --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                    <div class="w-8 h-8 bg-primary-100 text-primary-700 rounded-full flex items-center justify-center text-sm font-semibold">
                        {{ mb_substr($user->name ?? 'نامعلوم', 0, 1) }}
                    </div>
                    <div class="hidden sm:block text-right">
                        <p class="text-sm font-medium text-gray-700 leading-tight">{{ $user->name ?? 'نامعلوم' }} {{ $user->family ?? '' }}</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-400 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" @click.away="open = false" x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                     class="absolute left-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <p class="text-sm font-semibold text-gray-800">{{ $user->name ?? 'نامعلوم' }} {{ $user->family ?? '' }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $user->email ?? '' }}</p>
                    </div>
                    <a href="#" class="flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        خروج از حساب
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>
