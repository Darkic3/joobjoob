@extends('layouts.site.guest')

@section('title', __('messages.otp_login'))

@section('content')
<div class="reveal-on-scroll is-visible" x-data="loginForm()" x-init="init()">

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
                {{ __('messages.otp_subtitle') }}
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

            <form action="{{ localizedRoute('login.otp.send') }}" method="POST" class="form-grid mt-6" @submit.prevent="handleSubmit">
                @csrf

                {{-- فیلد نام فقط برای کاربران جدید — بالای فیلد شماره ظاهر می‌شود --}}
                <div x-show="showNameField" x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                     class="form-field" style="{{ $errors->has('name') || ($showNameField ?? false) ? '' : 'display:none;' }}">
                    <label for="otp-name" class="form-label">{{ __('messages.otp_name') }}</label>
                    <input id="otp-name" type="text" name="name" value="{{ old('name') }}"
                           :required="showNameField"
                           placeholder="{{ __('messages.otp_name_ph') }}" class="neu-input"
                           maxlength="100"
                           x-ref="nameInput"
                           @error('name') style="box-shadow: inset 4px 4px 8px var(--shadow-dark), inset -4px -4px 8px var(--shadow-light), 0 0 0 2px color-mix(in srgb, var(--danger) 45%, transparent);" @enderror>
                    @error('name')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-field">
                    <label for="otp-mobile" class="form-label">{{ __('messages.otp_mobile') }}</label>
                    <input id="otp-mobile" type="tel" name="mobile" value="{{ old('mobile', $otpMobile) }}" required
                           placeholder="{{ __('messages.otp_mobile_ph') }}" class="neu-input" dir="ltr"
                           autocomplete="tel" inputmode="tel" maxlength="13"
                           x-model="mobile"
                           @blur="checkPhone"
                           @input="showNameField = false"
                           @error('mobile') style="box-shadow: inset 4px 4px 8px var(--shadow-dark), inset -4px -4px 8px var(--shadow-light), 0 0 0 2px color-mix(in srgb, var(--danger) 45%, transparent);" @enderror>
                    @error('mobile')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <button type="submit" class="neu-btn neu-btn-primary w-full mt-1" :disabled="loading">
                    <template x-if="loading">
                        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <template x-if="!loading">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4Z"/>
                        </svg>
                    </template>
                    <span x-text="showNameField ? '{{ __('messages.otp_register_btn') }}' : '{{ __('messages.otp_send_code') }}'"></span>
                </button>
            </form>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function loginForm() {
    return {
        mobile: '{{ old("mobile", $otpMobile ?? "") }}',
        showNameField: {{ ($errors->has("name") || ($showNameField ?? false)) ? "true" : "false" }},
        loading: false,

        init() {
            // If there's an old mobile value and we should show name field on load
            @if($errors->has('name') || ($showNameField ?? false))
                this.showNameField = true;
                this.$nextTick(() => {
                    if (this.$refs.nameInput) this.$refs.nameInput.focus();
                });
            @endif
        },

        async checkPhone() {
            const val = (this.mobile || '').trim();
            if (!val || val.length < 10) {
                this.showNameField = false;
                return;
            }

            try {
                const res = await fetch('{{ localizedRoute("login.otp.check") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ mobile: val }),
                });

                const data = await res.json();

                if (data.exists === false) {
                    this.showNameField = true;
                    this.$nextTick(() => {
                        if (this.$refs.nameInput) this.$refs.nameInput.focus();
                    });
                } else {
                    this.showNameField = false;
                }
            } catch (e) {
                // On error, let the server-side handle it
            }
        },

        handleSubmit() {
            this.loading = true;
            this.$el.closest('form').submit();
        }
    };
}
</script>
@endpush
