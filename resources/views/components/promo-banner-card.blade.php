@props(['banner'])

{{-- اگر لینک ندارد، بنر اصلاً کلیک‌پذیر نیست (div به‌جای a) و فلش/لیفت هاور نمایش داده نمی‌شود --}}
@if($banner->url)
    <a href="{{ $banner->url }}"
       class="promo-banner promo-banner--{{ $banner->style }} promo-banner--{{ $banner->tone }} group">
        <span class="promo-banner-icon" aria-hidden="true">
            <x-promo-banner-icon :icon="$banner->icon"/>
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
@else
    <div class="promo-banner promo-banner--static promo-banner--{{ $banner->style }} promo-banner--{{ $banner->tone }}">
        <span class="promo-banner-icon" aria-hidden="true">
            <x-promo-banner-icon :icon="$banner->icon"/>
        </span>
        <span class="promo-banner-body">
            <span class="promo-banner-title" data-digit-raw>{{ $banner->trans('title') }}</span>
            @if($banner->trans('subtitle'))
                <span class="promo-banner-subtitle">{{ $banner->trans('subtitle') }}</span>
            @endif
        </span>
    </div>
@endif
