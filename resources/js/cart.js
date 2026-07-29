export function initCart() {
    // افزودن به سبد خرید با انیمیشن
    document.querySelectorAll('[data-add-to-cart]').forEach(btn => {
        btn.addEventListener('click', function() {
            const originalText = this.innerHTML;
            this.classList.add('added');
            this.innerHTML = '<i class="fas fa-check"></i> {{ __("messages.added") }}';

            setTimeout(() => {
                this.classList.remove('added');
                this.innerHTML = originalText;
            }, 2000);

            // آپدیت تعداد سبد خرید
            updateCartCount();
        });
    });
}

function updateCartCount() {
    // ارسال AJAX برای آپدیت تعداد
    fetch('/cart/count')
        .then(res => res.json())
        .then(data => {
            document.querySelector('[data-cart-count]').textContent = data.count;
        });
}

// Quick View محصول
export function initQuickView() {
    document.querySelectorAll('[data-quick-view]').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            // باز کردن مودال با اطلاعات محصول
            showQuickViewModal(productId);
        });
    });
}

// Favorite/Wishlist
export function initWishlist() {
    document.querySelectorAll('[data-toggle-wishlist]').forEach(btn => {
        btn.addEventListener('click', function() {
            this.classList.toggle('active');
            const icon = this.querySelector('i');
            if (this.classList.contains('active')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                icon.style.color = 'var(--danger)';
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                icon.style.color = '';
            }
        });
    });
}
