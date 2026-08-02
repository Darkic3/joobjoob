import Alpine from 'alpinejs';
import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/pagination';

window.Alpine = Alpine;
Alpine.start();

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


/* ==========================================
   Custom Admin Scripts (Global)
   ========================================== */

document.addEventListener('DOMContentLoaded', function() {
    // مدیریت آپلود و پیش‌نمایش تصاویر (Dropzone)
    const dropzone = document.getElementById('dropzone');
    const imageInput = document.getElementById('image-input');
    const imagePreview = document.getElementById('image-preview');

    if (dropzone && imageInput) {
        // کلیک روی ناحیه برای باز کردن پنجره فایل
        dropzone.addEventListener('click', (e) => {
            if(e.target.tagName !== 'INPUT') imageInput.click();
        });

        // مدیریت رویدادهای Drag & Drop
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                dropzone.classList.add('dragover');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                dropzone.classList.remove('dragover');
            });
        });

        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            imageInput.files = e.dataTransfer.files;
            handleImagePreview();
        });

        imageInput.addEventListener('change', handleImagePreview);

        function handleImagePreview() {
            if (!imagePreview) return;

            imagePreview.innerHTML = '';
            if (imageInput.files.length > 0) {
                imagePreview.classList.remove('hidden');
                imagePreview.classList.add('grid');

                Array.from(imageInput.files).forEach(file => {
                    if (!file.type.startsWith('image/')) return;

                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const wrapper = document.createElement('div');
                        wrapper.className = 'relative group rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 aspect-square shadow-sm';

                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'w-full h-full object-cover';

                        const removeBtn = document.createElement('button');
                        removeBtn.type = 'button';
                        removeBtn.className = 'absolute top-2 left-2 bg-rose-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity';
                        removeBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';

                        // حذف از پیش‌نمایش (توجه: این فایل را از input اصلی حذف نمی‌کند، فقط از نمایش)
                        removeBtn.onclick = () => wrapper.remove();

                        wrapper.appendChild(img);
                        wrapper.appendChild(removeBtn);
                        imagePreview.appendChild(wrapper);
                    };
                    reader.readAsDataURL(file);
                });
            } else {
                imagePreview.classList.add('hidden');
                imagePreview.classList.remove('grid');
            }
        }
    }

    // تابع سراسری برای حذف تصویر (قابل فراخوانی از Blade)
    window.deleteImage = function(imageId) {
        if(confirm('آیا از حذف این تصویر اطمینان دارید؟')) {
            // اینجا می‌توانید کد AJAX خود را برای حذف واقعی قرار دهید
            console.log('Deleting image ID:', imageId);
            // مثال:
            // fetch(`/admin/products/images/${imageId}`, { method: 'DELETE', headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content} })
        }
    }
});
