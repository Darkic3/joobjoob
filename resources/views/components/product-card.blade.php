{{--
    کارت محصول — یک کامپوننت برای همه‌جا (محصولات ویژه، پیشنهادهای داغ، پرفروش‌ترین‌ها).
    Props:
    - product: name, image, price, old_price, rating, reviews, stock, is_new, sold_percent, url
    - rank: (اختیاری) 1 تا 3 برای مدال طلا/نقره/برنز در بخش پرفروش‌ترین‌ها؛ باقی اعداد رتبه‌ی خنثی می‌گیرند.
--}}
@props(['product', 'rank' => null])

@php
    $p = (object) $product;
    $hasDiscount = !empty($p->old_price) && $p->old_price > $p->price;
    $discountPercent = $hasDiscount ? round((1 - $p->price / $p->old_price) * 100) : 0;
    $lowStock = isset($p->stock) && $p->stock > 0 && $p->stock <= 5;

    $rankStyles = [
        1 => ['color' => 'var(--gold)',   'text' => '#fff'],
        2 => ['color' => 'var(--silver)', 'text' => '#fff'],
        3 => ['color' => 'var(--bronze)', 'text' => '#fff'],
    ];
    $rankStyle = $rank ? ($rankStyles[$rank] ?? ['color' => 'var(--bg-elevated)', 'text' => 'var(--text-secondary)']) : null;
@endphp

{{-- flex flex-col + h-full: کارت‌های یک ردیف همیشه هم‌قد می‌مانند و دکمه همیشه ته کارت می‌چسبد --}}
<div class="neu-card group relative flex flex-col h-full">

    @if($rank)
        <span class="neu-rank-badge" style="--rank-color: {{ $rankStyle['color'] }}; --rank-text: {{ $rankStyle['text'] }};">{{ $rank }}</span>
    @endif

    <div class="product-actions">
        <button type="button" class="neu-btn neu-btn-icon neu-btn-icon-sm text-sm" title="{{ __('messages.wishlist') }}">♡</button>
        <button type="button" class="neu-btn neu-btn-icon neu-btn-icon-sm text-sm" title="نمایش سریع">👁</button>
    </div>

    {{-- وقتی رتبه نشان داده می‌شود، بج تخفیف/کمبود را نمی‌گذاریم تا با نشان رتبه تداخل نکند --}}
    @if(!$rank)
        @if($lowStock)
            <span class="badge-urgency absolute top-3 start-3 z-10">فقط {{ $p->stock }} عدد مانده!</span>
        @elseif($hasDiscount)
            <span class="badge-sale absolute top-3 start-3 z-10">{{ $discountPercent }}٪-</span>
        @elseif(!empty($p->is_new))
            <span class="badge-new absolute top-3 start-3 z-10">جدید</span>
        @endif
    @endif

    <a href="{{ $p->url ?? '#' }}" class="block">
        <img src="{{ $p->image }}" alt="{{ $p->name }}" loading="lazy"
             class="w-full rounded-xl mb-3 object-cover" style="aspect-ratio:1/1;">

        {{-- min-h ثابت: عناوین یک‌خطی و دوخطی هر دو همان فضا را می‌گیرند --}}
        <h4 class="text-xs font-semibold mb-1 line-clamp-2 min-h-[2rem]">{{ $p->name }}</h4>

        <div class="flex items-center gap-1 text-[11px] mb-2">
            <span class="stars">{{ str_repeat('★', round($p->rating ?? 0)) }}{{ str_repeat('☆', 5 - round($p->rating ?? 0)) }}</span>
            <span style="color:var(--text-secondary);">({{ $p->reviews ?? 0 }})</span>
        </div>
    </a>

    {{-- mt-auto: قیمت + نوار موجودی + دکمه همیشه به کف کارت می‌چسبند --}}
    <div class="mt-auto">
        <div class="flex items-baseline gap-2 mb-2">
            <span class="mono font-bold text-sm" style="color:var(--accent);">{{ number_format($p->price) }}</span>
            @if($hasDiscount)
                <span class="mono text-[11px] line-through" style="color:var(--text-secondary);">{{ number_format($p->old_price) }}</span>
            @endif
        </div>

        @isset($p->sold_percent)
            <div class="stock-bar mb-1"><div class="stock-bar-fill" style="width:{{ $p->sold_percent }}%;"></div></div>
            <p class="text-[10px] font-semibold mb-2" style="color: {{ $p->sold_percent >= 80 ? 'var(--urgency)' : 'var(--text-secondary)' }};">
                {{ $p->sold_percent }}٪ فروخته شد
            </p>
        @endisset

        <button type="button"
                class="add-to-cart neu-btn neu-btn-primary neu-btn-sm w-full"
                data-url="{{ $p->add_to_cart_url ?? '#' }}"
            @disabled(isset($p->stock) && $p->stock === 0)>
            {{ (isset($p->stock) && $p->stock === 0) ? __('messages.out_of_stock') : __('messages.add_to_cart') }}
        </button>
    </div>
</div>
