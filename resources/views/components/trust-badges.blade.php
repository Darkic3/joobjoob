@props(['badges' => []])

{{-- داده‌ها (badges) توسط کنترلر آماده می‌شود — ویو فقط رندر است --}}
@if(count($badges))
    {{-- چیدمان مرکزگرا: با هر تعداد نشان (۱ تا ۶) به‌صورت متقارن وسط‌چین نمایش داده می‌شود --}}
    <div class="trust-strip eco-trust flex flex-wrap justify-center gap-4">
        @foreach($badges as $i => $badge)
            {{-- هر نشان یک لهجه از پالت Eco می‌گیرد (چرخشی: سیج/کلی/سند) --}}
            <div class="trust-item eco-card eco-card--interactive eco-trust-item p-5 w-[calc(50%-0.5rem)] sm:w-52 md:w-56 flex flex-col items-center text-center gap-3"
                  style="--trust-color: var(--eco-{{ ['sage','clay','sand'][($i % 3)] }});">

                {{-- آیکون در چیپ دایره‌ای رنگی --}}
                <span class="trust-icon eco-trust-icon" aria-hidden="true">{{ $badge->icon }}</span>

                {{-- عنوان (ترجمه‌شده) --}}
                <p class="text-xs font-bold leading-relaxed" style="color:var(--eco-ink);">{{ $badge->trans('title') }}</p>

                {{-- توضیحات (در صورت وجود) --}}
                @if($badge->trans('description'))
                    <p class="text-[11px] leading-relaxed" style="color: var(--eco-ink-2);">{{ $badge->trans('description') }}</p>
                @endif
            </div>
        @endforeach
    </div>
@endif
