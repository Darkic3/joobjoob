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

                // شمارندهٔ سبد — هم در هدر و هم در نوار پایین موبایل
                ['cart-count', 'cart-count-mobile'].forEach(function (id) {
                    const badge = document.getElementById(id);
                    if (!badge) return;
                    badge.textContent = (parseInt(badge.textContent || '0') + 1);
                    badge.classList.remove('count-pop');
                    void badge.offsetWidth;
                    badge.classList.add('count-pop');
                });

                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.classList.remove('neu-btn-success');
                    this.disabled = false;
                }, 1500);
            }, 800);
        });
    });
});


// ---------- اسکرول افقی حرفه‌ای (محصولات ویژه / پرفروش‌ترین‌ها) ----------
// تعامل: درگ با اینرسی + فلش‌هایی که دقیقاً روی مرز کارت می‌ایستند (اسنپ به کارت)
// + نوار پیشرفت اسکرول. فیزیک خروج اکسپو؛ بدون پریدن نیمه‌کارت.
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('[data-hscroll]').forEach(function (group) {
        const rail = group.querySelector('.hscroll-rail');
        if (!rail) return;
        const prev = group.querySelector('[data-hscroll-prev]');
        const next = group.querySelector('[data-hscroll-next]');
        const progress = group.querySelector('.hscroll-progress');

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

        const PAD = 16; // هماهنگ با padding-inline ریل در CSS

        function maxScroll() { return rail.scrollWidth - rail.clientWidth; }
        function inRange(v) {
            return rtlNegative
                ? Math.max(-maxScroll(), Math.min(0, v))
                : Math.max(0, Math.min(maxScroll(), v));
        }
        function atStart() { return Math.abs(rail.scrollLeft) <= 1; }
        function atEnd() { return Math.abs(rail.scrollLeft) >= maxScroll() - 1; }

        function updateArrows() {
            // اگر محتوا در یک عرض ریل جا می‌شود، فلش‌ها مخفی و نوار پیشرفت کامل نشان داده می‌شود
            const hasScroll = maxScroll() > 1;
            group.classList.toggle('hscroll-no-scroll', !hasScroll);
            if (prev) prev.disabled = !hasScroll || atStart();
            if (next) next.disabled = !hasScroll || atEnd();
            updateProgress();
        }

        // نوار پیشرفت — عرض نسبی با scaleX (GPU-safe)، جهت با RTL خودکار؛
        // بدون اسکرول، کامل و ثابت می‌ماند تا مشخص باشد چیزی برای جابه‌جا شدن نیست
        function updateProgress() {
            if (!progress) return;
            const max = maxScroll();
            progress.style.setProperty('--prog', max <= 0 ? 1 : Math.abs(rail.scrollLeft) / max);
        }

        // هدف اسنپ: کارت را هم‌راستای لبهٔ شروع ریل (با پدینگ ۱۶px) می‌کند
        function snapTarget(card) {
            const railRect = rail.getBoundingClientRect();
            if (rtlNegative) {
                const delta = railRect.right - card.getBoundingClientRect().right;
                return Math.max(-maxScroll(), Math.min(0, rail.scrollLeft - delta + PAD));
            }
            const delta = card.getBoundingClientRect().left - railRect.left;
            return Math.max(0, Math.min(maxScroll(), rail.scrollLeft + delta - PAD));
        }

        // انیمیشن نرم با فیزیک خروج اکسپو — شروع سریع، پایان آرام، بدون پرش
        let animId = null;
        function smoothScrollTo(target, duration) {
            cancelAnimationFrame(animId);
            const start = rail.scrollLeft;
            const delta = target - start;
            if (Math.abs(delta) < 1) { updateArrows(); return; }
            rail.classList.add('hscroll-animating');
            const d = duration || 620;
            const t0 = performance.now();
            function step(now) {
                const t = Math.min(1, (now - t0) / d);
                const eased = t === 1 ? 1 : 1 - Math.pow(2, -10 * t);
                rail.scrollLeft = start + delta * eased;
                if (t < 1) {
                    animId = requestAnimationFrame(step);
                } else {
                    rail.classList.remove('hscroll-animating');
                    updateArrows();
                }
            }
            animId = requestAnimationFrame(step);
        }

        // فلش‌ها: جابه‌جایی به تعداد کارت مشخص (پیش‌فرض ۱) — همیشه روی مرز کارت
        function moveCards(count) {
            const items = [...rail.children].filter(el => el.classList.contains('hscroll-item'));
            if (!items.length) return;
            const railRect = rail.getBoundingClientRect();
            const norm = rtlNegative ? (r) => -r.right : (r) => r.left;
            const viewNorm = rtlNegative ? -(railRect.right - PAD) : railRect.left + PAD;
            const pos = items.map(it => ({ it, n: norm(it.getBoundingClientRect()) }));

            let targetCard;
            if (count > 0) {
                const nexts = pos.filter(p => p.n > viewNorm + 2);
                const pick = nexts[count - 1] || nexts[nexts.length - 1] || pos[pos.length - 1];
                targetCard = pick.it;
            } else {
                const prevs = [...pos].reverse().filter(p => p.n < viewNorm - 2);
                const pick = prevs[Math.abs(count) - 1] || prevs[prevs.length - 1] || pos[0];
                targetCard = pick.it;
            }
            smoothScrollTo(snapTarget(targetCard));
        }

        // تعداد کارت در هر کلیک — data-hscroll-cards="2" (پیش‌فرض: ۱)
        const cardsPerClick = (function () {
            const attr = group.getAttribute('data-hscroll-cards');
            const n = parseInt(attr, 10);
            return !isNaN(n) && n > 0 ? n : 1;
        })();
        if (prev) prev.addEventListener('click', function () { moveCards(-cardsPerClick); });
        if (next) next.addEventListener('click', function () { moveCards(cardsPerClick); });

        // ---------- درگ با اینرسی + اسنپ ----------
        let pointerId = null, dragStartX = 0, dragStartScroll = 0;
        let dragLastX = 0, dragLastT = 0, velocity = 0, didDrag = false, inertiaId = null;

        rail.addEventListener('pointerdown', function (e) {
            if (e.pointerType === 'mouse' && e.button !== 0) return;
            pointerId = e.pointerId;
            cancelAnimationFrame(animId);
            cancelAnimationFrame(inertiaId);
            dragStartX = dragLastX = e.clientX;
            dragStartScroll = rail.scrollLeft;
            dragLastT = performance.now();
            velocity = 0;
            didDrag = false;
            rail.classList.add('hscroll-dragging');
            try { rail.setPointerCapture(e.pointerId); } catch (_) { /* قدیمی‌تر */ }
        });

        rail.addEventListener('pointermove', function (e) {
            if (e.pointerId !== pointerId) return;
            const dx = e.clientX - dragStartX;
            if (Math.abs(dx) > 4) didDrag = true;
            // RTL (منفی): کشیدن به چپ → سمت پایان؛ LTR برعکس
            rail.scrollLeft = inRange(rtlNegative ? dragStartScroll + dx : dragStartScroll - dx);
            const now = performance.now();
            const dt = now - dragLastT;
            if (dt > 0) velocity = (e.clientX - dragLastX) / dt; // px/ms
            dragLastX = e.clientX;
            dragLastT = now;
        });

        function endDrag(e) {
            if (e.pointerId !== pointerId) return;
            pointerId = null;
            rail.classList.remove('hscroll-dragging');
            if (!didDrag) return;
            e.preventDefault();
            const v = velocity;
            if (Math.abs(v) > 0.25) {
                // اینرسی: سرعت با نرخ نمایی می‌میرد، بعد اسنپ به نزدیک‌ترین کارت
                const t0 = performance.now();
                let lastT = t0;
                const sign = rtlNegative ? 1 : -1;
                function glide(now) {
                    const dt = Math.min(32, now - lastT);
                    lastT = now;
                    const vNow = v * Math.exp(-(now - t0) / 220);
                    if (Math.abs(vNow) < 0.12) { snap(); return; }
                    rail.scrollLeft = inRange(rail.scrollLeft + sign * vNow * dt);
                    inertiaId = requestAnimationFrame(glide);
                }
                inertiaId = requestAnimationFrame(glide);
            } else {
                snap();
            }
        }

        // اسنپ به نزدیک‌ترین کارت (بعد از درگ/اینرسی)
        function snap() {
            const items = [...rail.children].filter(el => el.classList.contains('hscroll-item'));
            if (!items.length) return;
            const railRect = rail.getBoundingClientRect();
            const norm = rtlNegative ? (r) => -r.right : (r) => r.left;
            const viewNorm = rtlNegative ? -(railRect.right - PAD) : railRect.left + PAD;
            let best = items[0], bestD = Infinity;
            items.forEach(it => {
                const d = Math.abs(norm(it.getBoundingClientRect()) - viewNorm);
                if (d < bestD) { bestD = d; best = it; }
            });
            smoothScrollTo(snapTarget(best), 380);
        }

        rail.addEventListener('pointerup', endDrag);
        rail.addEventListener('pointercancel', endDrag);
        // اگر کاربر کارت را کشید، کلیک روی لینک/دکمه اجرا نشود
        rail.addEventListener('click', function (e) {
            if (didDrag) {
                e.preventDefault();
                e.stopPropagation();
                didDrag = false;
            }
        }, true);

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

    // شبکهٔ اطمینان: اگر IntersectionObserver در دسترس نبود (یا در محیطی که کار نمی‌کند)،
    // همه‌چیز فوراً نمایان شود تا محتوا هرگز نامرئی نماند.
    if (typeof IntersectionObserver !== 'function') {
        document.querySelectorAll('.reveal-on-scroll, [data-stagger]').forEach(el => {
            el.classList.add(el.hasAttribute('data-stagger') ? 'is-in' : 'is-visible');
        });
    } else {
        const scrollObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // گریدهای پله‌ای (data-stagger) کلاس is-in می‌گیرند؛ بقیه is-visible
                    entry.target.classList.add(entry.target.hasAttribute('data-stagger') ? 'is-in' : 'is-visible');
                    observer.unobserve(entry.target); // فقط یک بار اجرا شود تا پرفورمنس حفظ شود
                }
            });
        }, observerOptions);

        // بخش‌های reveal و گریدهای stagger را رصد کن
        document.querySelectorAll('.reveal-on-scroll, [data-stagger]').forEach(el => {
            scrollObserver.observe(el);
        });
    }
});
//======================================================================================================


