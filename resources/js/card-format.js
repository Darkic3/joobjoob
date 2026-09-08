/**
 * فرمت‌دهی خودکار شماره کارت بانکی
 * - هنگام تایپ: 4 رقم → خط تیره → 4 رقم → ...
 * - هنگام ارسال فرم: خط تیره‌ها حذف شده و فقط عدد ارسال می‌شود
 */

document.addEventListener('DOMContentLoaded', function () {

    // ── فرمت‌دهی خودکار روی input های شماره کارت ──
    document.querySelectorAll('input[data-card-format]').forEach(function (input) {
        input.setAttribute('inputmode', 'numeric');
        input.setAttribute('maxlength', '19'); // 16 digits + 3 dashes

        input.addEventListener('input', function () {
            var cursorPos = this.selectionStart;
            var prevLen = this.value.length;

            // فقط اعداد را نگه دار
            var digits = this.value.replace(/[^0-9]/g, '');

            // حداکثر 16 رقم
            if (digits.length > 16) {
                digits = digits.substring(0, 16);
            }

            // فرمت با خط تیره
            var formatted = digits.replace(/(\d{4})(?=\d)/g, '$1-');
            this.value = formatted;

            // اصلاح موقعیت کورسر
            var diff = this.value.length - prevLen;
            this.setSelectionRange(cursorPos + diff, cursorPos + diff);
        });

        // جلوگیری از تایپ کاراکتر غیرعددی
        input.addEventListener('keypress', function (e) {
            if (!/[0-9]/.test(e.key) && !e.ctrlKey && !e.metaKey) {
                e.preventDefault();
            }
        });

        // paste فقط عددی
        input.addEventListener('paste', function (e) {
            e.preventDefault();
            var text = (e.clipboardData || window.clipboardData).getData('text');
            var digits = text.replace(/[^0-9]/g, '').substring(0, 16);
            this.value = digits.replace(/(\d{4})(?=\d)/g, '$1-');
            this.dispatchEvent(new Event('input'));
        });
    });

    // ── حذف فرمت هنگام ارسال فرم ──
    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function () {
            form.querySelectorAll('input[data-card-format]').forEach(function (input) {
                input.value = input.value.replace(/[^0-9]/g, '');
            });
        });
    });

    // ── دکمه کپی شماره کارت ──
    window.copyCardNumber = function () {
        var btn = document.getElementById('copyCardBtn');
        var cardNum = btn ? btn.getAttribute('data-card-number') : '';

        if (!cardNum) return;

        // نسخهٔ تمیز (فقط عدد) برای کپی
        var clean = cardNum.replace(/[^0-9]/g, '');

        // disabled موقت + متن "در حال کپی..."
        if (btn) {
            btn.disabled = true;
            btn.textContent = '✂ کپی می‌شود…';
            btn.classList.add('copying');
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(clean).then(function () {
                showCopySuccess(btn, clean);
            }).catch(function () {
                // اگر clipboard API کار نکرد (مثلاً در https نبود)، fallback به execCommand
                fallbackCopy(clean, btn);
            });
        } else {
            fallbackCopy(clean, btn);
        }
    };

    function fallbackCopy(text, btn) {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        try {
            document.execCommand('copy');
            showCopySuccess(btn, text);
        } catch (e) {
            if (btn) {
                btn.textContent = '✕ کپی نشد';
                btn.classList.remove('copying');
                btn.classList.add('copy-failed');
                setTimeout(function () {
                    btn.textContent = 'کپی شماره کارت';
                    btn.classList.remove('copy-failed');
                }, 1800);
            }
        }
        document.body.removeChild(ta);
    }

    function showCopySuccess(btn, cleanText) {
        // ۱) تغییر متن دکمه به ✓ کپی شد
        if (btn) {
            btn.textContent = '✓ کپی شد';
            btn.classList.add('copied');
            btn.classList.remove('copying');

            // بازگشت به متن اصلی پس از ۱.۸ ثانیه
            setTimeout(function () {
                btn.textContent = 'کپی شماره کارت';
                btn.classList.remove('copied');
                btn.disabled = false;
            }, 1800);
        }

        // ۲) نمایش یک توastr کوچک و زیبا دقیقاً زیر دکمه
        var toast = document.createElement('div');
        toast.className = 'card-copied-toast';
        toast.textContent = 'شماره کارت کپی شد ✓';
        document.body.appendChild(toast);

        // موقعیت‌دهی: زیر دکمه
        var btnRect = btn.getBoundingClientRect();
        toast.style.top = (btnRect.bottom + 10) + 'px';
        toast.style.left = (btnRect.left + btnRect.width / 2 - 80) + 'px';
        toast.style.position = 'fixed';
        toast.style.zIndex = '9999';

        // شروع انیمیشن
        requestAnimationFrame(function () {
            toast.classList.add('card-copied-toast-enter');
        });

        // حذف پس از ۱.۶ ثانیه + پاک کردن از DOM
        setTimeout(function () {
            toast.classList.remove('card-copied-toast-enter');
            toast.classList.add('card-copied-toast-leave');
            setTimeout(function () { toast.remove(); }, 250);
        }, 1600);
    }
});

