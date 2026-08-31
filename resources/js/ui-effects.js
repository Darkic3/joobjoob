// بومی‌سازی ارقام بر اساس زبان سایت (fa/ar → فارسی، سایر → انگلیسی)
import './localize-digits.js';

document.addEventListener('DOMContentLoaded', function () {

    // ---------- تاگل تم: فقط data-theme را عوض می‌کنیم، بقیه (موقعیت دات) با CSS خودکار است ----------
    // دو نمونه دارد: یکی در هدر دسکتاپ و یکی داخل منوی موبایل
    const root = document.documentElement;
    document.querySelectorAll('#theme-toggle, #theme-toggle-menu').forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            const next = root.getAttribute('data-theme') === 'dark-neumorphism' ? 'light-neumorphism' : 'dark-neumorphism';
            root.setAttribute('data-theme', next);
            localStorage.setItem('site_theme', next);
        });
    });

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

    // ---------- دراپ‌داون زبان (aria-expanded برای چرخش شورون) ----------
    const langBtn = document.querySelector('[data-lang-dropdown-btn]');
    const langMenu = document.querySelector('[data-lang-dropdown-menu]');
    if (langBtn && langMenu) {
        langBtn.addEventListener('click', function () {
            const nowHidden = langMenu.classList.toggle('hidden');
            langBtn.setAttribute('aria-expanded', String(!nowHidden));
        });
        document.addEventListener('click', function (e) {
            if (!langBtn.contains(e.target) && !langMenu.contains(e.target)) {
                langMenu.classList.add('hidden');
                langBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // ---------- منوی موبایل (همبرگر مورف) ----------
    // با JS خالص: باز/بسته با کلاس open، قفل اسکرول بدنه، بستن با کلیک/اسکیپ.
    // انیمیشن ورود پله‌ای لینک‌ها در CSS است (تأخیرهای nth-child).
    const menuBtn = document.querySelector('[data-menu-btn]');
    const siteMenu = document.querySelector('[data-site-menu]');
    if (menuBtn && siteMenu) {
        function setMenu(open) {
            siteMenu.classList.toggle('open', open);
            menuBtn.classList.toggle('is-open', open);
            menuBtn.setAttribute('aria-expanded', String(open));
            siteMenu.setAttribute('aria-hidden', String(!open));
            document.body.style.overflow = open ? 'hidden' : '';
        }
        menuBtn.addEventListener('click', function () {
            setMenu(!siteMenu.classList.contains('open'));
        });
        // بستن با کلیک روی پس‌زمینه یا هر لینک داخل منو
        siteMenu.querySelectorAll('[data-menu-close], a').forEach(function (el) {
            el.addEventListener('click', function () { setMenu(false); });
        });
        // بستن با Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') setMenu(false);
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


// ── Fly-to-Cart animation ─────────────────────────────────────────────
// مستقیم و آرام با انحنای ملایم · requestAnimationFrame
function flyToCart(sourceEl) {
    var card = sourceEl.closest('.product-card, [data-card-link], .quick-view-content');
    var img = card ? card.querySelector('.product-image-wrap img, .quick-view-gallery img, img') : null;

    var badge = document.getElementById('cart-count') || document.getElementById('cart-count-mobile');
    if (!badge) return;
    var target = badge.closest('a, button');
    if (!target) return;

    var src = img ? img.getBoundingClientRect() : sourceEl.getBoundingClientRect();
    var dst = target.getBoundingClientRect();

    // ── اندازه ──
    var startSize = Math.min(src.width * 0.75, src.height * 0.75, 72);
    var endSize = 12;

    var startX = src.left + src.width / 2;
    var startY = src.top + src.height / 2;
    var endX = dst.left + dst.width / 2;
    var endY = dst.top + dst.height / 2;

    // ── انحنای خیلی ملایم (فقط کمی بالاتر از مسیر مستقیم) ──
    var arcHeight = 35;

    // ── ساخت کلون ──
    var ghost = document.createElement('div');
    ghost.setAttribute('aria-hidden', 'true');
    ghost.style.cssText =
        'position:fixed;z-index:99999;pointer-events:none;'
        + 'width:' + startSize + 'px;height:' + startSize + 'px;'
        + 'border-radius:50%;'
        + 'background-image:url(' + (img ? img.src : '') + ');background-size:cover;background-position:center;'
        + 'box-shadow:0 8px 28px rgba(0,0,0,.3);'
        + 'will-change:transform,opacity;'
        + 'left:' + (startX - startSize / 2) + 'px;'
        + 'top:' + (startY - startSize / 2) + 'px;'
        + 'transform:scale(1);opacity:1;';
    document.body.appendChild(ghost);

    // ── easing ──
    function easeOutQuad(t) { return 1 - (1 - t) * (1 - t); }

    var duration = 1100; // ۱.۱ ثانیه
    var t0 = performance.now();

    function step(now) {
        var elapsed = now - t0;
        var raw = Math.min(elapsed / duration, 1);
        var t = easeOutQuad(raw);

        // ── مستقیم با انحنای خیلی ملایم ──
        var cx = startX + (endX - startX) * t;
        var linearY = startY + (endY - startY) * t;
        var arcY = -arcHeight * Math.sin(Math.PI * raw);
        var cy = linearY + arcY;

        // ── مقیاس: نرم کوچک شدن ──
        var s = startSize + (endSize - startSize) * t;

        // ── محو شدن در ۳۰٪ پایانی ──
        var opacity = raw < 0.7 ? 1 : 1 - ((raw - 0.7) / 0.3) * 0.85;

        ghost.style.left = (cx - s / 2) + 'px';
        ghost.style.top = (cy - s / 2) + 'px';
        ghost.style.width = s + 'px';
        ghost.style.height = s + 'px';
        ghost.style.opacity = opacity;

        if (raw < 1) {
            requestAnimationFrame(step);
        } else {
            ghost.remove();
            badge.classList.remove('count-pop');
            void badge.offsetWidth;
            badge.classList.add('count-pop');
        }
    }

    requestAnimationFrame(step);
}

// ---------- دکمهٔ افزودن به سبد: idle -> loading -> success ----------
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const btn = this;
            const originalText = btn.innerHTML;
            const successText = btn.dataset.success || '✓ اضافه شد';
            const cartUrl = btn.dataset.cartUrl;

            // اگر URL سبد وجود ندارد (مثلاً در quick-view)، از form والد استفاده کن
            if (!cartUrl) return;

            // حالت لودینگ
            btn.innerHTML = '<span class="btn-spinner"></span>';
            btn.disabled = true;

            fetch(cartUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': btn.dataset.csrf || document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ qty: 1 }),
            })
            .then(response => {
                if (response.status === 401 || response.status === 403) {
                    window.location.href = '/login';
                    return null;
                }
                if (response.status === 422) {
                    return response.json().then(function(errData) {
                        if (typeof showToast === 'function' && errData.message) {
                            showToast(errData.message, 'error', errData.stock_warning_sub);
                        }
                        btn.innerHTML = originalText;
                        btn.classList.remove('neu-btn-success');
                        btn.disabled = false;
                        return null;
                    });
                }
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (!data) return;

                if (data.success) {
                    btn.innerHTML = successText;
                    btn.classList.add('neu-btn-success');

                    // 🎯 انیمیشن پرواز به سبد خرید
                    flyToCart(btn);

                    const count = (typeof data.cart_count === 'number' && isFinite(data.cart_count)) ? data.cart_count : 0;
                    const countStr = window.localizeDigits ? window.localizeDigits.convert(String(count)) : String(count);
                    ['cart-count', 'cart-count-mobile', 'cart-count-guest'].forEach(function (id) {
                        const badge = document.getElementById(id);
                        if (!badge) return;
                        badge.textContent = countStr;
                        badge.style.display = '';
                        badge.classList.remove('count-pop');
                        void badge.offsetWidth;
                        badge.classList.add('count-pop');
                    });

                    // نمایش toast (اخطار موجودی یا موفقیت)
                    if (typeof showToast === 'function') {
                        if (data.stock_warning) {
                            showToast(data.stock_warning, 'error', data.stock_warning_sub);
                        } else {
                            showToast(data.message || 'محصول به سبد خرید اضافه شد', 'cart-add');
                        }
                        // اگر مهمان است، پیام لاگین نشان بده
                        if (!document.querySelector('meta[name="csrf-token"]') || !document.body.classList.contains('auth')) {
                            setTimeout(function() { showToast('برای ادامه خرید وارد شوید', 'info'); }, 3800);
                        }
                    }
                } else {
                    btn.innerHTML = data.message || 'خطا';
                }

                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.classList.remove('neu-btn-success');
                    btn.disabled = false;
                }, 1500);
            })
            .catch(() => {
                btn.innerHTML = originalText;
                btn.classList.remove('neu-btn-success');
                btn.disabled = false;
            });
        });
    });
});


