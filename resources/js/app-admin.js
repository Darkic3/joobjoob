import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/pagination';

import Alpine from 'alpinejs';
import 'swiper/css';
import 'swiper/css/pagination';
import { initCart } from './cart';


const heroSwiper = new Swiper('.hero-swiper', {
    modules: [Navigation, Pagination, Autoplay],
    loop: true,
    autoplay: { delay: 4000 },
    pagination: { el: '.swiper-pagination', clickable: true },
});

document.querySelectorAll('[data-countdown]').forEach(timer => {
    const endDate = new Date(timer.dataset.countdown).getTime();

    const interval = setInterval(() => {
        const now = new Date().getTime();
        const distance = endDate - now;

        if (distance < 0) {
            clearInterval(interval);
            timer.innerHTML = "پایان یافته";
            return;
        }

        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        timer.innerHTML = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }, 1000);
});


window.Alpine = Alpine;
Alpine.start();

// اجرای اولیه سبد خرید
document.addEventListener('DOMContentLoaded', () => {
    initCart();
});
