@props(['badges' => []])

@php
    // کامپوننت فقط رندر می‌کند — داده را کنترلر آماده کرده و پاس می‌دهد.
    $badges = collect($badges);
@endphp

@if($badges->isNotEmpty())
    {{-- چیدمان مرکزگرا: با هر تعداد نشان (۱ تا ۶) به‌صورت متقارن وسط‌چین نمایش داده می‌شود --}}
    <div class="trust-strip flex flex-wrap justify-center gap-4">
        @foreach($badges as $i => $badge)
            {{-- هر نشان یک رنگ از پالت می‌گیرد (چرخشی) --}}
            <div class="trust-item neu-flat-sm p-5 w-[calc(50%-0.5rem)] sm:w-52 md:w-56 flex flex-col items-center text-center gap-3"
                 style="--trust-color: var(--trust-{{ ($i % 4) + 1 }});">

                {{-- آیکون در چیپ دایره‌ای رنگی --}}
                <span class="trust-icon" aria-hidden="true">{{ $badge->icon }}</span>

                {{-- عنوان (ترجمه‌شده) --}}
                <p class="text-xs font-bold leading-relaxed">{{ $badge->trans('title') }}</p>

                {{-- توضیحات (در صورت وجود) --}}
                @if($badge->trans('description'))
                    <p class="text-[11px] leading-relaxed" style="color: var(--text-secondary);">{{ $badge->trans('description') }}</p>
                @endif
            </div>
        @endforeach
    </div>
@endif
