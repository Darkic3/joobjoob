/**
 * افکت‌های UI فروشگاه: تاگل تم، شمارش معکوس، دکمه افزودن به سبد (idle/loading/success)،
 * toast اثبات اجتماعی چرخشی، و نوار اعلان بالای هدر.
 * این فایل کاملاً مستقل از بک‌اند است (فقط تعاملات بصری).
 */
document.addEventListener('DOMContentLoaded', function () {

  // ---------- تاگل تم روشن/تیره ----------
  const root = document.documentElement;
  const toggle = document.getElementById('theme-toggle');
  const dot = document.getElementById('toggle-dot');

  function paintToggle() {
    if (!dot) return;
    const dark = root.getAttribute('data-theme') === 'dark-neumorphism';
    dot.style.insetInlineStart = dark ? '29px' : '3px';
  }
  paintToggle();

  if (toggle) {
    toggle.addEventListener('click', function () {
      const next = root.getAttribute('data-theme') === 'dark-neumorphism' ? 'light-neumorphism' : 'dark-neumorphism';
      root.setAttribute('data-theme', next);
      localStorage.setItem('site_theme', next);
      paintToggle();
    });
  }

  // ---------- دکمه‌های افزودن به سبد: idle -> loading -> success ----------
  document.querySelectorAll('.add-to-cart').forEach(function (btn) {
    const original = btn.innerHTML;
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      btn.disabled = true;
      btn.innerHTML = '<span class="btn-spinner"></span>';
      // در پروژه واقعی این‌جا fetch به route('cart.add', product) می‌رود
      setTimeout(function () {
        btn.innerHTML = '✓ اضافه شد';
        const badge = document.getElementById('cart-count');
        if (badge) badge.textContent = (parseInt(badge.textContent || '0') + 1);
        setTimeout(function () { btn.innerHTML = original; btn.disabled = false; }, 1200);
      }, 600);
    });
  });

  // ---------- toast اثبات اجتماعی چرخشی ----------
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
      toastEl.classList.remove('hidden');
      toastEl.classList.remove('toast-enter');
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

  // ---------- دراپ‌داون سوییچر زبان ----------
  const langBtn = document.querySelector('[data-lang-dropdown-btn]');
  const langMenu = document.querySelector('[data-lang-dropdown-menu]');
  if (langBtn && langMenu) {
    langBtn.addEventListener('click', () => langMenu.classList.toggle('hidden'));
    document.addEventListener('click', function (e) {
      if (!langBtn.contains(e.target) && !langMenu.contains(e.target)) {
        langMenu.classList.add('hidden');
      }
    });
  }
  document.querySelectorAll('[data-lang-select]').forEach(function (el) {
    el.addEventListener('click', function () {
      const url = new URL(window.location.href);
      url.searchParams.set('lang', el.getAttribute('data-lang-select'));
      window.location.href = url.toString();
    });
  });

  // ---------- فیلتر برند (چیپ‌های سایدبار محصولات) ----------
  document.querySelectorAll('[data-brand-chip]').forEach(function (chip) {
    chip.addEventListener('click', function () {
      document.querySelectorAll('[data-brand-chip]').forEach(c => c.classList.remove('active'));
      chip.classList.add('active');
    });
  });
});

/**
 * شمارش معکوس (به‌صورت تابع مجزا تا هم برای تایمر هدر دیل و هم کارت‌های تکی قابل استفاده باشد).
 * از اعداد فارسی استفاده می‌کند؛ برای EN/TR کافیست toFa را حذف کنید.
 */
function startCountdown(elIds, totalSeconds) {
  function toFa(n) {
    return String(n).padStart(2, '0').replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
  }
  function tick() {
    totalSeconds = totalSeconds > 0 ? totalSeconds - 1 : 0;
    if (elIds.h) document.getElementById(elIds.h).textContent = toFa(Math.floor(totalSeconds / 3600));
    if (elIds.m) document.getElementById(elIds.m).textContent = toFa(Math.floor((totalSeconds % 3600) / 60));
    if (elIds.s) document.getElementById(elIds.s).textContent = toFa(totalSeconds % 60);
  }
  tick();
  return setInterval(tick, 1000);
}
