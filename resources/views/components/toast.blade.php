<div id="toast-container" class="fixed bottom-4 end-4 z-50 space-y-2"></div>

<script>
    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');

        const colors = {
            success: 'var(--success)',
            error: 'var(--danger)',
            info: 'var(--accent)'
        };

        toast.className = 'neu-flat-sm px-4 py-3 flex items-center gap-3 min-w-[300px]';
        toast.style.borderRight = `4px solid ${colors[type]}`;
        toast.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"
           style="color: ${colors[type]}"></i>
        <span class="text-sm">${message}</span>
    `;

        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
</script>
