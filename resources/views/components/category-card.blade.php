{{--
    کارت دسته‌بندی — ابعاد ثابت (۷×۷ ریم) برای همه، صرف‌نظر از طول اسم دسته.
    اسم طولانی به‌جای شکستن به دو خط و بلندکردن باکس، با ellipsis کوتاه می‌شود
    تا همه‌ی باکس‌ها همیشه دقیقاً هم‌قد و هم‌عرض بمانند.
--}}
@props(['category'])

<a href="{{ $category['url'] ?? route('products.index') }}"
   class="neu-card neu-interactive flex flex-col items-center justify-center gap-2 text-center w-28 h-28 shrink-0">
    <span class="neu-icon-circle">{{ $category['icon'] }}</span>
    <span class="text-xs font-semibold leading-tight line-clamp-1 px-1">{{ $category['name'] }}</span>
</a>
