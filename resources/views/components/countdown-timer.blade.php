{{--
    تایمر شمارش معکوس نئومورفیک با برچسب.
    Props (از کلاس CountdownTimer): endsAt | endsInSeconds | id
    محاسبهٔ زمان پایان (endMs) در کلاس انجام می‌شود.
--}}

<div class="cd-wrap" id="{{ $id }}-wrap" dir="ltr" style="direction: ltr; unicode-bidi: isolate;" role="timer" aria-label="{{ __('messages.deal_ends_in') }}">
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
        function _cdLocal(n) {
            var s = String(n).padStart(2, '0');
            if (window.localizeDigits) {
                s = window.localizeDigits.convert(s);
            }
            return s;
        }
        @if($endMs !== null)
            startCountdownTo({ h: '{{ $id }}-h', m: '{{ $id }}-m', s: '{{ $id }}-s' }, {{ $endMs }}, _cdLocal);
        @else
            startCountdown({ h: '{{ $id }}-h', m: '{{ $id }}-m', s: '{{ $id }}-s' }, {{ (int) ($endsInSeconds ?? 3600) }}, _cdLocal);
        @endif
    });
</script>
