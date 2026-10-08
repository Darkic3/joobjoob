{{-- داده‌ها (name/url/count/icon/countLabel) در کلاس CategoryCard آماده می‌شود --}}
{{-- کارت دسته‌بندی — تم Eco-Collectible: مُهر دایره‌ای دورنگ، شمارهٔ کلکسیونی، چیپ شمارندهٔ مونو --}}
<a href="{{ $url }}" class="eco-category-card group">

    {{-- شمارهٔ کلکسیونی کارت (تزئینی) --}}
    @if($index)
        <span class="eco-category-num" aria-hidden="true" dir="ltr">№<span data-digit-raw>{{ str_pad($index, 2, '0', STR_PAD_LEFT) }}</span></span>
    @endif

    {{-- بلوک آیکون/تصویر --}}
    <span class="eco-category-icon">
        @if($category->image)
            <img src="{{ $category->image_url }}" alt="{{ $name }}" class="w-full h-full object-cover" loading="lazy">
        @else
            <span class="eco-category-emoji" aria-hidden="true">{{ $icon }}</span>
        @endif
    </span>

    {{-- نام دسته‌بندی --}}
    <span class="eco-category-name">{{ $name }}</span>

    {{-- شمارندهٔ مونو --}}
    @if($count > 0)
        <span class="eco-category-chip">
            <span class="eco-category-chip-dot" aria-hidden="true"></span>
            <span class="eco-category-chip-num" data-digit-raw>{{ $count }}</span>
            <span>{{ $countLabel }}</span>
        </span>
    @endif

    {{-- فلش، در هاور/فوکوس ظاهر می‌شود --}}
    <span class="eco-category-arrow" aria-hidden="true">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
    </span>

</a>
