@extends('layouts.site.guest')

@section('title', __('messages.otp_verify'))

@section('content')
<div class="max-w-md mx-auto reveal-on-scroll is-visible">

    <div class="neu-card relative overflow-hidden rounded-3xl !p-0">
        <div class="h-1.5 w-full" style="background: linear-gradient(90deg, var(--accent), color-mix(in srgb, var(--accent) 55%, var(--gold)));"></div>

        <div class="pointer-events-none absolute -top-20 -end-20 w-56 h-56 rounded-full"
             style="background: radial-gradient(circle, color-mix(in srgb, var(--accent) 20%, transparent), transparent 70%);"></div>

        <div class="relative p-7 md:p-9">

            <span class="page-eyebrow mb-4">
                {{ __('messages.account') }}
            </span>

            <h1 class="page-title !mt-4 !text-2xl md:!text-3xl">{{ __('messages.otp_verify_title') }}</h1>
            <p class="mt-2 text-sm leading-relaxed" style="color: var(--text-secondary);">
                {{ __('messages.otp_code_subtitle', ['mobile' => $otpMobile]) }}
            </p>

            {{-- پیام موفقیت --}}
            @if (session('success'))
                <div class="mt-5 flex items-center gap-2.5 rounded-2xl px-4 py-3 text-sm font-semibold"
                     style="background: color-mix(in srgb, var(--success, #16a34a) 12%, transparent); color: var(--success, #16a34a);">
                    <svg class="w-4.5 h-4.5 shrink-0" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            {{-- پیام خطا (مثلاً ارسال پیامک ناموفق) --}}
            @if (session('error'))
                <div class="mt-5 flex items-center gap-2.5 rounded-2xl px-4 py-3 text-sm font-semibold"
                     style="background: color-mix(in srgb, var(--danger) 12%, transparent); color: var(--danger);">
                    <svg class="w-4.5 h-4.5 shrink-0" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>
                    </svg>
                    {{ session('error') }}
                </div>
            @endif

            {{-- خطای عمومی --}}
            @if ($errors->has('code'))
                <div class="mt-5 flex items-center gap-2.5 rounded-2xl px-4 py-3 text-sm font-semibold"
                     style="background: color-mix(in srgb, var(--danger) 12%, transparent); color: var(--danger);">
                    <svg class="w-4.5 h-4.5 shrink-0" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>
                    </svg>
                    {{ $errors->first('code') }}
                </div>
            @endif

            <form action="{{ localizedRoute('login.otp.verify') }}" method="POST" class="form-grid mt-6">
                @csrf

                <div class="form-field">
                    <label for="otp-code" class="form-label">{{ __('messages.otp_code') }}</label>
                    <input id="otp-code" type="text" name="code" value="{{ old('code') }}" required
                           placeholder="{{ __('messages.otp_code_ph') }}" class="neu-input text-center text-lg tracking-[0.4em]"
                           dir="ltr" inputmode="numeric" autocomplete="one-time-code" maxlength="4"
                           @error('code') style="box-shadow: inset 4px 4px 8px var(--shadow-dark), inset -4px -4px 8px var(--shadow-light), 0 0 0 2px color-mix(in srgb, var(--danger) 45%, transparent);" @enderror>
                    @error('code')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <button type="submit" class="neu-btn neu-btn-primary w-full mt-1">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><path d="m10 17 5-5-5-5"/><path d="M15 12H3"/>
                    </svg>
                    <span>{{ __('messages.otp_verify') }}</span>
                </button>
            </form>

            <div class="mt-6 flex items-center justify-between gap-3 text-xs">
                <form action="{{ localizedRoute('login.otp.resend') }}" method="POST" id="resendForm">
                    @csrf
                    <button type="submit" id="resendBtn" disabled
                            class="resend-btn font-bold transition-all"
                            data-ready="{{ __('messages.otp_resend') }}"
                            aria-label="{{ __('messages.otp_resend') }}">
                        <span class="resend-ring-wrap" id="resendRingWrap" aria-hidden="true">
                            <svg class="resend-ring" width="40" height="40" viewBox="0 0 40 40">
                                <defs>
                                    <linearGradient id="resendRingGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" style="stop-color: var(--accent);" />
                                        <stop offset="100%" style="stop-color: var(--gold);" />
                                    </linearGradient>
                                </defs>
                                <circle cx="20" cy="20" r="15.5" fill="none" stroke-width="3.5" class="resend-ring-track" />
                                <circle cx="20" cy="20" r="15.5" fill="none" stroke-width="3.5" stroke-linecap="round"
                                        class="resend-ring-fg" id="resendRingFg" />
                            </svg>
                            <span class="resend-ring-num mono" id="resendSecs"></span>
                        </span>
                        <span id="resendBtnIcon" class="hidden">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a9 9 0 1 1-2.64-6.36" /><path d="M21 3v6h-6" /></svg>
                        </span>
                        <span id="resendHint" data-tpl="{{ __('messages.otp_resend_in', ['seconds' => '__S__']) }}"></span>
                    </button>
                </form>
                <a href="{{ localizedRoute('login', ['change' => 1]) }}"
                   class="font-semibold hover:opacity-70 transition-opacity shrink-0"
                   style="color: var(--text-secondary);">
                    {{ __('messages.otp_change_number') }}
                </a>
            </div>

        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* ─── تایمر ارسال مجدد کد: حلقه پیشرفت + درخشش ─── */
    .resend-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding-inline-start: 6px;
        padding-inline-end: 14px;
        padding-top: 6px;
        padding-bottom: 6px;
        border-radius: 16px;
        background: color-mix(in srgb, var(--accent) 7%, transparent);
        border: 1px solid color-mix(in srgb, var(--accent) 14%, transparent);
        color: var(--text-secondary);
        cursor: not-allowed;
    }
    .resend-btn.ready {
        cursor: pointer;
        color: var(--accent);
        background: color-mix(in srgb, var(--accent) 13%, transparent);
        border-color: color-mix(in srgb, var(--accent) 32%, transparent);
        animation: resendGlow 1.8s ease-in-out infinite;
    }
    .resend-btn.ready:hover {
        transform: translateY(-1px) scale(1.02);
    }
    .resend-btn.ready:active {
        transform: scale(0.97);
    }
    @keyframes resendGlow {
        0%, 100% { box-shadow: 0 0 0 0 color-mix(in srgb, var(--accent) 35%, transparent); }
        50% { box-shadow: 0 0 16px 4px color-mix(in srgb, var(--accent) 28%, transparent); }
    }
    .resend-ring-wrap {
        position: relative;
        width: 40px;
        height: 40px;
        display: grid;
        place-items: center;
        flex-shrink: 0;
    }
    .resend-ring {
        transform: rotate(-90deg);
    }
    .resend-ring-track {
        stroke: color-mix(in srgb, var(--text-primary) 10%, transparent);
    }
    .resend-ring-fg {
        stroke: url(#resendRingGrad);
        stroke-dasharray: 97.4;
        stroke-dashoffset: 97.4;
    }
    .resend-ring-num {
        position: absolute;
        inset: 0;
        display: grid;
        place-items: center;
        font-size: 12px;
        font-weight: 800;
        color: var(--accent);
    }
    #resendHint b {
        color: var(--accent);
        font-weight: 800;
    }
    @media (prefers-reduced-motion: reduce) {
        .resend-btn.ready { animation: none; }
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        var TOTAL = 30;
        var CIRC = 2 * Math.PI * 15.5;
        var form = document.getElementById('resendForm');
        var btn = document.getElementById('resendBtn');
        if (!form || !btn) return;
        var ringFg = document.getElementById('resendRingFg');
        var ringWrap = document.getElementById('resendRingWrap');
        var secsEl = document.getElementById('resendSecs');
        var hintEl = document.getElementById('resendHint');
        var iconEl = document.getElementById('resendBtnIcon');
        var readyLabel = btn.getAttribute('data-ready') || '';
        var tpl = hintEl.getAttribute('data-tpl') || '__S__';

        var FA = '۰۱۲۳۴۵۶۷۸۹', AR = '٠١٢٣٤٥٦٧٨٩';
        function loc(n) {
            var s = String(n);
            var a = document.documentElement.getAttribute('data-digits');
            var fam = (a === 'persian' || a === 'arabic' || a === 'latin') ? a : null;
            if (!fam) {
                var l = (document.documentElement.getAttribute('lang') || 'fa').toLowerCase();
                fam = l === 'fa' ? 'persian' : (l === 'ar' ? 'arabic' : 'latin');
            }
            if (fam === 'persian') return s.replace(/[0-9]/g, function (d) { return FA[+d]; });
            if (fam === 'arabic') return s.replace(/[0-9]/g, function (d) { return AR[+d]; });
            return s;
        }

        function render(remaining) {
            var t = loc(remaining);
            secsEl.textContent = t;
            hintEl.innerHTML = tpl.split('__S__').join('<b>' + t + '</b>');
            if (ringFg) ringFg.style.strokeDashoffset = (CIRC * remaining / TOTAL).toFixed(1);
        }

        var endAt = Date.now() + TOTAL * 1000;
        render(TOTAL);
        var timer = setInterval(function () {
            var remaining = Math.max(0, Math.ceil((endAt - Date.now()) / 1000));
            render(remaining);
            if (remaining <= 0) {
                clearInterval(timer);
                btn.disabled = false;
                btn.classList.add('ready');
                if (ringWrap) ringWrap.classList.add('hidden');
                if (iconEl) iconEl.classList.remove('hidden');
                hintEl.textContent = readyLabel;
            }
        }, 200);

        // جلوگیری از دابل‌سابمیت بعد از فعال شدن
        form.addEventListener('submit', function () {
            if (btn.disabled) return false;
            btn.disabled = true;
            btn.classList.remove('ready');
        });
    })();
</script>
@endpush
