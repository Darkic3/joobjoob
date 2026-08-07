{{--
    تایمر شمارش معکوس نئومورفیک.
    Props: endsInSeconds (int) — ثانیه‌های باقی‌مانده تا پایان پیشنهاد
           id (string) — پیشوند یکتا برای شناسه‌های DOM، وقتی چند تایمر در یک صفحه هست لازم است
--}}
@props(['endsInSeconds' => 3600, 'id' => 'cd'])

@php $isPersian = in_array(app()->getLocale(), ['fa', 'ar']); @endphp

<div class="flex items-center gap-2" id="{{ $id }}-wrap">
    <div class="neu-pressed px-3 py-2 mono font-bold text-lg" id="{{ $id }}-h">{{ $isPersian ? '۰۰' : '00' }}</div>:
    <div class="neu-pressed px-3 py-2 mono font-bold text-lg" id="{{ $id }}-m">{{ $isPersian ? '۰۰' : '00' }}</div>:
    <div class="neu-pressed px-3 py-2 mono font-bold text-lg" id="{{ $id }}-s">{{ $isPersian ? '۰۰' : '00' }}</div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        startCountdown({ h: '{{ $id }}-h', m: '{{ $id }}-m', s: '{{ $id }}-s' }, {{ (int) $endsInSeconds }});
    });
</script>
