<div id="toast-container"
     class="fixed inset-x-0 bottom-20 z-50 flex justify-center pointer-events-none lg:bottom-5 lg:items-end lg:justify-end lg:pe-5">
</div>

<style>
    .snackbar-enter {
        animation: snackbarSlideUp 0.35s cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    .snackbar-exit {
        animation: snackbarSlideDown 0.25s ease-in both;
    }
    @keyframes snackbarSlideUp {
        from {
            opacity: 0;
            transform: translateY(100%) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    @keyframes snackbarSlideDown {
        from {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
        to {
            opacity: 0;
            transform: translateY(100%) scale(0.95);
        }
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
            setTimeout(function() { old.remove(); }, 250);
        }

        var colors = {
            success: 'var(--success)',
            error: 'var(--danger)',
            info: 'var(--accent)'
        };
        var icons = {
            success: '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7"/></svg>',
            error: '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
            info: '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>'
        };

        var snackbar = document.createElement('div');
        snackbar.className = 'snackbar snackbar-enter pointer-events-auto';
        snackbar.style.cssText = 'display:inline-flex;align-items:center;gap:10px;padding:10px 18px;border-radius:14px;font-size:13px;font-weight:600;'
            + 'background:var(--bg-elevated);color:var(--text-primary);'
            + 'box-shadow:0 8px 32px rgba(0,0,0,0.18),0 2px 8px rgba(0,0,0,0.08);'
            + 'border:1px solid color-mix(in srgb, var(--shadow-dark) 8%, transparent);'
            + 'max-width:calc(100vw - 2rem);';

        var iconWrap = document.createElement('span');
        iconWrap.style.cssText = 'display:flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;flex-shrink:0;'
            + 'background:color-mix(in srgb, ' + colors[type] + ' 12%, transparent);color:' + colors[type] + ';';
        iconWrap.innerHTML = icons[type] || icons.info;

        var text = document.createElement('span');
        text.textContent = message;
        text.style.whiteSpace = 'nowrap';

        snackbar.appendChild(iconWrap);
        snackbar.appendChild(text);
        container.appendChild(snackbar);

        // حذف خودکار بعد از ۳ ثانیه
        setTimeout(function() {
            if (snackbar.parentNode) {
                snackbar.classList.remove('snackbar-enter');
                snackbar.classList.add('snackbar-exit');
                setTimeout(function() { snackbar.remove(); }, 250);
            }
        }, 3000);
    }
</script>
