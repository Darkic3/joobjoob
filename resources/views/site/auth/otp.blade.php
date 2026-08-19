@extends('layouts.site.app')

@section('title', __('messages.otp_login'))

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

            <h1 class="page-title !mt-4 !text-2xl md:!text-3xl">{{ __('messages.otp_login') }}</h1>
            <p class="mt-2 text-sm leading-relaxed" style="color: var(--text-secondary);">
                {{ $isNewUser ? __('messages.otp_subtitle_new_user') : __('messages.otp_subtitle') }}
            </p>

            {{-- خطای عمومی (شماره یا نام) --}}
            @if ($errors->has('mobile'))
                <div class="mt-5 flex items-center gap-2.5 rounded-2xl px-4 py-3 text-sm font-semibold"
                     style="background: color-mix(in srgb, var(--danger) 12%, transparent); color: var(--danger);">
                    <svg class="w-4.5 h-4.5 shrink-0" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>
                    </svg>
                    {{ $errors->first('mobile') }}
                </div>
            @endif

            @if ($errors->has('name'))
                <div class="mt-5 flex items-center gap-2.5 rounded-2xl px-4 py-3 text-sm font-semibold"
                     style="background: color-mix(in srgb, var(--danger) 12%, transparent); color: var(--danger);">
                    <svg class="w-4.5 h-4.5 shrink-0" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>
                    </svg>
                    {{ $errors->first('name') }}
                </div>
            @endif

            <form action="{{ localizedRoute('login.otp.send') }}" method="POST" class="form-grid mt-6">
                @csrf

                <div class="form-field">
                    <label for="otp-mobile" class="form-label">{{ __('messages.otp_mobile') }}</label>
                    <input id="otp-mobile" type="tel" name="mobile" value="{{ old('mobile', $otpMobile) }}" required
                           placeholder="{{ __('messages.otp_mobile_ph') }}" class="neu-input" dir="ltr"
                           autocomplete="tel" inputmode="tel" maxlength="13"
                           @error('mobile') style="box-shadow: inset 4px 4px 8px var(--shadow-dark), inset -4px -4px 8px var(--shadow-light), 0 0 0 2px color-mix(in srgb, var(--danger) 45%, transparent);" @enderror
                           {{ $isNewUser ? 'readonly style="opacity: 0.7;"' : '' }}>
                    @error('mobile')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                {{-- فیلد نام فقط برای کاربران جدید --}}
                @if ($isNewUser)
                    <div class="form-field">
                        <label for="otp-name" class="form-label">{{ __('messages.otp_name') }}</label>
                        <input id="otp-name" type="text" name="name" value="{{ old('name') }}" required
                               placeholder="{{ __('messages.otp_name_ph') }}" class="neu-input"
                               maxlength="100"
                               @error('name') style="box-shadow: inset 4px 4px 8px var(--shadow-dark), inset -4px -4px 8px var(--shadow-light), 0 0 0 2px color-mix(in srgb, var(--danger) 45%, transparent);" @enderror>
                        @error('name')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                @endif

                <button type="submit" class="neu-btn neu-btn-primary w-full mt-1">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4Z"/>
                    </svg>
                    <span>{{ $isNewUser ? __('messages.otp_register_btn') : __('messages.otp_send_code') }}</span>
                </button>
            </form>

        </div>
    </div>
</div>
@endsection
