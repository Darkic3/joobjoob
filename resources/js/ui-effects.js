// بومی‌سازی ارقام بر اساس زبان سایت (fa/ar → فارسی، سایر → انگلیسی)
import './localize-digits.js';

document.addEventListener('DOMContentLoaded', function () {

    // ---------- تاگل تم: فقط data-theme را عوض می‌کنیم، بقیه (موقعیت دات) با CSS خودکار است ----------
    const root = document.documentElement;
    const toggle = document.getElementById('theme-toggle');

    if (toggle) {
        toggle.addEventListener('click', function () {
            const next = root.getAttribute('data-theme') === 'dark-neumorphism' ? 'light-neumorphism' : 'dark-neumorphism';
            root.setAttribute('data-theme', next);
            localStorage.setItem('site_theme', next);
        });
    }

    // ---------- دکمهٔ افزودن به سبد: idle -> loading -> success (هندلر اصلی پایین است) ----------

    // ---------- toast اثبات اجتماعی ----------
    const toastEl = document.getElementById('social-toast');
    if (toastEl) {
        const nameEl = document.getElementById('toast-name');
        const msgEl = document.getElementById('toast-msg');
        const data = JSON.parse(toastEl.getAttribute('data-items') || '[]');
        let i = 0;
        function showToast() {
            if (!data.length) return;
            nameEl.textContent = data[i][0];
            msgEl.textContent = data[i][1];
            toastEl.classList.remove('hidden', 'toast-enter');
            void toastEl.offsetWidth;
            toastEl.classList.add('toast-enter');
            i = (i + 1) % data.length;
            setTimeout(function () { toastEl.classList.add('hidden'); }, 5000);
        }
        setTimeout(showToast, 3000);
        setInterval(showToast, 12000);
    }

    // ---------- نوار اعلان چرخشی ----------
    const announceEl = document.getElementById('announce-text');
    if (announceEl) {
        const messages = JSON.parse(announceEl.getAttribute('data-messages') || '[]');
        let a = 0;
        announceEl.style.transition = 'opacity .3s ease';
        setInterval(function () {
            a = (a + 1) % messages.length;
            announceEl.style.opacity = 0;
            setTimeout(function () { announceEl.textContent = messages[a]; announceEl.style.opacity = 1; }, 300);
        }, 4000);
    }

    // ---------- دراپ‌داون زبان ----------
    const langBtn = document.querySelector('[data-lang-dropdown-btn]');
    const langMenu = document.querySelector('[data-lang-dropdown-menu]');
    if (langBtn && langMenu) {
        langBtn.addEventListener('click', () => langMenu.classList.toggle('hidden'));
        document.addEventListener('click', function (e) {
            if (!langBtn.contains(e.target) && !langMenu.contains(e.target)) langMenu.classList.add('hidden');
        });
    }
    document.querySelectorAll('[data-lang-select]').forEach(function (el) {
        el.addEventListener('click', function () {
            const url = new URL(window.location.href);
            url.searchParams.set('lang', el.getAttribute('data-lang-select'));
            window.location.href = url.toString();
        });
    });

    document.querySelectorAll('[data-brand-chip]').forEach(function (chip) {
        chip.addEventListener('click', function () {
            document.querySelectorAll('[data-brand-chip]').forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
        });
    });
});

// توابع تایمر روی window قرار می‌گیرند تا اسکریپت‌های inline داخل Blade (کامپوننت countdown-timer)
// بتوانند آن‌ها را صدا بزنند — وگرنه Vite/Rollup آن‌ها را به‌عنوان کد استفاده‌نشده حذف می‌کند (tree-shake).
window.startCountdown = function (elIds, totalSeconds) {
    // ارقام بر اساس زبان سایت: فارسی/عربی → ۰۱۲۳، سایر → 0123
    function toLocal(n) {
        var s = String(n).padStart(2, '0');
        if (window.localizeDigits && window.localizeDigits.isPersian()) {
            s = s.replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
        }
        return s;
    }
    function tick() {
        totalSeconds = totalSeconds > 0 ? totalSeconds - 1 : 0;
        if (elIds.h) document.getElementById(elIds.h).textContent = toLocal(Math.floor(totalSeconds / 3600));
        if (elIds.m) document.getElementById(elIds.m).textContent = toLocal(Math.floor((totalSeconds % 3600) / 60));
        if (elIds.s) document.getElementById(elIds.s).textContent = toLocal(totalSeconds % 60);
    }
    tick();
    return setInterval(tick, 1000);
};

// تایمر دقیق: زمان پایان (میلی‌ثانیه) را می‌گیرد و هر ثانیه از روی Date.now() محاسبه می‌کند —
// اگر صفحه مدت‌ها باز بماند هم دقیق می‌ماند و بعد از پایان روی ۰۰:۰۰:۰۰ می‌ایستد.
window.startCountdownTo = function (elIds, endMs) {
    // ارقام بر اساس زبان سایت: فارسی/عربی → ۰۱۲۳، سایر → 0123
    function toLocal(n) {
        var s = String(n).padStart(2, '0');
        if (window.localizeDigits && window.localizeDigits.isPersian()) {
            s = s.replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
        }
        return s;
    }
    function tick() {
        var remaining = Math.max(0, Math.floor((endMs - Date.now()) / 1000));
        if (elIds.h) document.getElementById(elIds.h).textContent = toLocal(Math.floor(remaining / 3600));
        if (elIds.m) document.getElementById(elIds.m).textContent = toLocal(Math.floor((remaining % 3600) / 60));
        if (elIds.s) document.getElementById(elIds.s).textContent = toLocal(remaining % 60);
    }
    tick();
    return setInterval(tick, 1000);
};


