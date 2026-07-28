@props(['name', 'label', 'currentImage' => null, 'width' => 400, 'height' => 280])

<div class="col-span-2">
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ $label }}</label>

    <div class="flex items-start gap-4">
        {{-- پیش‌نمایش تصویر --}}
        <div class="relative group">
            <div id="preview-{{ $name }}" class="w-32 h-32 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 flex items-center justify-center overflow-hidden bg-gray-50 dark:bg-gray-700">
                @if($currentImage)
                    <img src="{{ $currentImage }}" class="w-full h-full object-cover" alt="preview">
                @else
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                @endif
            </div>
            @if($currentImage)
                <button type="button" onclick="removeImage('{{ $name }}')" class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            @endif
        </div>

        {{-- اینپوت فایل --}}
        <div class="flex-1">
            <input type="file" name="{{ $name }}" id="{{ $name }}" accept="image/*"
                   class="input-custom file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-primary-900/30 dark:file:text-primary-300 cursor-pointer"
                   onchange="previewImage(this, '{{ $name }}')">
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                فرمت‌های مجاز: JPG, PNG, WebP | حداکثر حجم: ۲ مگابایت
            </p>
            @error($name)
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

<script>
    function previewImage(input, name) {
        const preview = document.getElementById('preview-' + name);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" class="w-full h-full object-cover">';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function removeImage(name) {
        const input = document.getElementById(name);
        const preview = document.getElementById('preview-' + name);
        input.value = '';
        preview.innerHTML = '<svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>';
    }
</script>
