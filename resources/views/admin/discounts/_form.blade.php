@php
    $d = $discount ?? null;
@endphp
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    {{-- ستون اصلی --}}
    <div class="lg:col-span-2 space-y-4">
        {{-- اطلاعات پایه --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-3">اطلاعات پایه</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">عنوان <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $d?->title ?? '') }}" class="input-custom !py-1.5 !text-sm" placeholder="مثلاً: تخفیف خرید اول" required>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">کد تخفیف <span class="text-rose-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code', $d?->code ?? '') }}" class="input-custom !py-1.5 !text-sm font-mono" placeholder="مثلاً: WELCOME15" dir="ltr" required>
                    <p class="text-xs text-gray-400 mt-1">فقط حروف انگلیسی، اعداد، خط تیره (-) و زیرخط (_) مجاز است.</p>
                </div>
            </div>
        </div>

        {{-- تنظیمات تخفیف --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-3">تنظیمات تخفیف</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">نوع تخفیف <span class="text-rose-500">*</span></label>
                    <select name="type" class="input-custom !py-1.5 !text-sm" required id="discountTypeSelect">
                        <option value="percent" {{ old('type', $d?->type ?? '') === 'percent' ? 'selected' : '' }}>درصدی</option>
                        <option value="cash" {{ old('type', $d?->type ?? '') === 'cash' ? 'selected' : '' }}>مبلغ ثابت (تومان)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">مبلغ/درصد <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <input type="number" name="amount" value="{{ old('amount', $d?->amount ?? '') }}" class="input-custom !py-1.5 !text-sm" min="0" step="any" required id="discountAmountInput" dir="ltr">
                        <span class="absolute end-3 top-1/2 -translate-y-1/2 text-xs text-gray-400" id="discountAmountUnit">{{ old('type', $d?->type ?? 'percent') === 'percent' ? '٪' : 'تومان' }}</span>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">حداکثر دفعات قابل استفاده <span class="text-rose-500">*</span></label>
                    <input type="number" name="count" value="{{ old('count', $d?->count ?? 100) }}" class="input-custom !py-1.5 !text-sm" min="1" required dir="ltr">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">حداکثر استفاده هر کاربر <span class="text-rose-500">*</span></label>
                    <input type="number" name="max_usage_per_user" value="{{ old('max_usage_per_user', $d?->max_usage_per_user ?? 1) }}" class="input-custom !py-1.5 !text-sm" min="0" required dir="ltr">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">حداقل مبلغ سبد خرید (تومان)</label>
                    <input type="number" name="basket_minimum_price" value="{{ old('basket_minimum_price', $d?->basket_minimum_price ?? 0) }}" class="input-custom !py-1.5 !text-sm" min="0" dir="ltr" placeholder="۰ = بدون محدودیت">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">محدود به</label>
                    <select name="pay_type" class="input-custom !py-1.5 !text-sm" id="discountPayTypeSelect">
                        <option value="" {{ old('pay_type', $d?->pay_type ?? '') === '' ? 'selected' : '' }}>همه محصولات</option>
                        <option value="category" {{ old('pay_type', $d?->pay_type ?? '') === 'category' ? 'selected' : '' }}>دسته‌بندی</option>
                        <option value="brand" {{ old('pay_type', $d?->pay_type ?? '') === 'brand' ? 'selected' : '' }}>برند</option>
                        <option value="product" {{ old('pay_type', $d?->pay_type ?? '') === 'product' ? 'selected' : '' }}>محصول</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- انتخاب آیتم‌های مرتبط --}}
        @php
            $selectedCategories = old('discountable_ids', $d?->categories?->pluck('id')?->toArray() ?? []);
            $selectedBrands = old('discountable_ids', $d?->brands?->pluck('id')?->toArray() ?? []);
            $selectedProducts = old('discountable_ids', $d?->products?->pluck('id')?->toArray() ?? []);
            $currentPayType = old('pay_type', $d?->pay_type ?? '');
        @endphp
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4" id="discountableSection" style="{{ empty($currentPayType) ? 'display:none' : '' }}">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-3">آیتم‌های مرتبط</h3>

            {{-- دسته‌بندی --}}
            <div class="discountable-group" data-pay-type="category" style="{{ $currentPayType === 'category' ? '' : 'display:none' }}">
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">دسته‌بندی‌ها</label>
                <select name="discountable_ids[]" multiple class="input-custom !py-1.5 !text-sm w-full" size="6" id="discountableCategories">
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ in_array($category->id, $selectedCategories) ? 'selected' : '' }}>
                            {{ $category->trans('name') }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1">با نگه داشتن Ctrl چند مورد انتخاب کنید.</p>
            </div>

            {{-- برند --}}
            <div class="discountable-group" data-pay-type="brand" style="{{ $currentPayType === 'brand' ? '' : 'display:none' }}">
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">برندها</label>
                <select name="discountable_ids[]" multiple class="input-custom !py-1.5 !text-sm w-full" size="6" id="discountableBrands">
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ in_array($brand->id, $selectedBrands) ? 'selected' : '' }}>
                            {{ $brand->trans('title') }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1">با نگه داشتن Ctrl چند مورد انتخاب کنید.</p>
            </div>

            {{-- محصول --}}
            <div class="discountable-group" data-pay-type="product" style="{{ $currentPayType === 'product' ? '' : 'display:none' }}">
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">محصولات</label>
                <select name="discountable_ids[]" multiple class="input-custom !py-1.5 !text-sm w-full" size="6" id="discountableProducts">
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ in_array($product->id, $selectedProducts) ? 'selected' : '' }}>
                            {{ $product->trans('name') }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1">با نگه داشتن Ctrl چند مورد انتخاب کنید.</p>
            </div>
        </div>

        {{-- شرایط --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-3">شرایط</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">تاریخ شروع</label>
                    <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $d?->starts_at?->format('Y-m-d\TH:i') ?? '') }}" class="input-custom !py-1.5 !text-sm" dir="ltr">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">تاریخ انقضا</label>
                    <input type="datetime-local" name="expires_at" value="{{ old('expires_at', $d?->expires_at?->format('Y-m-d\TH:i') ?? '') }}" class="input-custom !py-1.5 !text-sm" dir="ltr">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">مخصوص کاربر خاص</label>
                    <input type="number" name="user_id" value="{{ old('user_id', $d?->user_id ?? '') }}" class="input-custom !py-1.5 !text-sm" min="0" dir="ltr" placeholder="خالی = همه کاربران">
                </div>
            </div>
        </div>
    </div>

    {{-- سایدبار --}}
    <div class="space-y-4">
        {{-- وضعیت --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-3">وضعیت</h3>
            <div class="space-y-3">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $d?->is_active ?? 1) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 rounded">
                    <span class="text-sm text-gray-700 dark:text-gray-300">فعال</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="first_purchase" value="1" {{ old('first_purchase', $d?->first_purchase ?? 0) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 rounded">
                    <span class="text-sm text-gray-700 dark:text-gray-300">فقط خرید اول</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="with_discount" value="1" {{ old('with_discount', $d?->with_discount ?? 0) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 rounded">
                    <span class="text-sm text-gray-700 dark:text-gray-300">قابل اعمال روی محصولات تخفیف‌دار</span>
                </label>
            </div>
        </div>

        {{-- پیش‌نمایش --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-3">پیش‌نمایش</h3>
            <div class="space-y-2 text-xs text-gray-500 dark:text-gray-400" id="discountPreview">
                <p><strong>نوع:</strong> <span id="previewType">—</span></p>
                <p><strong>مقدار:</strong> <span id="previewAmount">—</span></p>
                <p><strong>حداقل خرید:</strong> <span id="previewMin">—</span></p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('discountTypeSelect');
    const amountInput = document.getElementById('discountAmountInput');
    const amountUnit = document.getElementById('discountAmountUnit');
    const payTypeSelect = document.getElementById('discountPayTypeSelect');
    const discountableSection = document.getElementById('discountableSection');

    function updateAmountUnit() {
        const isPercent = typeSelect.value === 'percent';
        amountUnit.textContent = isPercent ? '٪' : 'تومان';
        amountInput.max = isPercent ? '100' : '100000000';
        updatePreview();
    }

    function updatePayTypeSection() {
        const payType = payTypeSelect.value;
        discountableSection.style.display = payType ? '' : 'none';
        document.querySelectorAll('.discountable-group').forEach(function (group) {
            group.style.display = group.dataset.payType === payType ? '' : 'none';
        });
        updatePreview();
    }

    function updatePreview() {
        const type = typeSelect.value === 'percent' ? 'درصدی' : 'مبلغ ثابت';
        const amount = amountInput.value || '—';
        const unit = typeSelect.value === 'percent' ? '٪' : 'تومان';
        const minPrice = document.querySelector('[name="basket_minimum_price"]').value;

        document.getElementById('previewType').textContent = type;
        document.getElementById('previewAmount').textContent = amount + ' ' + unit;
        document.getElementById('previewMin').textContent = minPrice ? Number(minPrice).toLocaleString('fa-IR') + ' تومان' : 'ندارد';
    }

    typeSelect.addEventListener('change', updateAmountUnit);
    amountInput.addEventListener('input', updatePreview);
    payTypeSelect.addEventListener('change', updatePayTypeSection);

    updateAmountUnit();
    updatePayTypeSection();
});
</script>
@endpush
