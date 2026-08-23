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

            <div class="mt-6 flex items-center justify-between text-xs">
                <form action="{{ localizedRoute('login.otp.resend') }}" method="POST">
                    @csrf
                    <button type="submit" class="font-semibold hover:opacity-70 transition-opacity"
                            style="color: var(--accent);">
                        {{ __('messages.otp_resend') }}
                    </button>
                </form>
                <a href="{{ localizedRoute('login', ['change' => 1]) }}"
                   class="font-semibold hover:opacity-70 transition-opacity"
                   style="color: var(--text-secondary);">
                    {{ __('messages.otp_change_number') }}
                </a>
            </div>

        </div>
    </div>
</div>
@endsection
