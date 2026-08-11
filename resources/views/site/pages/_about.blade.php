{{-- داستان ما --}}
<section class="page-section reveal-on-scroll">
    <div class="max-w-3xl">
        <h2 class="page-section-title">{{ $data['story_title'] }}</h2>
        <div class="page-prose">
            @foreach($data['story'] ?? [] as $paragraph)
                <p>{{ $paragraph }}</p>
            @endforeach
        </div>
    </div>
</section>

{{-- آمار — ورود پله‌ای --}}
@if(!empty($data['stats']))
    <section class="page-section reveal-on-scroll">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6" data-stagger>
            @foreach($data['stats'] as $stat)
                <div class="neu-card p-6 text-center">
                    <div class="page-stat-value" dir="ltr">{{ $stat['value'] }}</div>
                    <div class="page-stat-label">{{ $stat['label'] }}</div>
                </div>
            @endforeach
        </div>
    </section>
@endif

{{-- ارزش‌ها — گرید نامتقارن (کارت اول تمام‌عرض) --}}
@if(!empty($data['values']))
    <section class="page-section reveal-on-scroll">
        <h2 class="page-section-title">{{ $data['values_title'] }}</h2>
        <div class="grid md:grid-cols-2 gap-5" data-stagger>
            @foreach($data['values'] as $i => $value)
                <div class="neu-card p-6 flex flex-col sm:flex-row items-start gap-4 @if($i === 0) md:col-span-2 @endif">
                    <span class="page-num shrink-0" aria-hidden="true">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    <div>
                        <h3 class="font-bold mb-1.5" style="color: var(--text-primary);">{{ $value['title'] }}</h3>
                        <p class="text-sm leading-relaxed" style="color: var(--text-secondary);">{{ $value['text'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endif
