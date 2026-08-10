{{--
    تایمر شمارش معکوس نئومورفیک با برچسب.
    Props:
    - endsAt (DateTimeInterface|int|null) — زمان واقعی پایان پیشنهاد (دقیق‌تر: از روی Date.now() محاسبه می‌شود)
    - endsInSeconds (int|null) — ثانیه‌های باقی‌مانده از زمان رندر (فال‌بک قدیمی)
    - id (string) — پیشوند یکتا برای شناسه‌های DOM، وقتی چند تایمر در یک صفحه هست لازم است
--}}
@props(['endsAt' => null, 'endsInSeconds' => null, 'id' => 'cd'])

@php
    $endMs = null;
    if ($endsAt !== null) {
        if ($endsAt instanceof \DateTimeInterface) {
            $endMs = $endsAt->getTimestamp() * 1000;
        } else {
            $endMs = (int) $endsAt;
            // اگر ورودی به ثانیه بود (نه میلی‌ثانیه) به میلی‌ثانیه تبدیل کن
            if ($endMs < 100000000000) {
                $endMs *= 1000;
            }
        }
    }
@endphp

<div class="cd-wrap" id="{{ $id }}-wrap" role="timer" aria-label="{{ __('messages.deal_ends_in') }}">
    <div class="cd-unit">
        <div class="cd-chip mono" id="{{ $id }}-h">00</div>
        <span class="cd-label">{{ __('messages.hours') }}</span>
    </div>
    <span class="cd-sep" aria-hidden="true">:</span>
    <div class="cd-unit">
        <div class="cd-chip mono" id="{{ $id }}-m">00</div>
        <span class="cd-label">{{ __('messages.minutes') }}</span>
    </div>
    <span class="cd-sep" aria-hidden="true">:</span>
    <div class="cd-unit">
        <div class="cd-chip mono" id="{{ $id }}-s">00</div>
        <span class="cd-label">{{ __('messages.seconds') }}</span>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if($endMs !== null)
            startCountdownTo({ h: '{{ $id }}-h', m: '{{ $id }}-m', s: '{{ $id }}-s' }, {{ $endMs }});
        @else
            startCountdown({ h: '{{ $id }}-h', m: '{{ $id }}-m', s: '{{ $id }}-s' }, {{ (int) ($endsInSeconds ?? 3600) }});
        @endif
    });
</script>
