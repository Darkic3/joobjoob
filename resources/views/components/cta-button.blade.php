@props([
    'href' => null,
    'variant' => 'primary',   // primary | secondary | brutalist
    'icon' => true,           // فلش در دایرهٔ داخلی
    'emphasis' => null,       // شدت حلقه (پیش‌فرض: primary=1, secondary=.75)
    'duration' => null,       // چرخهٔ پالس (ثانیه)
])

{{-- دکمهٔ CTA با حلقهٔ «بازهٔ اعتماد» — معماری Button-in-Button، قابل استفاده در هر جای سایت --}}
{{-- واریانت brutalist: دکمهٔ مربعی با بوردر جوهری و سایهٔ هارد (هیرو بروتالیست) --}}
@php
    $style = '';
    if ($emphasis !== null) { $style .= "--ring-emphasis: {$emphasis};"; }
    if ($duration !== null) { $style .= "--ring-duration: {$duration};"; }

    if ($variant === 'brutalist') {
        $defaults = 'hero-brutal-btn font-bold text-sm group';
    } else {
        $isPrimary = $variant === 'primary';
        $defaults = 'neu-btn cta-ring cta-ring--' . $variant . ' font-bold text-sm group'
            . ($isPrimary ? ' neu-btn-primary' : '')
            . ($href ? ' shadow-lg hover:shadow-xl transition-all' : '');
    }
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $defaults]) }}@if($style) style="{{ $style }}"@endif>
@else
    <button type="button" {{ $attributes->merge(['class' => $defaults]) }}@if($style) style="{{ $style }}"@endif>
@endif
    <span class="cta-ring-label">{{ $slot }}</span>
    @if ($icon)
        <span class="cta-ring-icon" aria-hidden="true">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m11 6-6 6 6 6"/></svg>
        </span>
    @endif
@if ($href)
    </a>
@else
    </button>
@endif
