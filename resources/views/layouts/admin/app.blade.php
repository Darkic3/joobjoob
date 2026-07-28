<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('tab-name', 'پنل مدیریت')</title>
{{--    <link rel="shortcut icon" href="{{ $setting->imageFile('favicon') ?? asset('favicon.ico') }}">--}}

    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="font-sans bg-gray-50">

<x-admin.toast />

<div x-data="{ sidebarOpen: true, mobileSidebar: false }"
     @toggle-sidebar.window="sidebarOpen = !sidebarOpen"
     @toggle-mobile.window="mobileSidebar = true"
     @close-mobile.window="mobileSidebar = false"
     class="flex min-h-screen">

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

<x-admin.scripts />
@stack('scripts')
</body>
</html>
