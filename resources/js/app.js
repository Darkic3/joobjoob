import './bootstrap';
import Alpine from 'alpinejs';
import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/pagination';

// راه‌اندازی Alpine
window.Alpine = Alpine;
Alpine.start();

// راه‌اندازی Swiper (اگر المان وجود داشت)
const heroSwiper = document.querySelector('.hero-swiper');
if (heroSwiper) {
    new Swiper(heroSwiper, {
        modules: [Navigation, Pagination, Autoplay],
        loop: true,
        autoplay: { delay: 4000 },
        pagination: { el: '.swiper-pagination', clickable: true },
    });
}

// اجرای اسکریپت‌های سفارشی شما (اگر فایل‌های جداگانه دارید، آن‌ها را اینجا ایمپورت کنید)
// import './theme';
import './language';
// import './slider';