// ═══════════════════════════════════════════════════════════════════════
// CSS موقت برای انیمیشن کپی شماره کارت (درject می‌شود تا نیازی به فایل CSS جدید نباشد)
// ═══════════════════════════════════════════════════════════════════════
(function injectCardCopyStyles() {
    if (document.getElementById('card-copy-anim-style')) return;

    var style = document.createElement('style');
    style.id = 'card-copy-anim-style';
    style.textContent = [
        // دکمه کپی - حالت پیش‌فرض
        '.copy-card-btn {',
        '    transition: background-color 0.2s ease, color 0.2s ease, transform 0.15s ease;',
        '}',
        '.copy-card-btn:active {',
        '    transform: scale(0.95);',
        '}',
        // در حال کپی (idle → pressed)
        '.copy-card-btn.copying {',
        '    background-color: color-mix(in srgb, var(--accent) 25%, transparent) !important;',
        '    color: var(--accent) !important;',
        '    cursor: wait;',
        '    pointer-events: none;',
        '}',
        // کپی شد — glow سبز + کپک[
        '.copy-card-btn.copied {',
        '    background-color: color-mix(in srgb, var(--success) 20%, transparent) !important;',
        '    color: var(--success) !important;',
        '    animation: copySuccessPulse 0.6s ease;',
        '}',
        '@keyframes copySuccessPulse {',
        '    0%   { transform: scale(1);',
        '            box-shadow: 0 0 0 0 color-mix(in srgb, var(--success) 40%, transparent); }',
        '    50%  { transform: scale(1.04);',
        '            box-shadow: 0 0 0 8px color-mix(in srgb, var(--success) 0%, transparent); }',
        '    100% { transform: scale(1);',
        '            box-shadow: 0 0 0 0 color-mix(in srgb, var(--success) 0%, transparent); }',
        '}',
        //실패: erythema border + bg
        '.copy-card-btn.copy-failed {',
        '    background-color: color-mix(in srgb, var(--danger) 15%, transparent) !important;',
        '    color: var(--danger) !important;',
        '    animation: copyFailedShake 0.4s ease;',
        '}',
        '@keyframes copyFailedShake {',
        '    0%, 100% { transform: translateX(0); }',
        '    20%      { transform: translateX(-4px); }',
        '    40%      { transform: translateX(4px); }',
        '    60%      { transform: translateX(-3px); }',
        '    80%      { transform: translateX(2px); }',
        '}',
        // ═══ توastr کپی شده ═══
        '.card-copied-toast {',
        '    background: color-mix(in srgb, var(--accent) 92%, transparent);',
        '    color: var(--accent-contrast, #fff);',
        '    padding: 10px 18px;',
        '    border-radius: 12px;',
        '    font-size: 13px;',
        '    font-weight: 600;',
        '    box-shadow: 0 6px 20px rgba(0,0,0,0.18);',
        '    display: flex;',
        '    align-items: center;',
        '    gap: 6px;',
        '    opacity: 0;',
        '    transform: translateY(6px) scale(0.96);',
        '    transition: opacity 0.2s ease, transform 0.2s ease;',
        '    pointer-events: none;',
        '    will-change: transform, opacity;',
        '}',
        '.card-copied-toast::before {',
        '    content: "✓";',
        '    font-size: 15px;',
        '    line-height: 1;',
        '    color: var(--success, #34d399);',
        '}',
        '.card-copied-toast-enter {',
        '    opacity: 1;',
        '    transform: translateY(0) scale(1);',
        '}',
        '.card-copied-toast-leave {',
        '    opacity: 0;',
        '    transform: translateY(8px) scale(0.94);',
        '    transition: opacity 0.2s ease, transform 0.2s ease;',
        '}'
    ].join('\n');
    document.head.appendChild(style);
})();
