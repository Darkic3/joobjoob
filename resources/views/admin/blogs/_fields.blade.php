@props(['blog' => null, 'categories' => []])

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">

        {{-- عنوان --}}
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                عنوان <span class="text-red-500">*</span>
            </label>
            <input type="text" name="title" value="{{ old('title', $blog->title ?? '') }}"
                   class="input-custom"
                   placeholder="عنوان مقاله را وارد کنید" required>
            @error('title')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- URL (Slug) --}}
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                آدرس یکتا (URL) <span class="text-red-500">*</span>
            </label>
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap font-mono">{{ url('/') }}/</span>
                {{-- ✅ data-auto-slug برای فعال‌سازی JS همگانی SlugHelper --}}
                <input type="text" name="slug" value="{{ old('slug', $blog->slug ?? '') }}"
                       class="input-custom flex-1 font-mono text-sm"
                       placeholder="my-article-url" dir="ltr" data-auto-slug data-slug-source="input[name='title']">
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                در صورت خالی بودن، به‌صورت خودکار از روی عنوان ساخته می‌شود. فاصله‌ها خودکار به خط تیره (-) تبدیل می‌شوند.
            </p>
            @error('slug')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- خلاصه --}}
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                خلاصه
            </label>
            <textarea name="excerpt" rows="4"
                      class="input-custom resize-y min-h-[100px]"
                      placeholder="خلاصه‌ای از مقاله...">{{ old('excerpt', $blog->excerpt ?? '') }}</textarea>
            @error('excerpt')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- محتوا (CKEditor) --}}
        <div class="space-y-1.5 lg:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                محتوا <span class="text-red-500">*</span>
            </label>
            <textarea name="content"
                      id="content-editor"
                      class="ckeditor w-full"
                      rows="12">{{ old('content', $blog->content ?? '') }}</textarea>
            @error('content')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="space-y-5">

        {{-- کارت تنظیمات انتشار --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="text-base font-bold text-gray-800 dark:text-gray-100 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                تنظیمات انتشار
            </h3>

            <div class="space-y-4">
                {{-- دسته‌بندی --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        دسته‌بندی
                    </label>
                    <select name="blog_category_id" class="input-custom">
                        <option value="">بدون دسته</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}"
                                {{ old('blog_category_id', $blog->blog_category_id ?? '') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->title }}
                            </option>
                        @endforeach
                    </select>
                    @error('blog_category_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Toggle: فعال --}}
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">فعال</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active', $blog->is_active ?? true) ? 'checked' : '' }}
                               class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                    </label>
                </div>

                {{-- Toggle: نمایش در صفحه نخست --}}
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">نمایش در صفحه نخست</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="show_in_first_page" value="0">
                        <input type="checkbox" name="show_in_first_page" value="1"
                               {{ old('show_in_first_page', $blog->show_in_first_page ?? false) ? 'checked' : '' }}
                               class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                    </label>
                </div>
            </div>
        </div>

        {{-- کارت تصویر شاخص --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="text-base font-bold text-gray-800 dark:text-gray-100 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                تصویر شاخص
            </h3>

            <div class="space-y-3">
                @if($blog && $blog->image)
                    <div class="relative group">
                        {{-- ✅ اصلاح آدرس تصویر برای هماهنگی با ImageUploader --}}
                        <img src="{{ asset(config('paths.upload', 'uploads') . '/blogs/' . $blog->image) }}"
                             class="w-full rounded-lg border border-gray-200 dark:border-gray-600 object-cover"
                             alt="تصویر فعلی">
                        <button type="button" onclick="this.parentElement.remove()"
                                class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors shadow-sm opacity-0 group-hover:opacity-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                @endif

                <input type="file" name="image" accept="image/*"
                       class="input-custom file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-primary-900/30 dark:file:text-primary-300 cursor-pointer w-full">

                <p class="text-xs text-gray-500 dark:text-gray-400">
                    فرمت‌های مجاز: JPG, PNG, WebP | حداکثر حجم: ۲ مگابایت
                </p>
                @error('image')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
</div>

{{-- دکمه‌های اکشن --}}
<div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100 dark:border-gray-700 mt-6">
    <a href="{{ route('admin.blogs.index') }}"
       class="px-6 py-2.5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-colors font-medium">
        انصراف
    </a>
    <button type="submit" class="btn-primary flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ $blog ? 'ویرایش مقاله' : 'ایجاد مقاله' }}
    </button>
</div>