// ---------- جلوگیری از ناوبری لینک کارت هنگام کلیک روی دکمه‌های داخل آن ----------
// دکمه‌های "افزودن به سبد"، "علاقه‌مندی" و "پیش‌نمایش" داخل <a> کارت قرار دارند.
// این هندلر مطمئن می‌شود کلیک روی دکمه‌ها باعث باز شدن صفحهٔ محصول نشود.
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-card-link]').forEach(function (link) {
        link.addEventListener('click', function (e) {
            if (e.target.closest('button, [data-wishlist-toggle], [data-quick-view-url]')) {
                e.preventDefault();
                e.stopPropagation();
            }
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
            // pointer capture فقط وقتی درگ واقعی شروع می‌شود فعال می‌شود
            // تا کلیک عادی روی کارت محصول به <a> برسد و ناوبری انجام شود
        });

        rail.addEventListener('pointermove', function (e) {
            if (e.pointerId !== pointerId) return;
            const dx = e.clientX - dragStartX;
            if (!didDrag && Math.abs(dx) > 10) {
                didDrag = true;
                rail.classList.add('hscroll-dragging');
                try { rail.setPointerCapture(e.pointerId); } catch (_) { /* قدیمی‌تر */ }
            }
            // فقط بعد از شروع درگ اسکرول را تغییر بده — وگرنه مرورگر کلیک را لغو می‌کند
            if (!didDrag) return;
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
            // همیشه capture را آزاد کن تا کلیک عادی به <a> برسد
            try { rail.releasePointerCapture(e.pointerId); } catch (_) {}
            if (!didDrag) return;
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
        // فقط وقتی اسکرول واقعاً جابه‌جا شده از ناوبری جلوگیری کن
        // (حرکت خیلی کم انگشت/ماوس باعث نمی‌شود کلیک مسدود شود)
        rail.addEventListener('click', function (e) {
            const scrolled = didDrag && rail.scrollLeft !== dragStartScroll;
            didDrag = false;
            if (scrolled) {
                e.preventDefault();
                e.stopPropagation();
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
        // وقتی حلقه کامل شد (کاربر به انتهای صفحه رسید) کل دکمه همرنگ حلقه می‌شود
        backToTop.classList.toggle('complete', progress >= 0.995);
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
        // برگشت به حالت اولیه: سفید/نئومورفیک و حلقهٔ خالی
        backToTop.classList.remove('complete');
        backToTop.style.setProperty('--bt-progress', 0);
        window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
    });
});


// ---------- حالت جشن: بارش جشن روی هیرو (موتور مشترک در festive-particles.js) ----------
import { createFestiveRain } from './festive-particles.js';

document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('festive-confetti');
    if (!canvas) return;

    createFestiveRain(canvas, canvas.dataset.style || 'gold');
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
                if (data && typeof data.cart_count === 'number' && isFinite(data.cart_count)) {
                    const countStr = window.localizeDigits ? window.localizeDigits.convert(String(data.cart_count)) : String(data.cart_count);
                    ['cart-count', 'cart-count-mobile', 'cart-count-guest'].forEach(function (id) {
                        const badge = document.getElementById(id);
                        if (!badge) return;
                        badge.textContent = countStr;
                        badge.style.display = '';
                        badge.classList.remove('count-pop');
                        void badge.offsetWidth;
                        badge.classList.add('count-pop');
                    });
                }
                if (btn) { btn.textContent = window.quickViewAddedText || '✓'; }

                // 🎯 انیمیشن پرواز به سبد خرید
                if (btn) flyToCart(btn);

                // نمایش toast (اخطار موجودی یا موفقیت)
                if (typeof showToast === 'function' && data && data.success) {
                    if (data.stock_warning) {
                        showToast(data.stock_warning, 'error', data.stock_warning_sub);
                    } else {
                        showToast(data.message || 'محصول به سبد خرید اضافه شد', 'cart-add');
                    }
                }

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


// ---------- چرخش نظرات مشتریان (rotator) ----------
// بچ‌های ۳تایی (۱ کارت برجسته + ۲ ثانویه) هر چند ثانیه با کراس‌فید عوض می‌شوند.
// مکث با هاور/فوکوس، چرخش فقط وقتی بخش در دید است؛ در reduced-motion فقط دات‌ها کار می‌کنند.
document.addEventListener('DOMContentLoaded', function () {
    const rotator = document.querySelector('[data-reviews-rotator]');
    if (!rotator) return;

    const batches = [...rotator.querySelectorAll('[data-reviews-batch]')];
    const dots = [...rotator.querySelectorAll('[data-reviews-dot]')];
    if (batches.length < 2) return;

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const interval = parseInt(rotator.getAttribute('data-reviews-interval'), 10) || 6000;
    // برای هماهنگی نوار پیشرفت دات‌ها با زمان‌بندی چرخش
    rotator.style.setProperty('--rev-interval', interval + 'ms');

    let current = 0;
    let timer = null;
    let paused = false;

    // ریپلی ورود پله‌ای محتوای بچ تازه فعال‌شده
    function replayStagger(container) {
        container.querySelectorAll('[data-stagger]').forEach(el => {
            el.classList.remove('is-in');
            void el.offsetWidth; // reflow تا transition دوباره اجرا شود
            el.classList.add('is-in');
        });
    }

    function activate(index, userInitiated) {
        const same = index === current;
        current = index;

        batches.forEach((b, i) => {
            const on = i === index;
            b.classList.toggle('is-active', on);
            b.setAttribute('aria-hidden', on ? 'false' : 'true');
        });

        // دات‌ها: حذف کلاس از همه + reflow + افزودن به دات هدف → انیمیشن پیشرفت ریستart می‌شود
        dots.forEach(d => d.classList.remove('is-active'));
        void dots[0].offsetWidth;
        dots.forEach((d, i) => {
            const on = i === index;
            d.classList.toggle('is-active', on);
            d.setAttribute('aria-selected', on ? 'true' : 'false');
            d.setAttribute('tabindex', on ? '0' : '-1');
        });

        if (same) {
            replayStagger(batches[index]);
        } else {
            requestAnimationFrame(() => replayStagger(batches[index]));
        }
        if (userInitiated) restartTimer();
    }

    function stopTimer() {
        if (timer) { clearInterval(timer); timer = null; }
    }

    function startTimer() {
        stopTimer();
        if (reduceMotion || paused) return;
        timer = setInterval(() => activate((current + 1) % batches.length, false), interval);
    }

    function restartTimer() { startTimer(); }

    // کلیک روی دات‌ها
    dots.forEach((dot, i) => {
        dot.addEventListener('click', () => activate(i, true));
    });

    // دکمه‌های قبلی/بعدی — هماهنگ با ناوبری هیرو: چرخش بی‌پایان + ری‌استارت تایمر
    const nextBtn = rotator.querySelector('[data-reviews-next]');
    const prevBtn = rotator.querySelector('[data-reviews-prev]');
    if (nextBtn) nextBtn.addEventListener('click', () => activate((current + 1) % batches.length, true));
    if (prevBtn) prevBtn.addEventListener('click', () => activate((current - 1 + batches.length) % batches.length, true));

    // مکث با هاور / فوکوس
    rotator.addEventListener('mouseenter', () => { paused = true; stopTimer(); });
    rotator.addEventListener('mouseleave', () => { paused = false; startTimer(); });
    rotator.addEventListener('focusin', () => { paused = true; stopTimer(); });
    rotator.addEventListener('focusout', (e) => {
        if (rotator.contains(e.relatedTarget)) return;
        paused = false;
        startTimer();
    });

    // فقط وقتی بخش در دید است بچرخد (صرفه‌جویی در پرفورمنس)
    if ('IntersectionObserver' in window) {
        const io = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !paused && !reduceMotion) startTimer();
                else if (!entry.isIntersecting) stopTimer();
            });
        }, { threshold: 0.2 });
        io.observe(rotator);
    } else {
        startTimer();
    }

    // مثل هیرو: وقتی تب مخفی می‌شود چرخش متوقف و با برگشت ادامه می‌یابد
    let rotatorHidden = document.hidden;
    const onRotatorVisibility = () => {
        if (document.hidden) {
            rotatorHidden = true;
            stopTimer();
        } else if (rotatorHidden) {
            rotatorHidden = false;
            if (!paused && !reduceMotion) startTimer();
        }
    };
    document.addEventListener('visibilitychange', onRotatorVisibility);
});


