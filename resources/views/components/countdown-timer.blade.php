{{--
    تایمر شمارش معکوس نئومورفیک.
    Props: endsInSeconds (int) — ثانیه‌های باقی‌مانده تا پایان پیشنهاد
           id (string) — پیشوند یکتا برای شناسه‌های DOM، وقتی چند تایمر در یک صفحه هست لازم است
--}}
@props(['endsInSeconds' => 3600, 'id' => 'cd'])

<div class="flex items-center gap-2" id="{{ $id }}-wrap">
    <div class="neu-pressed px-3 py-2 mono font-bold text-lg" id="{{ $id }}-h">۰۰</div>:
    <div class="neu-pressed px-3 py-2 mono font-bold text-lg" id="{{ $id }}-m">۰۰</div>:
    <div class="neu-pressed px-3 py-2 mono font-bold text-lg" id="{{ $id }}-s">۰۰</div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        startCountdown({ h: '{{ $id }}-h', m: '{{ $id }}-m', s: '{{ $id }}-s' }, {{ (int) $endsInSeconds }});
    });
</script>
