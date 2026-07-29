/**
 * سوییچ زبان: کاربر یک زبان را از لیست انتخاب می‌کند،
 * صفحه با ?lang=xx رفرش می‌شود و SetLocale middleware آن را در session ذخیره می‌کند.
 */
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('[data-lang-select]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      e.preventDefault();
      const lang = el.getAttribute('data-lang-select');
      const url = new URL(window.location.href);
      url.searchParams.set('lang', lang);
      window.location.href = url.toString();
    });
  });

  // بستن/باز کردن دراپ‌داون زبان
  const dropdownBtn = document.querySelector('[data-lang-dropdown-btn]');
  const dropdownMenu = document.querySelector('[data-lang-dropdown-menu]');
  if (dropdownBtn && dropdownMenu) {
    dropdownBtn.addEventListener('click', () => dropdownMenu.classList.toggle('hidden'));
    document.addEventListener('click', function (e) {
      if (!dropdownBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
        dropdownMenu.classList.add('hidden');
      }
    });
  }
});
