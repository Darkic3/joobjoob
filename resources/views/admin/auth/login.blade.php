@php
    // زبان و جهت فعال (توسط SetLocale به همه ویوها share می‌شود)
    $currentLocale = $currentLocale ?? app()->getLocale();
    $direction = $direction ?? (locale()->isRtl($currentLocale) ? 'rtl' : 'ltr');
    // زبان‌های فعال از سرویس — در حالت تک‌زبانه سوییچر مخفی می‌شود
    $locales = locale()->active();
@endphp
<!DOCTYPE html>
<html lang="{{ $currentLocale }}" dir="{{ $direction }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#4f46e5">
    <title>{{ __('admin.login.title') }}</title>

    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    {{-- استایل‌های اختصاصی این صفحه قبل از Vite لود می‌شوند تا کلاس‌های Tailwind
         (مانند pl-11) در برخورد با آن‌ها اولویت داشته باشند --}}
    <style>
        /* ===== پس‌زمینه و انیمیشن‌ها ===== */
        .bg-grid {
            background-image:
                linear-gradient(to left, rgba(255,255,255,.06) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255,255,255,.06) 1px, transparent 1px);
            background-size: 44px 44px;
        }

        @keyframes blob-float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33%      { transform: translate(28px, -34px) scale(1.08); }
            66%      { transform: translate(-20px, 22px) scale(.94); }
        }
        .animate-blob       { animation: blob-float 14s ease-in-out infinite; }
        .animate-blob-slow  { animation: blob-float 22s ease-in-out infinite reverse; }

        @keyframes fade-up {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-up { animation: fade-up .7s cubic-bezier(.22, 1, .36, 1) both; }
        .d-1 { animation-delay: .08s; }
        .d-2 { animation-delay: .16s; }
        .d-3 { animation-delay: .24s; }
        .d-4 { animation-delay: .32s; }
        .d-5 { animation-delay: .40s; }

        @keyframes ring-pulse {
            0%   { box-shadow: 0 0 0 0 rgba(255,255,255,.35); }
            70%  { box-shadow: 0 0 0 16px rgba(255,255,255,0); }
            100% { box-shadow: 0 0 0 0 rgba(255,255,255,0); }
        }
        .animate-ring { animation: ring-pulse 2.6s ease-out infinite; }

        @keyframes bar-grow {
            from { transform: scaleY(0); }
            to   { transform: scaleY(1); }
        }
        .animate-bar { transform-origin: bottom; animation: bar-grow .9s cubic-bezier(.22, 1, .36, 1) both; }

        /* ===== حالت شیشه‌ای کارت ورود ===== */
        .glass-card {
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 24px 60px -18px rgba(30, 27, 75, .18), 0 4px 14px rgba(30, 27, 75, .06);
        }

        /* ورودی‌های فرم — حالت focus با حلقه درخشان (CSS خالص، چون خارج از بیلد Tailwind است) */
        .login-input {
            width: 100%;
            border-radius: .75rem;
            border: 1px solid #e5e7eb;
            background: rgba(255, 255, 255, .85);
            font-size: .875rem;
            color: #1f2937;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .05);
            outline: none;
            transition: all .2s ease;
            padding: .85rem 3rem .85rem 1rem;
        }
        .login-input::placeholder { color: #9ca3af; }
        .dark .login-input {
            background: rgba(31, 41, 55, .8);
            border-color: #4b5563;
            color: #f3f4f6;
        }
        .dark .login-input::placeholder { color: #6b7280; }
        .login-input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, .16);
        }
        .dark .login-input:focus { border-color: #818cf8; }
        .login-input.has-error {
            border-color: #fb7185;
            box-shadow: 0 0 0 4px rgba(244, 63, 94, .1);
        }
        .dark .login-input.has-error { border-color: #f43f5e; }

        /* پنهان‌سازی عناصر قبل از اجرای Alpine */
        [x-cloak] { display: none !important; }

        /* ===== قفل صفحه: دقیقاً اندازه ویوپورت، بدون اسکرول عمودی/افقی ===== */
        html, body {
            height: 100%;
            overflow: hidden;
            overscroll-behavior: none;
        }

        /* فشرده‌سازی خودکار در صفحه‌های کوتاه تا محتوا از قاب بیرون نزند */
        @media (max-height: 760px) {
            .login-aside { padding: 1.5rem 2.5rem; }
            .login-aside-content { margin-top: .25rem; }
            .login-aside .login-features { margin-top: 1rem; }
            .login-stats-card { display: none; }
        }
        @media (max-height: 700px) {
            .login-card { padding: 1.5rem; }
            .login-card-header { margin-bottom: 1.25rem; }
        }
        @media (max-height: 640px) {
            .login-mobile-brand { display: none; }
        }
    </style>

    @vite(['resources/css/app-admin.css', 'resources/js/app-admin.js'])
</head>

@php
    // لاگین واقعی — POST /admin/login (admin.login.submit)
    $loginAction = route('admin.login.submit');
    $resetAction = Route::has('admin.password.email') ? route('admin.password.email') : null;
    $errorsBag   = $errors ?? null;
    $hasError    = session('error') || ($errorsBag && $errorsBag->any());
    $flashType   = session('error') ? 'error' : (session('success') ? 'success' : null);
@endphp

<body class="min-h-screen bg-gray-50 dark:bg-gray-950 text-gray-800 dark:text-gray-100 font-sans antialiased selection:bg-primary-500/30">

    {{-- ===== انتخاب زبان + حالت تاریک/روشن ===== --}}
    <div class="fixed top-5 left-5 z-50 animate-fade-up">
        <div class="flex items-center gap-1 p-1.5 rounded-xl bg-white/70 dark:bg-gray-900/70 border border-gray-200/60 dark:border-gray-700/60 backdrop-blur-md shadow-sm">
            @if (count($locales) > 1)
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" title="{{ __('admin.login.change_lang') }}"
                            class="flex items-center gap-1.5 px-2 py-1.5 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21a9 9 0 100-18 9 9 0 000 18zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <span class="text-xs font-bold">{{ strtoupper($currentLocale) }}</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                         class="absolute left-0 mt-2 w-40 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 py-1.5 z-50">
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
                <div class="w-px h-6 bg-gray-200/60 dark:bg-gray-700/60 mx-1"></div>
            @endif
            <x-admin.theme-toggle />
        </div>
    </div>

    <div class="min-h-screen lg:grid lg:grid-cols-2">

        {{-- ============================================================
             پنل برندینگ (فقط در صفحه‌های بزرگ)
        ============================================================ --}}
        <aside class="login-aside hidden lg:flex relative flex-col justify-between overflow-hidden
                    bg-gradient-to-br from-indigo-700 via-primary-700 to-violet-800 text-white p-12">

            {{-- حباب‌های شناور + شبکه --}}
            <div class="absolute inset-0 bg-grid"></div>
            <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-indigo-400/40 blur-3xl animate-blob"></div>
            <div class="absolute bottom-0 -left-24 w-[28rem] h-[28rem] rounded-full bg-fuchsia-500/30 blur-3xl animate-blob-slow"></div>
            <div class="absolute top-1/3 left-1/4 w-64 h-64 rounded-full bg-primary-300/20 blur-3xl animate-blob"></div>

            {{-- هدر پنل --}}
            <div class="relative z-10 flex items-center gap-3 animate-fade-up">
                <div class="w-11 h-11 rounded-2xl bg-white/15 backdrop-blur-md border border-white/25 flex items-center justify-center shadow-lg animate-ring">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-lg font-extrabold leading-tight">{{ __('messages.site_name') }}</div>
                    <div class="text-xs text-white/70">{{ __('admin.login.panel_of') }}</div>
                </div>
            </div>

            {{-- متن اصلی --}}
            <div class="login-aside-content relative z-10 max-w-md">
                <h1 class="text-3xl xl:text-4xl font-extrabold leading-snug animate-fade-up d-1">
                    {{ __('admin.login.brand_headline') }}
                    <span class="text-transparent bg-clip-text bg-gradient-to-l from-amber-200 to-orange-300">{{ __('admin.login.brand_highlight') }}</span>
                </h1>
                <p class="mt-4 text-white/75 leading-7 text-sm xl:text-base animate-fade-up d-2">
                    {{ __('admin.login.brand_desc') }}
                </p>

                {{-- ویژگی‌ها --}}
                <ul class="login-features mt-8 space-y-4">
                    <li class="flex items-center gap-3 animate-fade-up d-3">
                        <span class="w-9 h-9 shrink-0 rounded-xl bg-white/12 border border-white/20 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </span>
                        <span class="text-sm text-white/85">{{ __('admin.login.feature_1') }}</span>
                    </li>
                    <li class="flex items-center gap-3 animate-fade-up d-4">
                        <span class="w-9 h-9 shrink-0 rounded-xl bg-white/12 border border-white/20 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </span>
                        <span class="text-sm text-white/85">{{ __('admin.login.feature_2') }}</span>
                    </li>
                    <li class="flex items-center gap-3 animate-fade-up d-5">
                        <span class="w-9 h-9 shrink-0 rounded-xl bg-white/12 border border-white/20 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </span>
                        <span class="text-sm text-white/85">{{ __('admin.login.feature_3') }}</span>
                    </li>
                </ul>

                {{-- کارت شناور آمار --}}
                <div class="login-stats-card mt-10 inline-flex items-center gap-5 rounded-2xl bg-white/10 border border-white/15 backdrop-blur-md px-6 py-4 shadow-xl animate-fade-up d-5">
                    <div>
                        <div class="text-2xl font-extrabold">{{ __('admin.login.stats_visits_value') }}</div>
                        <div class="text-[11px] text-white/70">{{ __('admin.login.stats_visits') }}</div>
                    </div>
                    <div class="w-px h-10 bg-white/20"></div>
                    <div class="flex items-end gap-1.5 h-11">
                        @foreach([35, 55, 40, 70, 58, 88, 100] as $i => $h)
                            <div class="w-2.5 rounded-t-md bg-white/80 animate-bar" style="height:{{ $h }}%; animation-delay:{{ $i * .07 }}s"></div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- فوتر پنل --}}
            <div class="relative z-10 flex items-center justify-between text-xs text-white/60 animate-fade-up">
                <span>© {{ show_date(null, 'Y') }} — {{ __('messages.site_name') }}</span>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 hover:text-white transition-colors">
                    {{ __('admin.login.back_to_site') }}
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
            </div>
        </aside>

        {{-- ============================================================
             سمت فرم ورود
        ============================================================ --}}
        <main class="relative flex items-center justify-center min-h-screen overflow-hidden p-5 sm:p-8">
            {{-- حباب‌های پس‌زمینه سمت فرم (موبایل هم دیده شوند) --}}
            <div class="absolute -top-28 -left-28 w-80 h-80 rounded-full bg-primary-300/25 dark:bg-primary-600/15 blur-3xl animate-blob"></div>
            <div class="absolute -bottom-24 -right-24 w-72 h-72 rounded-full bg-violet-300/25 dark:bg-violet-600/15 blur-3xl animate-blob-slow"></div>

            <div class="relative z-10 w-full max-w-md">

                {{-- برند موبایل --}}
                <div class="login-mobile-brand lg:hidden flex flex-col items-center mb-8 animate-fade-up">
                    <div class="w-16 h-16 rounded-3xl bg-gradient-to-br from-indigo-600 to-violet-700 text-white flex items-center justify-center shadow-xl shadow-primary-600/30">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                    <div class="mt-3 text-xl font-extrabold">{{ __('messages.site_name') }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('admin.login.panel_of') }}</div>
                </div>

                {{-- هشدارها --}}
                <div x-data="{ show: {{ $hasError ? 'true' : 'false' }} }" class="mb-5 space-y-3">
                    @if($hasError)
                        <div x-show="show" x-transition.opacity.duration.300ms
                             class="flex items-start gap-3 rounded-xl border border-rose-200 dark:border-rose-500/30 bg-rose-50 dark:bg-rose-500/10 px-4 py-3 text-sm text-rose-700 dark:text-rose-300">
                            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                            </svg>
                            <div class="flex-1 leading-6">
                                @if(session('error'))
                                    {{ session('error') }}
                                @elseif($errorsBag)
                                    <ul class="list-disc pr-4 space-y-0.5">
                                        @foreach($errorsBag->all() as $err)<li>{{ $err }}</li>@endforeach
                                    </ul>
                                @endif
                            </div>
                            <button type="button" @click="show = false" class="text-rose-400 hover:text-rose-600 dark:hover:text-rose-200 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    @endif

                    @if($flashType === 'success')
                        <div x-data="{ show: true }" x-show="show" x-transition.opacity.duration.300ms
                             class="flex items-start gap-3 rounded-xl border border-emerald-200 dark:border-emerald-500/30 bg-emerald-50 dark:bg-emerald-500/10 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
                            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="flex-1 leading-6">{{ session('success') }}</div>
                            <button type="button" @click="show = false" class="text-emerald-400 hover:text-emerald-600 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    @endif
                </div>

                {{-- کارت ورود --}}
                <div class="login-card glass-card rounded-3xl bg-white/85 dark:bg-gray-900/75 border border-gray-200/70 dark:border-gray-700/60 p-7 sm:p-9 animate-fade-up d-1">
                    {{-- هدر کارت --}}
                    <div class="login-card-header text-center mb-8">
                        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-700 text-white shadow-lg shadow-primary-600/25 mb-4">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-extrabold">{{ __('admin.login.welcome') }}</h2>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('admin.login.welcome_sub') }}</p>
                    </div>

                    <form method="POST" action="{{ $loginAction }}"
                          x-data="{ showPassword: false, loading: false, email: '{{ old('email') }}' }"
                          @submit="loading = true"
                          class="space-y-5">

                        @csrf

                        {{-- ایمیل --}}
                        <div>
                            <label for="login-email" class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1.5">
                                {{ __('admin.login.email') }}
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-gray-400 dark:text-gray-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                    </svg>
                                </span>
                                <input type="email" id="login-email" name="email" required autofocus autocomplete="email"
                                       x-model="email"
                                       placeholder="name@example.com"
                                       class="login-input {{ $errorsBag && $errorsBag->has('email') ? 'has-error' : '' }}">
                            </div>
                            @if($errorsBag && $errorsBag->has('email'))
                                <p class="mt-1.5 text-xs text-rose-500">{{ $errorsBag->first('email') }}</p>
                            @endif
                        </div>

                        {{-- رمز عبور --}}
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="login-password" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">{{ __('admin.login.password') }}</label>
                                @if($resetAction)
                                    <a href="{{ $resetAction }}" class="text-xs text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-colors">{{ __('admin.login.forgot') }}</a>
                                @endif
                            </div>
                            <div class="relative">
                                <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-gray-400 dark:text-gray-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                                    </svg>
                                </span>
                                <input :type="showPassword ? 'text' : 'password'" id="login-password" name="password" required
                                       autocomplete="current-password" placeholder="••••••••"
                                       class="login-input pl-11 {{ $errorsBag && $errorsBag->has('password') ? 'has-error' : '' }}">
                                <button type="button" @click="showPassword = !showPassword"
                                        :title="showPassword ? '{{ __('admin.login.hide_password') }}' : '{{ __('admin.login.show_password') }}'"
                                        :aria-label="showPassword ? '{{ __('admin.login.hide_password') }}' : '{{ __('admin.login.show_password') }}'"
                                        :aria-pressed="showPassword"
                                        class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400 dark:text-gray-500 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                    <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                                    </svg>
                                </button>
                            </div>
                            @if($errorsBag && $errorsBag->has('password'))
                                <p class="mt-1.5 text-xs text-rose-500">{{ $errorsBag->first('password') }}</p>
                            @endif
                        </div>

                        {{-- مرا به خاطر بسپار --}}
                        <label x-data="{ remember: false }" class="group flex items-center gap-2.5 cursor-pointer select-none">
                            <input type="checkbox" name="remember" value="1" x-model="remember" class="sr-only">
                            <span :class="remember
                                    ? 'bg-primary-600 border-primary-600 shadow-sm shadow-primary-600/30'
                                    : 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 group-hover:border-primary-400'"
                                  class="w-5 h-5 rounded-md border-2 flex items-center justify-center transition-all duration-200">
                                <svg :class="remember ? 'opacity-100 scale-100' : 'opacity-0 scale-50'"
                                     class="w-3.5 h-3.5 text-white transition-all duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                            <span class="text-sm text-gray-600 dark:text-gray-300">{{ __('admin.login.remember') }}</span>
                        </label>

                        {{-- دکمه ورود --}}
                        <button type="submit" :disabled="loading"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3.5 text-sm font-bold text-white
                                       bg-gradient-to-l from-indigo-600 via-primary-600 to-violet-600
                                       hover:from-indigo-500 hover:via-primary-500 hover:to-violet-500
                                       shadow-lg shadow-primary-600/25 hover:shadow-xl hover:shadow-primary-600/35
                                       hover:-translate-y-0.5 active:translate-y-0 active:scale-[.99]
                                       transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-primary-500/25
                                       disabled:opacity-70 disabled:cursor-not-allowed disabled:hover:translate-y-0">
                            <svg x-show="!loading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
                            </svg>
                            <svg x-show="loading" x-cloak class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-show="!loading">{{ __('admin.login.submit') }}</span>
                            <span x-show="loading" x-cloak>{{ __('admin.login.submitting') }}</span>
                        </button>

                        <p class="text-center text-xs text-gray-400 dark:text-gray-500 pt-1">
                            {{ __('admin.login.restricted') }}
                        </p>
                    </form>
                </div>

                {{-- لینک بازگشت (موبایل) --}}
                <div class="lg:hidden mt-6 text-center animate-fade-up d-2">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        {{ __('admin.login.back_to_site') }}
                    </a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
