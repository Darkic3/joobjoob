/**
 * اسلایدر هیرو صفحه اول + تایمر شمارش معکوس پیشنهادات ویژه.
 */
document.addEventListener('DOMContentLoaded', function () {
  // ---- هیرو اسلایدر ----
  const slides = document.querySelectorAll('[data-hero-slide]');
  let current = 0;

  if (slides.length > 1) {
    setInterval(function () {
      slides[current].classList.add('hidden');
      current = (current + 1) % slides.length;
      slides[current].classList.remove('hidden');
    }, 5000);
  }

  // ---- تایمر شمارش معکوس ----
  document.querySelectorAll('[data-countdown]').forEach(function (el) {
    const endTime = new Date(el.getAttribute('data-countdown')).getTime();

    const tick = function () {
      const distance = endTime - Date.now();

      if (distance <= 0) {
        el.textContent = '00:00:00';
        return;
      }

      const h = String(Math.floor(distance / 3600000)).padStart(2, '0');
      const m = String(Math.floor((distance % 3600000) / 60000)).padStart(2, '0');
      const s = String(Math.floor((distance % 60000) / 1000)).padStart(2, '0');
      el.textContent = `${h}:${m}:${s}`;
    };

    tick();
    setInterval(tick, 1000);
  });
});
