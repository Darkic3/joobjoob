{{--
    کارت محصول — سازگار با مدل Product و پشتیبانی چندزبانه
    Props:
    - product: instance of App\Models\Product
    - rank: (اختیاری) 1 تا 3 برای مدال طلا/نقره/برنز در بخش پرفروش‌ترین‌ها
    - soldPercent: (اختیاری) درصد فروش برای نمایش نوار پیشرفت (مخصوص پیشنهادهای داغ)
--}}
@props(['product', 'rank' => null, 'soldPercent' => null])

@php
    /** @var \App\Models\Product $product */

    // اطلاعات پایه
    $name = $product->trans('name');
    $imageUrl = $product->main_image_url ?? 'https://picsum.photos/seed/fallback/300/300';
//    dd($product , $product->images , $imageUrl);
    $url = localizedRoute('products.show', ['product' => $product->slug]);

    // قیمت و تخفیف (با استفاده از Accessorهای مدل)
    $hasDiscount = $product->has_discount;
    $discountPercent = $product->discount_percentage ?? 0;
    $finalPrice = $product->final_price;
    $originalPrice = $product->price;

    // وضعیت موجودی
    $lowStock = $product->stock > 0 && $product->stock <= 5;
    $outOfStock = $product->stock === 0;

    // نشان جدید (محصولاتی که در ۱۴ روز اخیر اضافه شده‌اند)
    $isNew = $product->created_at->diffInDays(now()) <= 14;

    // امتیاز و نظرات (با fallback ایمن)
    $rating = round($product->reviews->avg('rating') ?? 0, 1);
    $reviewCount = $product->reviews->count();

    // استایل رتبه‌بندی (منطبق با طراحی قبلی شما)
    $rankStyles = [
        1 => ['color' => 'var(--gold)',   'text' => '#000'],
        2 => ['color' => 'var(--silver)', 'text' => '#000'],
        3 => ['color' => 'var(--bronze)', 'text' => '#fff'],
    ];
    $rankStyle = $rank ? ($rankStyles[$rank] ?? ['color' => 'var(--bg-elevated)', 'text' => 'var(--text-secondary)']) : null;
@endphp

