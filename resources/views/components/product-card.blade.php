{{--
    کامپوننت کارت محصول — نسخه‌ی حرفه‌ای با عناصر روان‌شناسی فروش.

    Props:
    - product: object|array شامل name, image, price, old_price, rating, reviews, stock, badge
      badge می‌تواند یکی از این مقادیر باشد: 'new', 'sale', 'urgent:متن' یا خالی
--}}
@props(['product'])

@php
    $p = (object) $product;
    $hasDiscount = !empty($p->old_price) && $p->old_price > $p->price;
    $discountPercent = $hasDiscount ? round((1 - $p->price / $p->old_price) * 100) : 0;
    $lowStock = isset($p->stock) && $p->stock > 0 && $p->stock <= 5;
@endphp

<div class="neu-card p-4 group relative">

    {{-- اکشن‌های سریع: نمایش روی هاور --}}
    <div class="product-actions">
        <button type="button" class="neu-btn w-9 h-9 grid place-items-center text-sm" title="{{ __('messages.wishlist') }}">♡</button>
        <button type="button" class="neu-btn w-9 h-9 grid place-items-center text-sm" title="نمایش سریع">👁</button>
    </div>

    {{-- بج‌های روان‌شناسی فروش: اولویت با کمبود موجودی > تخفیف > جدید --}}
    @if($lowStock)
        <span class="badge-urgency absolute top-3 start-3 z-10">فقط {{ $p->stock }} عدد مانده!</span>
    @elseif($hasDiscount)
        <span class="badge-sale absolute top-3 start-3 z-10">{{ $discountPercent }}٪-</span>
    @elseif(!empty($p->is_new))
        <span class="badge-new absolute top-3 start-3 z-10">جدید</span>
    @endif

    <a href="{{ $p->url ?? '#' }}" class="block">
        <img src="{{ $p->image }}" alt="{{ $p->name }}" loading="lazy"
             class="w-full rounded-xl mb-3 object-cover" style="aspect-ratio:1/1;">

        <h4 class="text-xs font-semibold mb-1 line-clamp-2">{{ $p->name }}</h4>

        {{-- اثبات اجتماعی: امتیاز + تعداد نظر، همیشه قابل مشاهده --}}
        <div class="flex items-center gap-1 text-[11px] mb-2">
            <span class="stars">{{ str_repeat('★', round($p->rating ?? 0)) }}{{ str_repeat('☆', 5 - round($p->rating ?? 0)) }}</span>
            <span style="color:var(--text-secondary);">({{ $p->reviews ?? 0 }})</span>
        </div>

        <div class="flex items-baseline gap-2 mb-2">
            <span class="mono font-bold text-sm" style="color:var(--accent);">{{ number_format($p->price) }}</span>
            @if($hasDiscount)
                <span class="mono text-[11px] line-through" style="color:var(--text-secondary);">{{ number_format($p->old_price) }}</span>
            @endif
        </div>

        {{-- نوار پیشرفت فروش: فقط وقتی sold_percent ست شده باشد نمایش داده می‌شود --}}
        @isset($p->sold_percent)
            <div class="stock-bar mb-1"><div class="stock-bar-fill" style="width:{{ $p->sold_percent }}%;"></div></div>
            <p class="text-[10px] font-semibold mb-2" style="color: {{ $p->sold_percent >= 80 ? 'var(--urgency)' : 'var(--text-secondary)' }};">
                {{ $p->sold_percent }}٪ فروخته شد
            </p>
        @endisset
    </a>

    {{--
        دکمه افزودن به سبد: در حالت فقط-UI صرفاً حالت idle/loading/success را با JS شبیه‌سازی می‌کند
        (توسط ui-effects.js). برای اتصال واقعی، data-url را به route('cart.add', $product) بدهید
        و در ui-effects.js به‌جای setTimeout از fetch(url) استفاده کنید.
    --}}
    <button type="button"
            class="add-to-cart neu-btn neu-btn-primary w-full text-xs mt-1 py-2.5 flex items-center justify-center gap-2"
            data-url="{{ $p->add_to_cart_url ?? '#' }}"
            @disabled(isset($p->stock) && $p->stock === 0)>
        {{ (isset($p->stock) && $p->stock === 0) ? __('messages.out_of_stock') : __('messages.add_to_cart') }}
    </button>
</div>
