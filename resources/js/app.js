import Alpine from 'alpinejs';
import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay, EffectFade } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import 'swiper/css/effect-fade';

window.Alpine = Alpine;
Alpine.start();

// Swiper 14 دیگر باندل UMD (سراسری از CDN) ارائه نمی‌دهد؛ نمونهٔ باندل‌شده را
// با ماژول‌های موردنیاز هیرو به‌صورت سراسری در دسترس اسکریپت‌های inline صفحات
// (مثل hero.blade.php) قرار می‌دهیم تا بدون تغییر منطق آن‌ها کار کند.
Swiper.use([Navigation, Pagination, Autoplay, EffectFade]);
window.Swiper = Swiper;
