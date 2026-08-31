<div id="toast-container"
     class="fixed top-20 z-50 flex flex-col gap-2 pointer-events-none"
     style="left: 1.25rem;">
</div>

<style>
    /* ── Snackbar entry/exit ── */
    .snackbar-enter {
        animation: snackbarSlideIn 0.5s cubic-bezier(0.16, 1, 0.3, 1.15) both;
    }
    .snackbar-exit {
        animation: snackbarSlideOut 0.35s cubic-bezier(0.55, 0, 1, 0.45) both;
    }
    @keyframes snackbarSlideIn {
        from { opacity: 0; transform: translateX(-40px) scale(0.9); }
        to   { opacity: 1; transform: translateX(0) scale(1); }
    }
    @keyframes snackbarSlideOut {
        from { opacity: 1; transform: translateX(0) scale(1); }
        to   { opacity: 0; transform: translateX(-30px) scale(0.9); }
    }

    /* ── Cart-add celebration: ripple ring ── */
    .cart-toast-ripple {
        position: absolute;
        inset: -4px;
        border-radius: 50%;
        border: 2px solid var(--success);
        opacity: 0;
        animation: cartRipple 0.8s cubic-bezier(0, 0.5, 0.3, 1) 0.15s forwards;
    }
    @keyframes cartRipple {
        0%   { transform: scale(0.6); opacity: 0.6; }
        100% { transform: scale(2.4); opacity: 0; }
    }

    /* ── Cart-add celebration: checkmark stroke-draw ── */
    .cart-toast-check {
        stroke-dasharray: 22;
        stroke-dashoffset: 22;
        animation: drawCheck 0.45s cubic-bezier(0.65, 0, 0.35, 1) 0.3s forwards;
    }
    @keyframes drawCheck {
        to { stroke-dashoffset: 0; }
    }

    /* ── Cart-add celebration: icon bounce-in ── */
    .cart-toast-icon {
        animation: cartIconBounce 0.55s cubic-bezier(0.34, 1.56, 0.64, 1) both;
    }
    @keyframes cartIconBounce {
        0%   { transform: scale(0) rotate(-25deg); opacity: 0; }
        55%  { transform: scale(1.18) rotate(5deg); opacity: 1; }
        75%  { transform: scale(0.95) rotate(-2deg); }
        100% { transform: scale(1) rotate(0); opacity: 1; }
    }

    /* ── Cart-add celebration: spark particles ── */
    .cart-toast-particles {
        position: absolute;
        inset: 0;
        pointer-events: none;
    }
    .cart-toast-particle {
        position: absolute;
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: var(--success);
        opacity: 0;
        animation: particleBurst 0.7s cubic-bezier(0, 0.6, 0.4, 1) forwards;
    }
    .cart-toast-particle:nth-child(1)  { --px: -20px; --py: -16px; animation-delay: 0.18s; background: var(--success); }
    .cart-toast-particle:nth-child(2)  { --px: 22px;  --py: -12px; animation-delay: 0.24s; background: #34d399; }
    .cart-toast-particle:nth-child(3)  { --px: -16px; --py: 18px;  animation-delay: 0.21s; background: var(--accent); }
    .cart-toast-particle:nth-child(4)  { --px: 18px;  --py: 20px;  animation-delay: 0.27s; background: #60a5fa; }
    .cart-toast-particle:nth-child(5)  { --px: 2px;   --py: -22px; animation-delay: 0.16s; background: var(--gold, #f59e0b); }
    .cart-toast-particle:nth-child(6)  { --px: -24px; --py: 4px;   animation-delay: 0.29s; background: #a78bfa; }
    @keyframes particleBurst {
        0%   { transform: translate(0, 0) scale(0); opacity: 0; }
        30%  { opacity: 1; }
        100% { transform: translate(var(--px), var(--py)) scale(0); opacity: 0; }
    }

    /* ── Cart-add celebration: confetti sparkles ── */
    .cart-toast-sparkle {
        position: absolute;
        width: 7px;
        height: 7px;
        opacity: 0;
        animation: sparkleFloat 1s ease-out forwards;
    }
    .cart-toast-sparkle::before {
        content: '';
        display: block;
        width: 100%;
        height: 100%;
        background: currentColor;
        clip-path: polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%);
    }
    .cart-toast-sparkle:nth-child(1)  { --sx: -30px; --sy: -20px; --srot: 45deg;  color: var(--success); animation-delay: 0.1s; }
    .cart-toast-sparkle:nth-child(2)  { --sx: 28px;  --sy: -14px; --srot: -30deg; color: var(--gold, #f59e0b); animation-delay: 0.2s; }
    .cart-toast-sparkle:nth-child(3)  { --sx: -12px; --sy: 24px;  --srot: 60deg;  color: #60a5fa; animation-delay: 0.3s; }
    .cart-toast-sparkle:nth-child(4)  { --sx: 26px;  --sy: 18px;  --srot: -45deg; color: #a78bfa; animation-delay: 0.15s; }
    @keyframes sparkleFloat {
        0%   { transform: translate(0,0) rotate(0) scale(0); opacity: 0; }
        25%  { opacity: 1; }
        100% { transform: translate(var(--sx), var(--sy)) rotate(var(--srot)) scale(0); opacity: 0; }
    }

    /* ── Cart-add celebration: glow pulse on icon wrap ── */
    .cart-toast-glow {
        animation: cartGlowPulse 0.7s ease 0.1s both;
    }
    @keyframes cartGlowPulse {
        0%   { box-shadow: 0 0 0 0 color-mix(in srgb, var(--success) 40%, transparent); }
        40%  { box-shadow: 0 0 18px 8px color-mix(in srgb, var(--success) 25%, transparent); }
        100% { box-shadow: 0 0 0 0 color-mix(in srgb, var(--success) 0%, transparent); }
    }

    /* ── Cart-add success: smooth progress bar ── */
    .cart-toast-progress-wrap {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        overflow: hidden;
        border-radius: 0 0 16px 16px;
    }
    .cart-toast-progress {
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, var(--success), color-mix(in srgb, var(--success) 50%, var(--accent)));
        transform-origin: right center;
        animation: progressSmoothShrink 3.5s cubic-bezier(0.25, 0.1, 0.25, 1) forwards;
    }
    .cart-toast-progress::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, transparent 60%, color-mix(in srgb, #fff 40%, transparent));
        animation: progressSheen 1.8s ease-in-out infinite;
    }
    @keyframes progressSmoothShrink {
        0%   { transform: scaleX(1); }
        100% { transform: scaleX(0); }
    }
    @keyframes progressSheen {
        0%   { opacity: 0; transform: translateX(-100%); }
        50%  { opacity: 0.6; }
        100% { opacity: 0; transform: translateX(200%); }
    }
</style>

<script>
    function showToast(message, type) {
        if (typeof type === 'undefined') type = 'success';
        var container = document.getElementById('toast-container');
        if (!container) return;

        // پاک کردن toast قبلی
        var old = container.querySelector('.snackbar');
        if (old) {
            old.classList.remove('snackbar-enter');
            old.classList.add('snackbar-exit');
            setTimeout(function() { old.remove(); }, 350);
        }

        var colors = {
            success: 'var(--success)',
            error: 'var(--danger)',
            info: 'var(--accent)'
        };

        var isCartAdd = (type === 'cart-add');
        var effectiveType = isCartAdd ? 'success' : type;

        var icons = {
            success: '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path class="cart-toast-check" d="M5 13l4 4L19 7"/></svg>',
            error: '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
            info: '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>'
        };

        var snackbar = document.createElement('div');
        snackbar.className = 'snackbar snackbar-enter pointer-events-auto';
        snackbar.style.cssText = 'position:relative;overflow:visible;display:inline-flex;align-items:center;gap:12px;padding:12px 20px;border-radius:16px;font-size:14px;font-weight:600;'
            + 'background:var(--bg-elevated);color:var(--text-primary);'
            + 'box-shadow:0 12px 40px rgba(0,0,0,0.2),0 4px 12px rgba(0,0,0,0.08);'
            + 'border:1px solid color-mix(in srgb, var(--success) 15%, transparent);'
            + 'max-width:calc(100vw - 2.5rem);';

        var iconWrap = document.createElement('span');
        iconWrap.style.cssText = 'position:relative;display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;flex-shrink:0;overflow:visible;'
            + 'background:color-mix(in srgb, ' + colors[effectiveType] + ' 14%, transparent);color:' + colors[effectiveType] + ';';

        if (isCartAdd) {
            iconWrap.classList.add('cart-toast-icon', 'cart-toast-glow');
            // Ripple ring
            var ripple = document.createElement('span');
            ripple.className = 'cart-toast-ripple';
            iconWrap.appendChild(ripple);
            // Icon
            var iconSvg = document.createElement('span');
            iconSvg.innerHTML = icons[effectiveType] || icons.info;
            iconSvg.style.position = 'relative';
            iconSvg.style.zIndex = '1';
            iconWrap.appendChild(iconSvg);
            // Particles container
            var particles = document.createElement('span');
            particles.className = 'cart-toast-particles';
            for (var p = 0; p < 6; p++) {
                var dot = document.createElement('span');
                dot.className = 'cart-toast-particle';
                particles.appendChild(dot);
            }
            iconWrap.appendChild(particles);
            // Sparkles
            for (var s = 0; s < 4; s++) {
                var sparkle = document.createElement('span');
                sparkle.className = 'cart-toast-sparkle';
                snackbar.appendChild(sparkle);
            }
        } else {
            iconWrap.innerHTML = icons[effectiveType] || icons.info;
        }

        var text = document.createElement('span');
        text.innerHTML = message;
        text.style.whiteSpace = 'nowrap';

        snackbar.appendChild(iconWrap);
        snackbar.appendChild(text);

        // Progress bar for cart-add (wrapped to clip inside toast)
        if (isCartAdd) {
            var progressWrap = document.createElement('span');
            progressWrap.className = 'cart-toast-progress-wrap';
            var progress = document.createElement('span');
            progress.className = 'cart-toast-progress';
            progressWrap.appendChild(progress);
            snackbar.appendChild(progressWrap);
        }

        container.appendChild(snackbar);

        // حذف خودکار
        var duration = isCartAdd ? 3500 : 3000;
        setTimeout(function() {
            if (snackbar.parentNode) {
                snackbar.classList.remove('snackbar-enter');
                snackbar.classList.add('snackbar-exit');
                setTimeout(function() { snackbar.remove(); }, 350);
            }
        }, duration);
    }
</script>
