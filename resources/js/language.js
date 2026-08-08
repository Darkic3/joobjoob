/**
 * سوییچ زبان — SEO:
 * آیتم‌های سوییچر حالا لینک واقعی به پیشوند زبان هستند (مثل /en) — بدون رفرش.
 * این فایل فقط باز/بسته‌کردن دراپ‌داون را مدیریت می‌کند.
 */
document.addEventListener('DOMContentLoaded', function () {
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
