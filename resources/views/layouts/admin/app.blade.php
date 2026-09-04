{{-- داده‌های زبان/جهت/ارقام توسط LocaleComposer (سراسری) آماده می‌شود --}}
<!DOCTYPE html>
<html lang="{{ $currentLocale }}" dir="{{ $direction }}" data-digits="{{ $digitFamily }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('tab-name', __('admin.layout.panel'))</title>
    {{--    <link rel="shortcut icon" href="{{ $setting->imageFile('favicon') ?? asset('favicon.ico') }}">--}}

    <!-- Bootstrap 5 — نسخه RTL فقط برای زبان‌های راست‌به‌چپ، نسخه LTR برای بقیه.
         ⚠️ bootstrap.rtl.min.css معادل کلاس‌های text-start/text-end را با !important راست‌به‌چپ می‌کند
         و در زبان‌های چپ‌به‌چپ همه جدول‌ها و لیست‌ها را خراب می‌کرد؛ پس باید وابسته به جهت باشد. -->
    @if($direction === 'rtl')
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    @else
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @endif
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
            document.documentElement.setAttribute('data-bs-theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            document.documentElement.setAttribute('data-bs-theme', 'light');
        }
    </script>
    {{-- CKEditor محلی پروژه --}}
    <script src="{{ asset('assets/admin/ckeditor/ckeditor.js') }}"></script>
    {{-- جلالی: کتابخانه تبدیل تاریخ + پیکر شمسی --}}
    <script src="{{ asset('assets/admin/js/jalali.js') }}"></script>
    <script src="{{ asset('assets/admin/jalalidatepicker.min/jalalidatepicker.min.js') }}"></script>
    <!-- Jalali Datepicker -->
    <link rel="stylesheet" href="{{ asset('assets/admin/jalalidatepicker.min/jalalidatepicker.min.css') }}">
    {{-- SweetAlert2 — محلی (برای سوال‌های تأیید، خطاهای فرم و ...) --}}
    <script src="{{ asset('assets/admin/js/sweetalert2.all.min.js') }}"></script>

    @vite(['resources/css/app-admin.css', 'resources/js/app-admin.js', 'resources/js/card-format.js'])
    @stack('styles')
</head>
<body class="font-sans bg-gray-50">

<x-admin.toast/>

<div x-data="{ sidebarOpen: true, mobileSidebar: false }"
     @toggle-sidebar.window="sidebarOpen = !sidebarOpen; if (!sidebarOpen) $dispatch('sidebar-closed')"
     @toggle-mobile.window="mobileSidebar = true"
     @close-mobile.window="mobileSidebar = false"
     class="flex min-h-screen">

    <x-admin.sidebar/>

    <div class="flex-1 flex flex-col min-h-screen transition-all duration-300"
         :class="{ 'lg:ms-20': !sidebarOpen, 'lg:ms-64': sidebarOpen }">

        <x-admin.header/>

        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            @yield('content')
        </main>
    </div>

    <x-admin.mobile-sidebar/>
</div>

{{-- کراپر سراسری تصویر — برای همهٔ inputهای تصویری ادمین (window.ImageCropper) --}}
<x-admin.image-cropper />

@stack('modals')
<x-admin.scripts/>

<script>
{{-- window.notifIcon از باندل Vite می‌آید: resources/js/admin/notification-icon.js --}}
function adminNotificationBell() {
    return {
        open: false,
        count: {{ $unreadNotificationCount ?? 0 }},
        items: [],
        loading: false,

        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.fetchNotifications();
            }
        },

        // نمایش هماهنگ با زبان از همان اول (بدون چشمک انگلیسی→فارسی)
        displayCount() {
            const raw = this.count > 99 ? '99+' : String(this.count);
            return (window.localizeDigits && window.localizeDigits.convert)
                ? window.localizeDigits.convert(raw)
                : raw;
        },

        async fetchNotifications() {
            this.loading = true;
            try {
                const res = await fetch('{{ route("admin.api.notifications.unread-count") }}', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const data = await res.json();
                    this.count = data.count ?? 0;
                }
            } catch (e) {}

            try {
                const res = await fetch('{{ route("admin.notifications.index") }}?format=json', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const data = await res.json();
                    const loc = (window.localizeDigits && window.localizeDigits.convert)
                        ? window.localizeDigits.convert
                        : (s) => s;
                    this.items = (data.data || []).slice(0, 8).map((item) => ({
                        ...item,
                        created_at: item.created_at ? loc(item.created_at) : item.created_at,
                    }));
                    if (typeof data.count === 'number') this.count = data.count;
                }
            } catch (e) {}
            this.loading = false;
        },

        async markRead(id) {
            try {
                await fetch('{{ url("/admin/api/notifications") }}/' + id + '/read', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' }
                });
                const item = this.items.find(i => i.id === id);
                if (item) item.is_read = true;
                if (this.count > 0) this.count--;
            } catch (e) {}
        },

        async markAllRead() {
            try {
                await fetch('{{ url("/admin/api/notifications/read-all") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' }
                });
                this.items.forEach(i => i.is_read = true);
                this.count = 0;
            } catch (e) {}
        }
    };
}
</script>

@stack('scripts')
</body>
</html>