<div class="neu-card neu-interactive group relative flex flex-col h-full p-4">

    {{-- نشان رتبه (فقط برای بخش پرفروش‌ترین‌ها) --}}
    @if($rank)
        <span class="neu-rank-badge absolute -top-3 -start-3 z-20 w-8 h-8 flex items-center justify-center rounded-full font-black text-sm shadow-md"
              style="background-color: {{ $rankStyle['color'] }}; color: {{ $rankStyle['text'] }};">
            {{ $rank }}
        </span>
    @endif

    {{-- دکمه‌های عملیات سریع --}}
    <div class="product-actions absolute top-3 end-3 z-10 flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
        <button type="button" class="neu-btn neu-btn-icon w-8 h-8 flex items-center justify-center text-sm bg-[var(--bg-elevated)]" title="{{ __('messages.wishlist') }}">
            ♡
        </button>
        <button type="button" class="neu-btn neu-btn-icon w-8 h-8 flex items-center justify-center text-sm bg-[var(--bg-elevated)]" title="{{ __('messages.quick_view') }}">
            👁
        </button>
    </div>

    <a href="{{ $url }}" class="block flex-1 flex flex-col">
        {{-- تصویر محصول --}}
        <div class="relative mb-3 overflow-hidden rounded-xl bg-[var(--bg-elevated)]/50" style="aspect-ratio: 1/1;">
            <img src="{{ $imageUrl }}"
                 alt="{{ $name }}"
                 loading="lazy"
                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">

            {{-- نشان‌ها (فقط اگر رتبه‌بندی نباشد) --}}
            @if(!$rank)
                @if($lowStock)
                    <span class="badge-urgency absolute top-2 start-2 z-10 bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded-full animate-pulse">
                        {{ __('messages.only_left', ['count' => $product->stock]) }}
                    </span>
                @elseif($hasDiscount)
                    <span class="badge-sale absolute top-2 start-2 z-10">
                        {{ $discountPercent }}٪-
                    </span>
                @elseif($isNew)
                    <span class="badge-new absolute top-2 start-2 z-10 bg-[var(--success)] text-white text-[10px] font-bold px-2 py-1 rounded-full">
                        {{ __('messages.new_badge') }}
                    </span>
                @endif
            @endif
        </div>

        {{-- نام محصول — فونت بزرگ‌تر و بولدتر برای خوانایی بهتر (مخصوصاً فارسی) --}}
        <h4 class="text-[13px] font-bold mb-2 line-clamp-2 min-h-[2.6rem] leading-relaxed" style="color: var(--text-primary);">
            {{ $name }}
        </h4>

        {{-- امتیاز و تعداد نظرات — فقط اگر امتیاز وجود داشته باشد --}}
        @if($rating > 0)
            <div class="flex items-center gap-1 text-[11px] mb-2">
                <span class="text-[var(--gold)] tracking-widest">
                    {{ str_repeat('★', floor($rating)) }}{{ str_repeat('☆', 5 - floor($rating)) }}
                </span>
                <span style="color: var(--text-secondary);">({{ $reviewCount }})</span>
            </div>
        @endif
    </a>

    {{-- بخش قیمت و دکمه خرید --}}
    <div class="mt-auto pt-3 border-t border-[var(--shadow-light)]/50">
        <div class="mb-3">
            {{-- قیمت قبلی + برچسب تخفیف — خط کوچک بالای قیمت نهایی --}}
            @if($hasDiscount)
                <div class="flex items-center gap-1.5 mb-1">
                    <span class="mono text-[11px] font-semibold line-through" style="color: var(--text-secondary); opacity: .8;" dir="ltr">
                        {{ number_format($originalPrice) }}
                    </span>
                    <span class="price-off">{{ $discountPercent }}٪-</span>
                </div>
            @endif

            {{-- قیمت نهایی — بزرگ، بولد و با هاله برای جلب توجه --}}
            <div class="flex items-baseline gap-1.5">
                <span class="mono font-black text-xl leading-none price-glow" style="color: var(--accent);" dir="ltr">
                    {{ number_format($finalPrice) }}
                </span>
                <span class="text-[11px] font-bold" style="color: var(--text-secondary);">{{ __('messages.toman') }}</span>
            </div>
        </div>

        {{-- نوار پیشرفت فروش (فقط در صورت ارسال soldPercent) --}}
        @if($soldPercent !== null)
            <div class="stock-bar w-full h-1.5 rounded-full bg-[var(--shadow-dark)] mb-1 overflow-hidden">
                <div class="stock-bar-fill h-full rounded-full bg-[var(--urgency)] transition-all duration-1000"
                     style="width: {{ $soldPercent }}%;"></div>
            </div>
            <p class="text-[10px] font-semibold mb-3" style="color: {{ $soldPercent >= 80 ? 'var(--urgency)' : 'var(--text-secondary)' }};">
                {{ $soldPercent }}٪ {{ __('messages.sold_percent') }}
            </p>
        @endif

        {{-- دکمه افزودن به سبد خرید --}}
        <button type="button"
                class="add-to-cart"
                @disabled($outOfStock)
                data-success="{{ __('messages.added_short') }}"
                onclick="{{ $outOfStock ? '' : '/* منطق افزودن به سبد خرید */' }}">

            @if($outOfStock)
                <span aria-hidden="true">🚫</span>
                <span>{{ __('messages.out_of_stock') }}</span>
            @else
                <span class="add-to-cart-icon" aria-hidden="true">🛒</span>
                <span>{{ __('messages.add_to_cart') }}</span>
            @endif
        </button>
    </div>
</div>
