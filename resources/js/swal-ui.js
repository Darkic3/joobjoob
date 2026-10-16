/**
 * SwalUI — رابط یکپارچهٔ دیالوگ‌ها روی SweetAlert2
 * ------------------------------------------------------------------
 * همهٔ پیام‌های تأیید/خطا/موفقیت پروژه (پنل ادمین و سایت) از اینجا
 * عبور می‌کنند تا ظاهر، لحن و رفتار همه یکسان و بدون `alert()` بومی باشد.
 *
 * استفاده در Blade:
 *   SwalUI.confirm({ title, text, danger: true }).then(ok => …)
 *   SwalUI.error('خطا', 'توضیح')
 *   SwalUI.toast.success('ذخیره شد')
 *
 * استفادهٔ اعلانی در فرم‌ها (بدون جاوااسکریپت اضافه):
 *   <form data-swal-confirm data-swal-title="…" data-swal-text="…">
 *   <button data-swal-confirm data-swal-danger="1">
 */
(function (global) {
    'use strict';

    /* ---------------------------------------------------------------------
       آیکون‌ها — SVG درون‌خطی با ضخامت یکسان برای همهٔ حالت‌ها
       --------------------------------------------------------------------- */
    var ICONS = {
        trash:
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" ' +
            'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
            '<path d="M3 6h18"/><path d="M8 6V4.5A1.5 1.5 0 0 1 9.5 3h5A1.5 1.5 0 0 1 16 4.5V6"/>' +
            '<path d="M18.5 6 17.7 19.3A2 2 0 0 1 15.7 21.2H8.3a2 2 0 0 1-2-1.9L5.5 6"/>' +
            '<path d="M10 10.5v6M14 10.5v6"/></svg>',

        check:
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" ' +
            'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
            '<path d="M20 6 9 17l-5-5"/></svg>',

        cross:
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" ' +
            'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
            '<path d="M18 6 6 18M6 6l12 12"/></svg>',

        warning:
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" ' +
            'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
            '<circle cx="12" cy="12" r="9"/><path d="M12 7.5v5.5"/><path d="M12 16.3h.01"/></svg>',

        info:
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" ' +
            'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
            '<circle cx="12" cy="12" r="9"/><path d="M12 11v5.5"/><path d="M12 7.7h.01"/></svg>',

        question:
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" ' +
            'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
            '<circle cx="12" cy="12" r="9"/><path d="M9.6 9.4a2.5 2.5 0 1 1 3.2 2.4c-.6.2-.8.7-.8 1.3v.4"/>' +
            '<path d="M12 17h.01"/></svg>',
    };

    /* نام‌های مستعار برای لحن‌های استاندارد */
    ICONS.success = ICONS.check;
    ICONS.error = ICONS.cross;

    /* نگاشت آیکون به «لحن» بصری (رنگ پاپ‌آپ و دکمه) */
    var TONE_BY_ICON = {
        warning: 'warning',
        error: 'danger',
        trash: 'danger',
        success: 'success',
        info: 'info',
        question: 'primary',
    };

    /* SweetAlert2 فقط این پنج نام را می‌پذیرد و برای بقیه هیچ آیکونی رندر نمی‌کند.
       پس آیکون‌های سفارشی‌مان را به یک نام معتبر نگاشت می‌کنیم؛
       گلیف واقعی از راه iconHtml (SVG خودمان) رندر می‌شود. */
    var SWAL_ICON_NAME = {
        trash: 'warning',
        cross: 'error',
        check: 'success',
        warning: 'warning',
        info: 'info',
        question: 'question',
        success: 'success',
        error: 'error',
    };

    /* ---------------------------------------------------------------------
       رشته‌های ترجمه — از <x-swal-ui /> تزریق می‌شوند
       --------------------------------------------------------------------- */
    var FALLBACK_I18N = {
        confirm_title: 'Are you sure?',
        confirm_text: 'This action cannot be undone.',
        confirm_button: 'Yes, continue',
        delete_title: 'Delete permanently?',
        delete_text: 'This item will be permanently deleted.',
        delete_button: 'Yes, delete',
        delete_many_title: 'Delete :count items?',
        delete_many_text: ':count selected item(s) will be permanently deleted.',
        cancel: 'Cancel',
        ok: 'OK',
        close: 'Close',
        warning_title: 'Warning',
        error_title: 'Something went wrong',
        success_title: 'Done',
        info_title: 'Heads up',
    };

    var strings = (global.SwalUI_I18N && typeof global.SwalUI_I18N === 'object') ? global.SwalUI_I18N : {};

    function t(key, replacements) {
        var value = typeof strings[key] === 'string' && strings[key] !== '' ? strings[key] : FALLBACK_I18N[key];

        if (typeof value !== 'string') {
            return key;
        }

        if (replacements) {
            Object.keys(replacements).forEach(function (name) {
                value = value.split(':' + name).join(String(replacements[name]));
            });
        }

        return value;
    }

    /* ---------------------------------------------------------------------
       ابزارهای کمکی
       --------------------------------------------------------------------- */
    function hasSwal() {
        return typeof global.Swal !== 'undefined' && typeof global.Swal.fire === 'function';
    }

    function isRtl() {
        var dir = document.documentElement.getAttribute('dir');
        return dir ? dir.toLowerCase() === 'rtl' : false;
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    /* متن ساده → HTML امن با حفظ خط‌بندی */
    function textToHtml(value) {
        return escapeHtml(value).replace(/\r?\n/g, '<br>');
    }

    /* ---------------------------------------------------------------------
       ساخت آرگومان‌های Swal.fire بر اساس تم SwalUI
       --------------------------------------------------------------------- */
    function buildOptions(opts) {
        var o = opts || {};
        var icon = o.icon || 'question';
        var danger = o.danger === true;
        var tone = o.tone || TONE_BY_ICON[icon] || 'primary';

        if (danger && !o.tone) {
            tone = 'danger';
        }

        var iconHtml = o.iconHtml !== undefined ? o.iconHtml : (icon === 'none' ? '' : ICONS[icon] || ICONS.question);
        var bodyHtml = o.html || (o.text ? textToHtml(o.text) : '');

        var args = {
            title: o.title || '',
            html: bodyHtml,
            icon: icon === 'none' ? false : (SWAL_ICON_NAME[icon] || icon),
            iconHtml: iconHtml,
            buttonsStyling: false,
            showCancelButton: !!o.showCancelButton,
            /* ترتیب دکمه‌ها همیشه «تأیید، انصراف» است؛ در حالت راست‌به‌چپ
               خودِ flex-direction این را به «تأیید سمت راست» تبدیل می‌کند. */
            reverseButtons: o.reverseButtons !== undefined ? o.reverseButtons : false,
            focusCancel: o.focusCancel !== undefined ? o.focusCancel : danger,
            allowOutsideClick: o.allowOutsideClick !== undefined ? o.allowOutsideClick : !danger,
            allowEscapeKey: o.allowEscapeKey !== undefined ? o.allowEscapeKey : !danger,
            customClass: {
                popup: 'swal-ui-popup swal-ui-popup--' + tone,
                icon: 'swal-ui-icon swal-ui-icon--' + tone,
                title: 'swal-ui-title',
                htmlContainer: 'swal-ui-text',
                actions: 'swal-ui-actions',
                confirmButton: 'swal-ui-btn swal-ui-btn--' + (o.confirmTone || (danger ? 'danger' : tone)),
                cancelButton: 'swal-ui-btn swal-ui-btn--ghost',
                denyButton: 'swal-ui-btn swal-ui-btn--ghost',
                loader: 'swal-ui-loader',
                closeButton: 'swal-ui-close',
            },
        };

        if (o.showConfirmButton !== false) {
            args.confirmButtonText = o.confirmText || t('ok');
            args.confirmButtonAriaLabel = args.confirmButtonText;
        } else {
            args.showConfirmButton = false;
        }

        if (o.showCancelButton) {
            args.cancelButtonText = o.cancelText || t('cancel');
            args.cancelButtonAriaLabel = args.cancelButtonText;
        }

        if (o.denyText) {
            args.showDenyButton = true;
            args.denyButtonText = o.denyText;
        }

        ['timer', 'timerProgressBar', 'width', 'padding', 'position', 'grow', 'draggable', 'returnFocus', 'willOpen', 'didOpen', 'didClose'].forEach(function (key) {
            if (o[key] !== undefined) {
                args[key] = o[key];
            }
        });

        return args;
    }

    /**
     * لایهٔ پایه — اگر SweetAlert2 در دسترس نبود به‌جای سکوت، به بومی برمی‌گردد.
     */
    function fire(opts) {
        if (!hasSwal()) {
            return Promise.resolve({ isConfirmed: false, isDenied: false, isDismissed: true });
        }

        return global.Swal.fire(buildOptions(opts));
    }

    /* ---------------------------------------------------------------------
     * confirm — همیشه دکمهٔ تأیید و انصراف دارد و Promise<boolean> برمی‌گرداند
     * --------------------------------------------------------------------- */
    function confirm(opts) {
        var o = typeof opts === 'string' ? { title: opts } : (opts || {});
        var danger = o.danger !== undefined ? o.danger : (o.icon === 'warning' || o.icon === 'error');
        /* برای عملیات خطرناک، آیکون سطل زباله + رنگ قرمز واضح‌ترین سیگنال بصری است */
        var icon = o.icon || (danger ? 'trash' : 'question');

        var settings = {
            title: o.title || (danger ? t('delete_title') : t('confirm_title')),
            text: o.text !== undefined ? o.text : (danger ? t('delete_text') : t('confirm_text')),
            html: o.html,
            icon: icon,
            tone: o.tone,
            danger: danger,
            confirmText: o.confirmText || (danger ? t('delete_button') : t('confirm_button')),
            cancelText: o.cancelText || t('cancel'),
            showCancelButton: o.showCancelButton !== undefined ? o.showCancelButton : true,
            showConfirmButton: o.showConfirmButton,
            allowOutsideClick: o.allowOutsideClick,
            allowEscapeKey: o.allowEscapeKey,
            focusCancel: o.focusCancel,
            width: o.width,
        };

        if (!hasSwal()) {
            var fallback = global.confirm(settings.title + (settings.text ? '\n' + settings.text : ''));
            return Promise.resolve(!!fallback);
        }

        return global.Swal.fire(buildOptions(settings)).then(function (result) {
            return !!result.isConfirmed;
        });
    }

    /* ---------------------------------------------------------------------
     * alert — جایگزین alert() بومی؛ یک دکمهٔ «باشه»
     * --------------------------------------------------------------------- */
    function alert(opts) {
        var o = typeof opts === 'string' ? { title: opts } : (opts || {});
        var icon = o.icon || 'info';
        var tone = o.tone || TONE_BY_ICON[icon] || 'primary';

        var settings = {
            title: o.title || '',
            text: o.text !== undefined ? o.text : '',
            html: o.html,
            icon: icon,
            tone: tone,
            confirmText: o.confirmText || t('ok'),
            showCancelButton: false,
            showConfirmButton: true,
            allowOutsideClick: true,
            allowEscapeKey: true,
            width: o.width,
            timer: o.timer,
            timerProgressBar: o.timerProgressBar,
        };

        if (!hasSwal()) {
            if (typeof global.alert === 'function') {
                global.alert(settings.title + (settings.text ? '\n' + settings.text : ''));
            }
            return Promise.resolve();
        }

        return global.Swal.fire(buildOptions(settings));
    }

    function message(icon, title, text, extra) {
        return alert(Object.assign({ icon: icon, title: title, text: text }, extra || {}));
    }

    var success = function (title, text, extra) { return message('success', title || t('success_title'), text, extra); };
    var error = function (title, text, extra) { return message('error', title || t('error_title'), text, extra); };
    var warning = function (title, text, extra) { return message('warning', title || t('warning_title'), text, extra); };
    var info = function (title, text, extra) { return message('info', title || t('info_title'), text, extra); };

    /**
     * نمایش لیست خطاهای اعتبارسنجی در یک دیالوگ مرکزچین
     */
    function errors(title, list) {
        var items = (list || []).filter(function (m) { return !!m; });

        if (!items.length) {
            return Promise.resolve();
        }

        var html = '<ul>' + items.map(function (m) { return '<li>' + escapeHtml(m) + '</li>'; }).join('') + '</ul>';

        return alert({ icon: 'error', title: title || t('error_title'), html: html });
    }

    /* ---------------------------------------------------------------------
     * toast — پیام گذرا، خودکار-محو، بدون قفل‌کردن صفحه
     * مستقل از SweetAlert2 پیاده شده تا چیدمان کاملاً تحت کنترل باشد.
     * --------------------------------------------------------------------- */
    var TOAST_MAX_VISIBLE = 3;
    var toastStack = null;

    function toastStackEl() {
        if (toastStack && document.body.contains(toastStack)) {
            return toastStack;
        }

        toastStack = document.querySelector('[data-swal-ui-toasts]');

        if (!toastStack) {
            toastStack = document.createElement('div');
            toastStack.className = 'swal-ui-toasts';
            toastStack.setAttribute('data-swal-ui-toasts', '');
            document.body.appendChild(toastStack);
        }

        return toastStack;
    }

    function dismissToast(el) {
        if (!el || el.dataset.swalUiLeaving === '1') {
            return;
        }

        el.dataset.swalUiLeaving = '1';
        el.classList.add('swal-ui-toast--leaving');

        window.setTimeout(function () {
            if (el.parentNode) {
                el.parentNode.removeChild(el);
            }
        }, 220);
    }

    function toast(opts) {
        var o = typeof opts === 'string' ? { title: opts } : (opts || {});
        var icon = o.icon || 'info';
        var tone = o.tone || TONE_BY_ICON[icon] || 'primary';
        var duration = o.timer !== undefined ? o.timer : 2800;

        var stack = toastStackEl();
        var el = document.createElement('div');

        el.className = 'swal-ui-toast swal-ui-toast--' + tone;
        el.setAttribute('role', o.role || 'status');
        el.setAttribute('aria-live', tone === 'danger' ? 'assertive' : 'polite');

        el.innerHTML =
            '<span class="swal-ui-toast__icon" aria-hidden="true">' + (o.iconHtml || ICONS[icon] || ICONS.info) + '</span>' +
            '<span class="swal-ui-toast__body">' +
            (o.title ? '<strong class="swal-ui-toast__title">' + escapeHtml(o.title) + '</strong>' : '') +
            (o.text ? '<span class="swal-ui-toast__text">' + textToHtml(o.text) + '</span>' : '') +
            '</span>' +
            '<button type="button" class="swal-ui-toast__close" aria-label="' + escapeHtml(t('close')) + '">&times;</button>' +
            (o.timerProgressBar === false ? '' : '<span class="swal-ui-toast__bar" style="animation-duration:' + duration + 'ms"></span>');

        el.querySelector('.swal-ui-toast__close').addEventListener('click', function () {
            dismissToast(el);
        });

        stack.appendChild(el);

        /* حداکثر چند پیام هم‌زمان */
        var items = stack.querySelectorAll('.swal-ui-toast:not([data-swal-ui-leaving="1"])');
        for (var i = 0; i < items.length - TOAST_MAX_VISIBLE; i++) {
            dismissToast(items[i]);
        }

        var timer = duration > 0 ? window.setTimeout(function () { dismissToast(el); }, duration) : null;

        el.addEventListener('mouseenter', function () {
            if (timer) {
                window.clearTimeout(timer);
                timer = null;
            }

            var bar = el.querySelector('.swal-ui-toast__bar');
            if (bar) {
                bar.style.animationPlayState = 'paused';
            }
        });

        el.addEventListener('mouseleave', function () {
            var bar = el.querySelector('.swal-ui-toast__bar');
            if (bar) {
                bar.style.animationPlayState = 'running';
            }

            if (!timer) {
                timer = window.setTimeout(function () { dismissToast(el); }, 1200);
            }
        });

        return Promise.resolve(el);
    }

    var toastApi = {
        success: function (title, text, extra) { return toast(Object.assign({ icon: 'success', title: title, text: text }, extra || {})); },
        error: function (title, text, extra) { return toast(Object.assign({ icon: 'error', title: title, text: text }, extra || {})); },
        warning: function (title, text, extra) { return toast(Object.assign({ icon: 'warning', title: title, text: text }, extra || {})); },
        info: function (title, text, extra) { return toast(Object.assign({ icon: 'info', title: title, text: text }, extra || {})); },
    };

    /* ---------------------------------------------------------------------
     * تشخیص خودکار «عملیات خطرناک» از روی فرم
     * --------------------------------------------------------------------- */
    function isDangerousForm(form) {
        if (!form) {
            return false;
        }

        var action = form.getAttribute('action') || '';

        var methodInput = form.querySelector('input[name="_method"]');
        var method = methodInput
            ? String(methodInput.value || '').toLowerCase()
            : String(form.getAttribute('method') || 'get').toLowerCase();

        if (method === 'delete') {
            return true;
        }

        if (method !== 'post') {
            return false;
        }

        return /delete|destroy|remove|purge|bulk-resolve/i.test(action);
    }

    /* ---------------------------------------------------------------------
     * خواندن تنظیمات دیالوگ از data-attribute ها
     *
     *   data-swal-confirm        → فعال‌کردن تأیید (بدون مقدار)
     *   data-swal-confirm-text   → متن دکمهٔ تأیید
     *   data-swal-cancel-text    → متن دکمهٔ انصراف
     *   data-swal-title / -text / -html / -icon / -danger / -width
     *   data-swal-count=".sel"   → پرکردن زندهٔ :count در متن‌ها
     * --------------------------------------------------------------------- */
    function readOptions(el, form) {
        var data = el.dataset || {};
        var danger = data.swalDanger !== undefined
            ? data.swalDanger !== 'false' && data.swalDanger !== '0'
            : isDangerousForm(form);

        var replacements = null;

        /* شمارندهٔ پویا: data-swal-count=".product-checkbox" → «:count» را پر می‌کند.
           اگر انتخاب‌گر در فرم پیدا نشد (مثلاً با form="…" به فرم وصل شده) کل سند جست‌وجو می‌شود. */
        if (data.swalCount) {
            var matched = form ? form.querySelectorAll(data.swalCount) : [];

            if (!matched.length) {
                matched = document.querySelectorAll(data.swalCount);
            }

            var count = 0;

            for (var i = 0; i < matched.length; i++) {
                if (matched[i].matches(':checked')) {
                    count++;
                }
            }

            replacements = { count: count };
        }

        var many = replacements && parseInt(replacements.count, 10) > 1;

        return {
            title: fill(data.swalTitle, replacements) || (many ? t('delete_many_title', replacements) : (danger ? t('delete_title') : t('confirm_title'))),
            text: fill(data.swalText, replacements) || (many ? t('delete_many_text', replacements) : (danger ? t('delete_text') : t('confirm_text'))),
            icon: data.swalIcon || (danger ? 'trash' : 'question'),
            danger: danger,
            confirmText: fill(data.swalConfirmText, replacements) || (danger ? t('delete_button') : t('confirm_button')),
            cancelText: data.swalCancelText || t('cancel'),
            html: data.swalHtml || undefined,
            width: data.swalWidth || undefined,
        };
    }

    /* جایگزینی `:count` در رشته‌های ترجمه‌شدهٔ رندرشده در Blade */
    function fill(value, replacements) {
        if (!value) {
            return '';
        }

        if (!replacements) {
            return value;
        }

        return value.replace(/:count/g, String(replacements.count));
    }

    /* ---------------------------------------------------------------------
     * ارسال فرم پس از تأیید — با حفظ بقیهٔ شنونده‌های submit
     * --------------------------------------------------------------------- */
    function submitForm(form, submitter) {
        if (!form) {
            return;
        }

        form.dataset.swalApproved = '1';

        if (typeof form.requestSubmit === 'function') {
            try {
                form.requestSubmit(submitter || undefined);
                return;
            } catch (e) {
                /* مرورگرهای قدیمی ممکن است submitter نپذیرند */
            }
        }

        delete form.dataset.swalApproved;
        form.submit();
    }

    var CONFIRM_SELECTOR = '[data-swal-confirm], .delete-form';
    var confirmPending = false;

    function bindDelegates() {
        if (bindDelegates._done) {
            return;
        }
        bindDelegates._done = true;

        /* فرم‌ها: تأیید قبل از ارسال */
        document.addEventListener('submit', function (event) {
            var form = event.target;

            if (!form || form.nodeName !== 'FORM') {
                return;
            }

            if (form.dataset.swalApproved === '1') {
                delete form.dataset.swalApproved;
                return;
            }

            var submitter = event.submitter || null;
            var source = null;

            if (form.matches(CONFIRM_SELECTOR)) {
                source = form;
            } else if (submitter && submitter.closest(CONFIRM_SELECTOR)) {
                source = submitter.closest(CONFIRM_SELECTOR);
            }

            if (!source) {
                return;
            }

            event.preventDefault();

            /* کلیک دوباره روی دکمه نباید دیالوگ دومی بسازد */
            if (confirmPending) {
                return;
            }

            confirmPending = true;

            var submitterRef = submitter;

            confirm(readOptions(source, form)).then(function (ok) {
                confirmPending = false;

                if (ok) {
                    submitForm(form, submitterRef);
                }
            });
        }, true);

        /* لینک‌ها: تأیید قبل از رفتن به مقصد */
        document.addEventListener('click', function (event) {
            var link = event.target.closest ? event.target.closest('a[data-swal-confirm]') : null;

            if (!link || !link.getAttribute('href') || confirmPending) {
                return;
            }

            event.preventDefault();

            var href = link.getAttribute('href');

            confirmPending = true;

            confirm(readOptions(link, null)).then(function (ok) {
                confirmPending = false;

                if (ok) {
                    global.location.href = href;
                }
            });
        }, true);
    }

    /* ---------------------------------------------------------------------
     * API عمومی
     * --------------------------------------------------------------------- */
    global.SwalUI = {
        t: t,
        isRtl: isRtl,
        hasSwal: hasSwal,
        fire: fire,
        confirm: confirm,
        alert: alert,
        errors: errors,
        success: success,
        error: error,
        warning: warning,
        info: info,
        toast: toastApi,
        isDangerousForm: isDangerousForm,
        submitForm: submitForm,
        bindDelegates: bindDelegates,
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindDelegates);
    } else {
        bindDelegates();
    }
})(window);