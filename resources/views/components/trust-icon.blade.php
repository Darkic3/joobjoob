@props(['icon' => ''])

@php
    // اگر کلید در مجموعهٔ آیکون‌ها باشد SVG مربوطه رندر می‌شود؛
    // در غیر این صورت (مقادیر قدیمی مثل ✓) همان متن قبلی نمایش داده می‌شود.
    $inner = is_string($icon) ? (config('trust-icons', [])[$icon] ?? null) : null;
@endphp

@if($inner)
    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $inner !!}</svg>
@elseif($icon !== '' && $icon !== null)
    <span class="leading-none">{{ $icon }}</span>
@endif
