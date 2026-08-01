@props(['category'])

@php
    /** @var \App\Models\ProductCategory $category */

    $name = $category->trans('name') ?? 'دسته‌بندی';
    $url = route('products.index', ['category' => $category->slug]);
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

    $icon = $iconMap[$category->icon] ?? '📦';
@endphp


<a href="{{ $url }}" class="neu-card neu-interactive flex flex-col items-center justify-center p-4 gap-3 group hover:-translate-y-1 transition-all duration-300 w-[120px]">

    {{-- آیکون دسته‌بندی --}}
    <div class="w-14 h-14 rounded-full neu-flat flex items-center justify-center text-2xl group-hover:scale-110 transition-transform duration-300 bg-[var(--bg-elevated)]">
        {{ $icon }}
    </div>

    {{-- نام دسته‌بندی --}}
    <span class="text-xs font-bold text-center line-clamp-2 leading-relaxed" style="color: var(--text-primary);">
        {{ $name }}
    </span>

    {{-- تعداد محصولات --}}
    @if($count > 0)
        <span class="text-[10px] mono font-semibold px-2 py-0.5 rounded-full neu-flat-sm" style="color: var(--text-secondary);">
            {{ $count }} {{ __('messages.product') }}
        </span>
    @endif

</a>
