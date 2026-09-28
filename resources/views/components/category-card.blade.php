{{-- داده‌ها (name/url/count/icon/countLabel) در کلاس CategoryCard آماده می‌شود --}}
{{-- کارت بروتالیست: کاغذ کرم + بوردر جوهری + سایهٔ هارد + چرخش ریتمیک (پلی‌فول) --}}
<a href="{{ $url }}" class="category-brutal-card group">

    {{-- نوار نئونی بالای کارت (رنگ‌بندی با nth-child در CSS) --}}
    <span class="category-brutal-tape" aria-hidden="true"></span>

    {{-- بلوک آیکون/تصویر — مربعی با سایهٔ هارد --}}
    <span class="category-brutal-icon">
        @if($category->image)
            <img src="{{ $category->image_url }}" alt="{{ $name }}" class="w-full h-full object-cover" loading="lazy">
        @else
            <span class="category-brutal-emoji">{{ $icon }}</span>
        @endif
    </span>

    {{-- نام دسته‌بندی --}}
    <span class="category-brutal-name">{{ $name }}</span>

    {{-- شمارندهٔ مونو --}}
    @if($count > 0)
        <span class="category-brutal-chip">
            <span class="category-brutal-chip-num">{{ $count }}</span>
            <span>{{ $countLabel }}</span>
        </span>
    @endif

    {{-- فلش ته کارت، در هاور ظاهر می‌شود --}}
    <span class="category-brutal-arrow" aria-hidden="true">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
    </span>

</a>
