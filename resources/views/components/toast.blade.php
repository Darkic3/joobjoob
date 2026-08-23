<div id="toast-container" class="fixed bottom-20 end-4 z-50 space-y-2 lg:bottom-4 max-w-[calc(100vw-2rem)]"></div>

<script>
    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');

        const colors = {
            success: 'var(--success)',
            error: 'var(--danger)',
            info: 'var(--accent)'
        };

        toast.className = 'neu-flat-sm px-3 py-2.5 flex items-center gap-2.5 min-w-[200px] lg:min-w-[300px] lg:px-4 lg:py-3 lg:gap-3';
        toast.style.borderRight = `4px solid ${colors[type]}`;
        toast.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"
           style="color: ${colors[type]}"></i>
        <span class="text-sm">${message}</span>
    `;

        container.appendChild(toast);

        // انیمیشن ورود
        requestAnimationFrame(() => {
            toast.style.transition = 'opacity 0.3s, transform 0.3s';
            toast.style.opacity = '1';
            toast.style.transform = 'translateY(0)';
        });

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(10px)';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
</script>
