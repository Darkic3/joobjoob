export function initCart() {
    document.querySelectorAll('[data-add-to-cart]').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            const originalContent = this.innerHTML;

            // ۱. حالت لودینگ
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            this.disabled = true;

            try {
                // ۲. ارسال درخواست واقعی به سرور
                const response = await fetch(`/cart/add/${productId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ qty: 1 })
                });

                const data = await response.json();

                if (data.success) {
                    // ۳. نمایش تیک موفقیت
                    this.innerHTML = '<i class="fas fa-check"></i> ' + (data.message || 'افزوده شد');
                    this.style.background = 'var(--success)';
                    this.style.color = '#fff';

                    // ۴. آپدیت بج سبد خرید در هدر
                    const cartBadge = document.querySelector('[data-cart-count]');
                    if (cartBadge) cartBadge.textContent = data.cart_count;

                    // ۵. نمایش Toast Notification
                    if (typeof window.showToast === 'function') {
                        window.showToast(data.message, 'success');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                if (typeof window.showToast === 'function') {
                    window.showToast('خطا در افزودن به سبد خرید', 'error');
                }
            } finally {
                // ۶. برگشت به حالت اول بعد از ۲ ثانیه
                setTimeout(() => {
                    this.innerHTML = originalContent;
                    this.style.background = '';
                    this.style.color = '';
                    this.disabled = false;
                }, 2000);
            }
        });
    });
}
