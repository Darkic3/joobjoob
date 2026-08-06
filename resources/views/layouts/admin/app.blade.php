@php
    // زبان و جهت فعال (توسط SetLocale به همه ویوها share می‌شود)
    $currentLocale = $currentLocale ?? app()->getLocale();
    $direction = $direction ?? (in_array($currentLocale, config('app.rtl_locales', ['fa', 'ar'])) ? 'rtl' : 'ltr');
@endphp
<!DOCTYPE html>
<html lang="{{ $currentLocale }}" dir="{{ $direction }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('tab-name', __('admin.layout.panel'))</title>
    {{--    <link rel="shortcut icon" href="{{ $setting->imageFile('favicon') ?? asset('favicon.ico') }}">--}}

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    {{-- CKEditor محلی پروژه --}}
    <script src="{{ asset('assets/admin/ckeditor/ckeditor.js') }}"></script>
    {{-- جلالی: کتابخانه تبدیل تاریخ + پیکر شمسی --}}
    <script src="{{ asset('assets/admin/js/jalali.js') }}"></script>
    <script src="{{ asset('assets/admin/jalalidatepicker.min/jalalidatepicker.min.js') }}"></script>
    <!-- Jalali Datepicker -->
    <link rel="stylesheet" href="{{ asset('assets/admin/jalalidatepicker.min/jalalidatepicker.min.css') }}">

    @vite(['resources/css/app-admin.css', 'resources/js/app-admin.js'])
    @stack('styles')
</head>
<body class="font-sans bg-gray-50">

<x-admin.toast/>

<div x-data="{ sidebarOpen: true, mobileSidebar: false }"
     @toggle-sidebar.window="sidebarOpen = !sidebarOpen"
     @toggle-mobile.window="mobileSidebar = true"
     @close-mobile.window="mobileSidebar = false"
     class="flex min-h-screen">

    <x-admin.sidebar/>

    <div class="flex-1 flex flex-col min-h-screen transition-all duration-300"
         :class="{ 'lg:mr-20': !sidebarOpen, 'lg:mr-64': sidebarOpen }">

        <x-admin.header/>

        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            @yield('content')
        </main>
    </div>

    <x-admin.mobile-sidebar/>
</div>

<x-admin.scripts/>
@stack('scripts')
</body>
</html>