// ---------- دکمهٔ بازگشت به بالا با حلقهٔ پیشرفت ----------
document.addEventListener('DOMContentLoaded', function () {
    const backToTop = document.getElementById('back-to-top');
    if (!backToTop) return;

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let ticking = false;

    function updateBackToTop() {
        ticking = false;
        const doc = document.documentElement;
        const max = doc.scrollHeight - window.innerHeight;
        const progress = max > 0 ? Math.min(1, window.scrollY / max) : 0;

        backToTop.style.setProperty('--bt-progress', progress);
        backToTop.classList.toggle('visible', window.scrollY > 300);
    }

    window.addEventListener('scroll', function () {
        if (!ticking) {
            ticking = true;
            requestAnimationFrame(updateBackToTop);
        }
    }, { passive: true });
    window.addEventListener('resize', updateBackToTop);
    updateBackToTop();

    backToTop.addEventListener('click', function () {
        window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
    });
});


// ---------- حالت جشن: بارش جشن روی هیرو (سبک از تنظیمات: gold | colorful | snow | hearts) ----------
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('festive-confetti');
    if (!canvas) return;

    // به reduced-motion احترام بگذار — بدون بارش
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const section = canvas.closest('.hero-fullbleed') || canvas.parentElement;
    const ctx = canvas.getContext('2d');
    const DPR = Math.min(2, window.devicePixelRatio || 1);

    const style = canvas.dataset.style || 'gold';
    // تم تیره؟ (برف و درخشش‌ها باید با پس‌زمینه هماهنگ شوند)
    const isDark = (document.documentElement.dataset.theme || '').toLowerCase().includes('dark');

    // تعریف هر سبک: پالت، شکل ذرات، اندازه، سرعت، چرخش، تاب و تراکم
    const STYLES = {
        gold: {
            colors: ['#ffd700', '#f5a623', '#ffc53d', '#e8b923', '#ffb84d', '#ffd75e'],
            shapes: ['rect', 'sparkle'],
            size: 4.5, sizeVar: 4.5,
            speed: [1.1, 3.0], sway: 0.7, spin: 0.09, density: 9000,
        },
        colorful: {
            colors: ['#ff5252', '#ffd740', '#69f0ae', '#40c4ff', '#b388ff', '#ff8a65', '#f06292', '#00e5ff'],
            shapes: ['rect', 'circle', 'sparkle'],
            size: 4, sizeVar: 4.5,
            speed: [1.3, 3.2], sway: 0.8, spin: 0.13, density: 8500,
        },
        snow: {
            // در تم روشن، برف با ته‌رنگ آبی + هالهٔ قوی‌تر و دانه‌های بزرگ‌تر دیده می‌شود
            colors: isDark ? ['#ffffff', '#f0f6ff', '#dce8f5', '#e8f0fa'] : ['#ffffff', '#eef5ff', '#ddeafd'],
            shapes: ['circle'],
            size: isDark ? 2 : 2.4, sizeVar: isDark ? 2.5 : 3.2,
            speed: [0.5, 1.4], sway: 0.45, spin: 0.02, density: 14000,
            glow: isDark ? 'rgba(200,220,255,0.28)' : 'rgba(100,140,210,0.5)',
        },
        hearts: {
            colors: ['#ff4d6d', '#ff6b81', '#e5383b', '#ff8fa3', '#f22b4b', '#ffb3c1'],
            shapes: ['heart'],
            size: 3.5, sizeVar: 2.5,
            speed: [0.9, 2.2], sway: 0.6, spin: 0.05, density: 10000,
        },
    };
    const cfg = STYLES[style] || STYLES.gold;

    let particles = [];
    let rafId = null;
    let running = false;

    // ستارهٔ چهارپر (درخشش)
    function drawSparkle(s) {
        ctx.beginPath();
        for (let i = 0; i < 8; i++) {
            const r = i % 2 === 0 ? s : s * 0.3;
            const a = (Math.PI / 4) * i;
            const x = Math.cos(a) * r;
            const y = Math.sin(a) * r;
            if (i === 0) ctx.moveTo(x, y); else ctx.lineTo(x, y);
        }
        ctx.closePath();
    }

    // قلب (دو بزیه)
    function drawHeart(s) {
        ctx.beginPath();
        ctx.moveTo(0, s * 0.3);
        ctx.bezierCurveTo(-s * 0.5, -s * 0.6, -s, s * 0.1, 0, s);
        ctx.bezierCurveTo(s, s * 0.1, s * 0.5, -s * 0.6, 0, s * 0.3);
        ctx.closePath();
    }

    function seed() {
        const area = (canvas.width / DPR) * (canvas.height / DPR);
        const count = Math.min(140, Math.max(50, Math.round(area / cfg.density)));
        particles = Array.from({ length: count }, function () {
            const size = (cfg.size + Math.random() * cfg.sizeVar) * DPR;
            return {
                x: Math.random() * canvas.width,
                y: -20 - Math.random() * canvas.height,
                size,
                vy: (cfg.speed[0] + Math.random() * (cfg.speed[1] - cfg.speed[0])) * DPR,
                vx: (Math.random() - 0.5) * cfg.sway * DPR,
                rot: Math.random() * Math.PI * 2,
                vr: (Math.random() - 0.5) * cfg.spin * DPR,
                shape: cfg.shapes[(Math.random() * cfg.shapes.length) | 0],
                color: cfg.colors[(Math.random() * cfg.colors.length) | 0],
                sway: Math.random() * Math.PI * 2,
                swaySpeed: 0.015 + Math.random() * 0.035,
            };
        });
    }

    function resize() {
        const rect = section.getBoundingClientRect();
        canvas.width = Math.max(1, Math.round(rect.width * DPR));
        canvas.height = Math.max(1, Math.round(rect.height * DPR));
        seed();
    }

    function drawShape(p) {
        const s = p.size;

        if (p.shape === 'circle') {
            // هالهٔ نرم پشت دانهٔ برف تا در تم روشن هم دیده شود
            if (cfg.glow) {
                const g = ctx.createRadialGradient(0, 0, 0, 0, 0, s * 2.6);
                g.addColorStop(0, cfg.glow);
                g.addColorStop(1, 'rgba(0,0,0,0)');
                ctx.fillStyle = g;
                ctx.beginPath();
                ctx.arc(0, 0, s * 2.6, 0, Math.PI * 2);
                ctx.fill();
            }
            ctx.fillStyle = p.color;
            ctx.beginPath();
            ctx.arc(0, 0, s * 0.5, 0, Math.PI * 2);
            ctx.fill();
            return;
        }

        if (p.shape === 'sparkle') {
            ctx.fillStyle = p.color;
            drawSparkle(s * 0.62);
            ctx.fill();
            // هستهٔ روشن برای درخشش
            ctx.fillStyle = 'rgba(255,255,255,0.9)';
            ctx.beginPath();
            ctx.arc(0, 0, s * 0.09, 0, Math.PI * 2);
            ctx.fill();
            return;
        }

        if (p.shape === 'heart') {
            ctx.fillStyle = p.color;
            drawHeart(s);
            ctx.fill();
            return;
        }

        // مستطیل — با گرادیان برای عمق و حالت فلزی
        const g = ctx.createLinearGradient(0, -s, 0, s);
        g.addColorStop(0, p.color);
        g.addColorStop(1, 'rgba(0,0,0,0.22)');
        ctx.fillStyle = g;
        ctx.fillRect(-s, -s, s * 2, s * 1.8);
    }

    function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        for (const p of particles) {
            p.sway += p.swaySpeed;
            p.y += p.vy;
            p.x += p.vx + Math.sin(p.sway) * 0.7 * DPR;
            p.rot += p.vr;

            if (p.y - p.size > canvas.height) {
                p.y = -p.size;
                p.x = Math.random() * canvas.width;
            }

            ctx.save();
            ctx.translate(p.x, p.y);
            ctx.rotate(p.rot);
            drawShape(p);
            ctx.restore();
        }
        rafId = requestAnimationFrame(draw);
    }

    function start() { if (!running) { running = true; rafId = requestAnimationFrame(draw); } }
    function stop() { running = false; if (rafId) { cancelAnimationFrame(rafId); rafId = null; } }

    // فقط وقتی هیرو در دید است انیمیشن اجرا شود (صرفه‌جویی در پرفورمنس)
    if (typeof IntersectionObserver === 'function') {
        const io = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) { e.isIntersecting ? start() : stop(); });
        }, { threshold: 0.05 });
        io.observe(section);
    } else {
        start();
    }

    window.addEventListener('resize', resize);
    resize();
});


