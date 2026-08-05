/**
 * بومی‌سازی خودکار ارقام بر اساس زبان سایت
 * ------------------------------------------------------------
 * زبان‌های فارسی و عربی  →  ارقام فارسی (۰۱۲۳۴۵۶۷۸۹)
 * سایر زبان‌ها (en, tr, …)  →  ارقام انگلیسی (+ تبدیل ٪ به %)
 *
 * - روی تمام متن‌های قابل مشاهده‌ی صفحه اعمال می‌شود: قیمت، تعداد،
 *   موجودی، تاریخ، درصد، امتیاز، تایمر، بج سبد خرید، صفحه‌بندی و…
 * - به مقادیر فرم‌ها (input / select / textarea) و کدها
 *   (script / style / pre / code) دست نمی‌زند تا داده‌های ارسالی سالم بمانند.
 * - MutationObserver برای محتوای داینامیک (تایمر شمارش معکوس، تغییر قیمت
 *   واریانت، بج سبد خرید، toast و…) در هر دو جهت زبان کار می‌کند.
 */
(function () {
    'use strict';

    var FA = '۰۱۲۳۴۵۶۷۸۹';   // ارقام فارسی
    var AR = '٠١٢٣٤٥٦٧٨٩';   // ارقام عربی استاندارد
    var ALL = FA + AR;
    var ASCII_RE = /[0-9]/g;
    var FA_AR_RE = /[۰-۹٠-٩]/g;
    var PERCENT_RE = /٪/g;
    var COMMA_RE = /,/g;
    var PERSIAN_COMMA_RE = /٬/g;

    function isPersianLocale() {
        var lang = (document.documentElement.getAttribute('lang') || 'fa').toLowerCase();
        return lang === 'fa' || lang === 'ar';
    }

    function convertText(str) {
        if (isPersianLocale()) {
            // ارقام فارسی + جداکننده‌ی هزارگان استاندارد فارسی (٬)
            return str
                .replace(ASCII_RE, function (d) { return FA[+d]; })
                .replace(COMMA_RE, '٬');
        }
        return str
            .replace(FA_AR_RE, function (ch) {
                var i = ALL.indexOf(ch);
                return String(i < 10 ? i : i - 10);
            })
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

    function start() {
        walk(document.body);

        var timer = null;
        new MutationObserver(function (mutations) {
            for (var i = 0; i < mutations.length; i++) {
                var m = mutations[i];
                if (m.type === 'characterData') {
                    // به‌روزرسانی متنِ یک گره موجود (تایمر، بج سبد، قیمت واریانت…)
                    if (skipTextNode(m.target)) continue;
                    var v = m.target.nodeValue;
                    if (!/[0-9۰-۹٠-٩٪]/.test(v)) continue;
                    var c = convertText(v);
                    // فقط اگر واقعاً تغییر کرده بنویس تا حلقه‌ی بینهایت نشود
                    if (c !== v) m.target.nodeValue = c;
                } else if (m.type === 'childList' && m.addedNodes.length) {
                    // محتوای جدید اضافه شده — با debounce کل صفحه یک‌بار مرور شود
                    if (!timer) {
                        timer = setTimeout(function () {
                            timer = null;
                            walk(document.body);
                        }, 300);
                    }
                    break;
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
    window.localizeDigits = { convert: convertText, isPersian: isPersianLocale, walk: walk };
})();
