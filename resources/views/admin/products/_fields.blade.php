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

    // ========== داده‌های مشخصات و تنوع برای سازنده واریانت ==========
    // مشخصه‌های والد (گروه‌ها) با فرزندان
    $specGroups = $specifications ?? collect();

    // مقادیر انتخاب‌شده فعلی محصول: [parent_id => [child_id, ...]]
    $selectedSpecs = [];
    $textSpecValues = [];

    if ($isEdit) {
        foreach ($product->productSpecifications as $ps) {
            // ردیف والد (گروه) — فرزندان آن را می‌گیریم
            if ($ps->value === null && $ps->parent_id === null) {
                $selectedSpecs[$ps->specification_id] = $ps->children->pluck('specification_id')->map(fn($id) => (int) $id)->all();
            }
            // مقادیر نوشتاری
            if ($ps->value !== null) {
                $textSpecValues[$ps->specification_id] = $ps->value;
            }
        }
    }

    // واریانت‌های موجود: [{id, spec_ids[], price, discount_price, stock, weight}]
    $existingVariants = [];
    if ($isEdit) {
        foreach ($product->variants as $v) {
            $existingVariants[] = [
                'id'             => $v->id,
                'spec_ids'       => $v->specifications->pluck('id')->map(fn($id) => (int) $id)->sort()->values()->all(),
                'price'          => (int) $v->price,
                'discount_price' => (int) $v->discounted_price,
                'stock'          => (int) $v->stock,
                'weight'         => (int) $v->weight,
            ];
        }
    }

    // ویژگی‌های آزاد فعلی
    $existingProperties = [];
    if ($isEdit) {
        foreach ($product->properties as $prop) {
            $existingProperties[] = [
                'id'    => $prop->id,
                'title' => $prop->trans('title') ?? '',
                'value' => $prop->value,
            ];
        }
    }

    // محصولات مرتبط و مکمل فعلی
    $relatedIds = $isEdit ? $product->related->pluck('id')->map(fn($id) => (int) $id)->all() : [];
    $complementIds = $isEdit ? $product->complement->pluck('id')->map(fn($id) => (int) $id)->all() : [];

    // لیست محصولات برای انتخابگر مرتبط/مکمل (از کنترلر با نام products می‌آید)
    $allProducts = $products ?? collect();
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
        <button type="button" @click="tab='specs'" :class="tab === 'specs' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400' : 'text-gray-500 hover:text-gray-800 dark:hover:text-gray-200'" class="flex items-center gap-2 px-4 py-2 rounded-lg font-medium text-sm transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
            مشخصات و تنوع
        </button>
        <button type="button" @click="tab='related'" :class="tab === 'related' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400' : 'text-gray-500 hover:text-gray-800 dark:hover:text-gray-200'" class="flex items-center gap-2 px-4 py-2 rounded-lg font-medium text-sm transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 010 5.656l-3 3a4 4 0 01-5.656-5.656l1.5-1.5m10.656-1.5l1.5-1.5a4 4 0 10-5.656-5.656l-3 3a4 4 0 000 5.656"></path></svg>
            مرتبط و مکمل
        </button>
        <button type="button" @click="tab='attributes'" :class="tab === 'attributes' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400' : 'text-gray-500 hover:text-gray-800 dark:hover:text-gray-200'" class="flex items-center gap-2 px-4 py-2 rounded-lg font-medium text-sm transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            تنظیمات
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
                <!-- کلاس data-auto-slug برای اتصال به JS همگانی SlugHelper -->
                <input type="text" name="slug" value="{{ $oldVal('slug') }}" class="w-full pr-16 pl-4 py-2.5 rounded-xl border-gray-200 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 ltr:text-left" placeholder="samsung-galaxy" dir="ltr" data-auto-slug data-slug-source="input[name='name[en]']">
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

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">برند</label>
            <select name="brand_id" class="w-full rounded-xl border-gray-200 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200">
                <option value="">بدون برند</option>
                @foreach($brands ?? [] as $brand)
                    <option value="{{ $brand->id }}" @if($oldVal('brand_id') == $brand->id) selected @endif>{{ $brand->trans('title') }}</option>
                @endforeach
            </select>
            @error('brand_id') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
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
            <x-admin.jalali-datetime name="deal_ends_at" :value="$oldVal('deal_ends_at')" label="تاریخ پایان تخفیف" />
        </div>

        <div class="lg:col-span-3 flex items-center justify-between bg-gray-50 dark:bg-gray-700/50 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
            <div>
                <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">تایمر تخفیف بازه‌ای</span>
                <span class="block text-xs text-gray-400 mt-0.5">نمایش پیشنهاد ویژه فقط در بازه زمانی مشخص</span>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="hidden" name="timer_active" value="0">
                <input type="checkbox" name="timer_active" value="1" class="sr-only peer" @if($oldVal('timer_active')) checked @endif>
                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:ring-2 peer-checked:ring-indigo-500 dark:bg-gray-600 peer-checked:bg-indigo-600 transition-all"></div>
                <div class="absolute right-1 top-1 bg-white w-4 h-4 rounded-full transition-transform peer-checked:-translate-x-5 dark:bg-gray-300"></div>
            </label>
        </div>

        <div class="lg:col-span-1">
            <x-admin.jalali-datetime name="start_timer" :value="$oldVal('start_timer')" label="شروع تایمر" />
        </div>

        <div class="lg:col-span-1">
            <x-admin.jalali-datetime name="end_timer" :value="$oldVal('end_timer')" label="پایان تایمر" />
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

    <!-- تب ۳: مشخصات و تنوع -->
    <div x-show="tab === 'specs'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6" style="display: none;">
        <x-admin.product-spec-builder
            :spec-groups="$specGroups"
            :text-specifications="$textSpecifications ?? collect()"
            :selected-specs="$selectedSpecs"
            :text-spec-values="$textSpecValues"
            :existing-variants="$existingVariants"
            :existing-properties="$existingProperties"
        />
    </div>

    <!-- تب ۴: محصولات مرتبط و مکمل -->
    <div x-show="tab === 'related'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6" style="display: none;">
        <x-admin.product-associations-picker
            :products="$products ?? collect()"
            :related-ids="$relatedIds"
            :complement-ids="$complementIds"
            :current-product-id="$isEdit ? $product->id : null"
        />
    </div>

    <!-- تب ۵: تنظیمات -->
    <div x-show="tab === 'attributes'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6" style="display: none;">
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

            <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700/50 p-4 rounded-xl border border-gray-100 dark:border-gray-700" x-data="{ active: {{ $oldVal('is_active', true) ? 'true' : 'false' }} }">
                <div>
                    <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">نمایش در سایت</span>
                    <span class="block text-xs mt-0.5 font-medium" :class="active ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400 dark:text-gray-500'" x-text="active ? 'فعال' : 'غیرفعال'">{{ $oldVal('is_active', true) ? 'فعال' : 'غیرفعال' }}</span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" x-model="active" class="sr-only peer" @if($oldVal('is_active', true)) checked @endif>
                    <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:ring-2 peer-checked:ring-indigo-500 dark:bg-gray-600 peer-checked:bg-indigo-600 transition-all"></div>
                    <div class="absolute right-1 top-1 bg-white w-4 h-4 rounded-full transition-transform peer-checked:-translate-x-5 dark:bg-gray-300"></div>
                </label>
            </div>
        </div>
    </div>

    <!-- تب ۶: گالری تصاویر -->
    <div x-show="tab === 'images'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6" style="display: none;">

        {{-- کامپوننت کراپر همگانی (یک بار در صفحه) --}}
        <x-admin.image-cropper />

        {{-- اینپوت‌های مخفی مدیریت گالری --}}
        <input type="hidden" name="main_image_id" id="main-image-id" value="{{ $isEdit ? ($product->images->firstWhere('is_primary', true)?->id ?? '') : '' }}">
        <input type="hidden" name="main_new_index" id="main-new-index" value="">
        <div id="delete-images-container"></div>

        {{-- راهنما --}}
        <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-xl p-4 flex gap-3">
            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div class="text-sm text-indigo-800 dark:text-indigo-200">
                <strong>راهنما:</strong> افزودن تصویر جدید هیچ تصویری را حذف نمی‌کند. با کلیک روی <span class="text-amber-500 font-bold">⭐</span> تصویر اصلی را انتخاب کنید و با دکمه <span class="text-rose-500 font-bold">✕</span> هر تصویری را حذف کنید.
            </div>
        </div>

        {{-- دراپ‌زون --}}
        <div id="dropzone" class="dropzone-area border-2 border-dashed border-gray-200 dark:border-gray-600 rounded-2xl p-8 text-center hover:border-indigo-500 hover:bg-indigo-50/50 dark:hover:bg-indigo-500/5 transition-all cursor-pointer">
            <input type="file" name="images[]" id="image-input" multiple accept="image/png, image/jpeg, image/webp" class="hidden">
            <label for="image-input" class="cursor-pointer flex flex-col items-center justify-center">
                <div class="w-16 h-16 rounded-full bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                </div>
                <p class="text-gray-700 dark:text-gray-200 font-semibold">تصاویر را اینجا رها کنید یا کلیک کنید</p>
                <p class="text-gray-400 text-sm mt-1">هر تصویر ابتدا برای برش باز می‌شود — PNG, JPG, WEBP (حداکثر 3MB)</p>
            </label>
        </div>

        {{-- پیش‌نمایش تصاویر جدید (کراپ شده) --}}
        <div>
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                تصاویر جدید
                <span class="text-xs font-normal text-gray-400">(هنوز ذخیره نشده‌اند)</span>
            </h3>
            <div id="gallery-pending" class="hidden grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4"></div>
        </div>

        {{-- تصاویر فعلی محصول --}}
        @if($isEdit && $product->images->count() > 0)
            <div class="pt-6 border-t border-gray-100 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4 flex items-center gap-2">
                    تصاویر فعلی محصول
                    <span class="text-xs font-normal text-gray-400">(برای تعویض تصویر اصلی روی ⭐ کلیک کنید)</span>
                </h3>
                <div id="gallery-existing" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    @foreach($product->images as $image)
                        <div class="gallery-tile relative group rounded-xl overflow-hidden border-2 aspect-square shadow-sm bg-gray-100 dark:bg-gray-800 transition-all {{ $image->is_primary ? 'border-amber-400' : 'border-gray-200 dark:border-gray-700' }}" data-image-id="{{ $image->id }}">
                            <img src="{{ $image->url }}" class="w-full h-full object-cover" alt="تصویر محصول">
                            <span class="gallery-main-badge absolute top-2 left-2 bg-amber-400 text-amber-950 text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm {{ $image->is_primary ? '' : 'hidden' }}">⭐ اصلی</span>
                            {{-- ستاره: انتخاب تصویر اصلی --}}
                            <button type="button" class="gallery-star absolute bottom-2 left-2 w-7 h-7 rounded-full bg-white/80 dark:bg-gray-900/70 backdrop-blur-sm {{ $image->is_primary ? 'text-amber-500' : 'text-gray-500 hover:text-amber-500' }} flex items-center justify-center transition-all" title="انتخاب به عنوان تصویر اصلی">
                                <svg class="w-4 h-4" fill="{{ $image->is_primary ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.562.562 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/></svg>
                            </button>
                            {{-- دکمه حذف (بالا سمت راست) --}}
                            <button type="button" class="gallery-delete absolute top-2 right-2 w-7 h-7 rounded-full bg-rose-500 hover:bg-rose-600 text-white flex items-center justify-center shadow-sm opacity-60 group-hover:opacity-100 transition-all" title="حذف تصویر">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                            {{-- پوشش هنگام علامت‌گذاری برای حذف --}}
                            <div class="gallery-deleting-overlay absolute inset-0 bg-rose-500/20 backdrop-blur-[1px] items-center justify-center hidden">
                                <span class="text-[10px] font-bold bg-rose-600 text-white px-2 py-1 rounded-full shadow-sm">در انتظار حذف</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
