/**
 * بومی‌سازی خودکار ارقام بر اساس زبان سایت
 * ------------------------------------------------------------
 * منبع واحد: صفت `data-digits` روی <html> که از سمت سرور بر اساس
 * LocaleService::digitFamily() تنظیم می‌شود:
 *   persian → ۰۱۲۳۴۵۶۷۸۹ + جداکننده‌ی هزارگان ٬
 *   arabic  → ٠١٢٣٤٥٦٧٨٩ + جداکننده‌ی هزارگان ٬
 *   latin   → 0123456789 + جداکننده‌ی ، و تبدیل ٪ → %
 * (fallback قدیمی: اگر data-digits نبود، از lang نتیجه می‌گیریم)
 *
 * - روی تمام متن‌های قابل مشاهده‌ی صفحه اعمال می‌شود: قیمت، تعداد،
 *   موجودی، تاریخ، درصد، امتیاز، تایمر، بج سبد خرید، صفحه‌بندی و…
 * - به مقادیر فرم‌ها (input / select / textarea) و کدها
 *   (script / style / pre / code) دست نمی‌زند تا داده‌های ارسالی سالم بمانند.
 * - از نسخهٔ ۲ معماری دو لایه است:
 *     ۱) سمت سرور: میدل‌ور LocalizeHtmlDigits کلِ HTML را قبل از اولین paint
 *        بومی می‌کند (هیچ چشمکی برای محتوای استاتیک باقی نمی‌ماند).
 *     ۲) این فایل فقط برای محتوای داینامیکِ ساخته‌شده توسط JS است و گره‌های
 *        تازه‌اضافه‌شده را در همان میکروتسکِ درج (پیش از paintِ همان فریم)
 *        تبدیل می‌کند — بدون debounce و بدون مرور کل صفحه.
 * - MutationObserver برای محتوای داینامیک (تایمر شمارش معکوس، تغییر قیمت
 *   واریانت، بج سبد خرید، toast، اعلان‌ها و…) در هر دو جهت زبان کار می‌کند.
 */
(function () {
    'use strict';

    var FA = '۰۱۲۳۴۵۶۷۸۹';   // ارقام فارسی
    var AR = '٠١٢٣٤٥٦٧٨٩';   // ارقام عربی استاندارد
    var FA_RE = /[۰-۹]/g;
    var AR_RE = /[٠-٩]/g;
    var ASCII_RE = /[0-9]/g;
    var PERCENT_RE = /٪/g;
    var COMMA_RE = /,/g;
    var PERSIAN_COMMA_RE = /٬/g;

    /**
     * خانواده‌ی ارقام فعلی: persian | arabic | latin
     * اولویت: data-digits (منبع واحد از سرور) → تشخیص از lang
     */
    function digitFamily() {
        var el = document.documentElement;
        var attr = el && el.getAttribute('data-digits');
        if (attr === 'persian' || attr === 'arabic' || attr === 'latin') {
            return attr;
        }
        var lang = ((el && el.getAttribute('lang')) || 'fa').toLowerCase();
        if (lang === 'fa') return 'persian';
        if (lang === 'ar') return 'arabic';
        return 'latin';
    }

    function isPersianLocale() {
        return digitFamily() !== 'latin';
    }

    function convertText(str) {
        var fam = digitFamily();

        if (fam === 'persian') {
            return str
                .replace(ASCII_RE, function (d) { return FA[+d]; })
                .replace(COMMA_RE, '٬');
        }
        if (fam === 'arabic') {
            return str
                .replace(ASCII_RE, function (d) { return AR[+d]; })
                .replace(COMMA_RE, '٬');
        }
        return str
            .replace(FA_RE, function (ch) { return String(FA.indexOf(ch)); })
            .replace(AR_RE, function (ch) { return String(AR.indexOf(ch)); })
            .replace(PERCENT_RE, '%')
            .replace(PERSIAN_COMMA_RE, ',');
    }

    function skipElement(el) {
        if (!el) return true;
        var tag = el.tagName;
        return tag === 'SCRIPT' || tag === 'STYLE' || tag === 'TEXTAREA' ||
               tag === 'PRE' || tag === 'CODE' || tag === 'NOSCRIPT';
    }

    function skipTextNode(node) {
        var p = node.parentElement;
        if (skipElement(p)) return true;
        // داخل فیلدهای فرم یا المان‌های قابل ادیت دست نمی‌زنیم
        if (p.closest('input, select, option, [contenteditable="true"], [data-digit-raw]')) return true;
        // اعداد لاتین هیرو (hero-num) همیشه لاتین می‌مانند
        if (p.closest('.hero-num')) return true;
        return false;
    }

    function walk(root) {
        var walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
            acceptNode: function (node) {
                if (skipTextNode(node)) return NodeFilter.FILTER_REJECT;
                return /[0-9۰-۹٠-٩٪]/.test(node.nodeValue)
                    ? NodeFilter.FILTER_ACCEPT
                    : NodeFilter.FILTER_REJECT;
            }
        });
        var nodes = [];
        while (walker.nextNode()) nodes.push(walker.currentNode);
        for (var i = 0; i < nodes.length; i++) {
            nodes[i].nodeValue = convertText(nodes[i].nodeValue);
        }
    }

    function convertTextNode(node) {
        if (skipTextNode(node)) return;
        var v = node.nodeValue;
        if (!/[0-9۰-۹٠-٩٪]/.test(v)) return;
        var c = convertText(v);
        // فقط اگر واقعاً تغییر کرده بنویس تا حلقه‌ی بی‌نهایت نشود
        if (c !== v) node.nodeValue = c;
    }

    function start() {
        walk(document.body);

        new MutationObserver(function (mutations) {
            for (var i = 0; i < mutations.length; i++) {
                var m = mutations[i];
                if (m.type === 'characterData') {
                    // به‌روزرسانی متنِ یک گره موجود (تایمر، بج سبد، قیمت واریانت…)
                    convertTextNode(m.target);
                } else if (m.type === 'childList') {
                    // محتوای جدید اضافه‌شده — همین‌حالا تبدیلش کن: کال‌بکِ MutationObserver
                    // در میکروتسکِ همان‌تسکِ درج اجرا می‌شود، یعنی پیش از render/paintِ
                    // همان فریم؛ پس رقمِ لاتین حتی یک فریم دیده نمی‌شود. دیگر debounceِ
                    // ۳۰۰ms و مرورِ کلِ صفحه لازم نیست.
                    for (var j = 0; j < m.addedNodes.length; j++) {
                        var node = m.addedNodes[j];
                        if (node.nodeType === Node.TEXT_NODE) {
                            convertTextNode(node);
                        } else if (node.nodeType === Node.ELEMENT_NODE) {
                            walk(node);
                        }
                    }
                }
            }
        }).observe(document.body, { childList: true, subtree: true, characterData: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }

    // در دسترس برای سایر اسکریپت‌ها (قبل از set کردن textContent)
    window.localizeDigits = { convert: convertText, isPersian: isPersianLocale, walk: walk, digitFamily: digitFamily };
})();