// ---------- مودال پیش‌نمایش سریع محصول ----------
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('quick-view-modal');
    if (!modal) return;

    const content = document.getElementById('quick-view-content');
    const csrfToken = () => { const m = document.querySelector('meta[name="csrf-token"]'); return m ? m.content : ''; };

    function openModal() {
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    // کلیک روی دکمهٔ quick-view کارت‌ها → fetch پارشال + تزریق داخل مودال
    document.addEventListener('click', function (e) {
        const trigger = e.target.closest('[data-quick-view-url]');
        if (!trigger) return;
        e.preventDefault();

        content.innerHTML = '<div class="quick-view-loading" aria-hidden="true"><span class="quick-view-spinner"></span></div>';
        openModal();

        fetch(trigger.getAttribute('data-quick-view-url'))
            .then(r => { if (!r.ok) throw new Error('quick-view-failed'); return r.text(); })
            .then(html => { content.innerHTML = html; })
            .catch(() => {
                content.innerHTML = '<div class="quick-view-error">' + (window.quickViewErrorText || 'Error') + '</div>';
            });
    });

    // بستن: بکدراپ / دکمهٔ × / Escape
    modal.querySelectorAll('[data-quick-view-close]').forEach(function (el) {
        el.addEventListener('click', closeModal);
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('open')) closeModal();
    });

    // افزودن به سبد داخل مودال — واقعی (fetch + JSON) بدون رفرش
    document.addEventListener('submit', function (e) {
        const form = e.target.closest('[data-quick-view-add]');
        if (!form) return;
        e.preventDefault();

        const btn = form.querySelector('button[type="submit"]');
        const original = btn ? btn.textContent : '';
        if (btn) { btn.disabled = true; btn.textContent = '…'; }

        fetch(form.action, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
            body: new FormData(form),
        })
            .then(r => r.json())
            .then(function (data) {
                if (data && data.cart_count !== undefined) {
                    ['cart-count', 'cart-count-mobile'].forEach(function (id) {
                        const badge = document.getElementById(id);
                        if (!badge) return;
                        badge.textContent = data.cart_count;
                        badge.classList.remove('count-pop');
                        void badge.offsetWidth;
                        badge.classList.add('count-pop');
                    });
                }
                if (btn) { btn.textContent = window.quickViewAddedText || '✓'; }
                setTimeout(function () { if (btn) { btn.disabled = false; btn.textContent = original; } }, 1200);
            })
            .catch(function () {
                if (btn) { btn.disabled = false; btn.textContent = original; }
            });
    });
});


