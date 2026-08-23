@extends('layouts.site.app')

@section('title', $product->trans('name'))

{{-- داده‌های نمایشی (mainImage/specGroups/textValues/mainSpecs/variantsData/minVariant) در کنترلر آماده می‌شود --}}
@section('content')

    {{-- Breadcrumb --}}
    <p class="text-xs mb-5" style="color: var(--text-secondary);">
        <a href="{{ localizedRoute('home') }}" class="hover:text-[var(--accent)] transition-colors">{{ __('messages.home') }}</a>
        <span class="mx-1">/</span>
        <a href="{{ localizedRoute('products.index') }}"
           class="hover:text-[var(--accent)] transition-colors">{{ __('messages.products') }}</a>
        @if($product->category)
            <span class="mx-1">/</span>
            <span style="color: var(--text-primary);">{{ $product->category->trans('name') }}</span>
        @endif
    </p>

    <div class="grid lg:grid-cols-2 gap-10 mb-14">

        {{-- ۱. گالری تصاویر --}}
        <div>
            <div class="neu-flat-lg p-4 mb-4">
                <div class="image-zoom-wrap" id="image-zoom-wrap">
                    <img id="main-image" src="{{ $mainImage }}"
                         alt="{{ $product->trans('name') }}"
                         class="w-full rounded-xl object-cover transition-all duration-300"
                         style="aspect-ratio:1/1;">
                    <div class="zoom-lens" id="zoom-lens"></div>
                </div>
            </div>

            @if($product->images->count() > 1)
                <div class="flex gap-3 overflow-x-auto pb-2 custom-scrollbar" data-gallery-thumbs>
                    @foreach($product->images as $img)
                        <button onclick="document.getElementById('main-image').src='{{ $img->url }}'"
                                class="neu-flat-sm p-1 w-20 h-20 flex-shrink-0 overflow-hidden hover:neu-pressed transition-all">
                            <img src="{{ $img->url }}" class="w-full h-full object-cover rounded-lg" alt="thumbnail">
                        </button>
                    @endforeach
                </div>
            @endif

            {{-- لایت‌باکس تمام‌صفحه --}}
            <div class="lightbox" id="product-lightbox">
                <button type="button" class="lightbox__close" onclick="closeLightbox()" aria-label="بستن">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
                <button type="button" class="lightbox__arrow lightbox__arrow--prev" onclick="lightboxNav(-1)" aria-label="قبلی">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                </button>
                <div class="lightbox__img-wrap">
                    <img class="lightbox__img" id="lightbox-img" src="" alt="">
                </div>
                <button type="button" class="lightbox__arrow lightbox__arrow--next" onclick="lightboxNav(1)" aria-label="بعدی">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
                <div class="lightbox__counter" id="lightbox-counter"></div>
            </div>
        </div>

        {{-- ۲. اطلاعات محصول --}}
        <div class="flex flex-col">
            @php
                $allImages = collect([$mainImage]);
                foreach ($product->images as $img) {
                    if ($img->url !== $mainImage) $allImages->push($img->url);
                }
            @endphp
            <h1 class="text-2xl font-extrabold mb-3 leading-tight">{{ $product->trans('name') }}</h1>

            {{-- برند + امتیاز --}}
            <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
                @if($product->brand)
                    <a href="{{ localizedRoute('products.index', ['brand' => $product->brand->url]) }}"
                       class="inline-flex items-center gap-2 neu-flat-sm px-4 py-2 rounded-xl text-sm font-bold hover:text-[var(--accent)] transition-colors">
                        @if($product->brand->image)
                            <img
                                src="{{ asset(config('paths.upload', 'uploads') . '/brands/' . $product->brand->image) }}"
                                class="w-6 h-6 object-contain" alt="{{ $product->brand->trans('title') }}">
                        @endif
                        {{ $product->brand->trans('title') }}
                    </a>
                @endif
                <div class="flex items-center gap-2">
                    <span class="text-[var(--gold)] tracking-wider">
                        {{ str_repeat('★', (int) round($product->average_rating)) }}{{ str_repeat('☆', 5 - (int) round($product->average_rating)) }}
                    </span>
                    <span class="text-sm" style="color: var(--text-secondary);">
                        ({{ $product->reviews_count }} {{ __('messages.reviews') }})
                    </span>
                </div>
            </div>

            {{-- دکمه‌های اشتراک‌گذاری --}}
            <div class="flex items-center gap-3 mb-6">
                <span class="text-sm font-semibold" style="color: var(--text-secondary);">{{ __('messages.share') }}:</span>
                <x-share-buttons :url="url()->current()" :title="$product->trans('name')" />
            </div>

            {{-- انتخابگر واریانت (رنگ/سایز) --}}
            <div id="variant-selector" class="space-y-5 mb-6">
                @foreach($mainSpecs as $group)
                    <div data-spec-group="{{ $group['id'] }}">
                        <label class="text-sm font-semibold block mb-3">
                            {{ $group['title'] }}
                            <span class="text-xs font-normal" style="color: var(--text-secondary);"
                                  data-selected-label></span>
                        </label>
                        <div class="flex flex-wrap gap-2.5">
                            @foreach($group['children'] as $child)
                                @if($group['is_color'] && $child['color_code'])
                                    <button type="button"
                                            class="variant-option color-option w-10 h-10 rounded-full shadow-sm transition-all duration-200"
                                            style="background-color: {{ $child['color_code'] }};"
                                            data-spec-group="{{ $group['id'] }}"
                                            data-spec-id="{{ $child['id'] }}"
                                            data-variant-ids='{{ json_encode($child['variant_ids']) }}'
                                            title="{{ $child['title'] }}">
                                        <span class="opacity-0 text-white text-sm font-bold">✓</span>
                                    </button>
                                @else
                                    <button type="button"
                                            class="variant-option neu-flat-sm px-4 py-2 rounded-xl text-sm font-semibold transition-all"
                                            data-spec-group="{{ $group['id'] }}"
                                            data-spec-id="{{ $child['id'] }}"
                                            data-variant-ids='{{ json_encode($child['variant_ids']) }}'>
                                        {{ $child['title'] }}
                                    </button>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- قیمت — سه حالت (موجود / تماس بگیرید / ناموجود) --}}
            <div class="mb-6" id="price-block">
                @if($product->has_variants)
                    {{-- قیمت از ارزان‌ترین واریانت --}}
                    @if($minVariant)
                        <div class="flex items-center gap-3" data-price-view>
                            <span class="text-3xl font-extrabold" style="color: var(--accent);" dir="ltr"
                                  id="display-final-price">
                                {{ number_format($minVariant->final_price) }}
                            </span>
                            @if($minVariant->discounted_price > 0 && $minVariant->discounted_price < $minVariant->price)
                                <span class="text-lg line-through" style="color: var(--text-secondary);" dir="ltr"
                                      id="display-old-price">
                                    {{ number_format($minVariant->price) }}
                                </span>
                                <span class="text-xs font-bold bg-red-500 text-white px-2 py-1 rounded-full"
                                      id="display-percent">
                                    {{ $minVariant->percent }}٪-
                                </span>
                            @endif
                        </div>
                    @else
                        <div class="text-3xl font-extrabold" style="color: var(--accent);" dir="ltr">
                            {{ number_format($product->final_price) }}
                        </div>
                    @endif
                @else
                    <div class="flex items-center gap-3">
                        @if($product->has_discount)
                            <span class="text-3xl font-extrabold" style="color: var(--accent);" dir="ltr">
                                {{ number_format($product->final_price) }}
                            </span>
                            <span class="text-lg line-through" style="color: var(--text-secondary);" dir="ltr">
                                {{ number_format($product->price) }}
                            </span>
                            <span class="text-xs font-bold bg-red-500 text-white px-2 py-1 rounded-full">
                                {{ $product->discount_percentage }}٪-
                            </span>
                        @else
                            <span class="text-3xl font-extrabold" style="color: var(--accent);" dir="ltr">
                                {{ number_format($product->final_price) }}
                            </span>
                        @endif
                    </div>
                @endif
                <span class="text-sm font-semibold" style="color: var(--text-secondary);">تومان</span>
            </div>

            {{-- توضیحات --}}
            <p class="mb-8 leading-relaxed text-sm" style="color: var(--text-secondary);">
                {{ $product->trans('short_description')  ?: __('messages.no_description') }}
            </p>
            {{--            ?: $product->trans('description')--}}
            {{-- فرم افزودن به سبد خرید --}}
            <form action="{{ route('cart.add', $product->id) }}" method="POST" class="space-y-6 mt-auto">
                @csrf
                <input type="hidden" name="variant_id" id="selected-variant-id" value="{{ $minVariant?->id ?? '' }}">

                {{-- تعداد و دکمه خرید --}}
                <div class="flex items-center gap-4 pt-4 border-t border-[var(--shadow-light)]">
                    <div class="neu-flat-sm flex items-center rounded-lg overflow-hidden">
                        <button type="button" onclick="decrementQty()"
                                class="w-10 h-10 flex items-center justify-center text-lg font-bold hover:bg-[var(--shadow-light)] transition-colors">
                            -
                        </button>
                        <input type="number" id="qty-input" name="qty" value="1" min="1" max="{{ $product->stock }}"
                               class="w-16 h-10 text-center bg-transparent border-none focus:ring-0 text-sm font-bold"
                               readonly>
                        <button type="button" onclick="incrementQty({{ max(1, $product->stock) }})"
                                class="w-10 h-10 flex items-center justify-center text-lg font-bold hover:bg-[var(--shadow-light)] transition-colors">
                            +
                        </button>
                    </div>

                    <button type="submit" id="add-to-cart-btn"
                            class="neu-btn neu-btn-primary flex-1 py-3 font-bold text-sm flex items-center justify-center gap-2"
                        @disabled($product->stock === 0)>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        {{ __('messages.add_to_cart') }}
                    </button>
                </div>

                {{-- وضعیت موجودی --}}
                <p class="text-sm font-semibold flex items-center gap-2" id="stock-status"
                   style="color: {{ $product->stock > 0 ? 'var(--success)' : 'var(--danger)' }}">
                    <span class="w-2 h-2 rounded-full"
                          style="background-color: {{ $product->stock > 0 ? 'var(--success)' : 'var(--danger)' }}"></span>
                    <span
                        id="stock-text">{{ $product->stock > 0 ? __('messages.in_stock') . " ({$product->stock} عدد)" : __('messages.out_of_stock') }}</span>
                </p>

                {{-- نشان‌های اعتماد --}}
                @if($trustBadges->isNotEmpty())
                    <div class="flex flex-wrap gap-2 pt-4 border-t border-[var(--shadow-light)]">
                        @foreach($trustBadges as $badge)
                            <div class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1.5 rounded-lg"
                                 style="background: color-mix(in srgb, var(--bg-elevated) 80%, transparent); color: var(--text-secondary); box-shadow: inset 1px 1px 3px var(--shadow-dark), inset -1px -1px 3px var(--shadow-light);">
                                <span class="text-sm" aria-hidden="true">{{ $badge->icon }}</span>
                                <span>{{ $badge->trans('title') }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- ۳. تب‌ها: توضیحات / مشخصات / نظرات --}}
    <section class="mb-14 reveal-on-scroll">
        <div class="flex flex-wrap gap-2 mb-6" id="detail-tabs">
            <button type="button" class="detail-tab neu-flat-sm px-5 py-2.5 rounded-xl text-sm font-bold active"
                    data-tab="description">{{ __('messages.description') }}</button>
            @if(count($specGroups) > 0 || count($textValues) > 0)
                <button type="button" class="detail-tab neu-flat-sm px-5 py-2.5 rounded-xl text-sm font-bold"
                        data-tab="specs">{{ __('messages.specifications') }}
                </button>
            @endif
            <button type="button" class="detail-tab neu-flat-sm px-5 py-2.5 rounded-xl text-sm font-bold"
                    data-tab="reviews">{{ __('messages.reviews') }}</button>
        </div>

        {{-- توضیحات --}}
        <div class="detail-tab-content" id="tab-description">
            <div class="neu-card p-6 leading-relaxed text-sm" style="color: var(--text-secondary);">
                {!! $product->trans('description') ?: '<p>توضیحاتی برای این محصول ثبت نشده است.</p>' !!}
            </div>
        </div>

        {{-- مشخصات فنی --}}
        @if(count($specGroups) > 0 || count($textValues) > 0)
            <div class="detail-tab-content hidden" id="tab-specs">
                <div class="neu-card p-6">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-[var(--shadow-light)]">
                        @foreach($specGroups as $group)
                            <tr>
                                <td class="py-3 pe-6 font-bold w-40">{{ $group['title'] }}</td>
                                <td class="py-3" style="color: var(--text-secondary);">{{ $group['values'] }}</td>
                            </tr>
                        @endforeach
                        @foreach($textValues as $tv)
                            <tr>
                                <td class="py-3 pe-6 font-bold w-40">{{ $tv['title'] }}</td>
                                <td class="py-3" style="color: var(--text-secondary);">{{ $tv['value'] }}</td>
                            </tr>
                        @endforeach
                        @foreach($product->properties as $prop)
                            <tr>
                                <td class="py-3 pe-6 font-bold w-40">{{ $prop->trans('title') }}</td>
                                <td class="py-3" style="color: var(--text-secondary);">{{ $prop->value }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- نظرات --}}
        <div class="detail-tab-content hidden" id="tab-reviews">

            {{-- ─── فرم ثبت نظر (همه کاربران) ─── --}}
            <div class="review-form-glass mb-8">
                <div class="review-form-glass__inner">
                    {{-- هدر فرم --}}
                    <div class="review-form-header">
                        <div class="review-form-header__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </div>
                        <div>
                            <h3 class="review-form-header__title">{{ __('messages.review_write') }}</h3>
                            <p class="review-form-header__sub">{{ __('messages.review_your_review') }}</p>
                        </div>
                    </div>

                    <form action="{{ route('products.reviews.store', $product->slug) }}" method="POST" class="review-form">
                        @csrf

                        {{-- ─── فیلدهای مهمان (فقط بدون لاگین) ─── --}}
                        @guest
                            <div class="review-guest-fields">
                                <div class="review-guest-fields__row">
                                    <div class="review-input-group">
                                        <label class="review-input-group__label" for="guest-name">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="review-input-group__icon"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                            {{ __('messages.review_guest_name') }}
                                        </label>
                                        <input type="text" id="guest-name" name="guest_name" maxlength="100"
                                               placeholder="{{ __('messages.review_guest_name_ph') }}"
                                               class="review-input-group__input"
                                               value="{{ old('guest_name') }}" required>
                                        @error('guest_name')<p class="review-form__error">{{ $message }}</p>@enderror
                                    </div>
                                    <div class="review-input-group">
                                        <label class="review-input-group__label" for="guest-phone">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="review-input-group__icon"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                                            {{ __('messages.review_guest_phone') }}
                                        </label>
                                        <input type="tel" id="guest-phone" name="guest_phone" maxlength="20"
                                               placeholder="{{ __('messages.review_guest_phone_ph') }}"
                                               class="review-input-group__input"
                                               dir="ltr" inputmode="numeric"
                                               value="{{ old('guest_phone') }}" required>
                                        @error('guest_phone')<p class="review-form__error">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                            </div>
                        @endguest

                        {{-- امتیاز ستاره‌ای --}}
                        <div class="review-form__rating">
                            <div class="star-rating" id="star-rating" role="radiogroup" aria-label="امتیاز">
                                @for($i = 1; $i <= 5; $i++)
                                    <button type="button"
                                            class="star-rating__star"
                                            data-value="{{ $i }}"
                                            onclick="setRating({{ $i }})"
                                            onmouseenter="previewRating({{ $i }})"
                                            onmouseleave="resetRatingPreview()"
                                            aria-label="{{ $i }} از ۵">
                                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                    </button>
                                @endfor
                                <input type="hidden" name="rating" id="rating-input" value="" required>
                            </div>
                            <span class="star-rating__label" id="rating-label"></span>
                        </div>
                        @error('rating')
                            <p class="review-form__error">{{ $message }}</p>
                        @enderror

                        {{-- متن نظر --}}
                        <div class="review-form__body">
                            <textarea
                                id="review-comment"
                                name="comment"
                                rows="4"
                                maxlength="1000"
                                placeholder="{{ __('messages.review_placeholder') }}"
                                class="review-form__textarea"
                                required
                            >{{ old('comment') }}</textarea>
                            <div class="review-form__footer">
                                <span class="review-form__char-count"><span id="char-count">0</span> / 1000</span>
                            </div>
                            @error('comment')
                                <p class="review-form__error">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- دکمه ارسال --}}
                        <button type="submit" class="review-submit-btn" id="review-submit-btn" disabled>
                            <span class="review-submit-btn__chip">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                            </span>
                            <span>{{ __('messages.review_submit') }}</span>
                        </button>
                    </form>
                </div>
            </div>

            {{-- ─── خلاصه امتیازات ─── --}}
            @if($product->reviews->count() > 0)
                <div class="review-summary-glass mb-8">
                    <div class="review-summary-glass__left">
                        <div class="review-summary-glass__score">
                            <span class="review-summary-glass__num">{{ number_format($product->reviews->avg('rating'), 1) }}</span>
                            <div class="review-summary-glass__stars">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg viewBox="0 0 24 24" fill="currentColor" class="review-summary-glass__star" style="color: {{ $i <= round($product->reviews->avg('rating')) ? 'var(--gold)' : 'var(--shadow-dark)' }};"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                @endfor
                            </div>
                            <span class="review-summary-glass__count">{{ $product->reviews->count() }} {{ __('messages.reviews') }}</span>
                        </div>
                    </div>
                    <div class="review-summary-glass__bars">
                        @for($i = 5; $i >= 1; $i--)
                            @php $count = $product->reviews->where('rating', $i)->count(); $pct = $product->reviews->count() > 0 ? ($count / $product->reviews->count() * 100) : 0; @endphp
                            <div class="review-summary-bar">
                                <span class="review-summary-bar__num">{{ $i }}</span>
                                <div class="review-summary-bar__track">
                                    <div class="review-summary-bar__fill" style="width: {{ $pct }}%;"></div>
                                </div>
                                <span class="review-summary-bar__count">{{ $count }}</span>
                            </div>
                        @endfor
                    </div>
                </div>
            @endif

            {{-- ─── لیست نظرات (درختی تو در تو) --}}
            <div class="review-list">
                @forelse($reviewTree as $review)
                    @include('site.products.partials.review-item', ['review' => $review, 'depth' => 0])
                @empty
                    <div class="review-empty">
                        <div class="review-empty__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 11.5a8.4 8.4 0 0 1-8.5 8.3 8.9 8.9 0 0 1-3.6-.7L3 21l1.9-5.3a8 8 0 0 1-.9-3.7A8.4 8.4 0 0 1 12.5 3.5 8.4 8.4 0 0 1 21 11.5z"/>
                            </svg>
                        </div>
                        <p class="review-empty__title">{{ __('messages.review_empty_title') }}</p>
                        <p class="review-empty__desc">{{ __('messages.review_empty_desc') }}</p>
                    </div>
                @endforelse
            </div>

        </div>
    </section>

    {{-- مودال پاسخ به نظر (خارج از سکشن برای position: fixed) --}}
    <div class="reply-modal" id="reply-modal">
        <div class="reply-modal__backdrop" onclick="closeReplyModal()"></div>
        <div class="reply-modal__content">
            <div class="reply-modal__header">
                <div class="reply-modal__title-row">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20" class="reply-modal__icon"><polyline points="9 14 4 9 9 4"/><path d="M20 20v-7a4 4 0 0 0-4-4H4"/></svg>
                    <h3 class="reply-modal__title">{{ __('messages.reply_to') }} <span id="reply-to-name"></span></h3>
                </div>
                <button type="button" class="reply-modal__close" onclick="closeReplyModal()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <form id="reply-form" method="POST" class="reply-modal__form">
                @csrf
                {{-- فیلدهای مهمان --}}
                @guest
                    <div class="review-guest-fields">
                        <div class="review-guest-fields__row">
                            <div class="review-input-group">
                                <label class="review-input-group__label">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="review-input-group__icon"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    {{ __('messages.review_guest_name') }}
                                </label>
                                <input type="text" name="guest_name" maxlength="100" placeholder="{{ __('messages.review_guest_name_ph') }}" class="review-input-group__input" required>
                            </div>
                            <div class="review-input-group">
                                <label class="review-input-group__label">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="review-input-group__icon"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                                    {{ __('messages.review_guest_phone') }}
                                </label>
                                <input type="tel" name="guest_phone" maxlength="20" placeholder="{{ __('messages.review_guest_phone_ph') }}" class="review-input-group__input" dir="ltr" inputmode="numeric" required>
                            </div>
                        </div>
                    </div>
                @endguest
                <div class="reply-modal__body">
                    <textarea name="comment" rows="3" maxlength="500" placeholder="{{ __('messages.reply_placeholder') }}" class="review-form__textarea reply-modal__textarea" required></textarea>
                    <div class="review-form__footer">
                        <span class="review-form__char-count"><span class="reply-char-count">0</span> / 500</span>
                    </div>
                </div>
                <button type="submit" class="review-submit-btn">
                    <span class="review-submit-btn__chip">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </span>
                    <span>{{ __('messages.reply_submit') }}</span>
                </button>
            </form>
        </div>
    </div>

    {{-- ۴. محصولات مکمل --}}
    @if($complement->isNotEmpty())
        <section class="mb-14 reveal-on-scroll">
            <h2 class="text-xl font-extrabold mb-6">پیشنهاد مکمل</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                @foreach($complement as $item)
                    <x-product-card :product="$item"/>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ۵. محصولات مرتبط --}}
    @if($related->isNotEmpty())
        <section class="mb-14 reveal-on-scroll">
            <h2 class="text-xl font-extrabold mb-6">{{ __('messages.related_products') }}</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                @foreach($related as $item)
                    <x-product-card :product="$item"/>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ۶. اسکیمای JSON-LD --}}
    <script type="application/ld+json">
        {
            "@context": "https://schema.org/",
            "@type": "Product",
            "name": "{{ $product->trans('name') }}",
        "image": "{{ $mainImage }}",
        @if($product->brand)
            "brand": { "@type": "Brand", "name": "{{ $product->brand->trans('title') }}" },
        @endif
        "offers": {
            "@type": "Offer",
            "url": "{{ url()->current() }}",
            "priceCurrency": "IRR",
            "price": "{{ $product->final_price * 10 }}",
            "availability": "{{ $product->stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' }}"
        }
    }
    </script>
