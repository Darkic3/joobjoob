import './bootstrap';
import Alpine from 'alpinejs';
import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/pagination';

// تعاملات UI فروشگاه (تم، تایمر، toast، دکمه افزودن به سبد) — قبلاً به اشتباه './language' ایمپورت می‌شد که وجود نداشت
import './ui-effects';

window.Alpine = Alpine;
Alpine.start();

const heroSwiper = document.querySelector('.hero-swiper');
if (heroSwiper) {
    new Swiper(heroSwiper, {
        modules: [Navigation, Pagination, Autoplay],
        loop: true,
        autoplay: { delay: 4000 },
        pagination: { el: '.swiper-pagination', clickable: true },
    });
}
