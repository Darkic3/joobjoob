{{-- داده‌ها (name/url/count/icon/countLabel) در کلاس CategoryCard آماده می‌شود --}}
<a href="{{ $url }}"
   class="neu-card neu-interactive group flex flex-col items-center justify-center w-full aspect-[4/5] gap-3 p-4 hover:-translate-y-1.5 transition-all duration-300">

    {{-- تصویر یا آیکون دسته‌بندی --}}
    <div class="w-16 h-16 rounded-full neu-flat grid place-items-center text-2xl overflow-hidden group-hover:scale-110 transition-transform duration-300 bg-[var(--bg-elevated)]">
        @if($category->image)
            <img src="{{ $category->image_url }}" alt="{{ $name }}" class="w-full h-full object-cover" loading="lazy">
        @else
            {{ $icon }}
        @endif
    </div>

    {{-- نام دسته‌بندی --}}
    <span class="text-xs font-bold text-center line-clamp-2 leading-relaxed" style="color: var(--text-primary);">
        {{ $name }}
    </span>

    {{-- تعداد محصولات --}}
    @if($count > 0)
        <span class="text-[10px] mono font-semibold px-2.5 py-0.5 rounded-full neu-flat-sm" style="color: var(--text-secondary);">
            {{ $count }} {{ $countLabel }}
        </span>
    @endif

</a>