// ---------- دکمهٔ افزودن به سبد: idle -> loading -> success ----------
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const originalText = this.innerHTML;
            const successText = this.dataset.success || '✓ اضافه شد';

            // حالت لودینگ
            this.innerHTML = '<span class="btn-spinner"></span>';
            this.disabled = true;

            // شبیه‌سازی درخواست — در پروژهٔ واقعی به‌جای setTimeout از fetch(url, {method: 'POST'})
            setTimeout(() => {
                this.innerHTML = successText;
                this.classList.add('neu-btn-success');

                // شمارندهٔ سبد در هدر (اگر وجود داشته باشد)
                const badge = document.getElementById('cart-count');
                if (badge) {
                    badge.textContent = (parseInt(badge.textContent || '0') + 1);
                    badge.classList.remove('count-pop');
                    void badge.offsetWidth;
                    badge.classList.add('count-pop');
                }

                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.classList.remove('neu-btn-success');
                    this.disabled = false;
                }, 1500);
            }, 800);
        });
    });
});


// ---------- اسکرول افقی با فلش (محصولات ویژه / پرفروش‌ترین‌ها) ----------
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('[data-hscroll]').forEach(function (group) {
        const rail = group.querySelector('.hscroll-rail');
        if (!rail) return;
        const prev = group.querySelector('[data-hscroll-prev]');
        const next = group.querySelector('[data-hscroll-next]');

        // تشخیص قرارداد اسکرول RTL روی خودِ ریل (نه یک عنصر آزمایشی جداگانه):
        // Chrome/Firefox مقدار scrollLeft منفی دارند (افزایش +۱ به صفر clamp می‌شود)،
        // Safari مقدار مثبت دارد (+۱ واقعاً اعمال می‌شود).
        const isRtl = getComputedStyle(rail).direction === 'rtl';
        const rtlNegative = isRtl && (function () {
            const was = rail.scrollLeft;
            rail.scrollLeft = was + 1;
            const negative = rail.scrollLeft === was;
            rail.scrollLeft = was;
            return negative;
        })();

        function maxScroll() { return rail.scrollWidth - rail.clientWidth; }
        function atStart() { return Math.abs(rail.scrollLeft) <= 1; }
        function atEnd() { return Math.abs(rail.scrollLeft) >= maxScroll() - 1; }

        function updateArrows() {
            if (prev) prev.disabled = atStart();
            if (next) next.disabled = atEnd();
        }

        // اندازهٔ اسکرول در هر کلیک — دو روش قابل تنظیم (هر دو بین ۱/۳ و یک عرض کامل قفل می‌شوند):
        //   ۱) data-hscroll-step="0.5"  → کسری از عرض دید ریل (۰٫۳۳ تا ۱، پیش‌فرض ۰٫۸۵)
        //   ۲) data-hscroll-cards="2"   → تعداد کارت‌هایی که با هر کلیک جلو می‌روند (بر اساس عرض واقعی کارت)
        function scrollStep() {
            const view = rail.clientWidth;

            const cardsAttr = group.getAttribute('data-hscroll-cards');
            if (cardsAttr !== null && cardsAttr !== '') {
                const cards = parseFloat(cardsAttr);
                if (!isNaN(cards) && cards > 0) {
                    const card = group.querySelector('.hscroll-item');
                    const cardWidth = card ? card.getBoundingClientRect().width : view / 4;
                    return Math.min(cards * cardWidth, view);
                }
            }

            let frac = parseFloat(group.getAttribute('data-hscroll-step') || '0.85');
            if (isNaN(frac) || frac <= 0) frac = 0.85;
            return view * Math.min(1, Math.max(1 / 3, frac));
        }

        function move(forward) {
            const step = scrollStep();
            const max = maxScroll();
            // در RTL جهت «بعدی/قبلی» برعکس است (بعدی = به سمت پایان/چپ)
            const delta = forward ? step : -step;
            let target;
            if (rtlNegative) {
                // Chrome/Firefox: محدودهٔ scrollLeft از 0 تا -max
                target = Math.max(-max, Math.min(0, rail.scrollLeft - delta));
            } else {
                // LTR و Safari-RTL: محدودهٔ scrollLeft از 0 تا max
                target = Math.max(0, Math.min(max, rail.scrollLeft + delta));
            }
            rail.scrollTo({ left: target, behavior: 'smooth' });
        }

        if (prev) prev.addEventListener('click', function () { move(false); });
        if (next) next.addEventListener('click', function () { move(true); });

        rail.addEventListener('scroll', updateArrows, { passive: true });
        window.addEventListener('resize', updateArrows);
        updateArrows();
    });
});


//FOR Scroll ======================================================================================================
document.addEventListener('DOMContentLoaded', () => {
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1 // وقتی ۱۰ درصد المان دیده شد فعال شود
    };

    const scrollObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target); // فقط یک بار اجرا شود تا پرفورمنس حفظ شود
            }
        });
    }, observerOptions);

    // فقط المان‌هایی که کلاس reveal-on-scroll دارند را رصد کن
    document.querySelectorAll('.reveal-on-scroll').forEach(el => {
        scrollObserver.observe(el);
    });
});
//======================================================================================================
