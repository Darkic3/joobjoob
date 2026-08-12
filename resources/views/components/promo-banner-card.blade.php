@props(['banner'])

<a href="{{ $banner->url ?: localizedRoute('products.index') }}"
   class="promo-banner promo-banner--{{ $banner->style }} promo-banner--{{ $banner->tone }} group">
    <span class="promo-banner-icon" aria-hidden="true">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v3a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-3a3 3 0 0 1 0-6V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/><path d="M13 5v2M13 17v2M13 11v2"/></svg>
    </span>
    <span class="promo-banner-body">
        {{-- data-digit-raw: کدهای تخفیف مثل WELCOME10 نباید ارقامش فارسی شود --}}
        <span class="promo-banner-title" data-digit-raw>{{ $banner->trans('title') }}</span>
        @if($banner->trans('subtitle'))
            <span class="promo-banner-subtitle">{{ $banner->trans('subtitle') }}</span>
        @endif
    </span>
    <span class="promo-banner-arrow" aria-hidden="true">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
    </span>
</a>
