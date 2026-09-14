{{--
    کامپوننت همگانی کراپر تصویر (Cropper.js)
    استفاده:
        <x-admin.image-cropper />
    سپس در جاوااسکریپت:
        const blob = await window.ImageCropper.open(file, {
            aspectRatio: 1,        // یا '4/3' ، '16/9' ، null برای آزاد
            outputWidth: 1200,     // اختیاری — اگر ندهید، خروجی با سایز طبیعی کادر کراپ می‌شود
            outputHeight: 1200,    // اختیاری — اگر ندهید، خروجی با سایز طبیعی کادر کراپ می‌شود
            format: 'image/webp',  // فرمت خروجی
            quality: 0.92,
        });
        // blob → فایل جدید برای آپلود
        const cropped = new File([blob], 'image.webp', { type: blob.type });
--}}
@props(['aspectRatio' => null])

@once
    <link rel="stylesheet" href="{{ asset('assets/admin/cropper/cropper.min.css') }}">
    <script src="{{ asset('assets/admin/cropper/cropper.min.js') }}"></script>
    <style>
        /* ایزولاسیون کامل و حل باگ RTL و Tailwind برای Cropper.js */
        #image-cropper-modal .cropper-container {
            direction: ltr !important;
            text-align: left !important;
            position: relative !important;
            user-select: none !important;
            -webkit-user-select: none !important;
            margin: 0 auto !important;
        }

        #image-cropper-modal .cropper-container *,
        #image-cropper-modal .cropper-container *::before,
        #image-cropper-modal .cropper-container *::after {
            box-sizing: content-box !important;
        }

        #image-cropper-modal .cropper-view-box {
            box-sizing: border-box !important;
            outline: 2px solid #6366f1 !important;
            outline-color: rgba(99, 102, 241, 0.9) !important;
        }

        #image-cropper-modal .cropper-view-box,
        #image-cropper-modal .cropper-face {
            border-radius: 0 !important;
        }

        /* جلوگیری از خروج کادر کراپ از سمت راست یا تغییر موقعیت در قالب RTL */
        #image-cropper-modal .cropper-crop-box,
        #image-cropper-modal .cropper-canvas {
            right: auto !important;
            left: 0 !important;
            direction: ltr !important;
        }

        #image-cropper-modal .cropper-wrap-box,
        #image-cropper-modal .cropper-drag-box,
        #image-cropper-modal .cropper-modal {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100% !important;
            height: 100% !important;
            direction: ltr !important;
        }

        #image-cropper-modal .cropper-container img,
        #image-cropper-modal .cropper-view-box img,
        #image-cropper-modal .cropper-wrap-box img,
        #image-cropper-modal .cropper-canvas img {
            max-width: none !important;
            max-height: none !important;
            min-width: 0 !important;
            min-height: 0 !important;
            transition: none !important;
            animation: none !important;
            transform-origin: center center !important;
            position: static !important;
            display: block !important;
        }

        #image-cropper-modal .cropper-point {
            background-color: #6366f1 !important;
            width: 8px !important;
            height: 8px !important;
            opacity: 0.95 !important;
        }

        #image-cropper-modal .cropper-line {
            background-color: #6366f1 !important;
        }
    </style>
    <script>
        window.ImageCropper = (function () {
            let cropper = null;
            let modalEl = null;
            let imageEl = null;
            let resolveFn = null;
            let rejectFn = null;
            let currentOptions = {};

            function ensureModal() {
                if (modalEl) return;

                modalEl = document.createElement('div');
                modalEl.id = 'image-cropper-modal';
                modalEl.className = 'fixed inset-0 z-[9999] hidden items-center justify-center bg-black/75 p-4 backdrop-blur-sm';
                modalEl.innerHTML = `
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden flex flex-col max-h-[92vh]">
                        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-base font-bold text-gray-800 dark:text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                برش تصویر
                            </h3>
                            <button type="button" data-cropper-action="cancel" class="w-9 h-9 rounded-xl text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-center transition-all" title="بستن">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        {{-- ناحیه تصویر: بدون اسکرول — ایزوله در جهت LTR برای هماهنگی کامل با Cropper.js --}}
                        <div class="bg-gray-950 flex-1 min-h-[360px] max-h-[62vh] flex items-center justify-center overflow-hidden" dir="ltr" style="direction: ltr; text-align: left;">
                            <div class="w-full h-full flex items-center justify-center" dir="ltr" style="direction: ltr;">
                                <img id="image-cropper-source" alt="تصویر برای برش" class="block max-w-full max-h-[60vh] mx-auto" style="direction: ltr;">
                            </div>
                        </div>

                        <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-700 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-2 flex-wrap">
                                <button type="button" data-cropper-action="rotate" class="px-3 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-all flex items-center gap-1.5" title="چرخش ۹۰ درجه">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    چرخش
                                </button>
                                <button type="button" data-cropper-action="flip" class="px-3 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-all flex items-center gap-1.5" title="آینه افقی">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                    آینه
                                </button>
                                <button type="button" data-cropper-action="zoom-in" class="px-3 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-all" title="بزرگ‌نمایی">+</button>
                                <button type="button" data-cropper-action="zoom-out" class="px-3 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-all" title="کوچک‌نمایی">−</button>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" data-cropper-action="cancel" class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium transition-all">انصراف</button>
                                <button type="button" data-cropper-action="confirm" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold shadow-sm hover:shadow-md transition-all flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    تایید برش
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                document.body.appendChild(modalEl);
                imageEl = modalEl.querySelector('#image-cropper-source');

                modalEl.addEventListener('click', (e) => {
                    const btn = e.target.closest('[data-cropper-action]');
                    if (!btn) return;
                    const action = btn.dataset.cropperAction;
                    if (action === 'confirm') confirmCrop();
                    else if (action === 'cancel') cancel();
                    else if (action === 'rotate') cropper?.rotate(90);
                    else if (action === 'flip') cropper?.scaleX(-cropper.getData().scaleX || -1);
                    else if (action === 'zoom-in') cropper?.zoom(0.1);
                    else if (action === 'zoom-out') cropper?.zoom(-0.1);
                });

                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && !modalEl.classList.contains('hidden')) cancel();
                    if (e.key === 'Enter' && !modalEl.classList.contains('hidden')) confirmCrop();
                });
            }

            function show() {
                modalEl.classList.remove('hidden');
                modalEl.classList.add('flex');
                document.body.style.overflow = 'hidden';
            }

            function hide() {
                modalEl.classList.add('hidden');
                modalEl.classList.remove('flex');
                document.body.style.overflow = '';
            }

            function confirmCrop() {
                if (!cropper) return;
                const { outputWidth, outputHeight, format, quality } = currentOptions;

                const canvas = cropper.getCroppedCanvas({
                    width: outputWidth,
                    height: outputHeight,
                    imageSmoothingQuality: 'high',
                    fillColor: '#fff',
                });

                canvas.toBlob((blob) => {
                    const resolve = resolveFn;
                    close();
                    if (blob && resolve) resolve(blob);
                }, format, quality);
            }

            function cancel() {
                const reject = rejectFn;
                close();
                if (reject) reject(new Error(@json(__('admin.common.crop_cancelled'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)));
            }

            function close() {
                if (cropper) { cropper.destroy(); cropper = null; }
                hide();
                resolveFn = null;
                rejectFn = null;
            }

            /**
             * باز کردن کراپر
             * @param {File} file فایل تصویر انتخاب‌شده
             * @param {object} options تنظیمات برش
             * @returns {Promise<Blob>}
             */
            function open(file, options = {}) {
                ensureModal();

                const aspect = options.aspectRatio ?? @json($aspectRatio) ?? null;
                currentOptions = {
                    aspectRatio: aspect,
                    // بدون ابعاد مشخص → سایز طبیعی کادر کراپ (کراپ آزاد)
                    outputWidth: options.outputWidth || undefined,
                    outputHeight: options.outputHeight || undefined,
                    format: options.format || 'image/webp',
                    quality: options.quality ?? 0.92,
                };

                return new Promise((resolve, reject) => {
                    resolveFn = resolve;
                    rejectFn = reject;

                    const reader = new FileReader();
                    reader.onload = (e) => {
                        imageEl.src = e.target.result;
                        imageEl.onload = () => {
                            show();
                            if (cropper) {
                                cropper.destroy();
                                cropper = null;
                            }
                            requestAnimationFrame(() => {
                                cropper = new Cropper(imageEl, {
                                    viewMode: 2,
                                    dragMode: 'crop',
                                    autoCropArea: 0.9,
                                    aspectRatio: currentOptions.aspectRatio || undefined,
                                    background: false,
                                    guides: true,
                                    center: true,
                                    highlight: true,
                                    responsive: true,
                                    checkOrientation: true,
                                    modal: true,
                                });
                            });
                        };
                    };
                    reader.onerror = () => { reject(new Error(@json(__('admin.common.read_image_error'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))); close(); };
                    reader.readAsDataURL(file);
                });
            }

            return { open };
        })();
    </script>
@endonce