@endsection

@push('scripts')
    <script>
        // ===== داده‌های واریانت از بک‌اند =====
        const PRODUCT_VARIANTS = @json($variantsData);
        const PRODUCT_MAIN_SPECS = @json($mainSpecs);

        // قالب‌بندی قیمت بر اساس زبان سایت (fa/ar → ارقام فارسی، سایر → انگلیسی)
        function formatPrice(n) {
            const useFa = window.localizeDigits && window.localizeDigits.isPersian();
            return new Intl.NumberFormat(useFa ? 'fa-IR' : 'en-US').format(n);
        }

        // ===== انتخابگر واریانت =====
        const selections = {}; // groupId -> specId
        let selectedVariant = null;

        function findVariant() {
            const ids = Object.values(selections);
            if (ids.length !== PRODUCT_MAIN_SPECS.length) return null;

            const sorted = [...ids].sort((a, b) => a - b);
            return PRODUCT_VARIANTS.find(v =>
                v.spec_ids.length === sorted.length &&
                sorted.every(id => v.spec_ids.includes(id))
            ) || null;
        }

        function updateVariantUI() {
            // به‌روزرسانی استایل انتخاب‌ها
            document.querySelectorAll('.variant-option').forEach(btn => {
                const g = btn.dataset.specGroup;
                const selected = selections[g] === parseInt(btn.dataset.specId);

                if (btn.classList.contains('color-option')) {
                    btn.classList.toggle('ring-2', selected);
                    btn.classList.toggle('ring-offset-2', selected);
                    btn.classList.toggle('scale-110', selected);
                    const check = btn.querySelector('span');
                    if (check) check.style.opacity = selected ? '1' : '0';
                } else {
                    btn.classList.toggle('active-variant', selected);
                }
            });

            // برچسب انتخاب‌شده
            document.querySelectorAll('[data-spec-group]').forEach(groupEl => {
                const g = groupEl.dataset.specGroup;
                const label = groupEl.querySelector('[data-selected-label]');
                if (label) {
                    const specId = selections[g];
                    const btn = groupEl.querySelector(`[data-spec-id="${specId}"]`);
                    label.textContent = btn ? `: ${btn.title || btn.textContent.trim()}` : '';
                }
            });

            selectedVariant = findVariant();

            // فعال/غیرفعال کردن گزینه‌های ناسازگار
            document.querySelectorAll('.variant-option').forEach(btn => {
                const g = btn.dataset.specGroup;
                const specId = parseInt(btn.dataset.specId);
                const variantIds = JSON.parse(btn.dataset.variantIds || '[]');

                // اگر برای گروه‌های دیگر انتخابی داریم، سازگاری را چک کن
                let compatible = true;
                for (const [otherGroup, otherSpec] of Object.entries(selections)) {
                    if (parseInt(otherGroup) === parseInt(g)) continue;
                    // واریانت‌هایی که هم این مقدار و هم انتخاب فعلی را دارند
                    const has = PRODUCT_VARIANTS.some(v =>
                        v.spec_ids.includes(specId) && v.spec_ids.includes(otherSpec)
                    );
                    if (!has) {
                        compatible = false;
                        break;
                    }
                }
                btn.classList.toggle('variant-disabled', !compatible);
            });

            // قیمت و موجودی
            if (selectedVariant) {
                document.getElementById('selected-variant-id').value = selectedVariant.id;
                const priceEl = document.getElementById('display-final-price');
                const oldEl = document.getElementById('display-old-price');
                const percentEl = document.getElementById('display-percent');
                if (priceEl) priceEl.textContent = formatPrice(selectedVariant.final_price);
                if (oldEl) {
                    if (selectedVariant.discount_price > 0) {
                        oldEl.textContent = formatPrice(selectedVariant.price);
                        oldEl.classList.remove('hidden');
                    } else {
                        oldEl.classList.add('hidden');
                    }
                }
                if (percentEl) {
                    if (selectedVariant.percent) {
                        percentEl.textContent = selectedVariant.percent + '٪-';
                        percentEl.classList.remove('hidden');
                    } else {
                        percentEl.classList.add('hidden');
                    }
                }

                const btn = document.getElementById('add-to-cart-btn');
                const status = document.getElementById('stock-status');
                const stockText = document.getElementById('stock-text');
                const qtyInput = document.getElementById('qty-input');

                if (selectedVariant.stock > 0) {
                    btn.disabled = false;
                    status.style.color = 'var(--success)';
                    stockText.textContent = '{{ __("messages.in_stock") }} (' + selectedVariant.stock + ' عدد)';
                    qtyInput.max = selectedVariant.stock;
                    if (parseInt(qtyInput.value) > selectedVariant.stock) qtyInput.value = selectedVariant.stock;
                } else {
                    btn.disabled = true;
                    status.style.color = 'var(--danger)';
                    stockText.textContent = '{{ __("messages.out_of_stock") }}';
                }
            }
        }

        document.querySelectorAll('.variant-option').forEach(btn => {
            btn.addEventListener('click', () => {
                const g = btn.dataset.specGroup;
                const specId = parseInt(btn.dataset.specId);
                if (selections[g] === specId) {
                    delete selections[g]; // دوباره کلیک = لغو انتخاب
                } else {
                    selections[g] = specId;
                }
                updateVariantUI();
            });
        });

        // انتخاب خودکار اولین واریانت از هر گروه
        document.addEventListener('DOMContentLoaded', () => {
            PRODUCT_MAIN_SPECS.forEach(group => {
                if (group.children.length > 0) {
                    selections[group.id] = group.children[0].id;
                }
            });
            if (Object.keys(selections).length > 0) updateVariantUI();
        });

        // ===== تعداد =====
        function incrementQty(max) {
            const input = document.getElementById('qty-input');
            const val = parseInt(input.value);
            const m = selectedVariant ? selectedVariant.stock : max;
            if (val < m) input.value = val + 1;
        }

        function decrementQty() {
            const input = document.getElementById('qty-input');
            const val = parseInt(input.value);
            if (val > 1) input.value = val - 1;
        }

        // ===== تب‌ها =====
        document.querySelectorAll('.detail-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.detail-tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.detail-tab-content').forEach(c => c.classList.add('hidden'));
                tab.classList.add('active');
                document.getElementById('tab-' + tab.dataset.tab).classList.remove('hidden');
            });
        });

        // ===== ذره‌بین تصویر اصلی =====
        (function() {
            var wrap = document.getElementById('image-zoom-wrap');
            var lens = document.getElementById('zoom-lens');
            if (!wrap || !lens) return;

            var img = document.getElementById('main-image');
            var zoom = parseFloat(getComputedStyle(lens).getPropertyValue('--zoom')) || 2.5;

            function updateLens(e) {
                var rect = wrap.getBoundingClientRect();
                var x = e.clientX - rect.left;
                var y = e.clientY - rect.top;

                // Clamp
                x = Math.max(0, Math.min(x, rect.width));
                y = Math.max(0, Math.min(y, rect.height));

                // Position lens
                lens.style.left = x + 'px';
                lens.style.top  = y + 'px';

                // Background: the actual image at zoom level
                var bgW = rect.width * zoom;
                var bgH = rect.height * zoom;
                var bgX = -(x * zoom - lens.offsetWidth / 2);
                var bgY = -(y * zoom - lens.offsetHeight / 2);

                lens.style.backgroundImage  = 'url("' + img.src + '")';
                lens.style.backgroundSize    = bgW + 'px ' + bgH + 'px';
                lens.style.backgroundPosition = bgX + 'px ' + bgY + 'px';
            }

            wrap.addEventListener('mousemove', updateLens);
            wrap.addEventListener('touchmove', function(e) {
                e.preventDefault();
                updateLens(e.touches[0]);
            }, { passive: false });

            // Reset on thumbnail change
            var observer = new MutationObserver(function() {
                lens.style.backgroundImage = '';
            });
            observer.observe(img, { attributes: true, attributeFilter: ['src'] });
        })();

        // ===== لایت‌باکس تمام‌صفحه =====
        (function() {
            var LIGHTBOX_IMAGES = @json($allImages);
            var currentIndex = 0;
            var lb = document.getElementById('product-lightbox');
            var lbImg = document.getElementById('lightbox-img');
            var lbCounter = document.getElementById('lightbox-counter');
            var wrap = document.getElementById('image-zoom-wrap');

            function showImage(i) {
                currentIndex = (i + LIGHTBOX_IMAGES.length) % LIGHTBOX_IMAGES.length;
                lbImg.style.opacity = '0';
                lbImg.style.transform = 'scale(0.95)';
                setTimeout(function() {
                    lbImg.src = LIGHTBOX_IMAGES[currentIndex];
                    lbImg.alt = 'تصویر ' + (currentIndex + 1);
                    lbCounter.textContent = (currentIndex + 1) + ' / ' + LIGHTBOX_IMAGES.length;
                    lbImg.style.opacity = '1';
                    lbImg.style.transform = 'scale(1)';
                }, 150);
            }

            window.openLightbox = function(startIndex) {
                currentIndex = startIndex || 0;
                lb.classList.add('is-active');
                document.body.style.overflow = 'hidden';
                showImage(currentIndex);
            };

            window.closeLightbox = function() {
                lb.classList.remove('is-active');
                document.body.style.overflow = '';
            };

            window.lightboxNav = function(dir) {
                showImage(currentIndex + dir);
            };

            // کلیک روی تصویر اصلی → باز شدن لایت‌باکس
            if (wrap) {
                wrap.addEventListener('click', function(e) {
                    // فقط کلیک مستقیم روی تصویر یا خود wrap → لایت‌باکس
                    if (e.target !== wrap && e.target !== document.getElementById('main-image')) return;
                    var curSrc = document.getElementById('main-image').src;
                    var idx = LIGHTBOX_IMAGES.findIndex(function(u) { return curSrc.includes(u) || u.includes(curSrc); });
                    openLightbox(idx >= 0 ? idx : 0);
                });
            }

            // کلیک روی thumbnail → باز شدن لایت‌باکس از همون عکس
            // فقط thumbnailهای گالری تصویر
            var thumbContainer = document.querySelector('[data-gallery-thumbs]');
            if (thumbContainer) {
                thumbContainer.querySelectorAll('button[onclick]').forEach(function(btn, i) {
                    btn.addEventListener('click', function(e) {
                        // اجازه تغییر عکس اصلی بده، بعد لایت‌باکس رو باز کن
                        setTimeout(function() { openLightbox(i); }, 50);
                    });
                });
            }

            // کلیک روی پس‌زمینه تاریک → بسته شدن
            lb.addEventListener('click', function(e) {
                if (e.target === lb || e.target.classList.contains('lightbox__img-wrap')) {
                    closeLightbox();
                }
            });

            // کلیدهای جهت‌دار + Escape
            document.addEventListener('keydown', function(e) {
                if (!lb.classList.contains('is-active')) return;
                if (e.key === 'Escape') closeLightbox();
                if (e.key === 'ArrowRight') lightboxNav(-1); // RTL: راست = قبلی
                if (e.key === 'ArrowLeft') lightboxNav(1);   // RTL: چپ = بعدی
            });

            // Swipe لمسی
            var touchStartX = 0;
            var touchStartY = 0;
            lb.addEventListener('touchstart', function(e) {
                touchStartX = e.touches[0].clientX;
                touchStartY = e.touches[0].clientY;
            }, { passive: true });
            lb.addEventListener('touchend', function(e) {
                var dx = e.changedTouches[0].clientX - touchStartX;
                var dy = e.changedTouches[0].clientY - touchStartY;
                if (Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy)) {
                    lightboxNav(dx > 0 ? -1 : 1); // RTL
                }
            }, { passive: true });
        })();

        // ===== فرم ثبت نظر: ستاره امتیاز + شمارنده حروف =====
        (function() {
            var input = document.getElementById('rating-input');
            var textarea = document.getElementById('review-comment');
            var charCount = document.getElementById('char-count');
            var submitBtn = document.getElementById('review-submit-btn');
            var label = document.getElementById('rating-label');
            var stars = document.querySelectorAll('.star-rating__star');
            if (!input || !stars.length) return;

            var currentRating = 0;
            var ratingLabels = ['','ضعیف','متوسط','خوب','عالی','فوق‌العاده'];

            window.setRating = function(val) {
                currentRating = val;
                input.value = val;
                syncStars();
                if (label) label.textContent = ratingLabels[val] || '';
                checkValid();
            };

            window.previewRating = function(val) {
                stars.forEach(function(s) {
                    var v = parseInt(s.dataset.value);
                    s.classList.toggle('is-preview', v <= val && v > currentRating);
                });
                if (label && val > currentRating) label.textContent = ratingLabels[val] || '';
            };

            window.resetRatingPreview = function() {
                stars.forEach(function(s) { s.classList.remove('is-preview'); });
                if (label) label.textContent = currentRating > 0 ? ratingLabels[currentRating] : '';
            };

            function syncStars() {
                stars.forEach(function(s) {
                    s.classList.toggle('is-active', parseInt(s.dataset.value) <= currentRating);
                });
            }

            function checkValid() {
                if (!submitBtn) return;
                var ok = currentRating > 0 && textarea && textarea.value.trim().length >= 3;
                submitBtn.disabled = !ok;
            }

            if (textarea) {
                textarea.addEventListener('input', function() {
                    if (charCount) charCount.textContent = textarea.value.length;
                    checkValid();
                });
            }

            if (textarea) {
                textarea.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) e.stopPropagation();
                });
            }
        })();

        // ===== مودال پاسخ به نظر =====
        function openReplyModal(reviewId, authorName) {
            var modal = document.getElementById('reply-modal');
            var form = document.getElementById('reply-form');
            var nameEl = document.getElementById('reply-to-name');
            if (!modal || !form) return;

            form.action = '{{ route('products.reviews.reply', [$product->slug, '__REVIEW_ID__']) }}'.replace('__REVIEW_ID__', reviewId);
            nameEl.textContent = authorName;
            modal.classList.add('is-active');
            document.body.style.overflow = 'hidden';

            // شمارنده حروف مودال
            var ta = form.querySelector('textarea[name="comment"]');
            var cc = form.querySelector('.reply-char-count');
            if (ta && cc) {
                ta.value = '';
                cc.textContent = '0';
                ta.focus();
                ta.oninput = function() { cc.textContent = ta.value.length; };
            }
        }

        function closeReplyModal() {
            var modal = document.getElementById('reply-modal');
            if (!modal) return;
            modal.classList.remove('is-active');
            document.body.style.overflow = '';
        }

        // بستن با Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                var modal = document.getElementById('reply-modal');
                if (modal && modal.classList.contains('is-active')) {
                    closeReplyModal();
                }
            }
        });
    </script>

    <style>
        .variant-option.active-variant {
            box-shadow: inset 3px 3px 6px var(--shadow-dark), inset -3px -3px 6px var(--shadow-light);
            color: var(--accent);
            font-weight: 700;
        }

        .variant-option.variant-disabled {
            opacity: 0.35;
            pointer-events: none;
        }

        .detail-tab.active {
            box-shadow: inset 3px 3px 6px var(--shadow-dark), inset -3px -3px 6px var(--shadow-light);
            color: var(--accent);
        }
    </style>
