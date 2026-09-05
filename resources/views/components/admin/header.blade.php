@props([])

@php $user = auth()->user(); @endphp

{{-- locales/currentLocale از LocaleComposer سراسری می‌آید --}}
<header class="sticky top-0 z-20 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 transition-colors duration-300">
    <div class="flex items-center justify-between h-16">

        {{-- Right Side: Menu Toggle + Breadcrumb --}}
        <div class="flex items-center gap-3">
            <button @click="$dispatch('toggle-sidebar'); $dispatch('toggle-mobile')"
                    class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            @hasSection('breadcrumb')
                @yield('breadcrumb')
            @else
                <h1 class="text-lg font-semibold text-gray-800 dark:text-gray-100">@yield('tab-name', __('admin.layout.panel'))</h1>
            @endif
        </div>

        {{-- Left Side: Actions --}}
        <div class="flex items-center gap-2">

            {{-- View Site --}}
            <a href="/" target="_blank"
               class="hidden sm:flex items-center gap-2 px-3 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                <span>{{ __('admin.layout.view_site') }}</span>
            </a>

            {{-- انتخاب زبان — فقط وقتی بیش از یک زبان فعال است --}}
            @if (count($locales) > 1)
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" title="{{ __('admin.layout.change_lang') }}"
                            class="flex items-center gap-1.5 p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21a9 9 0 100-18 9 9 0 000 18zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <span class="text-xs font-bold hidden sm:inline">{{ strtoupper($currentLocale) }}</span>
                    </button>

                    <div x-show="open" @click.away="open = false" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                         class="absolute end-0 mt-2 w-40 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 py-1.5 z-50">
                        @foreach($locales as $code => $lang)
                            <a href="{{ request()->fullUrlWithQuery(['lang' => $code]) }}"
                               class="flex items-center justify-between px-4 py-2 text-sm transition-colors {{ $currentLocale === $code ? 'text-primary-600 dark:text-primary-400 font-bold bg-primary-50 dark:bg-primary-900/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                <span>{{ $lang->localizedName() }}</span>
                                @if($currentLocale === $code)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Theme Toggle --}}
            <x-admin.theme-toggle/>

            {{-- Notifications --}}
            <div x-data="adminNotificationBell()" class="relative">
                <button @click="toggle()"
                        class="relative p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    {{-- بج روی گوشه‌ی آیکون (نه روی زنگ) — top/end منفی تا همیشه بیرون بزند؛ leading ثابت برای وسط‌چینی عمودی --}}
                    <span x-show="count > 0" x-text="displayCount()"
                          x-transition
                          class="absolute -top-1 -end-1 min-w-[18px] h-[18px] px-1 text-[10px] leading-[18px] font-bold rounded-full grid place-items-center bg-red-500 text-white ring-2 ring-white dark:ring-gray-800">{{ \App\Helpers\NumberHelper::localizeDigits(($unreadNotificationCount ?? 0) > 99 ? '99+' : ($unreadNotificationCount ?? 0), $digitFamily ?? 'latin') }}</span>
                </button>

                {{-- Notifications Dropdown --}}
                <div x-show="open" @click.away="open = false" x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                     class="absolute end-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 py-2 z-50">
                    <div class="flex items-center justify-between px-4 py-2 border-b border-gray-100 dark:border-gray-700">
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ __('admin.layout.notifications') }}</p>
                        <button x-show="count > 0" @click="markAllRead()" type="button"
                                class="text-xs font-bold text-primary-600 dark:text-primary-400 hover:underline">
                            {{ __('messages.mark_all_read') }}
                        </button>
                    </div>
                    <div class="max-h-72 overflow-y-auto">
                        <template x-if="loading">
                            <p class="px-4 py-6 text-sm text-gray-400 dark:text-gray-500 text-center">{{ __('messages.loading') }}</p>
                        </template>
                        <template x-if="!loading && items.length === 0">
                            <p class="px-4 py-6 text-sm text-gray-400 dark:text-gray-500 text-center">{{ __('admin.layout.no_notifications') }}</p>
                        </template>
                        <template x-for="item in items" :key="item.id">
                            <a :href="item.link"
                               class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                               :style="item.is_read ? '' : 'background: rgba(59,130,246,0.05);'"
                               @click.prevent="!item.is_read && markRead(item.id)">
                                <span x-html="notifIcon(item.type)" class="shrink-0"></span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate" x-text="item.title"></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-2" x-text="item.body"></p>
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1" x-text="item.created_at"></p>
                                </div>
                                <span x-show="!item.is_read" class="w-2 h-2 rounded-full shrink-0 mt-1.5 bg-primary-500"></span>
                            </a>
                        </template>
                    </div>
                    <a href="{{ route('admin.notifications.index') }}"
                       class="block text-center px-4 py-2.5 text-sm font-medium text-primary-600 dark:text-primary-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700">
                        {{ __('messages.view_all_notifications') }}
                    </a>
                </div>
            </div>

            {{-- Profile Dropdown --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                        class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <div class="w-8 h-8 bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300 rounded-full flex items-center justify-center text-sm font-semibold">
                        {{ mb_substr($user->name ?? __('admin.layout.unknown'), 0, 1) }}
                    </div>
                    <div class="hidden sm:block text-start">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-200 leading-tight">{{ $user->name ?? __('admin.layout.unknown') }} {{ $user->family ?? '' }}</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                {{-- Profile Dropdown Menu --}}
                <div x-show="open" @click.away="open = false" x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                     class="absolute end-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 py-2 z-50">
                    <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $user->name ?? __('admin.layout.unknown') }} {{ $user->family ?? '' }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $user->email ?? '' }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            {{ __('admin.layout.logout') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
