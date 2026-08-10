@props([
    'name',
    'label',
    'currentImage' => null,
    'removable' => false,
    'removeKey' => 'remove_image',
    'hint' => 'JPG، PNG یا WebP | حداکثر حجم: ۲ مگابایت',
    // تنظیمات کراپ — null یعنی آزاد (بدون نسبت/ابعاد اجباری)
    'cropAspectRatio' => null,
    'cropWidth' => null,
    'cropHeight' => null,
    // حداکثر حجم مجاز به بایت — null یعنی بدون محدودیت سمت کلاینت (سرویس‌ورژن هم چک می‌کند)
    'maxSize' => null,
])

<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ $label }}</label>

    <div class="flex flex-col sm:flex-row gap-4">
        {{-- پیش‌نمایش تصویر --}}
        <div class="relative shrink-0 mx-auto sm:mx-0">
            <div id="preview-{{ $name }}"
                 class="w-32 h-32 rounded-xl border-2 border-gray-200 dark:border-gray-600 overflow-hidden bg-gray-50 dark:bg-gray-800 flex items-center justify-center">
                @if($currentImage)
                    <img src="{{ $currentImage }}" class="w-full h-full object-cover" alt="پیش‌نمایش تصویر">
                @else
                    <svg class="w-10 h-10 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                @endif
            </div>

            {{-- دکمه پاک‌کردن انتخاب جدید / حذف تصویر فعلی --}}
            <button type="button" id="clear-btn-{{ $name }}"
                    onclick="fbImageClearOrRemove('{{ $name }}')"
                    title="حذف تصویر"
                    class="absolute -top-2 -right-2 w-7 h-7 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center shadow-md hover:scale-110 active:scale-95 transition-all duration-150 @if(!($currentImage && $removable)) hidden @endif">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- دراپ‌زون انتخاب تصویر --}}
        <div class="flex-1">
            <div id="dropzone-{{ $name }}"
                 class="dropzone-area relative flex flex-col items-center justify-center gap-2 w-full min-h-[8.5rem] rounded-2xl border-2 border-dashed border-gray-300 dark:border-gray-600 bg-gray-50/60 dark:bg-gray-800/40 hover:border-primary-400 hover:bg-primary-50/50 dark:hover:border-primary-500 dark:hover:bg-primary-900/10 cursor-pointer transition-all duration-200 px-6 py-6 text-center"
                 onclick="document.getElementById('{{ $name }}').click()"
                 ondragover="event.preventDefault(); this.classList.add('dragover')"
                 ondragleave="event.preventDefault(); this.classList.remove('dragover')"
                 ondrop="if (event.dataTransfer.files && event.dataTransfer.files[0] && !event.dataTransfer.files[0].type.startsWith('image/')) { fbImageDropInvalid(event, '{{ $name }}'); } else { fbImageDrop(event, '{{ $name }}'); }">

                <div class="w-12 h-12 rounded-full bg-primary-50 dark:bg-primary-900/30 text-primary-500 flex items-center justify-center transition-transform duration-200 group-hover:scale-110">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                </div>

                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">تصویر را انتخاب کنید</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">کلیک کنید یا فایل را اینجا رها کنید</p>

                {{-- نام فایل انتخاب‌شده --}}
                <div id="file-chip-{{ $name }}" class="hidden mt-1">
                    <span class="inline-flex items-center gap-1.5 text-xs font-medium bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 px-3 py-1.5 rounded-full">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span id="file-chip-text-{{ $name }}"></span>
                    </span>
                </div>
            </div>

            {{-- اعلان «تصویر فعلی حذف خواهد شد» --}}
            <div id="remove-notice-{{ $name }}" class="hidden mt-2">
                <p class="text-xs font-medium bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20 rounded-lg px-3 py-2">
                    تصویر فعلی هنگام ذخیره حذف خواهد شد.
                    <button type="button" onclick="fbImageUndoRemove('{{ $name }}')"
                            class="underline font-semibold hover:text-amber-700 dark:hover:text-amber-300 ms-1">انصراف</button>
                </p>
            </div>

            @if($hint)
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ $hint }}</p>
            @endif

            @error($name)
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- اینپوت فایل مخفی — با کلیک روی دراپ‌زون یا کشیدن فایل باز می‌شود --}}
    {{-- data-no-crop: کراپ داخل خود کامپوننت انجام می‌شود (نه توسط هندلر سراسری) --}}
    <input type="file" name="{{ $name }}" id="{{ $name }}" accept="image/*" class="hidden" data-no-crop
           onchange="fbImageSelect(this, '{{ $name }}')">

    {{-- حذف تصویر فعلی هنگام ذخیره --}}
    @if($removable)
        <input type="hidden" name="{{ $removeKey }}" id="remove-{{ $name }}" value="0">
    @endif
</div>