// ---------- فوتر: آکاردئون ستون‌ها در موبایل ----------
// فقط زیر ۷۶۸px فعال است؛ در دسکتاپ کلاس باز به‌زور زده می‌شود تا ستون‌ها همیشه باز بمانند.
// بدون JS هم همه‌چیز باز است (CSS پیش‌فرض باز) — این کد فقط حالت بسته‌شونده را فعال می‌کند.
document.addEventListener('DOMContentLoaded', function () {
    const footerAccs = [...document.querySelectorAll('[data-footer-acc]')];
    if (!footerAccs.length) return;

    // فقط وقتی JS اجرا می‌شود بستن فعال است (بدون JS: همیشه باز — progressive enhancement)
    const footerEl = footerAccs[0].closest('.site-footer');
    (footerEl || document.body).classList.add('js-footer-acc');

    const isDesktop = () => window.matchMedia('(min-width: 768px)').matches;

    // در موبایل شروع بسته؛ در دسکتاپ همیشه باز
    footerAccs.forEach(acc => {
        const toggle = acc.querySelector('[data-footer-acc-toggle]');
        const body = acc.querySelector('.site-footer-acc-body');
        if (!toggle || !body) return;

        const setOpen = (open) => {
            acc.classList.toggle('is-open', open);
            toggle.setAttribute('aria-expanded', String(open));
            body.setAttribute('aria-hidden', String(!open));
        };

        setOpen(isDesktop());

        toggle.addEventListener('click', () => {
            if (isDesktop()) return;
            setOpen(!acc.classList.contains('is-open'));
        });
    });

    // با تغییر سایز بین دسکتاپ و موبایل، وضعیت را هماهنگ کن
    const mq = window.matchMedia('(min-width: 768px)');
    const sync = () => footerAccs.forEach(acc => {
        const toggle = acc.querySelector('[data-footer-acc-toggle]');
        const body = acc.querySelector('.site-footer-acc-body');
        if (!toggle || !body) return;
        acc.classList.toggle('is-open', isDesktop());
        toggle.setAttribute('aria-expanded', String(isDesktop()));
        body.setAttribute('aria-hidden', String(!isDesktop()));
    });
    if (typeof mq.addEventListener === 'function') mq.addEventListener('change', sync);
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
