{{-- کامپوننت نشان‌های اعتماد — روی صفحه اول، جزئیات محصول و پرداخت استفاده شود --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <div class="neu-flat-sm p-5 flex flex-col items-center text-center gap-2">
        <span class="text-xl">🚚</span>
        <p class="text-xs font-bold">{{ __('messages.trust_free_shipping') }}</p>
        <p class="text-[11px]" style="color:var(--text-secondary);">{{ __('messages.trust_free_shipping_desc') }}</p>
    </div>
    <div class="neu-flat-sm p-5 flex flex-col items-center text-center gap-2">
        <span class="text-xl">🛡️</span>
        <p class="text-xs font-bold">{{ __('messages.trust_authenticity') }}</p>
        <p class="text-[11px]" style="color:var(--text-secondary);">{{ __('messages.trust_authenticity_desc') }}</p>
    </div>
    <div class="neu-flat-sm p-5 flex flex-col items-center text-center gap-2">
        <span class="text-xl">↩️</span>
        <p class="text-xs font-bold">{{ __('messages.trust_return') }}</p>
        <p class="text-[11px]" style="color:var(--text-secondary);">{{ __('messages.trust_return_desc') }}</p>
    </div>
    <div class="neu-flat-sm p-5 flex flex-col items-center text-center gap-2">
        <span class="text-xl">🎧</span>
        <p class="text-xs font-bold">{{ __('messages.trust_support') }}</p>
        <p class="text-[11px]" style="color:var(--text-secondary);">{{ __('messages.trust_support_desc') }}</p>
    </div>
</div>
