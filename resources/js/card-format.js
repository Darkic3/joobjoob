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

        if (navigator.clipboard && cardNum) {
            // ذخیره عددی خام (بدون فرمت)
            var clean = cardNum.replace(/[^0-9]/g, '');
            navigator.clipboard.writeText(clean).then(function () {
                if (btn) {
                    var orig = btn.textContent;
                    btn.textContent = '✓ کپی شد';
                    setTimeout(function () { btn.textContent = orig; }, 1500);
                }
            });
        }
    };
});