// ---------- علاقه‌مندی‌ها: toggle قلب روی کارت‌ها ----------
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = () => { const m = document.querySelector('meta[name="csrf-token"]'); return m ? m.content : ''; };

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-wishlist-toggle]');
        if (!btn) return;
        e.preventDefault();
        btn.disabled = true;

        fetch(btn.getAttribute('data-wishlist-url'), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
        })
            .then(r => r.json())
            .then(function (data) {
                const active = !!data.in_wishlist;
                btn.classList.toggle('active', active);
                btn.setAttribute('data-wishlist-active', active ? '1' : '0');
                // وقتی فعال شد، لِیبلِ آینده «حذف» است و برعکس
                btn.setAttribute('aria-label', active ? btn.getAttribute('data-remove-label') : btn.getAttribute('data-add-label'));
                btn.setAttribute('title', active ? btn.getAttribute('data-remove-label') : btn.getAttribute('data-add-label'));

                if (data.count !== undefined) {
                    ['wishlist-count', 'wishlist-count-mobile'].forEach(function (id) {
                        const badge = document.getElementById(id);
                        if (!badge) return;
                        badge.textContent = data.count;
                        badge.classList.remove('count-pop');
                        void badge.offsetWidth;
                        badge.classList.add('count-pop');
                    });
                }
            })
            .finally(function () { btn.disabled = false; });
    });
});


// ---------- نوار پایین موبایل: شیت جستجو ----------
document.addEventListener('DOMContentLoaded', function () {
    const sheet = document.querySelector('[data-mobile-search]');
    const btn = document.querySelector('[data-mobile-search-btn]');
    if (!sheet || !btn) return;

    const input = sheet.querySelector('[data-mobile-search-input]');

    function openSheet() {
        sheet.classList.add('open');
        sheet.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden'; // قفل اسکرول پشت شیت
        // بعد از شروع انیمیشن، فوکوس روی فیلد جستجو
        setTimeout(function () { if (input) input.focus(); }, 220);
    }

    function closeSheet() {
        sheet.classList.remove('open');
        sheet.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        btn.focus(); // برگرداندن فوکوس به دکمهٔ جستجو
    }

    btn.addEventListener('click', openSheet);

    sheet.querySelectorAll('[data-mobile-search-close]').forEach(function (el) {
        el.addEventListener('click', closeSheet);
    });

    // بستن با Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && sheet.classList.contains('open')) closeSheet();
    });
});
