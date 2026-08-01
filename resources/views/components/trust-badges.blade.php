@php
    // دریافت داده‌ها از کش (اگر کش وجود نداشته باشد، یک بار از دیتابیس می‌خواند و برای ۱ ساعت ذخیره می‌کند)
    // این کار باعث می‌شود حتی اگر این کامپوننت را ۱۰ بار در یک صفحه استفاده کنید، فقط ۱ کوئری به دیتابیس زده شود.
    $badges = \Illuminate\Support\Facades\Cache::remember('trust_badges_active', 3600, function () {
        return \App\Models\TrustBadge::where('is_active', true)
                                     ->orderBy('sort_order', 'asc')
                                     ->get();
    });
@endphp

@if($badges->isNotEmpty())
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($badges as $badge)
            <div class="neu-flat-sm p-5 flex flex-col items-center text-center gap-2">
                {{-- نمایش آیکون (ایموجی یا کلاس) --}}
                <span class="text-xl">{{ $badge->icon }}</span>

                {{-- نمایش عنوان --}}
                {{-- نکته: اگر در دیتابیس به جای متن فارسی، کلید ترجمه (مثل messages.trust_free_shipping) ذخیره کرده‌اید، از __($badge->title) استفاده کنید --}}
                <p class="text-xs font-bold">{{ $badge->title }}</p>

                {{-- نمایش توضیحات (در صورت وجود) --}}
                @if($badge->description)
                    <p class="text-[11px]" style="color:var(--text-secondary);">{{ $badge->description }}</p>
                @endif
            </div>
        @endforeach
    </div>
@endif
