<script>
(function() {
    var csrfToken = function() { var m = document.querySelector('meta[name="csrf-token"]'); return m ? m.content : ''; };

    function formatNum(n) {
        return new Intl.NumberFormat(document.documentElement.lang === 'fa' || document.documentElement.lang === 'ar' ? 'fa-IR' : 'en-US').format(n);
    }

    function updateTotals(data) {
        var subtotalEl = document.getElementById('cart-subtotal');
        var totalEl = document.getElementById('cart-total');
        var countBadge = document.getElementById('cart-count');
        var countBadgeMobile = document.getElementById('cart-count-mobile');
        var qtyBadge = document.getElementById('cart-qty-badge');

        var cartCount = (typeof data.cart_count === 'number' && isFinite(data.cart_count)) ? data.cart_count : 0;
        var cartCountStr = window.localizeDigits ? window.localizeDigits.convert(String(cartCount)) : String(cartCount);
        if (subtotalEl) subtotalEl.textContent = formatNum(data.subtotal);
        if (totalEl) totalEl.textContent = formatNum(data.total);
        if (countBadge) { countBadge.textContent = cartCountStr; countBadge.classList.remove('count-pop'); void countBadge.offsetWidth; countBadge.classList.add('count-pop'); }
        if (countBadgeMobile) { countBadgeMobile.textContent = cartCountStr; countBadgeMobile.classList.remove('count-pop'); void countBadgeMobile.offsetWidth; countBadgeMobile.classList.add('count-pop'); }
        if (qtyBadge) qtyBadge.textContent = cartCountStr;

        // حذف/نمایش کوپن
        var couponRow = document.getElementById('cart-coupon-row');
        if (couponRow && data.discount > 0) {
            couponRow.classList.remove('hidden');
            var couponAmount = document.getElementById('cart-coupon-amount');
            if (couponAmount) couponAmount.textContent = '-' + formatNum(data.discount);
        } else if (couponRow && data.discount <= 0) {
            couponRow.classList.add('hidden');
        }

        // اگر سبد خالی شد، رفرش کن تا حالت خالی نمایش داده شود
        if (data.is_empty) {
            location.reload();
        }
    }

    // ===== حذف آیتم (AJAX) =====
    document.addEventListener('click', function(e) {
        var removeBtn = e.target.closest('[data-cart-remove]');
        if (!removeBtn) return;
        e.preventDefault();

        var form = removeBtn.closest('form');
        if (!form) return;

        var card = form.closest('.neu-card');
        removeBtn.disabled = true;

        fetch(form.action, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken(), 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                if (card) {
                    card.style.transition = 'opacity 0.3s, transform 0.3s';
                    card.style.opacity = '0';
                    card.style.transform = 'translateX(30px)';
                    setTimeout(function() { card.remove(); }, 300);
                }
                updateTotals(data);
                if (typeof showToast === 'function') showToast('{{ __("messages.removed_from_cart") }}', 'success');
            }
        })
        .catch(function() { removeBtn.disabled = false; });
    });

    // ===== تغییر تعداد (AJAX) =====
    document.addEventListener('click', function(e) {
        var qtyBtn = e.target.closest('[data-cart-qty]');
        if (!qtyBtn) return;
        e.preventDefault();

        var form = qtyBtn.closest('form');
        if (!form) return;
        var inp = form.querySelector('input[name="qty"]');
        if (!inp) return;

        var dir = qtyBtn.getAttribute('data-cart-qty');
        var val = parseInt(inp.value);
        var max = parseInt(inp.getAttribute('max')) || 9999;

        if (dir === 'dec' && val <= 1) return;
        if (dir === 'inc' && val >= max) {
            if (typeof showToast === 'function') {
                showToast('موجودی این محصول تمام شده است', 'error');
            }
            return;
        }

        var newVal = dir === 'inc' ? val + 1 : val - 1;

        // غیرفعال کردن دکمه‌ها حین درخواست
        var allBtns = form.querySelectorAll('[data-cart-qty]');
        allBtns.forEach(function(b) { b.disabled = true; });

        fetch(form.action, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ qty: newVal }),
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            allBtns.forEach(function(b) { b.disabled = false; });
            if (data.success) {
                inp.value = data.item.qty;
                // اخطار موجودی
                if (data.stock_warning && typeof showToast === 'function') {
                    showToast(data.stock_warning, 'error');
                }
                // به‌روزرسانی قیمت ردیف
                var card = form.closest('.neu-card');
                if (card) {
                    var priceEl = card.querySelector('[data-cart-item-total]');
                    if (priceEl) priceEl.textContent = formatNum(data.item.price * data.item.qty);
                }
                updateTotals(data);
            }
        })
        .catch(function() { allBtns.forEach(function(b) { b.disabled = false; }); });
    });
})();
</script>
