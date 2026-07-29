@props(['variant' => 'full'])

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 my-8">
    <div class="neu-flat-sm p-4 flex flex-col items-center text-center gap-2">
        <span class="text-2xl">🚚</span>
        <p class="text-xs font-semibold">{{ __('messages.trust_free_shipping') }}</p>
        <p class="text-[11px]" style="color: var(--text-secondary);">{{ __('messages.trust_free_shipping_desc') }}</p>
    </div>
    <div class="neu-flat-sm p-4 flex flex-col items-center text-center gap-2">
        <span class="text-2xl">🛡️</span>
        <p class="text-xs font-semibold">{{ __('messages.trust_authenticity') }}</p>
        <p class="text-[11px]" style="color: var(--text-secondary);">{{ __('messages.trust_authenticity_desc') }}</p>
    </div>
    <div class="neu-flat-sm p-4 flex flex-col items-center text-center gap-2">
        <span class="text-2xl">↩️</span>
        <p class="text-xs font-semibold">{{ __('messages.trust_return') }}</p>
        <p class="text-[11px]" style="color: var(--text-secondary);">{{ __('messages.trust_return_desc') }}</p>
    </div>
    <div class="neu-flat-sm p-4 flex flex-col items-center text-center gap-2">
        <span class="text-2xl">🎧</span>
        <p class="text-xs font-semibold">{{ __('messages.trust_support') }}</p>
        <p class="text-[11px]" style="color: var(--text-secondary);">{{ __('messages.trust_support_desc') }}</p>
    </div>
</div>
