@extends('layouts.admin.app')

@php
    $currentLocale = $currentLocale ?? app()->getLocale();
    $hour = now()->hour;
    $greeting = $hour < 12 ? __('admin.dashboard.greeting_morning')
        : ($hour < 17 ? __('admin.dashboard.greeting_noon') : __('admin.dashboard.greeting_evening'));
    // تاریخ: برای فارسی شمسی (jdate)، بقیه با Carbon
    $todayDate = $currentLocale === 'fa' && function_exists('jdate_format')
        ? jdate_format('j F Y', time(), '', 'Asia/Tehran', 'fa')
        : now()->locale($currentLocale ?: 'en')->translatedFormat('j F Y');
@endphp

@section('tab-name', __('admin.dashboard.tab'))

@section('content')
    <div class="space-y-6 animate-slide-up">

        {{-- ۱. بنر خوش‌آمدگویی هوشمند --}}
        <div x-data="{ show: false }"
             x-init="setTimeout(() => show = true, 100)"
             x-show="show"
             x-transition:enter="transition ease-out duration-700"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="bg-gradient-to-l from-primary-600 to-primary-500 rounded-2xl p-6 sm:p-8 text-white shadow-soft relative overflow-hidden">

            <div class="absolute top-0 left-0 w-64 h-64 bg-white opacity-5 rounded-full -translate-x-1/2 -translate-y-1/2 blur-3xl"></div>
            <div class="absolute bottom-0 right-0 w-48 h-48 bg-primary-400 opacity-10 rounded-full translate-x-1/3 translate-y-1/3 blur-2xl"></div>

            <div class="relative z-10">
                <h2 class="text-xl sm:text-2xl font-bold flex items-center gap-2">
                    <span>{{ $greeting }}</span>، {{ Auth::user()->name ?? __('admin.dashboard.admin') }} 👋
                </h2>
                <p class="mt-2 text-primary-100 text-sm sm:text-base max-w-2xl">
                    {{ __('admin.dashboard.welcome') }}
                    <span class="hidden sm:inline">{{ __('admin.dashboard.today_is', ['date' => $todayDate]) }}</span>
                </p>

                <div class="mt-6 flex flex-wrap gap-3">
                    @if(auth()->user()->canAccess('admin.blogs.create'))
                        <a href="{{ route('admin.blogs.create') }}" class="px-4 py-2.5 bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white text-sm font-medium rounded-xl transition-all border border-white/20 flex items-center gap-2 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            {{ __('admin.dashboard.new_post') }}
                        </a>
                    @endif
                    <a href="/" target="_blank" class="px-4 py-2.5 bg-[#ffffff] text-primary-600 hover:bg-primary-50 text-sm font-medium rounded-xl transition-all shadow-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        {{ __('admin.dashboard.view_site') }}
                    </a>
                </div>
            </div>
        </div>

        {{-- ۲. کارت‌های آمار (Stats Grid) - بهینه‌شده برای جلوگیری از کش آمدن --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @php
                $canBlog = auth()->user()->canAccess('admin.blogs.index');
                $stats = [
                    ['label' => __('admin.dashboard.stats_users'), 'value' => 124, 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'color' => 'blue', 'trend' => '+12%', 'route' => null],
                    ['label' => __('admin.dashboard.stats_blogs'), 'value' => 45, 'icon' => 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z', 'color' => 'emerald', 'trend' => '+5%', 'route' => $canBlog ? route('admin.blogs.index') : null],
                    ['label' => __('admin.dashboard.stats_comments'), 'value' => 18, 'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z', 'color' => 'amber', 'trend' => '-2%', 'route' => null],
                    ['label' => __('admin.dashboard.stats_visits'), 'value' => '1,240', 'icon' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z', 'color' => 'violet', 'trend' => '+8%', 'route' => null],
                ];

                $colorMap = [
                    'blue'    => ['bg' => 'bg-blue-50 dark:bg-blue-900/20', 'text' => 'text-blue-600 dark:text-blue-400', 'border' => 'border-blue-100 dark:border-blue-800'],
                    'emerald' => ['bg' => 'bg-emerald-50 dark:bg-emerald-900/20', 'text' => 'text-emerald-600 dark:text-emerald-400', 'border' => 'border-emerald-100 dark:border-emerald-800'],
                    'amber'   => ['bg' => 'bg-amber-50 dark:bg-amber-900/20', 'text' => 'text-amber-600 dark:text-amber-400', 'border' => 'border-amber-100 dark:border-amber-800'],
                    'violet'  => ['bg' => 'bg-violet-50 dark:bg-violet-900/20', 'text' => 'text-violet-600 dark:text-violet-400', 'border' => 'border-violet-100 dark:border-violet-800'],
                ];
            @endphp

            @foreach($stats as $stat)
                @if($stat['route'])
                    <a href="{{ $stat['route'] }}" class="stat-card group cursor-pointer h-full flex flex-col justify-between">
                @else
                    <div class="stat-card group h-full flex flex-col justify-between">
                @endif
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-11 h-11 {{ $colorMap[$stat['color']]['bg'] }} rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-5 h-5 {{ $colorMap[$stat['color']]['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat['icon'] }}"/>
                            </svg>
                        </div>
                        <span class="text-xs font-bold {{ str_contains($stat['trend'], '+') ? 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30' : 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30' }} px-2 py-1 rounded-lg">
                            {{ $stat['trend'] }}
                        </span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-800 dark:text-gray-100 leading-none mb-1">{{ $stat['value'] }}</p>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $stat['label'] }}</p>
                    </div>
                @if($stat['route'])
                    </a>
                @else
                    </div>
                @endif
            @endforeach
        </div>

        {{-- ۳. جدول آخرین مطالب وبلاگ — فقط با دسترسی بلاگ --}}
        @if(auth()->user()->canAccess('admin.blogs.index'))
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-base font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                    {{ __('admin.dashboard.latest_posts') }}
                </h3>
                <a href="{{ route('admin.blogs.index') }}" class="text-xs font-semibold text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 flex items-center gap-1 transition-colors">
                    {{ __('admin.dashboard.view_all') }}
                    <svg class="w-3 h-3 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-start">
                    <thead class="bg-gray-50/50 dark:bg-gray-700/30 text-gray-500 dark:text-gray-400 font-semibold text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">{{ __('admin.dashboard.th_title') }}</th>
                        <th class="px-6 py-4">{{ __('admin.dashboard.th_author') }}</th>
                        <th class="px-6 py-4">{{ __('admin.dashboard.th_status') }}</th>
                        <th class="px-6 py-4">{{ __('admin.dashboard.th_date') }}</th>
                        <th class="px-6 py-4 text-end">{{ __('admin.dashboard.th_actions') }}</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    {{-- داده‌های نمونه (بعداً با دیتابیس جایگزین می‌شود) --}}
                    <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/40 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-800 dark:text-gray-200 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">{{ __('admin.dashboard.mock_title') }}</div>
                        </td>
                        <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ __('admin.dashboard.mock_author') }}</td>
                        <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full ms-1.5"></span>
                                    {{ __('admin.dashboard.status_published') }}
                                </span>
                        </td>
                        <td class="px-6 py-4 text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $todayDate }}</td>
                        <td class="px-6 py-4 text-end">
                            <div class="flex items-center justify-end gap-1">
                                <button class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-all" title="{{ __('admin.dashboard.edit') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all delete-form" title="{{ __('admin.dashboard.delete') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>
@endsection
