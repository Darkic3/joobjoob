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

/* ==========================================
   Global Slug Helper (همگانی)
   تمام فیلدهای slug در صفحات ادمین از این تابع استفاده می‌کنند
   ========================================== */

window.SlugHelper = {
    /**
     * تمیزسازی یک رشته به اسلاگ امن
     * - حذف کاراکترهای غیرمجاز و فارسی
     * - تبدیل فاصله به خط تیره
     * - جلوگیری از تکرار خط تیره/فاصله
     */
    slugify(value) {
        return String(value ?? '')
            .replace(/\s+/g, '-')          // هر نوع فاصله → خط تیره
            .toLowerCase()                  // حروف انگلیسی → کوچک
            .replace(/[^a-z0-9\-_]/g, '')  // حذف کاراکترهای غیرمجاز (فارسی و ...)
            .replace(/-{2,}/g, '-');        // جلوگیری از -- تکراری
    },

    /**
     * راه‌اندازی یک input اسلاگ
     * @param {HTMLInputElement} input
     */
    init(input) {
        if (!input || input.dataset.slugInit) return;
        input.dataset.slugInit = '1';

        let isManual = input.value.trim() !== '';

        // جلوگیری از تایپ کاراکترهای غیرمجاز (کلید به کلید)
        input.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey || e.altKey) return;
            if (e.key.length !== 1) return; // کلیدهای کنترلی مانند Backspace, Arrow

            const last = input.value.slice(-1);
            const isSpace = e.key === ' ';
            const isDash = e.key === '-';

            // جلوگیری از دوبار فاصله یا خط تیره پشت سر هم
            if ((isSpace || isDash) && (last === ' ' || last === '-')) {
                e.preventDefault();
                return;
            }

            // فقط حروف انگلیسی، اعداد، فاصله، آندرلاین و خط تیره مجازند
            if (!/[a-zA-Z0-9\-_ ]/.test(e.key)) {
                e.preventDefault();
            }
        });

        // تمیزکاری هنگام ورود (پوشش paste و ...)
        input.addEventListener('input', (e) => {
            if (input.value !== '') isManual = true;

            const start = input.selectionStart;
            const raw = input.value;
            const cleaned = window.SlugHelper.slugify(raw);

            if (cleaned !== raw) {
                const diff = raw.length - cleaned.length;
                input.value = cleaned;
                const pos = Math.max(0, (start ?? 0) - diff);
                input.setSelectionRange(pos, pos);
            }
        });

        // حذف خط تیره‌های ابتدا و انتها هنگام خروج
        input.addEventListener('blur', () => {
            input.value = input.value.replace(/^-+|-+$/g, '');
            if (input.value === '') isManual = false;
        });

        // تولید خودکار اسلاگ از روی فیلد مبدأ (data-slug-source یا عنوان)
        const sourceSel = input.dataset.slugSource;
        let source = null;

        if (sourceSel) {
            source = document.querySelector(sourceSel);
        } else {
            const form = input.closest('form');
            const candidates = [
                'input[name="title"]',
                'input[name="name[en]"]',
                'input[name="name[fa]"]',
            ];
            for (const sel of candidates) {
                source = (form || document).querySelector(sel);
                if (source) break;
            }
        }

        if (source) {
            const sync = () => {
                if (!isManual && source.value) {
                    // حذف خط تیره‌های اضافی ابتدا و انتها هنگام تولید خودکار
                    input.value = window.SlugHelper.slugify(source.value).replace(/^-+|-+$/g, '');
                }
            };
            source.addEventListener('input', sync);
            sync(); // اگر اسلاگ خالی بود، از مقدار فعلی مبدأ پر کن
        }

        // اگر فیلد خالی شد، دوباره حالت خودکار فعال شود
        input.addEventListener('focus', () => {
            isManual = input.value !== '';
        });
    },

    /**
     * راه‌اندازی همه فیلدهای اسلاگ در یک محدوده
     */
    initAll(scope = document) {
        scope.querySelectorAll('input[data-auto-slug]').forEach((el) => window.SlugHelper.init(el));
    },
};

document.addEventListener('DOMContentLoaded', () => {
    window.SlugHelper.initAll();
});
