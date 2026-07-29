@props(['category'])

<a href="{{ route('products.index', ['category' => $category->slug]) }}"
   class="neu-card neu-interactive flex flex-col items-center text-center gap-3">
    <span class="neu-icon-circle text-2xl">
        {{-- در پروژه واقعی از lucide-icons یا svg اختصاصی استفاده کنید --}}
        📦
    </span>
    <span class="font-semibold text-sm">{{ $category->name }}</span>
    <span class="text-xs" style="color: var(--text-secondary);">{{ $category->products_count ?? 0 }} {{ __('messages.products') }}</span>
</a>
