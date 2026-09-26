import Alpine from 'alpinejs';
import gsap from 'gsap';
import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

window.Alpine = Alpine;
Alpine.start();

// Swiper 14 دیگر باندل UMD (سراسری از CDN) ارائه نمی‌دهد؛ نمونهٔ باندل‌شده را
// با ماژول‌های موردنیاز (بنرهای تبلیغاتی) به‌صورت سراسری در دسترس اسکریپت‌های
// inline صفحات قرار می‌دهیم.
Swiper.use([Navigation, Pagination, Autoplay]);
window.Swiper = Swiper;

// GSAP به‌صورت سراسری برای اسکریپت‌های inline صفحات (مثل پارالاکس هیرو)
window.gsap = gsap;
