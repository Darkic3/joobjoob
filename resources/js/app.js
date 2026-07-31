import Alpine from 'alpinejs';
import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/pagination';

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
