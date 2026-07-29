<!DOCTYPE html>
<html lang="{{ $currentLocale ?? 'fa' }}" dir="{{ $direction ?? 'rtl' }}"
      data-theme="{{ session('theme', 'light-neumorphism') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('messages.site_name'))</title>

    @if(($direction ?? 'rtl') === 'rtl')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css">
    @endif
    {{-- Preload فونت‌ها --}}
    <link rel="preload" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css"
          as="style">

    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css">

    @vite(['resources/css/app.css','resources/css/neumorphism.css', 'resources/js/theme.js', 'resources/js/language.js', 'resources/js/slider.js'])
    {{-- Dynamic Theme Injection --}}
    @if(!empty($themeVariables))
        <style>
            :root, [data-theme="{{ $activeTheme }}"] {
                @foreach($themeVariables as $key => $value)
                --{{ $key }}: {{ $value }};
            @endforeach
        }
        </style>
    @endif

    {{-- اضافه کردن Alpine.js برای تعاملات سبد خرید --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>

    @stack('styles')
</head>
<body class="min-h-screen" style="background: var(--bg);">

<x-header/>

<main class="max-w-7xl mx-auto px-4 md:px-6 py-6">
    @if (session('success'))
        <div class="neu-flat-sm px-4 py-3 mb-4" style="color: var(--success)">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="neu-flat-sm px-4 py-3 mb-4" style="color: var(--danger)">{{ session('error') }}</div>
    @endif

    @yield('content')
</main>

<x-footer/>

<script>
    document.querySelectorAll('[data-add-to-cart]').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const originalContent = this.innerHTML;

            // حالت لودینگ
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            this.disabled = true;

            // شبیه‌سازی درخواست AJAX (اینجا باید درخواست واقعی به سرور بفرستید)
            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-check"></i> افزوده شد';
                this.style.background = 'var(--success)';
                this.style.color = '#fff';

                // برگشت به حالت اول بعد از ۲ ثانیه
                setTimeout(() => {
                    this.innerHTML = originalContent;
                    this.style.background = '';
                    this.style.color = '';
                    this.disabled = false;
                }, 2000);
            }, 800);
        });
    });

</script>
{{-- اسکریپت نمایش Toast --}}
@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // فراخوانی تابع showToast که قبلاً تعریف کردیم
            if (typeof showToast === 'function') {
                showToast('{{ session('success') }}', 'success');
            }
        });
    </script>
@endif

@if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof showToast === 'function') {
                showToast('{{ session('error') }}', 'error');
            }
        });
    </script>
@endif

@stack('scripts')
</body>
</html>
