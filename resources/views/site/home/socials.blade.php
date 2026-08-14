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
            <div class="neu-flat-lg p-8 md:p-9 flex flex-col justify-center">
                <span class="section-icon mb-4" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                </span>
                <h3 class="font-extrabold text-lg mb-2">{{ __('messages.join_section_title') }}</h3>
                <p class="text-xs mb-5" style="color: var(--text-secondary);">{{ __('messages.join_section_desc') }}</p>
                <x-social-links />
            </div>
        @endif

        {{-- ستون ۲: باکس ثبت‌نام + کد تخفیف ۱۰٪ (فقط مهمان‌ها) --}}
        <div class="neu-flat-lg p-8 md:p-9 relative overflow-hidden {{ $hasSocials ? '' : 'lg:col-span-2' }}">
            {{-- هالهٔ ملایم اکنت در پس‌زمینه --}}
            <div aria-hidden="true"
                 style="position: absolute; inset-inline-end: -60px; top: -60px; width: 200px; height: 200px; border-radius: 999px;
                        background: radial-gradient(circle, color-mix(in srgb, var(--accent) 14%, transparent), transparent 70%); pointer-events: none;"></div>

            <div class="relative">
                @guest
                    <span class="section-icon mb-4" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.4 8.4 0 0 1-8.5 8.3 8.9 8.9 0 0 1-3.6-.7L3 21l1.9-5.3a8 8 0 0 1-.9-3.7A8.4 8.4 0 0 1 12.5 3.5 8.4 8.4 0 0 1 21 11.5z"/></svg>
                    </span>
                    <h3 class="font-extrabold text-lg mb-2">{{ __('messages.register_title') }}</h3>
                    <p class="text-xs mb-5" style="color: var(--text-secondary);">{{ __('messages.register_desc') }}</p>

                    {{-- TODO: منطق ثبت‌نام + کد تخفیف ۱۰٪ — بعداً کانکت می‌شود
                         (action باید به route('site.register') اشاره کند؛ ذخیرهٔ کاربر + ساخت
                         کد تخفیف ۱۰٪ و پیام swal/toast محلی‌شده در آن پیاده شود) --}}
                    <form action="#" method="POST" data-register-form class="space-y-3">
                        @csrf
                        <div class="flex flex-col sm:flex-row gap-3">
                            <input type="text" name="name" placeholder="{{ __('messages.register_name_ph') }}"
                                   class="neu-input px-4 py-3 text-sm flex-1">
                            <input type="email" name="email" placeholder="{{ __('messages.register_email_ph') }}"
                                   class="neu-input px-4 py-3 text-sm flex-1" required>
                        </div>
                        <button type="submit" class="neu-btn neu-btn-primary w-full px-6 py-3 text-sm font-bold">
                            <svg class="inline-block me-1.5 -mt-0.5" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="8" width="18" height="4" rx="1"/><path d="M12 8v13M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-7M7.5 8a2.5 2.5 0 0 1 0-5C11 3 12 8 12 8s1-5 4.5-5a2.5 2.5 0 0 1 0 5"/></svg>
                            {{ __('messages.register_btn') }}
                        </button>
                    </form>

                    <p class="text-[11px] text-center mt-4" style="color: var(--text-secondary);">
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
