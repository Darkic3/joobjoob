@props(['banner'])

{{-- اگر لینک ندارد، بنر اصلاً کلیک‌پذیر نیست (div به‌جای a) و لیفت هاور نمایش داده نمی‌شود --}}
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
