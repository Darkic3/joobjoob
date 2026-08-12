@props([])

{{--
    دکمهٔ بازگشت به بالا با حلقهٔ پیشرفت اسکرول.
    حلقه (SVG) با متغیر CSS --bt-progress پر می‌شود — مقدارش در ui-effects.js
    از روی موقعیت اسکرول محاسبه و روی همین دکمه ست می‌شود (بدون منطق در ویو).
    موقعیت: پایین-انتهای صفحه، بالای نوار موبایل؛ در دسکتاپ پایین گوشه.
--}}
<button type="button" id="back-to-top" class="back-to-top" aria-label="{{ __('messages.back_to_top') }}">
    <svg class="back-to-top-ring" width="46" height="46" viewBox="0 0 46 46" aria-hidden="true">
        <circle class="back-to-top-track" cx="23" cy="23" r="20"/>
        <circle class="back-to-top-progress" cx="23" cy="23" r="20"/>
    </svg>
    <svg class="back-to-top-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="m4.5 15.75 7.5-7.5 7.5 7.5"/>
    </svg>
</button>
