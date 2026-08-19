@extends('layouts.site.app')

@section('title', __('messages.login'))

@section('content')
<div class="max-w-md mx-auto reveal-on-scroll is-visible">

    {{-- کارت لاگین — با نوار گرادینتی بالا و ورود پله‌ای --}}
    <div class="neu-card relative overflow-hidden rounded-3xl !p-0">
        {{-- نوار گرادینتی بالا --}}
        <div class="h-1.5 w-full" style="background: linear-gradient(90deg, var(--accent), color-mix(in srgb, var(--accent) 55%, var(--gold)));"></div>

        {{-- هاله‌ی پس‌زمینه --}}
        <div class="pointer-events-none absolute -top-20 -end-20 w-56 h-56 rounded-full"
             style="background: radial-gradient(circle, color-mix(in srgb, var(--accent) 20%, transparent), transparent 70%);"></div>

        <div class="relative p-7 md:p-9">

            {{-- برچسب کوچک بالا --}}
            <span class="page-eyebrow mb-4">
                {{ __('messages.account') }}
            </span>

            <h1 class="page-title !mt-4 !text-2xl md:!text-3xl">{{ __('messages.login_title') }}</h1>
            <p class="mt-2 text-sm leading-relaxed" style="color: var(--text-secondary);">{{ __('messages.login_subtitle') }}</p>

            {{-- خطای عمومی لاگین --}}
            @if ($errors->has('mobile'))
                <div class="mt-5 flex items-center gap-2.5 rounded-2xl px-4 py-3 text-sm font-semibold"
                     style="background: color-mix(in srgb, var(--danger) 12%, transparent); color: var(--danger);">
                    <svg class="w-4.5 h-4.5 shrink-0" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>
                    </svg>
                    {{ $errors->first('mobile') }}
                </div>
            @endif

            <form action="{{ localizedRoute('login.submit') }}" method="POST" class="form-grid mt-6">
                @csrf

                <div class="form-field">
                    <label for="login-mobile" class="form-label">{{ __('messages.otp_mobile') }}</label>
                    <input id="login-mobile" type="tel" name="mobile" value="{{ old('mobile') }}" required
                           placeholder="{{ __('messages.otp_mobile_ph') }}" class="neu-input" autocomplete="tel" dir="ltr"
                           inputmode="tel" maxlength="13"
                           @error('mobile') style="box-shadow: inset 4px 4px 8px var(--shadow-dark), inset -4px -4px 8px var(--shadow-light), 0 0 0 2px color-mix(in srgb, var(--danger) 45%, transparent);" @enderror>
                    @error('mobile')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-field">
                    <label for="login-password" class="form-label">{{ __('messages.password') }}</label>
                    <input id="login-password" type="password" name="password" required
                           placeholder="••••••••" class="neu-input" autocomplete="current-password" dir="ltr"
                           @error('password') style="box-shadow: inset 4px 4px 8px var(--shadow-dark), inset -4px -4px 8px var(--shadow-light), 0 0 0 2px color-mix(in srgb, var(--danger) 45%, transparent);" @enderror>
                    @error('password')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <label class="flex items-center gap-2.5 text-sm cursor-pointer select-none" style="color: var(--text-secondary);">
                    <input type="checkbox" name="remember" value="1"
                           class="w-4 h-4 rounded accent-current cursor-pointer"
                           style="accent-color: var(--accent);">
                    <span>{{ __('messages.remember_me') }}</span>
                </label>

                <button type="submit" class="neu-btn neu-btn-primary w-full mt-1">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                        <path d="m10 17 5-5-5-5"/><path d="M15 12H3"/>
                    </svg>
                    <span>{{ __('messages.login') }}</span>
                </button>
            </form>

            {{-- ثبت‌نام — بعداً به جریان ثبت‌نام کانکت می‌شود --}}
            <p class="mt-6 text-center text-xs leading-relaxed" style="color: var(--text-secondary);">
                {{ __('messages.no_account_hint') }}
            </p>

            {{-- ورود با کد یکبارمصرف --}}
            <div class="mt-4 pt-4 text-center border-t" style="border-color: color-mix(in srgb, var(--text-secondary) 15%, transparent);">
                <a href="{{ localizedRoute('login.otp') }}" class="text-sm font-semibold hover:opacity-70 transition-opacity" style="color: var(--accent);">
                    {{ __('messages.otp_login') }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
