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
                <x-admin.jalali-datetime name="starts_at" :value="old('starts_at', $d?->starts_at ?? '')" label="تاریخ شروع" placeholder="۱۴۰۴/۰۶/۱۱ ۱۰:۳۰" />
                <x-admin.jalali-datetime name="expires_at" :value="old('expires_at', $d?->expires_at ?? '')" label="تاریخ انقضا" placeholder="۱۴۰۴/۰۶/۱۱ ۱۰:۳۰" />
                <div x-data="userPicker()" class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">محدود به کاربر خاص</label>
                    <div class="relative">
                        <input type="text"
                               x-model="search"
                               @input="filterUsers()"
                               @click="open = true"
                               @click.outside="open = false"
                               placeholder="جستجو بر اساس نام یا شماره موبایل..."
                               class="input-custom !py-1.5 !text-sm w-full">
                        <div x-show="open && filteredUsers.length > 0" x-transition
                             class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl shadow-lg max-h-60 overflow-y-auto">
                            <template x-for="user in filteredUsers" :key="user.id">
                                <div @click="toggleUser(user)"
                                     class="flex items-center gap-3 px-4 py-2.5 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                     :class="isSelected(user.id) ? 'bg-indigo-50 dark:bg-indigo-900/20' : ''">
                                    <div class="w-5 h-5 rounded border-2 flex items-center justify-center flex-shrink-0"
                                         :class="isSelected(user.id) ? 'bg-indigo-600 border-indigo-600' : 'border-gray-300 dark:border-gray-500'">
                                        <svg x-show="isSelected(user.id)" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-800 dark:text-white truncate" x-text="user.name"></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate" x-text="user.mobile + (user.email ? ' · ' + user.email : '')"></p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 mt-2">
                        <template x-for="uid in selectedUserIds" :key="uid">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300">
                                <span x-text="getUserById(uid)?.name || uid"></span>
                                <button type="button" @click="removeUser(uid)" class="ml-0.5 hover:text-indigo-900 dark:hover:text-indigo-100">&times;</button>
                            </span>
                        </template>
                    </div>
                    <template x-for="uid in selectedUserIds" :key="uid+'-input'">
                        <input type="hidden" name="user_ids[]" :value="uid">
                    </template>
                    <p class="text-xs text-gray-400 mt-1">خالی = همه کاربران</p>
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
function userPicker() {
    return {
        open: false,
        search: '',
        allUsers: @json($users ?? []),
        filteredUsers: @json($users ?? []),
        selectedUserIds: @json(old('user_ids', $d?->users?->pluck('id')?->toArray() ?? [])),

        filterUsers() {
            const q = this.search.toLowerCase();
            if (!q) {
                this.filteredUsers = this.allUsers;
                return;
            }
            this.filteredUsers = this.allUsers.filter(u =>
                (u.name && u.name.toLowerCase().includes(q)) ||
                (u.mobile && u.mobile.includes(q)) ||
                (u.email && u.email.toLowerCase().includes(q))
            );
        },
        toggleUser(user) {
            const idx = this.selectedUserIds.indexOf(user.id);
            if (idx > -1) {
                this.selectedUserIds.splice(idx, 1);
            } else {
                this.selectedUserIds.push(user.id);
            }
        },
        removeUser(uid) {
            this.selectedUserIds = this.selectedUserIds.filter(id => id !== uid);
        },
        isSelected(uid) {
            return this.selectedUserIds.includes(uid);
        },
        getUserById(uid) {
            return this.allUsers.find(u => u.id === uid);
        }
    };
}

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
