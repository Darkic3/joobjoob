@extends('layouts.admin.app')

@section('tab-name', 'داشبورد')

@section('content')
    <div class="space-y-6">

        {{-- Welcome Banner --}}
        <div class="bg-gradient-to-l from-primary-600 to-primary-500 rounded-2xl p-6 sm:p-8 text-white shadow-soft">
            <h2 class="text-xl sm:text-2xl font-bold">سلام، {{ Auth::user()->name ?? 'کاربر' }} 👋</h2>
            <p class="mt-1 text-primary-100 text-sm sm:text-base">خوش آمدید به پنل مدیریت Shopora. امروز چه کاری انجام می‌دهید؟</p>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            @php
                $stats = [
                    ['label' => 'کاربران', 'value' => $content['totalUsers'] ?? 0, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>', 'color' => 'primary', 'route' => 'admin.user.index'],
                    ['label' => 'مقالات', 'value' => $content['totalBlogs'] ?? 0, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>', 'color' => 'blue', 'route' => 'admin.blogs.index'],
                    ['label' => 'آگهی‌ها', 'value' => $content['totalAdvertisements'] ?? 0, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>', 'color' => 'violet', 'route' => 'admin.advertisement.index'],
                    ['label' => 'تامین‌کنندگان', 'value' => $content['totalSuppliers'] ?? 0, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17h8M8 17v-4m8 4v-4M3 17h18M5 17V7a2 2 0 012-2h10a2 2 0 012 2v10"/>', 'color' => 'orange', 'route' => 'admin.suppliers.index'],
                    ['label' => 'اخبار', 'value' => $content['totalNews'] ?? 0, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>', 'color' => 'teal', 'route' => 'admin.news.index'],
                    ['label' => 'نقش‌ها', 'value' => $content['totalRoles'] ?? 0, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>', 'color' => 'indigo', 'route' => 'admin.permissions.index'],
                ];

                $colorMap = [
                    'primary' => ['bg' => 'bg-primary-50', 'text' => 'text-primary-600', 'border' => 'border-primary-100'],
                    'blue'    => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'border' => 'border-blue-100'],
                    'violet'  => ['bg' => 'bg-violet-50', 'text' => 'text-violet-600', 'border' => 'border-violet-100'],
                    'orange'  => ['bg' => 'bg-orange-50', 'text' => 'text-orange-600', 'border' => 'border-orange-100'],
                    'teal'    => ['bg' => 'bg-teal-50', 'text' => 'text-teal-600', 'border' => 'border-teal-100'],
                    'indigo'  => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-600', 'border' => 'border-indigo-100'],
                ];

                $hasLinks = collect($stats)->contains('route');
            @endphp

            @foreach($stats as $stat)
                @if(Route::getRoutes()->getByName($stat['route']))
                <a href="{{ route($stat['route']) }}"
                   class="stat-card group cursor-pointer">
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
                            <p class="text-2xl font-bold text-gray-800 leading-tight">{{ number_format($stat['value']) }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $stat['label'] }}</p>
                        </div>
                    </div>
                @if(Route::getRoutes()->getByName($stat['route']))
                </a>
                @else
                </div>
                @endif
            @endforeach
        </div>

        {{-- Quick Access --}}
        <x-admin.card title="دسترسی سریع" subtitle="عملیات رایج">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                @php
                    $quickLinks = [
                        ['label' => 'تنظیمات سایت', 'route' => 'admin.settings.edit', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>'],
                    ];
                @endphp

                @foreach($quickLinks as $link)
                    <a href="{{ route($link['route']) }}"
                       class="flex items-center justify-between p-3.5 bg-gray-50 hover:bg-primary-50 rounded-xl border border-gray-100 hover:border-primary-200 transition-all duration-200 group">
                        <span class="text-sm font-medium text-gray-700 group-hover:text-primary-700 transition-colors">{{ $link['label'] }}</span>
                        <div class="w-9 h-9 bg-white group-hover:bg-primary-100 rounded-lg flex items-center justify-center border border-gray-100 group-hover:border-primary-200 transition-all duration-200">
                            <svg class="w-4 h-4 text-gray-400 group-hover:text-primary-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                {!! $link['icon'] !!}
                            </svg>
                        </div>
                    </a>
                @endforeach
            </div>
        </x-admin.card>

        {{-- Component Demo --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Buttons Demo --}}
            <x-admin.card title="نمونه دکمه‌ها" subtitle="انواع اندازه‌ها و رنگ‌ها">
                <div class="space-y-4">
                    <div class="flex flex-wrap gap-2">
                        <x-admin.button variant="primary">اصلی</x-admin.button>
                        <x-admin.button variant="secondary">ثانویه</x-admin.button>
                        <x-admin.button variant="danger">خطر</x-admin.button>
                        <x-admin.button variant="success">موفقیت</x-admin.button>
                        <x-admin.button variant="warning">هشدار</x-admin.button>
                        <x-admin.button variant="outline">حاشیه‌ای</x-admin.button>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <x-admin.button variant="primary" size="xs">کوچک</x-admin.button>
                        <x-admin.button variant="primary" size="sm">متوسط</x-admin.button>
                        <x-admin.button variant="primary" size="md">بزرگ</x-admin.button>
                        <x-admin.button variant="primary" size="lg">خیلی بزرگ</x-admin.button>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <x-admin.button variant="primary" loading>در حال بارگذاری</x-admin.button>
                        <x-admin.button variant="primary" disabled>غیرفعال</x-admin.button>
                    </div>
                </div>
            </x-admin.card>

            {{-- Input Demo --}}
            <x-admin.card title="نمونه ورودی‌ها" subtitle="فیلد ها با آیکون و اعتبارسنجی">
                <form class="space-y-4">
                    <x-admin.input
                        name="name"
                        label="نام"
                        placeholder="نام خود را وارد کنید"
                    />

                    <x-admin.input
                        type="email"
                        name="email"
                        label="ایمیل"
                        placeholder="example@shopora.ir"
                        icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>'
                    />

                    <x-admin.input
                        name="error-field"
                        label="فیلد با خطا"
                        placeholder="این فیلد خطا دارد"
                        error="این فیلد اجباری است و پر نشده"
                    />

                    <x-admin.input
                        name="hint-field"
                        label="فیلد با راهنما"
                        placeholder="یک مقدار وارد کنید"
                        hint="این یک متن راهنمای است"
                    />

                    <x-admin.button type="submit" variant="primary">ذخیره تغییرات</x-admin.button>
                </form>
            </x-admin.card>
        </div>

        {{-- Info Card --}}
        <x-admin.card title="شروع سریع" subtitle="مسیرهای مورد نیاز">
            <div class="space-y-3 text-sm text-gray-600">
                <p class="flex items-start gap-2">
                    <span class="text-primary-500 mt-0.5">▸</span>
                    مسیرها را در <code class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">routes/admin.php</code> تعریف کنید.
                </p>
                <p class="flex items-start gap-2">
                    <span class="text-primary-500 mt-0.5">▸</span>
                    برای استفاده از دیتابیس SQLite، فایل <code class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">database/database.sqlite</code> را ایجاد کنید.
                </p>
                <p class="flex items-start gap-2">
                    <span class="text-primary-500 mt-0.5">▸</span>
                    بعد از افزودن routeها، کش را با <code class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">php artisan route:clear</code> پاک کنید.
                </p>
            </div>
        </x-admin.card>
    </div>
@endsection
