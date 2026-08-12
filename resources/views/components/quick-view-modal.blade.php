@props([])

{{--
    مودال سراسری پیش‌نمایش سریع محصول.
    محتوا با fetch از روت products.quick-view گرفته و داخل #quick-view-content ریخته می‌شود
    (منطق در ui-effects.js — بدون Alpine).
--}}
<div id="quick-view-modal" class="quick-view-modal" role="dialog" aria-modal="true"
     aria-label="{{ __('messages.quick_view') }}" aria-hidden="true">
    <div class="quick-view-backdrop" data-quick-view-close></div>

    <div class="quick-view-panel" role="document">
        <button type="button" data-quick-view-close class="quick-view-close" aria-label="{{ __('messages.close') }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/>
            </svg>
        </button>

        <div id="quick-view-content" class="quick-view-content">
            <div class="quick-view-loading" aria-hidden="true">
                <span class="quick-view-spinner"></span>
            </div>
        </div>
    </div>
</div>

<script>
    // متن‌های ترجمه‌شده برای JS مودال (بدون inline منطق)
    window.quickViewErrorText = '{{ __('messages.quick_view_error') }}';
    window.quickViewAddedText = '{{ __('messages.added_short') }}';
</script>
