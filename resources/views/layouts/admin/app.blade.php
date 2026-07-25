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
<body class="font-sans bg-gray-50">

<x-admin.toast />

{{-- x-data را اینجا تعریف می‌کنیم تا تمام کامپوننت‌های زیر به آن دسترسی داشته باشند --}}
<div x-data="{ sidebarOpen: true, mobileSidebar: false }"
     @toggle-sidebar.window="sidebarOpen = !sidebarOpen"
     @toggle-mobile.window="mobileSidebar = true"
     @close-mobile.window="mobileSidebar = false"
     class="flex min-h-screen">

    {{-- دیگر نیازی به پاس دادن متغیر نیست --}}
    <x-admin.sidebar />

    <div class="flex-1 flex flex-col min-h-screen transition-all duration-300"
         :class="{ 'lg:mr-20': !sidebarOpen, 'lg:mr-64': sidebarOpen }">

        <x-admin.header />

        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            @yield('content')
        </main>
    </div>

    <x-admin.mobile-sidebar />
</div>

@stack('modals')
@stack('scripts')
</body>
</html>
