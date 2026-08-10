import Alpine from 'alpinejs';
import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/pagination';

// بومی‌سازی ارقام بر اساس زبان سایت (fa/ar → فارسی، سایر → انگلیسی)
import './localize-digits.js';

window.Alpine = Alpine;

/* ==========================================
   سازنده واریانت محصول (Product Spec Builder)
   ترکیب مقادیر انتخاب‌شده مشخصات → جدول تنوع‌ها
   ========================================== */
window.productSpecBuilder = function (payload) {
    return {
        groups: payload.groups || [],
        textSpecs: payload.textSpecs || [],
        selected: payload.selected || {},
        textValues: payload.textValues || {},
        properties: payload.properties || [],
        variants: [],

        init() {
            this.initTextValues();
            this.rebuildVariants();
        },

        isSelected(groupId, childId) {
            return (this.selected[groupId] || []).includes(childId);
        },

        toggle(groupId, childId) {
            const arr = this.selected[groupId] || [];
            const idx = arr.indexOf(childId);
            if (idx >= 0) {
                arr.splice(idx, 1);
            } else {
                arr.push(childId);
            }
            this.selected[groupId] = arr;
            this.rebuildVariants();
        },

        // ساخت ضرب دکارتی مقادیر انتخاب‌شده
        buildCombos() {
            const groupsWithSelection = this.groups
                .map(g => ({ g, ids: (this.selected[g.id] || []) }))
                .filter(x => x.ids.length > 0);

            if (groupsWithSelection.length === 0) return [];

            // حفظ ترتیب اصلی گروه‌ها
            const ordered = this.groups
                .map(g => ({ g, ids: (this.selected[g.id] || []) }))
                .filter(x => x.ids.length > 0);

            let combos = [[]];
            for (const { g, ids } of ordered) {
                const next = [];
                for (const combo of combos) {
                    for (const id of ids) {
                        const child = g.children.find(c => c.id === id);
                        next.push([...combo, { groupId: g.id, childId: id, label: child ? child.title : '—' }]);
                    }
                }
                combos = next;
            }

            return combos.map(combo => {
                const specIds = combo.map(c => c.childId).sort((a, b) => a - b);
                return {
                    key: specIds.join('-'),
                    spec_ids: specIds,
                    label: combo.map(c => c.label).join(' / '),
                };
            });
        },

        rebuildVariants() {
            // حفظ مقادیر واردشده قبلی برای هر ترکیب
            const prevValues = {};
            this.variants.forEach(v => { prevValues[v.key] = v; });

            const existingByKey = {};
            (payload.variants || []).forEach(v => {
                const sorted = [...v.spec_ids].sort((a, b) => a - b);
                existingByKey[sorted.join('-')] = v;
            });

            const combos = this.buildCombos();
            this.variants = combos.map(combo => {
                const existing = existingByKey[combo.key];
                const prev = prevValues[combo.key];

                return {
                    key: combo.key,
                    label: combo.label,
                    spec_ids: combo.spec_ids,
                    id: prev && prev.id !== '' ? prev.id : (existing ? existing.id : ''),
                    price: prev && prev.price !== '' ? prev.price : (existing ? existing.price : ''),
                    discount_price: prev && prev.discount_price !== '' ? prev.discount_price : (existing ? existing.discount_price : ''),
                    stock: prev && prev.stock !== '' ? prev.stock : (existing ? existing.stock : ''),
                    weight: prev && prev.weight !== '' ? prev.weight : (existing ? existing.weight : 100),
                };
            });
        },

        addProperty() {
            this.properties.push({ id: '', title: '', value: '' });
        },

        initTextValues() {
            // اطمینان از وجود مقدار اولیه برای همه مشخصه‌های نوشتاری
            this.textSpecs.forEach(spec => {
                if (!(spec.id in this.textValues)) {
                    this.textValues[spec.id] = '';
                }
            });
        },
    };
};

/* ==========================================
   انتخابگر محصولات مرتبط/مکمل
   ========================================== */