@endpush

@push('styles')
    <style>
        /* ═══════════════════════════════════════
           Review Section — PRO
           ═══════════════════════════════════════ */

        /* ── فرم شیشه‌ای (Glass Morphism) ── */
        .review-form-glass {
            position: relative;
            border-radius: var(--radius-lg, 1.25rem);
            overflow: hidden;
        }
        .review-form-glass__inner {
            padding: clamp(1.25rem, 3vw, 2rem);
            border-radius: var(--radius-lg, 1.25rem);
            background: linear-gradient(160deg, color-mix(in srgb, var(--bg-elevated) 86%, transparent) 0%, color-mix(in srgb, var(--bg-elevated) 70%, transparent) 100%);
            border: 1px solid color-mix(in srgb, var(--accent) 14%, transparent);
            box-shadow: 10px 10px 26px color-mix(in srgb, var(--shadow-dark) 55%, transparent), -8px -8px 22px color-mix(in srgb, var(--shadow-light) 45%, transparent), inset 0 1px 0 color-mix(in srgb, #fff 35%, transparent);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }

        /* هدر فرم */
        .review-form-header {
            display: flex; align-items: center; gap: 0.85rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid color-mix(in srgb, var(--shadow-dark) 30%, transparent);
        }
        .review-form-header__icon {
            display: grid; place-items: center;
            width: 44px; height: 44px; flex-shrink: 0;
            border-radius: 50%;
            background: linear-gradient(145deg, color-mix(in srgb, var(--accent) 18%, var(--bg-elevated)) 0%, color-mix(in srgb, var(--accent) 6%, var(--bg-elevated)) 100%);
            border: 1px solid color-mix(in srgb, var(--accent) 25%, transparent);
            box-shadow: inset 0 1px 0 color-mix(in srgb, #fff 40%, transparent), 0 3px 10px -3px color-mix(in srgb, var(--accent) 30%, transparent);
            color: var(--accent);
        }
        .review-form-header__icon svg { width: 20px; height: 20px; }
        .review-form-header__title { font-size: 1rem; font-weight: 800; color: var(--text-primary); }
        .review-form-header__sub { font-size: 0.75rem; color: var(--text-secondary); margin-top: 2px; }

        .review-form { display: flex; flex-direction: column; gap: 1.25rem; }
        .review-form__error { color: var(--danger, #e53e3e); font-size: 0.75rem; margin-top: 0.25rem; }

        /* ── فیلدهای مهمان ── */
        .review-guest-fields { padding-bottom: 0.5rem; }
        .review-guest-fields__row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem; }
        @media (max-width: 540px) { .review-guest-fields__row { grid-template-columns: 1fr; } }
        .review-input-group__label {
            display: flex; align-items: center; gap: 0.35rem;
            font-size: 0.78rem; font-weight: 700; color: var(--text-primary);
            margin-bottom: 0.35rem;
        }
        .review-input-group__icon { width: 14px; height: 14px; color: var(--accent); flex-shrink: 0; }
        .review-input-group__input {
            width: 100%; padding: 0.6rem 0.85rem;
            border-radius: 0.7rem; border: 2px solid transparent;
            background: var(--bg); color: var(--text-primary);
            font-size: 0.85rem;
            box-shadow: inset 2px 2px 5px var(--shadow-dark), inset -2px -2px 5px var(--shadow-light);
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .review-input-group__input:focus {
            outline: none; border-color: var(--accent);
            box-shadow: inset 2px 2px 5px var(--shadow-dark), inset -2px -2px 5px var(--shadow-light), 0 0 0 3px color-mix(in srgb, var(--accent) 18%, transparent);
        }
        .review-input-group__input::placeholder { color: var(--text-secondary); opacity: 0.5; }

        /* ── ستاره امتیاز ── */
        .review-form__rating {
            display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;
        }
        .star-rating {
            display: flex; gap: 0.25rem;
            flex-direction: row-reverse; justify-content: flex-end;
        }
        .star-rating__star {
            width: 2.5rem; height: 2.5rem;
            background: none; border: none; cursor: pointer;
            color: color-mix(in srgb, var(--shadow-dark) 60%, transparent);
            transition: color 0.2s, transform 0.2s cubic-bezier(.4,0,.2,1), filter 0.2s;
            padding: 0; position: relative;
        }
        .star-rating__star svg { width: 100%; height: 100%; filter: drop-shadow(0 1px 2px rgba(0,0,0,0.1)); }
        .star-rating__star:hover { transform: scale(1.25) rotate(-5deg); }
        .star-rating__star.is-active {
            color: var(--gold, #f59e0b);
            transform: scale(1.15);
            filter: drop-shadow(0 2px 8px color-mix(in srgb, var(--gold, #f59e0b) 50%, transparent));
        }
        .star-rating__star.is-preview {
            color: var(--gold, #f59e0b);
            opacity: 0.5;
        }
        .star-rating__label {
            font-size: 0.8rem; font-weight: 700;
            color: var(--gold, #f59e0b);
            min-width: 60px;
            transition: opacity 0.2s;
        }

        /* ── Textarea ── */
        .review-form__body { position: relative; }
        .review-form__textarea {
            width: 100%; resize: vertical; min-height: 110px;
            padding: 0.9rem 1.1rem;
            border-radius: 0.85rem;
            border: 2px solid transparent;
            background: var(--bg);
            color: var(--text-primary);
            font-size: 0.88rem; line-height: 1.75;
            box-shadow: inset 2px 2px 6px var(--shadow-dark), inset -2px -2px 6px var(--shadow-light);
            transition: border-color 0.25s, box-shadow 0.25s;
        }
        .review-form__textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: inset 2px 2px 6px var(--shadow-dark), inset -2px -2px 6px var(--shadow-light), 0 0 0 3px color-mix(in srgb, var(--accent) 18%, transparent);
        }
        .review-form__textarea::placeholder { color: var(--text-secondary); opacity: 0.5; }
        .review-form__footer { display: flex; justify-content: space-between; align-items: center; margin-top: 0.35rem; }
        .review-form__char-count { font-size: 0.7rem; color: var(--text-secondary); }

        /* ── دکمه ارسال (الگوی add-to-cart) ── */
        .review-submit-btn {
            position: relative; display: inline-flex; align-items: center; justify-content: center;
            gap: 0.55rem;
            padding: 0.7rem 1.6rem;
            border: none; border-radius: 999px;
            font-weight: 800; font-size: 0.85rem;
            color: #fff; cursor: pointer;
            background: linear-gradient(135deg, var(--accent) 0%, color-mix(in srgb, var(--accent) 72%, #000 28%) 100%);
            box-shadow: 0 6px 16px color-mix(in srgb, var(--accent) 40%, transparent), inset 0 1px 0 rgba(255,255,255,0.22);
            transition: transform 0.25s cubic-bezier(.4,0,.2,1), box-shadow 0.3s, filter 0.3s, opacity 0.3s;
            overflow: hidden;
        }
        .review-submit-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            filter: brightness(1.08);
            box-shadow: 0 10px 22px color-mix(in srgb, var(--accent) 55%, transparent), inset 0 1px 0 rgba(255,255,255,0.22);
        }
        .review-submit-btn:active:not(:disabled) { transform: translateY(0) scale(0.97); }
        .review-submit-btn:disabled { opacity: 0.4; cursor: not-allowed; filter: saturate(0.3); transform: none !important; }
        .review-submit-btn__chip {
            display: inline-grid; place-items: center;
            width: 26px; height: 26px; flex-shrink: 0;
            border-radius: 50%;
            background: color-mix(in srgb, #fff 18%, transparent);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.28);
            transition: transform 0.3s cubic-bezier(.4,0,.2,1);
        }
        .review-submit-btn:hover:not(:disabled) .review-submit-btn__chip { transform: translateY(-1px) scale(1.12); }
        .review-submit-btn:active:not(:disabled) .review-submit-btn__chip { transform: scale(0.9); }
        .review-submit-btn__chip svg { width: 14px; height: 14px; }

        /* ── لاگین هینت (شیشه‌ای) ── */
        .review-login-glass {
            position: relative;
            text-align: center;
            padding: 2rem 1.5rem;
            margin-bottom: 2rem;
            border-radius: var(--radius-lg, 1.25rem);
            background: linear-gradient(160deg, color-mix(in srgb, var(--bg-elevated) 86%, transparent) 0%, color-mix(in srgb, var(--bg-elevated) 70%, transparent) 100%);
            border: 2px dashed color-mix(in srgb, var(--accent) 25%, transparent);
            box-shadow: 8px 8px 20px var(--shadow-dark), -8px -8px 20px var(--shadow-light);
            overflow: hidden;
        }
        .review-login-glass__glow {
            position: absolute; top: -60px; right: -60px;
            width: 160px; height: 160px; border-radius: 50%;
            background: radial-gradient(circle, color-mix(in srgb, var(--accent) 15%, transparent), transparent 70%);
            pointer-events: none;
        }
        .review-login-glass__icon {
            display: inline-grid; place-items: center;
            width: 56px; height: 56px; border-radius: 50%;
            background: linear-gradient(145deg, color-mix(in srgb, var(--accent) 15%, var(--bg-elevated)) 0%, var(--bg-elevated) 100%);
            border: 1px solid color-mix(in srgb, var(--accent) 22%, transparent);
            box-shadow: inset 0 1px 0 color-mix(in srgb, #fff 40%, transparent), 0 4px 14px -4px color-mix(in srgb, var(--accent) 30%, transparent);
            color: var(--accent);
            margin: 0 auto 1rem;
        }
        .review-login-glass__icon svg { width: 26px; height: 26px; }
        .review-login-glass__title { font-weight: 700; font-size: 0.9rem; color: var(--text-primary); margin-bottom: 1rem; }
        .review-login-glass__btn {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.55rem 1.4rem; border-radius: 999px;
            font-size: 0.82rem; font-weight: 700; color: #fff; text-decoration: none;
            background: linear-gradient(135deg, var(--accent) 0%, color-mix(in srgb, var(--accent) 72%, #000 28%) 100%);
            box-shadow: 0 4px 12px color-mix(in srgb, var(--accent) 35%, transparent);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .review-login-glass__btn:hover { transform: translateY(-1px); box-shadow: 0 6px 18px color-mix(in srgb, var(--accent) 50%, transparent); }

        /* ── خلاصه امتیازات ── */
        .review-summary-glass {
            display: flex; align-items: center; gap: 2rem; flex-wrap: wrap;
            padding: 1.5rem;
            border-radius: var(--radius-lg, 1.25rem);
            background: linear-gradient(160deg, color-mix(in srgb, var(--bg-elevated) 88%, transparent) 0%, var(--bg-elevated) 100%);
            border: 1px solid color-mix(in srgb, var(--shadow-light) 40%, transparent);
            box-shadow: 8px 8px 20px var(--shadow-dark), -8px -8px 20px var(--shadow-light), inset 0 1px 0 color-mix(in srgb, #fff 35%, transparent);
        }
        .review-summary-glass__left { text-align: center; min-width: 100px; }
        .review-summary-glass__num {
            font-size: 2.8rem; font-weight: 900; line-height: 1;
            background: linear-gradient(165deg, color-mix(in srgb, var(--accent) 74%, #fff 26%) 0%, var(--accent) 45%, color-mix(in srgb, var(--accent) 68%, #000 32%) 100%);
            -webkit-background-clip: text; background-clip: text; color: transparent;
        }
        .review-summary-glass__stars { display: flex; gap: 2px; justify-content: center; margin: 0.3rem 0 0.4rem; }
        .review-summary-glass__star { width: 18px; height: 18px; }
        .review-summary-glass__count { font-size: 0.75rem; color: var(--text-secondary); }
        .review-summary-glass__bars { flex: 1; min-width: 180px; display: flex; flex-direction: column; gap: 0.4rem; }
        .review-summary-bar { display: flex; align-items: center; gap: 0.5rem; }
        .review-summary-bar__num { font-size: 0.72rem; font-weight: 700; color: var(--text-secondary); width: 1rem; text-align: center; }
        .review-summary-bar__track {
            flex: 1; height: 7px; border-radius: 999px;
            background: var(--bg);
            box-shadow: inset 1px 1px 3px var(--shadow-dark), inset -1px -1px 3px var(--shadow-light);
            overflow: hidden;
        }
        .review-summary-bar__fill {
            height: 100%; border-radius: 999px;
            background: linear-gradient(90deg, var(--gold, #f59e0b), color-mix(in srgb, var(--gold, #f59e0b) 70%, #fff));
            transition: width 0.8s cubic-bezier(.4,0,.2,1);
        }
        .review-summary-bar__count { font-size: 0.7rem; color: var(--text-secondary); width: 1.2rem; text-align: center; }

        /* ── لیست نظرات ── */
        .review-list { display: flex; flex-direction: column; gap: 0.85rem; }
        .review-card {
            padding: 1.25rem;
            border-radius: var(--radius-lg, 1rem);
            background: var(--bg-elevated);
            box-shadow: 4px 4px 10px var(--shadow-dark), -4px -4px 10px var(--shadow-light);
            transition: transform 0.25s, box-shadow 0.25s;
            animation: reviewSlide 0.35s ease both;
        }
        .review-card:hover { transform: translateY(-2px); box-shadow: 6px 6px 16px var(--shadow-dark), -6px -6px 16px var(--shadow-light); }
        .review-card__head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem; }
        .review-card__user { display: flex; align-items: center; gap: 0.65rem; }
        .review-card__avatar {
            width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 0.8rem; font-weight: 800; color: var(--accent);
            background: linear-gradient(145deg, color-mix(in srgb, var(--accent) 12%, var(--bg-elevated)) 0%, var(--bg-elevated) 100%);
            box-shadow: inset 2px 2px 4px var(--shadow-dark), inset -2px -2px 4px var(--shadow-light);
        }
        .review-card__name { font-size: 0.82rem; font-weight: 700; color: var(--text-primary); }
        .review-card__date { font-size: 0.68rem; color: var(--text-secondary); margin-top: 1px; }
        .review-card__rating { display: flex; gap: 1px; }
        .review-card__star { width: 15px; height: 15px; }
        .review-card__body { font-size: 0.85rem; line-height: 1.75; color: var(--text-secondary); }

        /* ── دکمه پاسخ ── */
        .review-card__actions {
            display: flex; gap: 0.5rem; margin-top: 0.75rem;
            padding-top: 0.6rem;
            border-top: 1px solid color-mix(in srgb, var(--shadow-dark) 15%, transparent);
        }
        .review-reply-btn {
            display: inline-flex; align-items: center; gap: 0.3rem;
            padding: 0.3rem 0.7rem; border: none; border-radius: 999px;
            font-size: 0.72rem; font-weight: 600;
            color: var(--accent); background: color-mix(in srgb, var(--accent) 8%, transparent);
            cursor: pointer;
            transition: all 0.2s;
        }
        .review-reply-btn:hover {
            background: color-mix(in srgb, var(--accent) 16%, transparent);
            transform: translateY(-1px);
        }
        .review-reply-btn svg { flex-shrink: 0; }

        /* ── پاسخ‌ها (تو در تو) ── */
        .review-card--reply {
            padding: 0.85rem 1rem;
            margin-bottom: 0.5rem;
            border-radius: 0.85rem;
            background: color-mix(in srgb, var(--bg-elevated) 80%, var(--bg));
            box-shadow: inset 2px 2px 4px var(--shadow-dark), inset -2px -2px 4px var(--shadow-light);
            animation: reviewSlide 0.3s ease both;
        }
        .review-card--reply:hover {
            transform: none;
            box-shadow: inset 2px 2px 4px var(--shadow-dark), inset -2px -2px 4px var(--shadow-light);
        }
        .review-replies {
            margin-top: 0.65rem;
            padding-right: 0;
            padding-left: 1.25rem;
            border-right: 2px solid color-mix(in srgb, var(--accent) 20%, transparent);
        }
        .review-replies[style*="--depth: 2"] {
            border-right-color: color-mix(in srgb, var(--accent) 12%, transparent);
        }
        .review-replies[style*="--depth: 3"] {
            border-right-color: color-mix(in srgb, var(--accent) 8%, transparent);
        }
        .review-card__avatar--sm {
            width: 30px !important; height: 30px !important;
            font-size: 0.7rem !important;
        }
        .review-card__reply-badge {
            display: inline-flex; align-items: center; gap: 3px;
            font-size: 0.6rem; font-weight: 600;
            color: var(--accent);
            background: color-mix(in srgb, var(--accent) 10%, transparent);
            padding: 2px 6px; border-radius: 999px;
            margin-inline-start: 4px;
            vertical-align: middle;
        }

        /* ── مودال پاسخ ── */
        .reply-modal {
            display: none; position: fixed; inset: 0;
            z-index: 9998; align-items: center; justify-content: center;
            padding: 1rem;
        }
        .reply-modal.is-active { display: flex; }
        .reply-modal__backdrop {
            position: absolute; inset: 0;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
            animation: fadeIn 0.2s ease;
        }
        .reply-modal__content {
            position: relative; z-index: 1;
            width: 100%; max-width: 480px;
            max-height: 90vh; overflow-y: auto;
            border-radius: var(--radius-lg, 1.25rem);
            background: linear-gradient(160deg, color-mix(in srgb, var(--bg-elevated) 90%, transparent) 0%, var(--bg-elevated) 100%);
            border: 1px solid color-mix(in srgb, var(--accent) 12%, transparent);
            box-shadow: 12px 12px 30px var(--shadow-dark), -8px -8px 24px var(--shadow-light), inset 0 1px 0 rgba(255,255,255,0.3);
            animation: modalSlideUp 0.25s cubic-bezier(.4,0,.2,1);
        }
        .reply-modal__header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid color-mix(in srgb, var(--shadow-dark) 20%, transparent);
        }
        .reply-modal__title-row {
            display: flex; align-items: center; gap: 0.5rem;
        }
        .reply-modal__icon { color: var(--accent); }
        .reply-modal__title {
            font-size: 0.95rem; font-weight: 800; color: var(--text-primary);
        }
        .reply-modal__close {
            width: 36px; height: 36px; display: grid; place-items: center;
            border: none; border-radius: 50%; cursor: pointer;
            color: var(--text-secondary);
            background: color-mix(in srgb, var(--shadow-dark) 12%, transparent);
            transition: all 0.2s;
        }
        .reply-modal__close:hover {
            background: var(--danger, #e53e3e); color: #fff;
            transform: rotate(90deg);
        }
        .reply-modal__form {
            padding: 1.25rem 1.5rem;
            display: flex; flex-direction: column; gap: 1rem;
        }
        .reply-modal__body textarea {
            width: 100%; resize: vertical; min-height: 80px;
            padding: 0.8rem 1rem;
            border-radius: 0.75rem;
            border: 2px solid transparent;
            background: var(--bg);
            color: var(--text-primary);
            font-size: 0.85rem; line-height: 1.7;
            box-shadow: inset 2px 2px 5px var(--shadow-dark), inset -2px -2px 5px var(--shadow-light);
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .reply-modal__body textarea:focus {
            outline: none; border-color: var(--accent);
            box-shadow: inset 2px 2px 5px var(--shadow-dark), inset -2px -2px 5px var(--shadow-light), 0 0 0 3px color-mix(in srgb, var(--accent) 15%, transparent);
        }
        .reply-modal__body textarea::placeholder { color: var(--text-secondary); opacity: 0.5; }
        .reply-modal__form .review-submit-btn { align-self: flex-start; }
        @keyframes modalSlideUp {
            from { opacity: 0; transform: translateY(20px) scale(0.96); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }

        /* ── حالت خالی ── */
        .review-empty {
            text-align: center; padding: 2.5rem 1.5rem;
            border-radius: var(--radius-lg, 1.25rem);
            background: var(--bg-elevated);
            box-shadow: inset 3px 3px 8px var(--shadow-dark), inset -3px -3px 8px var(--shadow-light);
        }
        .review-empty__icon {
            display: inline-grid; place-items: center;
            width: 64px; height: 64px; border-radius: 50%; margin-bottom: 1rem;
            background: linear-gradient(145deg, color-mix(in srgb, var(--shadow-dark) 15%, var(--bg-elevated)) 0%, var(--bg-elevated) 100%);
            box-shadow: 4px 4px 10px var(--shadow-dark), -4px -4px 10px var(--shadow-light);
            color: var(--text-secondary);
        }
        .review-empty__icon svg { width: 28px; height: 28px; }
        .review-empty__title { font-weight: 800; font-size: 0.95rem; color: var(--text-primary); margin-bottom: 0.35rem; }
        .review-empty__desc { font-size: 0.8rem; color: var(--text-secondary); }

        /* ── انیمیشن ورود ── */
        @keyframes reviewSlide {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .review-card:nth-child(2) { animation-delay: 0.05s; }
        .review-card:nth-child(3) { animation-delay: 0.10s; }
        .review-card:nth-child(4) { animation-delay: 0.15s; }
        .review-card:nth-child(5) { animation-delay: 0.20s; }

        /* ═══════════════════════════════════════
           Image Zoom Lens
           ═══════════════════════════════════════ */
        .image-zoom-wrap {
            position: relative;
            overflow: hidden;
            border-radius: var(--radius-lg, 1rem);
            cursor: crosshair;
        }
        .zoom-lens {
            display: none;
            position: absolute;
            width: 160px;
            height: 160px;
            border-radius: 50%;
            border: 3px solid rgba(255,255,255,0.85);
            box-shadow: 0 0 0 1px rgba(0,0,0,0.1), 0 8px 32px rgba(0,0,0,0.18);
            pointer-events: none;
            z-index: 20;
            background-repeat: no-repeat;
            transform: translate(-50%, -50%);
            transition: opacity 0.15s ease;
            opacity: 0;
            /* 2.5x zoom */
            --zoom: 2.5;
        }
        .image-zoom-wrap:hover .zoom-lens {
            display: block;
            opacity: 1;
        }
        @media (max-width: 640px) {
            .zoom-lens { width: 120px; height: 120px; --zoom: 2; }
        }

        /* ═══════════════════════════════════════
           Lightbox
           ═══════════════════════════════════════ */
        .lightbox {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(0,0,0,0.92);
            backdrop-filter: blur(8px);
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.25s ease;
        }
        .lightbox.is-active {
            display: flex;
            opacity: 1;
        }
        .lightbox__close {
            position: absolute;
            top: 1rem; right: 1rem;
            width: 44px; height: 44px;
            display: flex; align-items: center; justify-content: center;
            background: rgba(255,255,255,0.1);
            border: none; border-radius: 50%;
            color: #fff; cursor: pointer;
            transition: background 0.2s;
            z-index: 10;
        }
        .lightbox__close:hover { background: rgba(255,255,255,0.25); }
        .lightbox__close svg { width: 22px; height: 22px; }
        .lightbox__img-wrap {
            display: flex; align-items: center; justify-content: center;
            width: 100%; height: 100%;
            padding: 60px 60px;
            user-select: none;
        }
        .lightbox__img {
            max-width: 100%; max-height: 100%;
            object-fit: contain;
            border-radius: 8px;
            transition: transform 0.3s cubic-bezier(.4,0,.2,1), opacity 0.25s ease;
            transform: scale(0.95);
            opacity: 0;
        }
        .lightbox.is-active .lightbox__img {
            transform: scale(1);
            opacity: 1;
        }
        .lightbox__arrow {
            position: absolute;
            top: 50%; transform: translateY(-50%);
            width: 48px; height: 48px;
            display: flex; align-items: center; justify-content: center;
            background: rgba(255,255,255,0.08);
            border: none; border-radius: 50%;
            color: #fff; cursor: pointer;
            transition: background 0.2s, transform 0.2s;
            z-index: 10;
        }
        .lightbox__arrow:hover { background: rgba(255,255,255,0.2); }
        .lightbox__arrow svg { width: 26px; height: 26px; }
        .lightbox__arrow--prev { left: 1rem; }
        .lightbox__arrow--next { right: 1rem; }
        .lightbox__counter {
            position: absolute;
            bottom: 1.25rem; left: 50%; transform: translateX(-50%);
            font-size: 0.85rem; font-weight: 600;
            color: rgba(255,255,255,0.7);
            letter-spacing: 0.05em;
        }
        @media (max-width: 640px) {
            .lightbox__img-wrap { padding: 50px 12px 60px; }
            .lightbox__arrow { width: 38px; height: 38px; }
            .lightbox__arrow--prev { left: 0.5rem; }
            .lightbox__arrow--next { right: 0.5rem; }
            .lightbox__arrow svg { width: 20px; height: 20px; }
        }

        /* ═══════════════════════════════════════
           Share Section — Pill + Overflow
           ═══════════════════════════════════════ */
        .share-section { margin-bottom: 0; }

        .share-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
        }

        /* ── Pill base ── */
        .share-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.4rem 0.75rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            background: var(--bg-elevated);
            color: var(--text-secondary);
            box-shadow: 3px 3px 7px var(--shadow-dark), -3px -3px 7px var(--shadow-light);
            transition: all 0.25s cubic-bezier(.4,0,.2,1);
            position: relative;
            overflow: hidden;
            white-space: nowrap;
        }
        .share-pill::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            opacity: 0;
            transition: opacity 0.25s ease;
            z-index: 0;
        }
        .share-pill:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 4px 5px 14px var(--shadow-dark), -4px -5px 14px var(--shadow-light);
        }
        .share-pill:active {
            transform: translateY(0) scale(0.96);
            box-shadow: inset 2px 2px 5px var(--shadow-dark), inset -2px -2px 5px var(--shadow-light);
            transition-duration: 0.1s;
        }
        .share-pill__icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 1rem;
            height: 1rem;
            flex-shrink: 0;
            position: relative;
            z-index: 1;
        }
        .share-pill__icon svg { width: 100%; height: 100%; }
        .share-pill__label {
            white-space: nowrap;
            line-height: 1;
            position: relative;
            z-index: 1;
        }

        /* ── Platform colors ── */
        .share-pill[data-platform="telegram"] { color: #0088cc; }
        .share-pill[data-platform="telegram"]::before { background: linear-gradient(135deg, #0088cc 0%, #229ed9 100%); }
        .share-pill[data-platform="telegram"]:hover { color: #fff; }
        .share-pill[data-platform="telegram"]:hover::before { opacity: 1; }

        .share-pill[data-platform="whatsapp"] { color: #25D366; }
        .share-pill[data-platform="whatsapp"]::before { background: linear-gradient(135deg, #25D366 0%, #128C7E 100%); }
        .share-pill[data-platform="whatsapp"]:hover { color: #fff; }
        .share-pill[data-platform="whatsapp"]:hover::before { opacity: 1; }

        /* Native share (همه اپ‌ها) */
        .share-pill--native { color: var(--accent); }
        .share-pill--native::before { background: linear-gradient(135deg, var(--accent) 0%, color-mix(in srgb, var(--accent) 70%, #000) 100%); }
        .share-pill--native:hover { color: #fff; }
        .share-pill--native:hover::before { opacity: 1; }
        .share-pill--native.share-pill--copied { color: var(--success) !important; }
        .share-pill--native.share-pill--copied::before { background: linear-gradient(135deg, var(--success) 0%, color-mix(in srgb, var(--success) 70%, #000) 100%) !important; opacity: 1 !important; }

        /* Toggle (بیشتر/کمتر) */
        .share-pill--toggle { color: var(--text-secondary); border: 1.5px dashed var(--text-secondary); background: transparent; box-shadow: none; }
        .share-pill--toggle::before { background: linear-gradient(135deg, var(--text-secondary) 0%, color-mix(in srgb, var(--text-secondary) 70%, #000) 100%); }
        .share-pill--toggle:hover { color: #fff; border-color: transparent; }
        .share-pill--toggle:hover::before { opacity: 1; }
        .share-pill--toggle .share-pill__icon svg { transition: transform 0.25s ease; }

        /* Copy link */
        .share-pill--copy { color: var(--text-secondary); }
        .share-pill--copy::before { background: linear-gradient(135deg, var(--accent) 0%, color-mix(in srgb, var(--accent) 70%, #000) 100%); }
        .share-pill--copy:hover { color: #fff !important; }
        .share-pill--copy:hover::before { opacity: 1; }
        .share-pill--copied { color: var(--success) !important; }
        .share-pill--copied::before { background: linear-gradient(135deg, var(--success) 0%, color-mix(in srgb, var(--success) 70%, #000) 100%) !important; opacity: 1 !important; }
        .share-pill--copied .share-pill__icon,
        .share-pill--copied .share-pill__label { position: relative; color: #fff !important; }

        /* Overflow platform colors */
        [data-platform="bale"] { color: #0d7c9d; }
        [data-platform="bale"]::before { background: linear-gradient(135deg, #0d7c9d 0%, #00a693 100%); }
        [data-platform="bale"]:hover { color: #fff; }
        [data-platform="bale"]:hover::before { opacity: 1; }
        [data-platform="eitaa"] { color: #e84393; }
        [data-platform="eitaa"]::before { background: linear-gradient(135deg, #e84393 0%, #a855f7 100%); }
        [data-platform="eitaa"]:hover { color: #fff; }
        [data-platform="eitaa"]:hover::before { opacity: 1; }
        [data-platform="instagram-story"] { color: #f77737; }
        [data-platform="instagram-story"]::before { background: linear-gradient(135deg, #f77737 0%, #c13584 50%, #833ab4 100%); }
        [data-platform="instagram-story"]:hover { color: #fff; }
        [data-platform="instagram-story"]:hover::before { opacity: 1; }
        [data-platform="instagram-dm"] { color: #c13584; }
        [data-platform="instagram-dm"]::before { background: linear-gradient(135deg, #c13584 0%, #833ab4 100%); }
        [data-platform="instagram-dm"]:hover { color: #fff; }
        [data-platform="instagram-dm"]:hover::before { opacity: 1; }

        /* ── Overflow row ── */
        .share-overflow {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
            margin-top: 0.4rem;
        }
        .share-overflow.hidden { display: none; }
        .share-overflow .share-pill {
            opacity: 0;
            transform: translateY(6px) scale(0.85);
        }
        .share-overflow:not(.hidden) .share-pill {
            animation: sharePop 0.3s ease both;
        }
        .share-overflow:not(.hidden) .share-pill:nth-child(1) { animation-delay: 0.00s; }
        .share-overflow:not(.hidden) .share-pill:nth-child(2) { animation-delay: 0.05s; }
        .share-overflow:not(.hidden) .share-pill:nth-child(3) { animation-delay: 0.10s; }
        .share-overflow:not(.hidden) .share-pill:nth-child(4) { animation-delay: 0.15s; }
        .share-overflow:not(.hidden) .share-pill:nth-child(5) { animation-delay: 0.20s; }

        /* ── Stagger entrance ── */
        @keyframes sharePop {
            from { opacity: 0; transform: translateY(6px) scale(0.85); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        .share-pills .share-pill:nth-child(1) { animation: sharePop 0.3s 0.00s both; }
        .share-pills .share-pill:nth-child(2) { animation: sharePop 0.3s 0.05s both; }
        .share-pills .share-pill:nth-child(3) { animation: sharePop 0.3s 0.10s both; }
        .share-pills .share-pill:nth-child(4) { animation: sharePop 0.3s 0.15s both; }
        .share-pills .share-pill:nth-child(5) { animation: sharePop 0.3s 0.20s both; }
        .share-pills .share-pill:nth-child(6) { animation: sharePop 0.3s 0.25s both; }
        .share-pills .share-pill:nth-child(7) { animation: sharePop 0.3s 0.30s both; }

        /* ── Ripple pulse ── */
        .share-pill::after {
            content: '';
            position: absolute;
            width: 0; height: 0;
            border-radius: 50%;
            background: rgba(255,255,255,0.25);
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.4s ease, height 0.4s ease, opacity 0.4s ease;
            opacity: 0;
            z-index: 0;
        }
        .share-pill:active::after {
            width: 200%; height: 200%;
            opacity: 1;
            transition: 0s;
        }
    </style>
@endpush
