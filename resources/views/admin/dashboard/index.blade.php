@extends('layouts.admin.app')

@section('tab-name', 'داشبورد')

@section('content')
    <div class="space-y-6">

        {{-- Welcome Banner --}}
        <div x-data="{
                show: false,
                greeting: (() => {
                    const hour = new Date().getHours();
                    if (hour < 12) return 'صبح بخیر';
                    if (hour < 17) return 'ظهر بخیر';
                    return 'عصر بخیر';
                })()
            }"
             x-init="setTimeout(() => show = true, 100)"
             x-show="show"
             x-transition:enter="transition ease-out duration-700"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="bg-gradient-to-l from-primary-600 to-primary-500 rounded-2xl p-6 sm:p-8 text-white shadow-soft relative overflow-hidden">

            <div class="absolute top-0 left-0 w-64 h-64 bg-white opacity-5 rounded-full -translate-x-1/2 -translate-y-1/2 blur-3xl"></div>

            <div class="relative z-10">
                <h2 class="text-xl sm:text-2xl font-bold flex items-center gap-2">
                    <span x-text="greeting"></span>، {{ Auth::user()->name ?? 'کاربر' }} 👋
                </h2>
                <p class="mt-2 text-primary-100 text-sm sm:text-base max-w-2xl">
                    خوش آمدید به پنل مدیریت Shopora.
                    <span class="hidden sm:inline">امروز {{ now()->format('j F Y') }} است و همه سیستم‌ها آماده به کار هستند.</span>
                </p>

                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('admin.settings.edit') }}" class="px-4 py-2 bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white text-sm font-medium rounded-lg transition-colors border border-white/20">
                        ⚙️ تنظیمات سریع
                    </a>
                    <a href="/" target="_blank" class="px-4 py-2 bg-white text-primary-600 hover:bg-primary-50 text-sm font-medium rounded-lg transition-colors shadow-sm">
                        👁️ مشاهده سایت
                    </a>
                </div>
            </div>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            @php
                $stats = [
                    ['label' => 'کاربران', 'value' => $content['totalUsers'] ?? 0, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>', 'color' => 'primary', 'route' => 'admin.user.index'],
                    // ... بقیه آمار ...
                ];

                $colorMap = [
                    'primary' => ['bg' => 'bg-primary-50 dark:bg-primary-900/20', 'text' => 'text-primary-600 dark:text-primary-400', 'border' => 'border-primary-100 dark:border-primary-800'],
                    'blue'    => ['bg' => 'bg-blue-50 dark:bg-blue-900/20', 'text' => 'text-blue-600 dark:text-blue-400', 'border' => 'border-blue-100 dark:border-blue-800'],
                    // ... بقیه رنگ‌ها ...
                ];
            @endphp

            @foreach($stats as $stat)
                @if(Route::getRoutes()->getByName($stat['route']))
                    <a href="{{ route($stat['route']) }}" class="stat-card group cursor-pointer">
                        @else
                            <div class="stat-card">
                                @endif
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 {{ $colorMap[$stat['color']]['bg'] }} rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 {{ $colorMap[$stat['color']]['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            {!! $stat['icon'] !!}
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-2xl font-bold text-gray-800 dark:text-gray-100 leading-tight">{{ number_format($stat['value']) }}</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $stat['label'] }}</p>
                                    </div>
                                </div>
                            @if(Route::getRoutes()->getByName($stat['route']))
                    </a>
                @else
        </div>
        @endif
        @endforeach
    </div>

    {{-- ... بقیه کد داشبورد با اضافه کردن dark: به تمام کلاس‌ها ... --}}

    </div>
@endsection
