@props(['category'])
@php
    $icons = [
        'mobile-tablet' => '📱', 'laptop-computer' => '💻', 'fashion' => '👕',
        'shoes-bags' => '👟', 'home-appliances' => '🏠', 'beauty-health' => '💄',
        'books-stationery' => '📚', 'sports-travel' => '⚽',
    ];
    $icon = $icons[$category->slug] ?? '📦';
@endphp

<a href="{{ route('products.index', ['category' => $category->slug]) }}"
   class="neu-card neu-interactive flex flex-col items-center text-center gap-3">
    <span class="neu-icon-circle text-3xl transition-transform group-hover:scale-110">
        {{ $icon }}
    </span>
    <span class="font-semibold text-sm">{{ $category->name }}</span>
    <span class="text-xs" style="color: var(--text-secondary);">{{ $category->products_count ?? 0 }} {{ __('messages.products') }}</span>
</a>