<script>
    window.fbImageState = window.fbImageState || {};
    (function () {
        const name = '{{ $name }}';
        const preview = document.getElementById('preview-' + name);
        const placeholderHtml = '<svg class="w-10 h-10 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>';
        window.fbImageState[name] = {
            current: @json($currentImage),
            removable: @json($removable),
            originalHtml: preview.innerHTML,
            placeholderHtml: placeholderHtml,
            hasSelection: false,
            cropAspectRatio: @json($cropAspectRatio),
            cropWidth: @json($cropWidth),
            cropHeight: @json($cropHeight),
            maxSize: @json($maxSize),
            croppedUrl: null,
        };
    })();

    // نکته: اگر در یک صفحه چند نمونه از این کامپوننت دارید، برای هر کدام name یکتا بدهید
    // (state و شناسه‌ها بر اساس name ساخته می‌شوند)
    // انتخاب تصویر → باز شدن کراپر → نمایش پیش‌نمایش تصویر کراپ‌شده
    async function fbImageSelect(input, name) {
        const file = input.files && input.files[0];
        if (!file) return;

        const st = window.fbImageState[name];

        // فایل انتخابی تصویر نیست — خطا و حذف انتخاب
        if (!file.type.startsWith('image/')) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: @json(__('admin.common.not_an_image'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    text: file.name,
                    confirmButtonText: @json(__('admin.common.ok'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                });
            }
            input.value = '';
            return;
        }

        // حجم بیش از حد مجاز — خطا و حذف انتخاب
        if (st.maxSize && file.size > st.maxSize) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: @json(__('admin.common.file_too_large'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    text: file.name,
                    confirmButtonText: @json(__('admin.common.ok'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                });
            }
            input.value = '';
            return;
        }

        st.hasSelection = true;

        let blob;
        try {
            blob = await window.ImageCropper.open(file, {
                aspectRatio: st.cropAspectRatio,
                outputWidth: st.cropWidth || undefined,
                outputHeight: st.cropHeight || undefined,
            });
        } catch (e) {
            // انصراف از کراپ — فایل انتخاب‌شده حذف می‌شود
            input.value = '';
            st.hasSelection = false;
            return;
        }

        // جایگزینی فایل اینپوت با خروجی کراپ‌شده
        const croppedName = file.name.replace(/\.[^.]+$/, '') + '.webp';
        const dt = new DataTransfer();
        dt.items.add(new File([blob], croppedName, { type: blob.type || 'image/webp' }));
        input.files = dt.files;

        if (st.croppedUrl) URL.revokeObjectURL(st.croppedUrl);
        st.croppedUrl = URL.createObjectURL(blob);

        const preview = document.getElementById('preview-' + name);
        preview.innerHTML = '<img src="' + st.croppedUrl + '" class="w-full h-full object-cover">';
        preview.classList.remove('opacity-40', 'grayscale');
        document.getElementById('clear-btn-' + name).classList.remove('hidden');

        // نام فایل
        document.getElementById('file-chip-text-' + name).textContent = croppedName;
        document.getElementById('file-chip-' + name).classList.remove('hidden');

        // انتخاب فایل جدید یعنی حذفِ تصویرِ قبلی منتفی است
        const removeInput = document.getElementById('remove-' + name);
        if (removeInput) removeInput.value = '0';
        document.getElementById('remove-notice-' + name).classList.add('hidden');
    }

    function fbImageClearOrRemove(name) {
        const input = document.getElementById(name);
        if (input.files && input.files.length > 0) {
            fbImageClear(name);
        } else {
            fbImageRemoveCurrent(name);
        }
    }

    function fbImageClear(name) {
        const st = window.fbImageState[name];
        const input = document.getElementById(name);
        input.value = '';
        st.hasSelection = false;
        if (st.croppedUrl) { URL.revokeObjectURL(st.croppedUrl); st.croppedUrl = null; }

        const preview = document.getElementById('preview-' + name);
        preview.innerHTML = st.originalHtml;
        preview.classList.remove('opacity-40', 'grayscale');
        document.getElementById('file-chip-' + name).classList.add('hidden');

        // اگر تصویر فعلی موجود و قابل حذف باشد، دکمه X باز می‌ماند
        const clearBtn = document.getElementById('clear-btn-' + name);
        if (st.current && st.removable) {
            clearBtn.classList.remove('hidden');
        } else {
            clearBtn.classList.add('hidden');
        }
    }

    function fbImageRemoveCurrent(name) {
        const st = window.fbImageState[name];
        if (!st.current || !st.removable || st.hasSelection) return;

        const preview = document.getElementById('preview-' + name);
        preview.innerHTML = st.placeholderHtml;
        preview.classList.add('opacity-40', 'grayscale');
        document.getElementById('clear-btn-' + name).classList.add('hidden');
        document.getElementById('remove-' + name).value = '1';
        document.getElementById('remove-notice-' + name).classList.remove('hidden');
    }

    function fbImageUndoRemove(name) {
        const st = window.fbImageState[name];
        const preview = document.getElementById('preview-' + name);
        preview.innerHTML = st.originalHtml;
        preview.classList.remove('opacity-40', 'grayscale');
        document.getElementById('clear-btn-' + name).classList.remove('hidden');
        document.getElementById('remove-' + name).value = '0';
        document.getElementById('remove-notice-' + name).classList.add('hidden');
    }

    function fbImageDrop(event, name) {
        event.preventDefault();
        const zone = document.getElementById('dropzone-' + name);
        zone.classList.remove('dragover');

        const file = event.dataTransfer.files && event.dataTransfer.files[0];
        if (!file) return;
        if (!file.type.startsWith('image/')) return;

        const input = document.getElementById(name);
        const dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
        fbImageSelect(input, name);
    }

    // دراپ فایل غیرتصویر — از آپلود جلوگیری و خطا نمایش داده می‌شود
    function fbImageDropInvalid(event, name) {
        event.preventDefault();
        const zone = document.getElementById('dropzone-' + name);
        zone.classList.remove('dragover');

        const file = event.dataTransfer.files && event.dataTransfer.files[0];
        if (!file) return;

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: @json(__('admin.common.not_an_image'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                text: file.name,
                confirmButtonText: @json(__('admin.common.ok'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            });
        }
    }
</script>
