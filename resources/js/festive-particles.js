// =====================================================================
// موتور بارش جشن — مشترک بین سایت (هیرو) و پنل ادمین (پیش‌نمایش تنظیمات)
//
//   createFestiveRain(canvas, style[, options]) → تابع توقف (stop)
//
//   style: gold | colorful | snow | hearts
//   options.density:  تراکم (px² به ازای هر ذره) — برای پیش‌نمایش‌های کوچک کوچک‌تر
//   options.staticOnly: فقط یک قاب ثابت رسم کن (بدون حلقه) — برای قاب اولیهٔ پیش‌نمایش‌ها
//   options.staticPreview: در reduced-motion (برای پیش‌نمایش‌ها) یک قاب ثابت رسم شود
// =====================================================================
export function createFestiveRain(canvas, style, options = {}) {
    if (!canvas || typeof canvas.getContext !== 'function') return () => {};
    const ctx = canvas.getContext('2d');
    if (!ctx) return () => {};

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
    const density = options.density || cfg.density;

    const section = canvas.closest('.hero-fullbleed') || canvas.parentElement || canvas;
    const DPR = Math.min(2, window.devicePixelRatio || 1);
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
        const count = Math.min(140, Math.max(12, Math.round(area / density)));
        particles = Array.from({ length: count }, function () {
            const size = (cfg.size + Math.random() * cfg.sizeVar) * DPR;
            return {
                x: Math.random() * canvas.width,
                // در پیش‌نمایش‌ها ذرات داخل کادر شروع می‌شوند تا قاب اول هم پر باشد
                y: options.seedInView
                    ? Math.random() * canvas.height
                    : -20 - Math.random() * canvas.height,
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
        const rect = canvas.getBoundingClientRect();
        if (rect.width < 2 || rect.height < 2) return;
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

    function renderFrame() {
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
    }

    function draw() {
        renderFrame();
        rafId = requestAnimationFrame(draw);
    }

    function start() { if (!running) { running = true; rafId = requestAnimationFrame(draw); } }
    function stop() { running = false; if (rafId) { cancelAnimationFrame(rafId); rafId = null; } }

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // قاب ثابت (پیش‌نمایش‌های ادمین یا reduced-motion در پیش‌نمایش) — بدون حلقه
    if (options.staticOnly || (reducedMotion && options.staticPreview)) {
        resize();
        renderFrame();
        return () => {};
    }

    // reduced-motion در سایت: بدون بارش
    if (reducedMotion) return () => {};

    let io = null;
    // فقط وقتی در دید است انیمیشن اجرا شود (صرفه‌جویی در پرفورمنس)
    if (typeof IntersectionObserver === 'function') {
        io = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) { e.isIntersecting ? start() : stop(); });
        }, { threshold: 0.05 });
        io.observe(section);
    } else {
        start();
    }

    window.addEventListener('resize', resize);
    resize();

    return function cleanup() {
        stop();
        if (io) io.disconnect();
        window.removeEventListener('resize', resize);
    };
}
