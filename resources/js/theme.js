/**
 * مدیریت تم: خواندن از localStorage، اعمال روی <html data-theme="...">,
 * و ذخیره در دیتابیس/کوکی برای کاربران لاگین (از طریق route('theme.set')).
 */
(function () {
  const STORAGE_KEY = 'site_theme';
  const root = document.documentElement;

  function applyTheme(theme) {
    root.setAttribute('data-theme', theme);
    localStorage.setItem(STORAGE_KEY, theme);
  }

  function persistToServer(theme) {
    fetch('/set-theme', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
      },
      body: JSON.stringify({ theme }),
    }).catch(() => {});
  }

  // مقداردهی اولیه: اولویت با localStorage، در غیر این صورت مقدار سرور (data-theme فعلی از Blade)
  const saved = localStorage.getItem(STORAGE_KEY);
  if (saved) {
    root.setAttribute('data-theme', saved);
  }

  document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.querySelector('[data-theme-toggle]');
    if (toggle) {
      toggle.addEventListener('click', function () {
        const current = root.getAttribute('data-theme');
        const next = current === 'dark-neumorphism' ? 'light-neumorphism' : 'dark-neumorphism';
        applyTheme(next);
        persistToServer(next);
      });
    }

    // سوییچرهای تم سفارشی (مثلاً در پنل ادمین یا منوی تم‌ها)
    document.querySelectorAll('[data-theme-select]').forEach(function (el) {
      el.addEventListener('click', function () {
        const theme = el.getAttribute('data-theme-select');
        applyTheme(theme);
        persistToServer(theme);
      });
    });
  });
})();
