@php
    $user = auth()->user();
@endphp
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('tab-name', 'پنل مدیریت')</title>
{{--    <link rel="shortcut icon" href="{{ $setting->imageFile('favicon') ?? asset('favicon.ico') }}">--}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="font-sans">

    {{-- Flash Messages Toast --}}
    @if(session('success') || session('error') || session('warning') || session('info'))
    <div x-data="toast()" x-init="showToast()" class="fixed top-6 left-6 z-[9999] max-w-sm w-full" dir="rtl">
        @if(session('success'))
        <div x-show="visible" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
             class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-5 py-4 rounded-xl shadow-soft-lg flex items-center gap-3">
            <div class="flex-shrink-0 w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <p class="text-sm font-medium flex-1">{{ session('success') }}</p>
            <button @click="visible = false" class="text-emerald-500 hover:text-emerald-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        @endif

        @if(session('error'))
        <div x-show="visible" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
             class="bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-xl shadow-soft-lg flex items-center gap-3">
            <div class="flex-shrink-0 w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <p class="text-sm font-medium flex-1">{{ session('error') }}</p>
            <button @click="visible = false" class="text-red-500 hover:text-red-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        @endif

        @if(session('warning'))
        <div x-show="visible" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
             class="bg-amber-50 border border-amber-200 text-amber-700 px-5 py-4 rounded-xl shadow-soft-lg flex items-center gap-3">
            <div class="flex-shrink-0 w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
            </div>
            <p class="text-sm font-medium flex-1">{{ session('warning') }}</p>
            <button @click="visible = false" class="text-amber-500 hover:text-amber-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        @endif

        @if(session('info'))
        <div x-show="visible" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
             class="bg-blue-50 border border-blue-200 text-blue-700 px-5 py-4 rounded-xl shadow-soft-lg flex items-center gap-3">
            <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-sm font-medium flex-1">{{ session('info') }}</p>
            <button @click="visible = false" class="text-blue-500 hover:text-blue-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        @endif
    </div>
    @endif

    {{-- Main Wrapper --}}
    <div x-data="{ sidebarOpen: true, mobileSidebar: false }" class="flex min-h-screen">

        {{-- Desktop Sidebar --}}
        <aside :class="sidebarOpen ? 'w-64' : 'w-20'"
               class="hidden lg:flex flex-col bg-white border-s border-gray-200 transition-all duration-300 ease-in-out fixed inset-y-0 right-0 z-30">

            {{-- Sidebar Header --}}
            <div class="flex items-center justify-between h-16 px-4 border-b border-gray-100">
                <a href="#" class="flex items-center gap-2.5 overflow-hidden">
                    <div class="flex-shrink-0 w-9 h-9 bg-primary-600 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-lg">S</span>
                    </div>
                    <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200"
                          x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                          class="font-bold text-gray-800 whitespace-nowrap">
                        Shopora
                    </span>
                </a>
                <button @click="sidebarOpen = !sidebarOpen"
                        class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors hidden lg:flex">
                    <svg x-show="sidebarOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                    </svg>
                    <svg x-show="!sidebarOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>

            {{-- Sidebar Navigation --}}
            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
                {{-- Dashboard --}}
                <a href="#"
                   class="sidebar-link {{ request()->routeIs('admin.dashboard.*') ? 'sidebar-link-active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 12a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1v-7z"/>
                    </svg>
                    <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">داشبورد</span>
                </a>

                {{-- Divider: Interactions --}}
                <div x-show="sidebarOpen" class="pt-4 pb-1">
                    <p class="px-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">تعاملات</p>
                </div>
{{-- TODO:ahora_fix --}}
{{--                @if($user->hasAccess('admin.recruitment.index'))--}}
{{--                <a href="#"--}}
                <a href=""
                   class="sidebar-link {{ request()->routeIs('admin.recruitment.*') ? 'sidebar-link-active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">درخواست‌های استخدام</span>
                    @if(isset($unreadRecruitmentCount) && $unreadRecruitmentCount > 0)
                    <span x-show="sidebarOpen" class="mr-auto bg-blue-100 text-blue-700 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $unreadRecruitmentCount }}</span>
                    @endif
                </a>
{{--                @endif--}}

{{--                @if($user->hasAccess('admin.contact.index'))--}}
                <a href="#"
                   class="sidebar-link {{ request()->routeIs('admin.contact.*') ? 'sidebar-link-active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">درخواست مخاطبین</span>
                    @if(isset($unreadContactCount) && $unreadContactCount > 0)
                    <span x-show="sidebarOpen" class="mr-auto bg-rose-100 text-rose-700 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $unreadContactCount }}</span>
                    @endif
                </a>
{{--                @endif--}}

{{--                @if($user->hasAccess('admin.comments.index'))--}}
                <a href="#"
                   class="sidebar-link {{ request()->routeIs('admin.comments.*') ? 'sidebar-link-active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">نظرات کاربران</span>
                    @if(isset($unreadCommentCount) && $unreadCommentCount > 0)
                    <span x-show="sidebarOpen" class="mr-auto bg-amber-100 text-amber-700 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $unreadCommentCount }}</span>
                    @endif
                </a>
{{--                @endif--}}

                {{-- Divider: Content --}}
                <div x-show="sidebarOpen" class="pt-4 pb-1">
                    <p class="px-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">مدیریت محتوا</p>
                </div>

                {{-- Blog Dropdown --}}
{{--                @if($user->hasAccess('admin.blogs.index'))--}}
                <div x-data="{ open: {{ request()->routeIs('admin.blogs.*') || request()->routeIs('admin.blog-category.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                            class="sidebar-link w-full {{ request()->routeIs('admin.blogs.*') || request()->routeIs('admin.blog-category.*') ? 'sidebar-link-active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
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
                        <a href="#"
                           class="block px-3 py-2 text-sm rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('admin.blogs.*') ? 'text-primary-700 bg-primary-50 font-medium' : '' }}">
                            لیست مقالات
                        </a>
{{--                        @if($user->hasAccess('admin.blog-category.index'))--}}
                        <a href="#"
                           class="block px-3 py-2 text-sm rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('admin.blog-category.*') ? 'text-primary-700 bg-primary-50 font-medium' : '' }}">
                            دسته‌بندی مقالات
                        </a>
{{--                        @endif--}}
                    </div>
                </div>
{{--                @endif--}}

                {{-- News Dropdown --}}
{{--                @if($user->hasAccess('admin.news.index'))--}}
                <div x-data="{ open: {{ request()->routeIs('admin.news.*') || request()->routeIs('admin.news-category.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                            class="sidebar-link w-full {{ request()->routeIs('admin.news.*') || request()->routeIs('admin.news-category.*') ? 'sidebar-link-active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                        </svg>
                        <span x-show="sidebarOpen" x-transition class="whitespace-nowrap flex-1 text-right">اخبار</span>
                        <svg x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -rotate-90" x-transition:enter-end="opacity-100 rotate-0"
                             :class="open ? '-rotate-90' : ''"
                             class="w-4 h-4 transition-transform duration-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" x-collapse x-cloak class="sidebar-submenu">
                        <a href="#"
                           class="block px-3 py-2 text-sm rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('admin.news.*') ? 'text-primary-700 bg-primary-50 font-medium' : '' }}">
                            لیست اخبار
                        </a>
{{--                        @if($user->hasAccess('admin.news-category.index'))--}}
                        <a href="#"
                           class="block px-3 py-2 text-sm rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('admin.news-category.*') ? 'text-primary-700 bg-primary-50 font-medium' : '' }}">
                            دسته‌بندی اخبار
                        </a>
{{--                        @endif--}}
                    </div>
                </div>
{{--                @endif--}}

                {{-- Advertisement Dropdown --}}
{{--                @if($user->hasAccess('admin.advertisement.index'))--}}
                <div x-data="{ open: {{ request()->routeIs('admin.advertisement.*') || request()->routeIs('admin.advertisement-category.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                            class="sidebar-link w-full {{ request()->routeIs('admin.advertisement.*') || request()->routeIs('admin.advertisement-category.*') ? 'sidebar-link-active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                        </svg>
                        <span x-show="sidebarOpen" x-transition class="whitespace-nowrap flex-1 text-right">آگهی‌ها</span>
                        <svg x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -rotate-90" x-transition:enter-end="opacity-100 rotate-0"
                             :class="open ? '-rotate-90' : ''"
                             class="w-4 h-4 transition-transform duration-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" x-collapse x-cloak class="sidebar-submenu">
                        <a href="#"
                           class="block px-3 py-2 text-sm rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('admin.advertisement.*') && !request()->routeIs('admin.advertisement-category.*') ? 'text-primary-700 bg-primary-50 font-medium' : '' }}">
                            لیست آگهی‌ها
                        </a>
{{--                        @if($user->hasAccess('admin.advertisement-category.index'))--}}
                        <a href="#"
                           class="block px-3 py-2 text-sm rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('admin.advertisement-category.*') ? 'text-primary-700 bg-primary-50 font-medium' : '' }}">
                            دسته‌بندی آگهی
                        </a>
{{--                        @endif--}}
                    </div>
                </div>
{{--                @endif--}}

{{--                @if($user->hasAccess('admin.sliders.index'))--}}
                <a href="#"
                   class="sidebar-link {{ request()->routeIs('admin.sliders.*') ? 'sidebar-link-active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">اسلایدرها</span>
                </a>
{{--                @endif--}}

                {{-- Divider: Organization --}}
                <div x-show="sidebarOpen" class="pt-4 pb-1">
                    <p class="px-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">ساختار سازمانی</p>
                </div>

{{--                @if($user->hasAccess('admin.information.index'))--}}
                <a href="#"
                   class="sidebar-link {{ request()->routeIs('admin.information.*') ? 'sidebar-link-active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">درباره ما</span>
                </a>
{{--                @endif--}}

{{--                @if($user->hasAccess('admin.branches.index'))--}}
                <a href="#"
                   class="sidebar-link {{ request()->routeIs('admin.branches.*') ? 'sidebar-link-active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">شعب</span>
                </a>
{{--                @endif--}}

{{--                @if($user->hasAccess('admin.suppliers.index'))--}}
                <a href="#"
                   class="sidebar-link {{ request()->routeIs('admin.suppliers.*') ? 'sidebar-link-active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 17h8M8 17v-4m8 4v-4M3 17h18M5 17V7a2 2 0 012-2h10a2 2 0 012 2v10"/>
                    </svg>
                    <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">تامین‌کنندگان</span>
                </a>
{{--                @endif--}}

                {{-- Team Dropdown --}}
{{--                @if($user->hasAccess('admin.managers.index') || $user->hasAccess('admin.teams.index'))--}}
                <div x-data="{ open: {{ request()->routeIs('admin.managers.*') || request()->routeIs('admin.teams.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                            class="sidebar-link w-full {{ request()->routeIs('admin.managers.*') || request()->routeIs('admin.teams.*') ? 'sidebar-link-active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span x-show="sidebarOpen" x-transition class="whitespace-nowrap flex-1 text-right">مدیران</span>
                        <svg x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -rotate-90" x-transition:enter-end="opacity-100 rotate-0"
                             :class="open ? '-rotate-90' : ''"
                             class="w-4 h-4 transition-transform duration-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" x-collapse x-cloak class="sidebar-submenu">
{{--                        @if($user->hasAccess('admin.managers.index'))--}}
                        <a href="#"
                           class="block px-3 py-2 text-sm rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('admin.managers.*') ? 'text-primary-700 bg-primary-50 font-medium' : '' }}">
                            مدیران ستاد
                        </a>
{{--                        @endif--}}
{{--                        @if($user->hasAccess('admin.teams.index'))--}}
                        <a href="#"
                           class="block px-3 py-2 text-sm rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('admin.teams.*') ? 'text-primary-700 bg-primary-50 font-medium' : '' }}">
                            هیئت مدیره
                        </a>
{{--                        @endif--}}
                    </div>
                </div>
{{--                @endif--}}

{{--                @if($user->hasAccess('admin.contact_infos.index'))--}}
                <a href="#"
                   class="sidebar-link {{ request()->routeIs('admin.contact_infos.*') ? 'sidebar-link-active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">اطلاعات تماس</span>
                </a>
{{--                @endif--}}

                {{-- Divider: System --}}
                <div x-show="sidebarOpen" class="pt-4 pb-1">
                    <p class="px-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">تنظیمات سیستم</p>
                </div>

                {{-- Access Management Dropdown --}}
{{--                @if($user->hasAccess('admin.user.index') || $user->hasAccess('admin.permissions.index'))--}}
                <div x-data="{ open: {{ request()->routeIs('admin.user.*') || request()->routeIs('admin.permissions.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                            class="sidebar-link w-full {{ request()->routeIs('admin.user.*') || request()->routeIs('admin.permissions.*') ? 'sidebar-link-active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <span x-show="sidebarOpen" x-transition class="whitespace-nowrap flex-1 text-right">مدیریت دسترسی</span>
                        <svg x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -rotate-90" x-transition:enter-end="opacity-100 rotate-0"
                             :class="open ? '-rotate-90' : ''"
                             class="w-4 h-4 transition-transform duration-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" x-collapse x-cloak class="sidebar-submenu">
{{--                        @if($user->hasAccess('admin.user.index'))--}}
                        <a href="#"
                           class="block px-3 py-2 text-sm rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('admin.user.*') ? 'text-primary-700 bg-primary-50 font-medium' : '' }}">
                            ادمین‌ها
                        </a>
{{--                        @endif--}}
{{--                        @if($user->hasAccess('admin.permissions.index'))--}}
                        <a href="#"
                           class="block px-3 py-2 text-sm rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('admin.permissions.*') ? 'text-primary-700 bg-primary-50 font-medium' : '' }}">
                            نقش‌ها و دسترسی‌ها
                        </a>
{{--                        @endif--}}
                    </div>
                </div>
{{--                @endif--}}

{{--                @if($user->hasAccess('admin.seo.index'))--}}
                <a href="#"
                   class="sidebar-link {{ request()->routeIs('admin.seo.*') ? 'sidebar-link-active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">سئو</span>
                </a>
{{--                @endif--}}

{{--                @if($user->hasAccess('admin.logos.index'))--}}
                <a href="#"
                   class="sidebar-link {{ request()->routeIs('admin.logos.*') ? 'sidebar-link-active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                    <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">نمادهای فوتر</span>
                </a>
{{--                @endif--}}

{{--                @if($user->hasAccess('admin.menu.index'))--}}
                <a href="#"
                   class="sidebar-link {{ request()->routeIs('admin.menu.*') ? 'sidebar-link-active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">تنظیمات منو</span>
                </a>
{{--                @endif--}}

{{--                @if($user->hasAccess('admin.settings.edit'))--}}
                <a href="#"
                   class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'sidebar-link-active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">تنظیمات کلی</span>
                </a>
{{--                @endif--}}

{{--                @if($user->hasAccess('admin.request-logs.index'))--}}
                <a href="#"
                   class="sidebar-link {{ request()->routeIs('admin.request-logs.*') ? 'sidebar-link-active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">لاگ‌های سیستم</span>
                </a>
{{--                @endif--}}

                {{-- Logout --}}
                <div class="pt-4">
                    <a href="#"
                       class="sidebar-link text-red-600 hover:bg-red-50 hover:text-red-700 border border-red-200">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">خروج از حساب</span>
                    </a>
                </div>
            </nav>
        </aside>

        {{-- Mobile Sidebar Overlay --}}
        <div x-show="mobileSidebar" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @click="mobileSidebar = false"
             class="fixed inset-0 bg-gray-900/50 z-40 lg:hidden" x-cloak></div>

        {{-- Mobile Sidebar --}}
        <aside x-show="mobileSidebar" x-transition:enter="transition ease-out duration-300"
               x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in duration-200"
               x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
               class="fixed inset-y-0 right-0 w-72 bg-white z-50 lg:hidden flex flex-col shadow-soft-lg" x-cloak>

            <div class="flex items-center justify-between h-16 px-4 border-b border-gray-100">
                <a href="#" class="flex items-center gap-2.5">
                    <div class="w-9 h-9 bg-primary-600 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-lg">S</span>
                    </div>
                    <span class="font-bold text-gray-800">Shopora</span>
                </a>
                <button @click="mobileSidebar = false" class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
                <a href="#"
                   class="sidebar-link {{ request()->routeIs('admin.dashboard.*') ? 'sidebar-link-active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 12a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1v-7z"/>
                    </svg>
                    <span class="whitespace-nowrap">داشبورد</span>
                </a>

                <div class="pt-4 pb-1">
                    <p class="px-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">تعاملات</p>
                </div>

{{--                @if($user->hasAccess('admin.recruitment.index'))--}}
                <a href="#"
                   class="sidebar-link {{ request()->routeIs('admin.recruitment.*') ? 'sidebar-link-active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    <span class="whitespace-nowrap">درخواست‌های استخدام</span>
                </a>
{{--                @endif--}}

{{--                @if($user->hasAccess('admin.contact.index'))--}}
                <a href="#"
                   class="sidebar-link {{ request()->routeIs('admin.contact.*') ? 'sidebar-link-active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span class="whitespace-nowrap">درخواست مخاطبین</span>
                </a>
{{--                @endif--}}

{{--                @if($user->hasAccess('admin.comments.index'))--}}
                <a href="#"
                   class="sidebar-link {{ request()->routeIs('admin.comments.*') ? 'sidebar-link-active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <span class="whitespace-nowrap">نظرات کاربران</span>
                </a>
{{--                @endif--}}

                <div class="pt-4 pb-1">
                    <p class="px-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">مدیریت محتوا</p>
                </div>

{{--                @if($user->hasAccess('admin.blogs.index'))--}}
                <div x-data="{ open: false }">
                    <button @click="open = !open"
                            class="sidebar-link w-full {{ request()->routeIs('admin.blogs.*') || request()->routeIs('admin.blog-category.*') ? 'sidebar-link-active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="whitespace-nowrap flex-1 text-right">وبلاگ و مقالات</span>
                        <svg :class="open ? '-rotate-90' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" x-collapse x-cloak class="sidebar-submenu">
                        <a href="#" class="block px-3 py-2 text-sm rounded-lg text-gray-600 hover:bg-gray-100">لیست مقالات</a>
{{--                        @if($user->hasAccess('admin.blog-category.index'))--}}
                        <a href="#" class="block px-3 py-2 text-sm rounded-lg text-gray-600 hover:bg-gray-100">دسته‌بندی مقالات</a>
{{--                        @endif--}}
                    </div>
                </div>
{{--                @endif--}}

{{--                @if($user->hasAccess('admin.settings.edit'))--}}
                <a href="#"
                   class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'sidebar-link-active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="whitespace-nowrap">تنظیمات کلی</span>
                </a>
{{--                @endif--}}

                <div class="pt-4">
                    <a href="#"
                       class="sidebar-link text-red-600 hover:bg-red-50 hover:text-red-700 border border-red-200">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span class="whitespace-nowrap">خروج از حساب</span>
                    </a>
                </div>
            </nav>
        </aside>

        {{-- Main Content Area --}}
        <div class="flex-1 flex flex-col min-h-screen lg:mr-64 transition-all duration-300"
             :class="{ 'lg:mr-20': !sidebarOpen }">

            {{-- Topbar --}}
            <header class="sticky top-0 z-20 bg-white border-b border-gray-200 px-4 sm:px-6">
                <div class="flex items-center justify-between h-16">
                    {{-- Right Side: Mobile Menu Toggle + Breadcrumb --}}
                    <div class="flex items-center gap-3">
                        <button @click="mobileSidebar = true"
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
                        {{-- View Site --}}
                        <a href="/" target="_blank"
                           class="hidden sm:flex items-center gap-2 px-3 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            <span>مشاهده سایت</span>
                        </a>

                        {{-- Notifications --}}
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open"
                                    class="relative p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
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
                                 class="absolute left-0 mt-2 w-80 bg-white rounded-xl shadow-soft-lg border border-gray-100 py-2 z-50">
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
                            <button @click="open = !open"
                                    class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-gray-100 transition-colors">
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
                                 class="absolute left-0 mt-2 w-56 bg-white rounded-xl shadow-soft-lg border border-gray-100 py-2 z-50">
                                <div class="px-4 py-3 border-b border-gray-100">
                                    <p class="text-sm font-semibold text-gray-800">{{ $user->name ?? 'نامعلوم' }} {{ $user->family ?? '' }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $user->email ?? '' }}</p>
                                </div>
                                <a href="#"
                                   class="flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    خروج از حساب
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Page Content --}}
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('modals')
    @stack('scripts')
</body>
</html>
