@php
    $currentLocale = $currentLocale ?? app()->getLocale();
    $direction = $direction ?? (locale()->isRtl($currentLocale) ? 'rtl' : 'ltr');
    $digitFamily = locale()->digitFamily($currentLocale);
@endphp
<!DOCTYPE html>
<html lang="{{ $currentLocale }}" dir="{{ $direction }}" data-digits="{{ $digitFamily }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('messages.site_name') }} — 429</title>
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    <style>
        html, body { height: 100%; }
        body {
            margin: 0;
            font-family: system-ui, -apple-system, 'Segoe UI', Tahoma, sans-serif;
            background: linear-gradient(135deg, #eef2ff 0%, #f5f3ff 50%, #ede9fe 100%);
            color: #1f2937;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .dark body, body.dark {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 55%, #312e81 100%);
            color: #f3f4f6;
        }
        .card {
            background: rgba(255, 255, 255, .9);
            border: 1px solid #e5e7eb;
            border-radius: 1.5rem;
            padding: 3rem 2.5rem;
            text-align: center;
            max-width: 26rem;
            width: calc(100% - 2rem);
            box-shadow: 0 24px 60px -18px rgba(30, 27, 75, .2);
            backdrop-filter: blur(12px);
        }
        .dark .card { background: rgba(17, 24, 39, .85); border-color: #374151; }
        .code {
            font-size: 4rem;
            font-weight: 800;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            line-height: 1;
        }
        .icon { margin-bottom: 1rem; }
        h1 { font-size: 1.15rem; margin: .75rem 0 .4rem; }
        p { font-size: .875rem; color: #6b7280; margin: 0 0 1.5rem; line-height: 1.8; }
        .dark p { color: #9ca3af; }
        a {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            font-weight: 700;
            font-size: .875rem;
            padding: .7rem 1.4rem;
            border-radius: .75rem;
            text-decoration: none;
            box-shadow: 0 10px 24px -8px rgba(99, 102, 241, .5);
            transition: transform .15s ease, box-shadow .15s ease;
        }
        a:hover { transform: translateY(-2px); box-shadow: 0 14px 28px -8px rgba(99, 102, 241, .6); }
    </style>
</head>
<body>
<div class="card">
    <div class="icon">
        <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="1.6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
        </svg>
    </div>
    <div class="code">429</div>
    <h1>@lang('admin.errors.too_many_attempts')</h1>
    <p>@lang('admin.errors.too_many_attempts_desc')</p>
    <a href="{{ route('admin.login') }}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        @lang('admin.errors.back_to_login')
    </a>
</div>
</body>
</html>
