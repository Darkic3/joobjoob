{{-- سوالات متداول — آکاردئون سمنتیک با <details> (بدون JS) --}}
<section class="page-section reveal-on-scroll">
    <div class="max-w-3xl space-y-3" data-stagger>
        @foreach($data['items'] ?? [] as $item)
            <details class="faq-item">
                <summary>
                    <span class="text-sm md:text-[.92rem] font-bold" style="color: var(--text-primary);">{{ $item['q'] }}</span>
                    <span class="faq-plus shrink-0" aria-hidden="true">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                             stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                    </span>
                </summary>
                <div class="faq-body">
                    <div class="faq-body-inner">
                        <p class="text-sm leading-relaxed" style="color: var(--text-secondary);">{{ $item['a'] }}</p>
                    </div>
                </div>
            </details>
        @endforeach
    </div>
</section>
