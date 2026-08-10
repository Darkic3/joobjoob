@props(['category'])

@php
    /** @var \App\Models\ProductCategory $category */

    $name = $category->trans('name') ?? __('messages.categories');
    $url = localizedRoute('products.index', ['category' => $category->slug]);
    $count = $category->products_count ?? 0;

    // نگاشت آیکون‌های متنی به ایموجی
    $iconMap = [
        'shirt-outline' => '👕',
        'glasses-outline' => '👓',
        'footsteps-outline' => '🧦',
        'phone-outline' => '📱',
        'laptop-outline' => '💻',
        'home-outline' => '🏠',
        'book-outline' => '📚',
        'sports-outline' => '⚽',
        'car-outline' => '🚗',
        'heart-outline' => '💄',
    ];

    $storedIcon = trim((string) ($category->icon ?? ''));
    // اگر آیکون یک نماد/ایموجی واقعی باشد (تک‌کاراکتری یا شامل کاراکتر غیر-ASCII) مستقیم نمایش داده می‌شود؛
    // کلیدهای نام‌دار انگلیسی از نگاشت به ایموجی تبدیل می‌شوند.
    $isSymbol = $storedIcon !== '' && (mb_strlen($storedIcon) === 1 || ! preg_match('/^[a-zA-Z0-9_-]+$/', $storedIcon));
    $icon = $isSymbol ? $storedIcon : ($iconMap[$storedIcon] ?? '📦');

    // برچسب تعداد محصولات:
    // فارسی و ترکی بعد از عدد همیشه مفرد می‌گیرند («۲ محصول» / «2 Ürün»)؛
    // انگلیسی و عربی برای بیش از یک، جمع.
    $countLabel = in_array(app()->getLocale(), ['fa', 'tr'], true)
        ? __('messages.product')
        : ($count === 1 ? __('messages.product') : __('messages.products'));
@endphp


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
