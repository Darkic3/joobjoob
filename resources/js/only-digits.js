/**
 * محدودسازی سراسری ورودی‌های تلفنی و عددی (کل سایت)
 * ------------------------------------------------------------------
 * تنها یک منبع برای تمام اینپوت‌هایی که فقط عدد قبول می‌کنند:
 *
 *   input[type="tel"]        → شماره موبایل / تلفن
 *   input[inputmode="tel"]   → شماره موبایل (کیبورد تلفنی)
 *   input[inputmode="numeric"] → کد تأیید، تعداد، کد رهگیری و…
 *
 * رفتار:
 *   - هر کاراکتر غیرعددی (حرف، فاصله، خط تیره و…) هنگام تایپ یا paste حذف می‌شود؛
 *     در نتیجه در فیلد شماره موبایل فقط عدد باقی می‌ماند.
 *   - ارقام فارسی (۰-۹) و عربی (٠-٩) خودکار به ارقام لاتین (0-9) تبدیل می‌شوند تا
 *     اعتبارسنجی سمت سرور/کلاینت یکسان بماند (نمونه: ۰۹۱۲۳۴۵۶۷۸۹ → 09123456789).
 *   - با رویدادهای «capture» روی document اجرا می‌شود تا قبل از هر خوانندهٔ دیگر
 *     (مثل Alpine x-model) مقدارِ فیلد تمیز باشد و المان‌های داینامیک هم پوشش داده شوند.
 *
 * مستثنی‌ها:
 *   - فیلدهای readonly / disabled
 *   - فیلدهای شماره کارت (data-card-format) — فرمت‌دهی خودکار در card-format.js است
 *   - فیلدهای type="number" — خود مرورگر فقط عدد می‌پذیرد (و ممکن است اعشار بخواهد)
 */

(function () {
    'use strict';

    // فقط ورودی‌هایی که قرار است «فقط رقم» داشته باشند.
    function isDigitOnlyField(el) {
        if (el.tagName !== 'INPUT') return false;
        if (el.readOnly || el.disabled) return false;
        if (el.hasAttribute('data-card-format')) return false;

        var type = (el.getAttribute('type') || '').toLowerCase();
        var inputmode = (el.getAttribute('inputmode') || '').toLowerCase();
        return type === 'tel' || inputmode === 'tel' || inputmode === 'numeric';
    }

    // هر رقم از هر خانوادهٔ ارقام را به رقم لاتین تبدیل و بقیهٔ کاراکترها را حذف می‌کند.
    function keepDigitsOnly(value) {
        if (!value) return '';
        var out = '';
        for (var i = 0; i < value.length; i++) {
            var code = value.charCodeAt(i);
            if (code >= 48 && code <= 57) {          // 0-9 لاتین
                out += value.charAt(i);
            } else if (code >= 1632 && code <= 1641) { // ٠-٩ عربی
                out += String(code - 1632);
            } else if (code >= 1776 && code <= 1785) { // ۰-۹ فارسی
                out += String(code - 1776);
            }
            // هر چیز دیگری (حرف، فاصله، خط تیره، + و…) دور ریخته می‌شود
        }
        return out;
    }

    document.addEventListener('input', function (e) {
        var el = e.target;
        if (!el || !el.getAttribute || !isDigitOnlyField(el)) return;

        var start = el.selectionStart;
        var before = el.value;
        var after = keepDigitsOnly(before);
        if (after === before) return;

        el.value = after;
        // مکان نما را سر جایش نگه دار (اگر کاراکتر پاک‌شده جلوی مکان‌نما بود)
        if (typeof start === 'number') {
            var pos = Math.min(start, after.length);
            try {
                el.setSelectionRange(pos, pos);
            } catch (_) { /* برخی مرورگرها (مثلاً نوع خاص) اجازه نمی‌دهند */ }
        }
    }, true); // capture → قبل از Alpine و هندلرهای دیگر صفحه
})();
