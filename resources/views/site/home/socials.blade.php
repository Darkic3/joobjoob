{{-- ============================================================================
    ستون دوگانه پایانی صفحهٔ اصلی (مسیر C):
    ۱) شبکه‌های اجتماعی — از تنظیمات (social_instagram / social_telegram / …)
    ۲) باکس ثبت‌نام + کد تخفیف ۱۰٪ — فقط برای کاربرانی که ثبت‌نام نکرده‌اند

    ⚠️ TODO (بعداً انجام شود — فعلاً فرم غیرفعال است):
        - روت ثبت‌نام سمت سایت (route('site.register')) + ذخیرهٔ کاربر
        - ساخت/اعطای کد تخفیف ۱۰٪ برای کاربر تازه‌ثبت‌نام‌کرده
        - پیام swal/toast محلی‌شده بعد از ثبت‌نام موفق
        - در صورت لاگین بودن (@auth) نمایش کدهای تخفیف واقعی کاربر
    ============================================================================ --}}
@php
    // آیا شبکه‌ای از تنظیمات فعال است؟ (رندر خودِ دکمه‌ها در کامپوننت <x-social-links />)
    $hasSocials = collect([
        'social_instagram', 'social_telegram', 'social_whatsapp', 'social_twitter', 'social_youtube',
    ])->contains(fn ($key) => setting($key));
@endphp

<section class="reveal-on-scroll py-6 mb-4">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- ستون ۱: شبکه‌های اجتماعی (فقط اگر از تنظیمات پر شده باشند) --}}
        @if($hasSocials)
            <div class="neu-flat-lg relative overflow-hidden flex flex-col justify-center">
                <div aria-hidden="true"
                     style="position: absolute; inset-inline-start: -40px; top: -40px; width: 160px; height: 160px; border-radius: 999px;
                            background: radial-gradient(circle, color-mix(in srgb, var(--accent) 10%, transparent), transparent 70%); pointer-events: none;"></div>
                <div class="relative p-8 md:p-9">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-11 h-11 rounded-2xl grid place-items-center shrink-0"
                             style="background: color-mix(in srgb, var(--accent) 12%, var(--bg-elevated)); color: var(--accent);">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-lg leading-snug">{{ __('messages.join_section_title') }}</h3>
                            <p class="text-xs mt-1" style="color: var(--text-secondary);">{{ __('messages.join_section_desc') }}</p>
                        </div>
                    </div>
                    <x-social-links />
                </div>
            </div>
        @endif

        {{-- ستون ۲: باکس ثبت‌نام + کد تخفیف ۱۰٪ (فقط مهمان‌ها) --}}
        <div class="neu-flat-lg relative overflow-hidden {{ $hasSocials ? '' : 'lg:col-span-2' }}">
            {{-- هالهٔ تزئینی پس‌زمینه --}}
            <div aria-hidden="true"
                 style="position: absolute; inset-inline-end: -50px; top: -50px; width: 180px; height: 180px; border-radius: 999px;
                        background: radial-gradient(circle, color-mix(in srgb, var(--accent) 12%, transparent), transparent 70%); pointer-events: none;"></div>
            <div aria-hidden="true"
                 style="position: absolute; inset-inline-start: -40px; bottom: -40px; width: 140px; height: 140px; border-radius: 999px;
                        background: radial-gradient(circle, color-mix(in srgb, var(--accent) 8%, transparent), transparent 70%); pointer-events: none;"></div>

            <div class="relative p-8 md:p-9">
                @guest
                    {{-- هدر با آیکون و بج تخفیف --}}
                    <div class="flex items-start justify-between mb-5">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-2xl grid place-items-center shrink-0"
                                 style="background: color-mix(in srgb, var(--accent) 12%, var(--bg-elevated)); color: var(--accent);">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-lg leading-snug">{{ __('messages.register_title') }}</h3>
                                <p class="text-xs mt-1" style="color: var(--text-secondary);">{{ __('messages.register_desc') }}</p>
                            </div>
                        </div>
                        <span class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold"
                              style="background: color-mix(in srgb, var(--accent) 14%, transparent); color: var(--accent);">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>
                            ۱۰٪
                        </span>
                    </div>

                    {{-- فرم ثبت‌نام --}}
                    <form action="{{ localizedRoute('login.otp.send') }}" method="POST" data-register-form class="space-y-3">
                        @csrf
                        <div class="flex flex-col sm:flex-row gap-3">
                            {{-- فیلد موبایل — آیکون تلفن در سمت چپ --}}
                            <div class="flex-1 relative">
                                <span class="absolute inset-y-0 left-0 w-10 grid place-items-center pointer-events-none" style="color: var(--text-secondary);">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                </span>
                                <input type="tel" name="mobile" placeholder="{{ __('messages.register_mobile_ph') }}"
                                       class="neu-input py-3 text-sm" style="padding-left:2.5rem; padding-right:0.75rem;" required dir="ltr"
                                       inputmode="tel" autocomplete="tel" maxlength="13">
                            </div>
                            {{-- فیلد نام — آیکون کاربر در سمت راست --}}
                            <div class="flex-1 relative">
                                <span class="absolute inset-y-0 right-0 w-10 grid place-items-center pointer-events-none" style="color: var(--text-secondary);">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                </span>
                                <input type="text" name="name" placeholder="{{ __('messages.register_name_ph') }}"
                                       class="neu-input py-3 text-sm" style="padding-right:2.5rem; padding-left:1rem;" required>
                            </div>
                        </div>
                        <button type="submit" class="neu-btn neu-btn-primary w-full px-6 py-3.5 text-sm font-bold tracking-wide">
                            <svg class="inline-block me-1.5 -mt-0.5" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 12v10H4V12"/><path d="M2 7h20v5H2z"/><path d="M12 22V7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>
                            {{ __('messages.register_btn') }}
                        </button>
                    </form>

                    {{-- متن قوانین --}}
                    <p class="text-[11px] text-center mt-4 leading-relaxed" style="color: var(--text-secondary);">
                        {{ __('messages.register_agree') }}
                    </p>
                @else
                    {{-- TODO: وقتی سیستم کاربری و کدهای تخفیف راه افتاد، کدهای واقعی کاربر اینجا نمایش داده شود --}}
                    <span class="section-icon mb-4" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    </span>
                    <h3 class="font-extrabold text-lg mb-2">{{ __('messages.already_member_title') }}</h3>
                    <p class="text-xs" style="color: var(--text-secondary);">{{ __('messages.already_member_desc') }}</p>
                @endguest
            </div>
        </div>
    </div>
</section>
