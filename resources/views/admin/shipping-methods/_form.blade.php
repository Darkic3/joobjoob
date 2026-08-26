{{-- فرم مشترک ایجاد/ویرایش روش ارسال --}}

@if(isset($errors) && $errors->any())
    <div class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-800 rounded-xl p-4 mb-6">
        <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-400 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- ستون اصلی --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- اطلاعات پایه --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white mb-5">اطلاعات پایه</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">عنوان <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $method->title ?? '') }}" class="input-custom" placeholder="مثال: پست پیشتاز" required>
                    @error('title') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">نوع ارسال <span class="text-rose-500">*</span></label>
                    <select name="type" class="input-custom" required>
                        <option value="fixed_price" {{ old('type', $method->type ?? '') === 'fixed_price' ? 'selected' : '' }}>قیمت ثابت</option>
                        <option value="with_weight" {{ old('type', $method->type ?? '') === 'with_weight' ? 'selected' : '' }}>بر اساس وزن</option>
                        <option value="chapar" {{ old('type', $method->type ?? '') === 'chapar' ? 'selected' : '' }}>چاپار</option>
                        <option value="cod24" {{ old('type', $method->type ?? '') === 'cod24' ? 'selected' : '' }}>Cod24</option>
                    </select>
                    @error('type') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">نوع سرویس چاپار</label>
                    <input type="text" name="chapar_type" value="{{ old('chapar_type', $method->chapar_type ?? '') }}" class="input-custom" placeholder="اختیاری">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">هزینه ارسال (تومان)</label>
                    <input type="number" name="price" value="{{ old('price', $method->price ?? '') }}" class="input-custom" min="0" placeholder="0">
                    <p class="text-xs text-gray-400 mt-1">برای وزنی: هزینه هر ۱۰۰ گرم</p>
                    @error('price') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">سقف ارسال رایگان (تومان)</label>
                    <input type="number" name="price_ceiling" value="{{ old('price_ceiling', $method->price_ceiling ?? 0) }}" class="input-custom" min="0" placeholder="0">
                    <p class="text-xs text-gray-400 mt-1">بالای این مبلغ، ارسال رایگان. ۰ = غیرفعال</p>
                    @error('price_ceiling') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">توضیحات</label>
                    <textarea name="description" rows="2" class="input-custom" placeholder="توضیحات اختیاری...">{{ old('description', $method->description ?? '') }}</textarea>
                </div>
            </div>
        </div>

        {{-- زمان ارسال --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white">زمان تخمینی ارسال</h3>
                <x-admin.toggle name="show_estimated_days" label="نمایش در سایت" :checked="old('show_estimated_days', $method->show_estimated_days ?? 1)" />
            </div>

            <div id="estimatedDaysFields" class="space-y-5 {{ old('show_estimated_days', $method->show_estimated_days ?? 1) ? '' : 'hidden' }}">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">حداقل زمان (روز کاری)</label>
                        <input type="number" name="estimated_days_min" value="{{ old('estimated_days_min', $method->estimated_days_min ?? 1) }}" class="input-custom" min="1" max="30">
                        @error('estimated_days_min') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">حداکثر زمان (روز کاری)</label>
                        <input type="number" name="estimated_days_max" value="{{ old('estimated_days_max', $method->estimated_days_max ?? 3) }}" class="input-custom" min="1" max="30">
                        @error('estimated_days_max') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="bg-indigo-50 dark:bg-indigo-500/10 rounded-xl p-4 flex items-center gap-3">
                    <svg class="w-5 h-5 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm text-indigo-700 dark:text-indigo-400">
                        پیش‌نمایش: <strong>{{ old('estimated_days_min', $method->estimated_days_min ?? 1) }} تا {{ old('estimated_days_max', $method->estimated_days_max ?? 3) }} روز کاری</strong>
                    </p>
                </div>
            </div>
        </div>

        {{-- اطلاعات فرستنده --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white mb-5">اطلاعات فرستنده</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">نام فرستنده</label>
                    <input type="text" name="sender_name" value="{{ old('sender_name', $method->sender_name ?? '') }}" class="input-custom">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">شرکت فرستنده</label>
                    <input type="text" name="sender_company" value="{{ old('sender_company', $method->sender_company ?? '') }}" class="input-custom">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">تلفن فرستنده</label>
                    <input type="text" name="sender_phone" value="{{ old('sender_phone', $method->sender_phone ?? '') }}" class="input-custom" dir="ltr" placeholder="021-XXXXXXXX">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">موبایل فرستنده</label>
                    <input type="text" name="sender_mobile" value="{{ old('sender_mobile', $method->sender_mobile ?? '') }}" class="input-custom" dir="ltr" placeholder="09XXXXXXXXX">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">ایمیل فرستنده</label>
                    <input type="email" name="sender_email" value="{{ old('sender_email', $method->sender_email ?? '') }}" class="input-custom" dir="ltr">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">شهر فرستنده</label>
                    <select name="sender_city_id" class="input-custom">
                        <option value="">انتخاب شهر</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}" {{ old('sender_city_id', $method->sender_city_id ?? '') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">آدرس فرستنده</label>
                    <textarea name="sender_address" rows="2" class="input-custom">{{ old('sender_address', $method->sender_address ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">کدپستی فرستنده</label>
                    <input type="text" name="sender_postal_code" value="{{ old('sender_postal_code', $method->sender_postal_code ?? '') }}" class="input-custom" dir="ltr" placeholder="XXXXXXXXXXXX">
                </div>
            </div>
        </div>
    </div>

    {{-- سایدبار --}}
    <div class="space-y-6">
        {{-- وضعیت --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white mb-5">وضعیت</h3>
            <div class="space-y-3">
                <x-admin.toggle name="status" label="فعال" :checked="old('status', $method->status ?? 0)" />
                <x-admin.toggle name="freight_balance" label="پس کرایه (ارسال رایگان)" :checked="old('freight_balance', $method->freight_balance ?? 0)" />
            </div>
        </div>

        {{-- شهرهای مقصد --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white mb-2">شهرهای مقصد</h3>
            <p class="text-xs text-gray-400 mb-4">شهرهایی که این روش ارسال برای آن‌ها فعال است:</p>
            <div class="max-h-64 overflow-y-auto space-y-1" style="scrollbar-width: thin;">
                @foreach($cities as $city)
                    <label class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors">
                        <input type="checkbox" name="cities[]" value="{{ $city->id }}"
                               {{ in_array($city->id, $selectedCityIds ?? []) ? 'checked' : '' }}
                               class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $city->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- دکمه‌ها --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl shadow-sm transition-all hover:shadow-md hover:-translate-y-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                ذخیره
            </button>
            <a href="{{ route('admin.shipping-methods.index') }}" class="mt-3 w-full inline-flex items-center justify-center px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 font-medium transition-all text-sm">
                انصراف
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var toggle = document.querySelector('input[name="show_estimated_days"]');
        var fields = document.getElementById('estimatedDaysFields');
        if (toggle && fields) {
            toggle.addEventListener('change', function() {
                fields.classList.toggle('hidden', !this.checked);
            });
        }
    });
</script>
@endpush
