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
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">محدود به کاربر خاص</label>
                    
                    {{-- فیلترهای هوشمند --}}
                    <div class="flex flex-wrap gap-2 mb-3">
                        <button type="button" @click="loadFilter('new_week')" :disabled="loadingFilter"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors"
                                :class="activeFilter === 'new_week' ? 'bg-indigo-100 dark:bg-indigo-900/30 border-indigo-300 dark:border-indigo-600 text-indigo-700 dark:text-indigo-300' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:border-indigo-300 dark:hover:border-indigo-600'">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            ثبت‌نام یک هفته اخیر
                        </button>
                        <button type="button" @click="loadFilter('new_month')" :disabled="loadingFilter"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors"
                                :class="activeFilter === 'new_month' ? 'bg-indigo-100 dark:bg-indigo-900/30 border-indigo-300 dark:border-indigo-600 text-indigo-700 dark:text-indigo-300' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:border-indigo-300 dark:hover:border-indigo-600'">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            ثبت‌نام یک ماه اخیر
                        </button>
                        <button type="button" @click="loadFilter('no_orders')" :disabled="loadingFilter"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors"
                                :class="activeFilter === 'no_orders' ? 'bg-indigo-100 dark:bg-indigo-900/30 border-indigo-300 dark:border-indigo-600 text-indigo-700 dark:text-indigo-300' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:border-indigo-300 dark:hover:border-indigo-600'">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            بدون سفارش
                        </button>
                        <button type="button" @click="loadFilter('no_paid_orders')" :disabled="loadingFilter"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors"
                                :class="activeFilter === 'no_paid_orders' ? 'bg-indigo-100 dark:bg-indigo-900/30 border-indigo-300 dark:border-indigo-600 text-indigo-700 dark:text-indigo-300' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:border-indigo-300 dark:hover:border-indigo-600'">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            خرید اولی‌ها
                        </button>
                        <button type="button" @click="loadFilter('has_orders')" :disabled="loadingFilter"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors"
                                :class="activeFilter === 'has_orders' ? 'bg-indigo-100 dark:bg-indigo-900/30 border-indigo-300 dark:border-indigo-600 text-indigo-700 dark:text-indigo-300' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:border-indigo-300 dark:hover:border-indigo-600'">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                            دارای سفارش
                        </button>
                        <button type="button" @click="loadFilter('active_7d')" :disabled="loadingFilter"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors"
                                :class="activeFilter === 'active_7d' ? 'bg-indigo-100 dark:bg-indigo-900/30 border-indigo-300 dark:border-indigo-600 text-indigo-700 dark:text-indigo-300' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:border-indigo-300 dark:hover:border-indigo-600'">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            فعال ۷ روز اخیر
                        </button>
                        <button type="button" @click="loadFilter('inactive_30d')" :disabled="loadingFilter"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors"
                                :class="activeFilter === 'inactive_30d' ? 'bg-indigo-100 dark:bg-indigo-900/30 border-indigo-300 dark:border-indigo-600 text-indigo-700 dark:text-indigo-300' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:border-indigo-300 dark:hover:border-indigo-600'">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            غیرفعال ۳۰ روز اخیر
                        </button>
                    </div>

                    {{-- جستجو --}}
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

                    {{-- کاربران انتخاب شده --}}
                    <div class="flex flex-wrap gap-2 mt-2">
                        <template x-for="uid in selectedUserIds" :key="uid">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300">
                                <span x-text="getUserById(uid)?.name || uid"></span>
                                <button type="button" @click="removeUser(uid)" class="ml-0.5 hover:text-indigo-900 dark:hover:text-indigo-100">&times;</button>
                            </span>
                        </template>
                    </div>

                    {{-- ارسال hidden inputs --}}
                    <template x-for="uid in selectedUserIds" :key="uid+'-input'">
                        <input type="hidden" name="user_ids[]" :value="uid">
                    </template>

                    {{-- وضعیت بارگذاری --}}
                    <div x-show="loadingFilter" class="flex items-center gap-2 mt-2 text-xs text-gray-500">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <span>در حال بارگذاری کاربران...</span>
                    </div>
                    <div x-show="filterResult" x-text="filterResult" class="text-xs text-emerald-600 dark:text-emerald-400 mt-1"></div>

                    <p class="text-xs text-gray-400 mt-2">خالی = همه کاربران</p>
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
                <x-admin.toggle name="is_active" label="فعال" :checked="old('is_active', $d?->is_active ?? 1)" />
                <x-admin.toggle name="first_purchase" label="فقط خرید اول" :checked="old('first_purchase', $d?->first_purchase ?? 0)" />
                <x-admin.toggle name="with_discount" label="قابل اعمال روی محصولات تخفیف‌دار" :checked="old('with_discount', $d?->with_discount ?? 0)" />
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
        loadingFilter: false,
        activeFilter: null,
        filterResult: '',
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

        async loadFilter(filter) {
            // اگر همین فیلتر فعال است، غیرفعالش کن (بازگشت به لیست اولیه)
            if (this.activeFilter === filter) {
                this.activeFilter = null;
                this.allUsers = @json($users ?? []);
                this.filteredUsers = this.allUsers;
                this.filterResult = '';
                return;
            }

            this.loadingFilter = true;
            this.activeFilter = filter;
            this.filterResult = '';

            try {
                const url = '{{ url('admin/discounts/users/filter') }}?filter=' + encodeURIComponent(filter);
                const response = await fetch(url, {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }

                const data = await response.json();
                this.allUsers = data.users;
                this.filteredUsers = data.users;
                this.search = '';
                this.open = true;

                // نمایش تعداد نتایج
                const filterNames = {
                    'new_week': 'ثبت‌نام یک هفته اخیر',
                    'new_month': 'ثبت‌نام یک ماه اخیر',
                    'no_orders': 'بدون سفارش',
                    'no_paid_orders': 'خرید اولی‌ها',
                    'has_orders': 'دارای سفارش',
                    'active_7d': 'فعال ۷ روز اخیر',
                    'inactive_30d': 'غیرفعال ۳۰ روز اخیر'
                };
                this.filterResult = data.users.length + ' کاربر با فیلتر "' + (filterNames[filter] || filter) + '" یافت شد';
            } catch (e) {
                console.error('Filter error:', e);
                this.filterResult = 'خطا در بارگذاری اطلاعات';
            } finally {
                this.loadingFilter = false;
            }
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
