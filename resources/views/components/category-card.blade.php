@props(['category'])

<a href="{{ $category['url'] ?? route('products.index') }}"
   class="neu-card neu-interactive shrink-0 w-28 flex flex-col items-center justify-center gap-2 py-5 px-3 text-center">
    <span class="neu-icon-circle w-14 h-14 grid place-items-center text-xl">{{ $category['icon'] }}</span>
    <span class="text-xs font-semibold leading-tight line-clamp-2">{{ $category['name'] }}</span>
</a>
