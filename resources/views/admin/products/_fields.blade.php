@php
    // اگر $product پاس داده نشده باشد، مقدار آن را null قرار می‌دهیم
    $product = $product ?? null;
    $isEdit = $product !== null;

    $oldVal = function($key, $default = '') use ($isEdit, $product) {
        if (old($key) !== null) return old($key);
        if ($isEdit && $product) {
            $keys = explode('.', $key);
            $value = $product;
            foreach ($keys as $k) {
                if (is_object($value) && isset($value->$k)) $value = $value->$k;
                elseif (is_array($value) && isset($value[$k])) $value = $value[$k];
                else return $default;
            }
            return $value;
        }
        return $default;
    };
@endphp

<div x-data="{ tab: 'general' }" class="space-y-6">

    <!-- نوار تب‌ها -->
    <div class="flex flex-wrap gap-2 border-b border-gray-200 dark:border-gray-700 pb-4">
        <button type="button" @click="tab='general'" :class="tab === 'general' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400' : 'text-gray-500 hover:text-gray-800 dark:hover:text-gray-200'" class="flex items-center gap-2 px-4 py-2 rounded-lg font-medium text-sm transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            اطلاعات اصلی
        </button>
        <button type="button" @click="tab='pricing'" :class="tab === 'pricing' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400' : 'text-gray-500 hover:text-gray-800 dark:hover:text-gray-200'" class="flex items-center gap-2 px-4 py-2 rounded-lg font-medium text-sm transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            قیمت و موجودی
        </button>
        <button type="button" @click="tab='attributes'" :class="tab === 'attributes' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400' : 'text-gray-500 hover:text-gray-800 dark:hover:text-gray-200'" class="flex items-center gap-2 px-4 py-2 rounded-lg font-medium text-sm transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
            ویژگی‌ها و تنظیمات
        </button>
        <button type="button" @click="tab='images'" :class="tab === 'images' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400' : 'text-gray-500 hover:text-gray-800 dark:hover:text-gray-200'" class="flex items-center gap-2 px-4 py-2 rounded-lg font-medium text-sm transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            گالری تصاویر
        </button>
    </div>

    <!-- تب ۱: اطلاعات اصلی -->
    <div x-show="tab === 'general'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">نام محصول (فارسی) <span class="text-rose-500">*</span></label>
            <input type="text" name="name[fa]" value="{{ $oldVal('name.fa') }}" class="w-full rounded-xl border-gray-200 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200" placeholder="مثال: گوشی موبایل سامسونگ" required>
            @error('name.fa') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">نام انگلیسی <span class="text-rose-500">*</span></label>
            <input type="text" name="name[en]" value="{{ $oldVal('name.en') }}" class="w-full rounded-xl border-gray-200 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200" placeholder="Samsung Galaxy" dir="ltr" required>
            @error('name.en') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="lg:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">آدرس (Slug)</label>
            <div class="relative">
                <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-gray-400 text-sm">/p/</span>
                <!-- کلاس data-auto-slug برای اتصال به JS اختصاصی شما -->
                <input type="text" name="slug" value="{{ $oldVal('slug') }}" class="w-full pr-16 pl-4 py-2.5 rounded-xl border-gray-200 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 ltr:text-left" placeholder="samsung-galaxy" dir="ltr" data-auto-slug>
            </div>
            @error('slug') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">دسته‌بندی <span class="text-rose-500">*</span></label>
            <select name="product_category_id" class="w-full rounded-xl border-gray-200 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200" required>
                <option value="">انتخاب کنید...</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @if($oldVal('product_category_id') == $category->id) selected @endif>{{ $category->trans('name') }}</option>
                @endforeach
            </select>
            @error('product_category_id') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="lg:col-span-3">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">توضیحات کوتاه</label>
            <textarea name="short_description[fa]" rows="2" class="w-full rounded-xl border-gray-200 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200" placeholder="یک توضیح مختصر برای پیش‌نمایش محصول...">{{ $oldVal('short_description.fa') }}</textarea>
        </div>

        <div class="lg:col-span-3">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">توضیحات کامل</label>
            <textarea name="description[fa]" class="ckeditor w-full rounded-xl border-gray-200 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200" rows="6">{{ $oldVal('description.fa') }}</textarea>
        </div>
    </div>

    <!-- تب ۲: قیمت و موجودی -->
    <div x-show="tab === 'pricing'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" style="display: none;">

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">قیمت اصلی (تومان) <span class="text-rose-500">*</span></label>
            <input type="number" name="price" value="{{ $oldVal('price') }}" class="w-full rounded-xl border-gray-200 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200" placeholder="5000000" required>
            @error('price') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">قیمت با تخفیف (تومان)</label>
            <input type="number" name="discount_price" value="{{ $oldVal('discount_price') }}" class="w-full rounded-xl border-gray-200 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200" placeholder="4500000">
            @error('discount_price') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">تاریخ پایان تخفیف</label>
            <input type="datetime-local" name="deal_ends_at" value="{{ $oldVal('deal_ends_at') ? \Carbon\Carbon::parse($oldVal('deal_ends_at'))->format('Y-m-d\TH:i') : '' }}" class="w-full rounded-xl border-gray-200 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200">
            @error('deal_ends_at') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">موجودی انبار <span class="text-rose-500">*</span></label>
            <input type="number" name="stock" value="{{ $oldVal('stock', 0) }}" class="w-full rounded-xl border-gray-200 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200" placeholder="50" required>
            @error('stock') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">کد محصول (SKU)</label>
            <input type="text" name="sku" value="{{ $oldVal('sku') }}" class="w-full rounded-xl border-gray-200 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200" placeholder="PRD-12345" dir="ltr">
            @error('sku') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">وزن (گرم)</label>
            <input type="number" name="weight" value="{{ $oldVal('weight') }}" class="w-full rounded-xl border-gray-200 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200" placeholder="250">
        </div>
    </div>

    <!-- تب ۳: ویژگی‌ها و تنظیمات -->
    <div x-show="tab === 'attributes'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6" style="display: none;">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">رنگ‌ها</label>
                <input type="text" name="colors" value="{{ is_array($oldVal('colors')) ? implode(', ', $oldVal('colors')) : $oldVal('colors') }}" class="w-full rounded-xl border-gray-200 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200" placeholder="قرمز, آبی, مشکی">
                <p class="text-xs text-gray-400 mt-1.5">رنگ‌ها را با کاما (,) از هم جدا کنید.</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">سایزها</label>
                <input type="text" name="sizes" value="{{ is_array($oldVal('sizes')) ? implode(', ', $oldVal('sizes')) : $oldVal('sizes') }}" class="w-full rounded-xl border-gray-200 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200" placeholder="S, M, L, XL">
                <p class="text-xs text-gray-400 mt-1.5">سایزها را با کاما (,) از هم جدا کنید.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
            <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700/50 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">محصول ویژه</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="is_featured" value="0">
                    <input type="checkbox" name="is_featured" value="1" class="sr-only peer" @if($oldVal('is_featured')) checked @endif>
                    <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:ring-2 peer-checked:ring-indigo-500 dark:bg-gray-600 peer-checked:bg-indigo-600 transition-all"></div>
                    <div class="absolute right-1 top-1 bg-white w-4 h-4 rounded-full transition-transform peer-checked:-translate-x-5 dark:bg-gray-300"></div>
                </label>
            </div>

            <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700/50 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">پرفروش‌ترین</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="is_best_seller" value="0">
                    <input type="checkbox" name="is_best_seller" value="1" class="sr-only peer" @if($oldVal('is_best_seller')) checked @endif>
                    <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:ring-2 peer-checked:ring-indigo-500 dark:bg-gray-600 peer-checked:bg-indigo-600 transition-all"></div>
                    <div class="absolute right-1 top-1 bg-white w-4 h-4 rounded-full transition-transform peer-checked:-translate-x-5 dark:bg-gray-300"></div>
                </label>
            </div>

            <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">وضعیت انتشار <span class="text-rose-500">*</span></label>
                <select name="status" class="w-full bg-transparent border-none focus:ring-0 text-sm font-semibold text-gray-800 dark:text-white">
                    <option value="draft" @if($oldVal('status', 'draft') == 'draft') selected @endif>پیش‌نویس</option>
                    <option value="published" @if($oldVal('status') == 'published') selected @endif>منتشر شده</option>
                    <option value="archived" @if($oldVal('status') == 'archived') selected @endif>آرشیو شده</option>
                </select>
            </div>
        </div>
    </div>

    <!-- تب ۴: گالری تصاویر -->
    <div x-show="tab === 'images'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6" style="display: none;">

        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4 flex gap-3">
            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <div class="text-sm text-amber-800 dark:text-amber-200">
                <strong>توجه:</strong> با آپلود تصاویر جدید، تمام تصاویر قبلی این محصول حذف و تصاویر جدید جایگزین خواهند شد. اولین تصویر آپلود شده به عنوان تصویر اصلی تنظیم می‌شود.
            </div>
        </div>

        <div id="dropzone" class="dropzone-area border-2 border-dashed border-gray-200 dark:border-gray-600 rounded-2xl p-8 text-center hover:border-indigo-500 hover:bg-indigo-50/50 dark:hover:bg-indigo-500/5 transition-all cursor-pointer">
            <input type="file" name="images[]" id="image-input" multiple accept="image/png, image/jpeg, image/webp" class="hidden">
            <label for="image-input" class="cursor-pointer flex flex-col items-center justify-center">
                <div class="w-16 h-16 rounded-full bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                </div>
                <p class="text-gray-700 dark:text-gray-200 font-semibold">تصاویر را اینجا رها کنید یا کلیک کنید</p>
                <p class="text-gray-400 text-sm mt-1">فقط فایل‌های PNG, JPG, WEBP (حداکثر 3MB)</p>
            </label>
        </div>

        <div id="image-preview" class="hidden grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4"></div>

        @if($isEdit && $product->images->count() > 0)
            <div class="pt-6 border-t border-gray-100 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">تصاویر فعلی محصول</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    @foreach($product->images as $image)
                        <div class="relative group rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 aspect-square shadow-sm bg-gray-100 dark:bg-gray-800">
                            <img src="{{ $image->url }}" class="w-full h-full object-cover">
                            @if($image->is_primary)
                                <span class="absolute top-2 right-2 bg-indigo-600 text-white text-[10px] font-bold px-2 py-1 rounded-full shadow-sm">اصلی</span>
                            @endif
                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                <button type="button" class="text-rose-400 hover:text-rose-300 text-xs flex items-center gap-1 bg-white/10 px-2 py-1 rounded-lg backdrop-blur-sm" onclick="deleteImage({{ $image->id }})">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    حذف
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