window.productAssociationsPicker = function (payload) {
    return {
        products: payload.products || [],
        relatedIds: payload.relatedIds || [],
        complementIds: payload.complementIds || [],
        search: '',

        init() {},

        get filteredProducts() {
            const q = this.search.trim().toLowerCase();
            if (!q) return this.products;
            return this.products.filter(p => p.label.toLowerCase().includes(q));
        },

        isRelated(id) { return this.relatedIds.includes(id); },
        isComplement(id) { return this.complementIds.includes(id); },

        toggleRelated(id) {
            const idx = this.relatedIds.indexOf(id);
            if (idx >= 0) this.relatedIds.splice(idx, 1);
            else this.relatedIds.push(id);
        },

        toggleComplement(id) {
            const idx = this.complementIds.indexOf(id);
            if (idx >= 0) this.complementIds.splice(idx, 1);
            else this.complementIds.push(id);
        },

        get selectedRelated() {
            return this.products.filter(p => this.relatedIds.includes(p.id));
        },

        get selectedComplement() {
            return this.products.filter(p => this.complementIds.includes(p.id));
        },
    };
};

Alpine.start();

document.querySelectorAll('[data-countdown]').forEach(timer => {
    const endDate = new Date(timer.dataset.countdown).getTime();
    const isPersian = window.localizeDigits && window.localizeDigits.isPersian();

    const interval = setInterval(() => {
        const now = new Date().getTime();
        const distance = endDate - now;

        if (distance < 0) {
            clearInterval(interval);
            timer.innerHTML = isPersian ? "پایان یافته" : "Ended";
            return;
        }

        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        const pad = n => String(n).padStart(2, '0');
        let out = `${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;
        if (isPersian) out = out.replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
        timer.innerHTML = out;
    }, 1000);
});


/* ==========================================
   Custom Admin Scripts (Global)
   ========================================== */

document.addEventListener('DOMContentLoaded', function() {
    initProductGallery();
    initGlobalImageCrop();
});

/* ==========================================
   کراپ سراسری تصویر — هر input[type=file] تک‌تصویری ادمین
   (هیرو اسلاید، بلاگ، برند، لوگو/فاوآیکون تنظیمات و …)
   ========================================== */
function initGlobalImageCrop() {
    // ورودی‌هایی که خودشان کراپ می‌کنند (image-upload) با data-no-crop علامت زده شده‌اند
    // و گالری محصول (multiple) فلو اختصاصی خودش را دارد.
    document.addEventListener('change', async (e) => {
        const input = e.target;
        if (!(input instanceof HTMLInputElement) || input.type !== 'file') return;
        if (input.multiple || input.dataset.noCrop !== undefined) return;
        if (!input.accept || !input.accept.toLowerCase().includes('image')) return;

        const file = input.files && input.files[0];
        if (!file || !window.ImageCropper) return;

        try {
            const aspect = input.dataset.cropAspect ? parseFloat(input.dataset.cropAspect) : null;
            const blob = await window.ImageCropper.open(file, { aspectRatio: aspect });

            const croppedName = file.name.replace(/\.[^.]+$/, '') + '.webp';
            const dt = new DataTransfer();
            dt.items.add(new File([blob], croppedName, { type: blob.type || 'image/webp' }));
            input.files = dt.files;
        } catch (e) {
            // کاربر انصراف داد — فایل انتخاب‌شده حذف می‌شود
            input.value = '';
        }
    });
}

/* ==========================================
   گالری تصاویر محصول — کراپ + تصویر اصلی + حذف
   ========================================== */
function initProductGallery() {
    const dropzone = document.getElementById('dropzone');
    const imageInput = document.getElementById('image-input');
    const pendingContainer = document.getElementById('gallery-pending');
    const existingContainer = document.getElementById('gallery-existing');
    const mainImageIdInput = document.getElementById('main-image-id');
    const mainNewIndexInput = document.getElementById('main-new-index');
    const deleteContainer = document.getElementById('delete-images-container');

    if (!dropzone || !imageInput || !pendingContainer) return;

    const form = imageInput.closest('form');

    // وضعیت گالری
    let pendingImages = [];            // [{ file, url }] — تصاویر جدید کراپ‌شده
    let mainImageId = mainImageIdInput.value || '';   // تصویر اصلی موجود
    let mainNewIndex = mainNewIndexInput.value || ''; // تصویر اصلی جدید (ایندکس pending)
    const deletedIds = new Set();      // شناسه تصاویر موجود که باید حذف شوند

    // ---------- رندر کاشی‌های pending ----------
    function renderPending() {
        pendingContainer.innerHTML = '';

        if (pendingImages.length === 0) {
            pendingContainer.classList.add('hidden');
            pendingContainer.classList.remove('grid');
            return;
        }

        pendingContainer.classList.remove('hidden');
        pendingContainer.classList.add('grid');

        pendingImages.forEach((item, index) => {
            const isMain = mainNewIndex === String(index);

            const tile = document.createElement('div');
            tile.className = 'gallery-tile relative group rounded-xl overflow-hidden border-2 aspect-square shadow-sm bg-gray-100 dark:bg-gray-800 transition-all ' +
                (isMain ? 'border-amber-400' : 'border-gray-200 dark:border-gray-700');

            tile.innerHTML = `
                <img src="${item.url}" class="w-full h-full object-cover" alt="تصویر جدید">
                <span class="gallery-main-badge absolute top-2 left-2 bg-amber-400 text-amber-950 text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm ${isMain ? '' : 'hidden'}">⭐ اصلی</span>
                <button type="button" class="gallery-star absolute bottom-2 left-2 w-7 h-7 rounded-full bg-white/80 dark:bg-gray-900/70 backdrop-blur-sm ${isMain ? 'text-amber-500' : 'text-gray-500 hover:text-amber-500'} flex items-center justify-center transition-all" title="انتخاب به عنوان تصویر اصلی">
                    <svg class="w-4 h-4" fill="${isMain ? 'currentColor' : 'none'}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.562.562 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/></svg>
                </button>
                <button type="button" class="gallery-delete absolute top-2 right-2 w-7 h-7 rounded-full bg-rose-500 hover:bg-rose-600 text-white flex items-center justify-center shadow-sm opacity-60 group-hover:opacity-100 transition-all" title="حذف تصویر">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            `;

            // ستاره: انتخاب به عنوان تصویر اصلی
            tile.querySelector('.gallery-star').addEventListener('click', () => {
                mainNewIndex = String(index);
                mainImageId = '';
                renderPending();
                refreshExisting();
                syncHidden();
            });

            // ضربدر: حذف از لیست pending
            tile.querySelector('.gallery-delete').addEventListener('click', () => {
                pendingImages.splice(index, 1);
                if (mainNewIndex === String(index)) {
                    mainNewIndex = '';
                    // اگر تصویر اول حذف شد و تصویر دیگری هست، اولین را اصلی کن
                    if (pendingImages.length > 0) mainNewIndex = '0';
                } else if (mainNewIndex !== '' && parseInt(mainNewIndex) > index) {
                    mainNewIndex = String(parseInt(mainNewIndex) - 1);
                }
                renderPending();
                syncHidden();
            });

            pendingContainer.appendChild(tile);
        });
    }

    // ---------- به‌روزرسانی کاشی‌های موجود (اصلی/حذف) ----------
    function refreshExisting() {
        if (!existingContainer) return;

        existingContainer.querySelectorAll('.gallery-tile').forEach(tile => {
            const id = tile.dataset.imageId;
            const isMain = mainImageId === id;
            const isDeleted = deletedIds.has(id);

            tile.classList.toggle('gallery-deleted', isDeleted);
            tile.classList.toggle('border-amber-400', isMain);
            tile.classList.toggle('border-gray-200', !isMain);
            tile.classList.toggle('dark:border-gray-700', !isMain);

            const badge = tile.querySelector('.gallery-main-badge');
            if (badge) badge.classList.toggle('hidden', !isMain);

            const star = tile.querySelector('.gallery-star');
            if (star) {
                star.classList.toggle('text-amber-500', isMain);
                star.classList.toggle('text-gray-500', !isMain);
                star.querySelector('svg').setAttribute('fill', isMain ? 'currentColor' : 'none');
            }

            const overlay = tile.querySelector('.gallery-deleting-overlay');
            if (overlay) {
                overlay.classList.toggle('hidden', !isDeleted);
                overlay.classList.toggle('flex', isDeleted);
            }
        });
    }

    // ---------- همگام‌سازی input‌های مخفی ----------
    function syncHidden() {
        mainImageIdInput.value = mainImageId;
        mainNewIndexInput.value = mainNewIndex;

        // ساخت inputهای مخفی delete_images[]
        deleteContainer.innerHTML = '';
        deletedIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'delete_images[]';
            input.value = id;
            deleteContainer.appendChild(input);
        });
    }

    // ---------- کراپ و افزودن تصاویر جدید ----------
    async function processFiles(fileList) {
        for (const file of Array.from(fileList)) {
            if (!file.type.startsWith('image/')) continue;

            let blob;
            try {
                // باز کردن کراپر برای هر تصویر (کراپر کامپوننت همگانی)
                blob = await window.ImageCropper.open(file, {
                    aspectRatio: 1,          // مربع — هماهنگ با رسیز محصول
                    outputWidth: 1200,
                    outputHeight: 1200,
                    format: 'image/webp',
                    quality: 0.92,
                });
            } catch (e) {
                // کاربر انصراف داد یا خطا — رد شدن از این تصویر
                continue;
            }

            const croppedFile = new File([blob], `image-${Date.now()}.webp`, { type: 'image/webp' });
            pendingImages.push({
                file: croppedFile,
                url: URL.createObjectURL(croppedFile),
            });
        }

        // اگر هنوز تصویر اصلی انتخاب نشده، اولین تصویر جدید را اصلی کن
        if (mainImageId === '' && mainNewIndex === '' && pendingImages.length > 0) {
            mainNewIndex = '0';
        }

        renderPending();
        syncHidden();
        imageInput.value = ''; // امکان انتخاب مجدد همان فایل
    }

    // ---------- رویدادهای دراپ‌زون ----------
    dropzone.addEventListener('click', (e) => {
        if (e.target.tagName !== 'INPUT' && !e.target.closest('label')) imageInput.click();
    });

    ['dragenter', 'dragover'].forEach(evt => {
        dropzone.addEventListener(evt, (e) => {
            e.preventDefault();
            dropzone.classList.add('dragover');
        });
    });

    ['dragleave', 'drop'].forEach(evt => {
        dropzone.addEventListener(evt, (e) => {
            e.preventDefault();
            dropzone.classList.remove('dragover');
        });
    });

    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        processFiles(e.dataTransfer.files);
    });

    imageInput.addEventListener('change', () => processFiles(imageInput.files));

    // ---------- رویدادهای کاشی‌های موجود ----------
    if (existingContainer) {
        existingContainer.querySelectorAll('.gallery-tile').forEach(tile => {
            const id = tile.dataset.imageId;

            tile.querySelector('.gallery-star').addEventListener('click', () => {
                mainImageId = id;
                mainNewIndex = '';
                refreshExisting();
                renderPending();
                syncHidden();
            });

            tile.querySelector('.gallery-delete').addEventListener('click', () => {
                if (deletedIds.has(id)) {
                    deletedIds.delete(id);
                } else {
                    deletedIds.add(id);
                    // اگر تصویر اصلی را علامت حذف زدیم، تصویر اصلی را به اولین تصویر سالم منتقل کن
                    if (mainImageId === id) {
                        mainImageId = '';
                        const next = Array.from(existingContainer.querySelectorAll('.gallery-tile'))
                            .find(t => t.dataset.imageId !== id && !deletedIds.has(t.dataset.imageId));
                        if (next) mainImageId = next.dataset.imageId;
                    }
                }
                refreshExisting();
                syncHidden();
            });
        });
    }

    // ---------- قبل از ارسال فرم: فایل‌های کراپ‌شده را به input فایل بچسبان ----------
    form.addEventListener('submit', () => {
        syncHidden();
        const dt = new DataTransfer();
        pendingImages.forEach(item => dt.items.add(item.file));
        imageInput.files = dt.files;
    });

    // رندر اولیه
    renderPending();
    refreshExisting();
    syncHidden();
}

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

/* ==========================================
   دارک مود CKEditor — همگام‌سازی بدنه داخل iframe با تم
   محتوای CKEditor داخل iframe مستقل است؛ با JS استایل بدنه را تنظیم می‌کنیم.
   ========================================== */
window.applyAdminThemeToCkeditor = function () {
    if (typeof CKEDITOR === 'undefined') return;

    const isDark = document.documentElement.classList.contains('dark');
    const bg = isDark ? '#1f2937' : '#ffffff';   // gray-800 / سفید
    const fg = isDark ? '#e5e7eb' : '#111827';   // gray-100 / خاکستری تیره

    Object.keys(CKEDITOR.instances).forEach(name => {
        const editor = CKEDITOR.instances[name];
        try {
            const body = editor.document && editor.document.getBody();
            if (body) {
                body.setStyle('background-color', bg);
                body.setStyle('color', fg);
            }
        } catch (e) {
            /* editor هنوز آماده نیست */
        }
    });
};

if (typeof CKEDITOR !== 'undefined') {
    CKEDITOR.on('instanceReady', () => {
        window.applyAdminThemeToCkeditor();
    });
}
