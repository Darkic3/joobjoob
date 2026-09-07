{{-- فرم مشترک ایجاد/ویرایش روش ارسال --}}

@if(isset($errors) && $errors->any())
    <div class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-800 rounded-lg p-2.5 mb-3">
        <ul class="list-disc list-inside text-xs text-red-600 dark:text-red-400">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    {{-- ستون اصلی --}}
    <div class="lg:col-span-2 space-y-4">
        {{-- اطلاعات پایه --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-3">{{ __('admin.shipping_methods.basic_info') }}</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.shipping_methods.field_title') }} <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $method->title ?? '') }}" class="input-custom !py-1.5 !text-sm" placeholder="{{ __('admin.shipping_methods.field_title_placeholder') }}" required>
                    @error('title') <p class="text-rose-500 text-xs mt-0.5">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.shipping_methods.field_type') }} <span class="text-rose-500">*</span></label>
                    <select name="type" class="input-custom !py-1.5 !text-sm" required>
                        <option value="fixed_price" {{ old('type', $method->type ?? '') === 'fixed_price' ? 'selected' : '' }}>{{ __('admin.shipping_methods.type_fixed_price') }}</option>
                        <option value="with_weight" {{ old('type', $method->type ?? '') === 'with_weight' ? 'selected' : '' }}>{{ __('admin.shipping_methods.type_with_weight') }}</option>
                        <option value="cod24" {{ old('type', $method->type ?? '') === 'cod24' ? 'selected' : '' }}>{{ __('admin.shipping_methods.type_cod24') }}</option>
                    </select>
                    @error('type') <p class="text-rose-500 text-xs mt-0.5">{{ $message }}</p> @enderror
                </div>


                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.shipping_methods.field_price') }}</label>
                    <input type="number" name="price" value="{{ old('price', $method->price ?? '') }}" class="input-custom !py-1.5 !text-sm" min="0" placeholder="0">
                    @error('price') <p class="text-rose-500 text-xs mt-0.5">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.shipping_methods.field_price_ceiling') }}</label>
                    <input type="number" name="price_ceiling" value="{{ old('price_ceiling', $method->price_ceiling ?? 0) }}" class="input-custom !py-1.5 !text-sm" min="0" placeholder="0">
                    @error('price_ceiling') <p class="text-rose-500 text-xs mt-0.5">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.shipping_methods.field_description') }}</label>
                    <textarea name="description" rows="1" class="input-custom !py-1.5 !text-sm" placeholder="{{ __('admin.shipping_methods.description_placeholder') }}">{{ old('description', $method->description ?? '') }}</textarea>
                </div>
            </div>
        </div>

        {{-- زمان ارسال --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white">{{ __('admin.shipping_methods.estimated_days') }}</h3>
                <x-admin.toggle name="show_estimated_days" label="{{ __('admin.shipping_methods.show_on_site') }}" :checked="old('show_estimated_days', $method->show_estimated_days ?? 1)" />
            </div>

            <div id="estimatedDaysFields" class="{{ old('show_estimated_days', $method->show_estimated_days ?? 1) ? '' : 'hidden' }}">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.shipping_methods.min_days') }} ({{ __('admin.shipping_methods.days_unit') }})</label>
                        <input type="number" name="estimated_days_min" value="{{ old('estimated_days_min', $method->estimated_days_min ?? 1) }}" class="input-custom !py-1.5 !text-sm" min="1" max="30">
                        @error('estimated_days_min') <p class="text-rose-500 text-xs mt-0.5">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.shipping_methods.max_days') }} ({{ __('admin.shipping_methods.days_unit') }})</label>
                        <input type="number" name="estimated_days_max" value="{{ old('estimated_days_max', $method->estimated_days_max ?? 3) }}" class="input-custom !py-1.5 !text-sm" min="1" max="30">
                        @error('estimated_days_max') <p class="text-rose-500 text-xs mt-0.5">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="bg-indigo-50 dark:bg-indigo-500/10 rounded-lg p-2 mt-2 flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-xs text-indigo-700 dark:text-indigo-400">
                        {{ __('admin.shipping_methods.preview') }}: <strong>{{ old('estimated_days_min', $method->estimated_days_min ?? 1) }} {{ __('admin.shipping_methods.preview') }} {{ old('estimated_days_max', $method->estimated_days_max ?? 3) }} {{ __('admin.shipping_methods.days_unit') }}</strong>
                    </p>
                </div>
            </div>
        </div>

        {{-- اطلاعات فرستنده --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-3">{{ __('admin.shipping_methods.sender_info') }}</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.shipping_methods.sender_name') }}</label>
                    <input type="text" name="sender_name" value="{{ old('sender_name', $method->sender_name ?? '') }}" class="input-custom !py-1.5 !text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.shipping_methods.sender_company') }}</label>
                    <input type="text" name="sender_company" value="{{ old('sender_company', $method->sender_company ?? '') }}" class="input-custom !py-1.5 !text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.shipping_methods.sender_phone') }}</label>
                    <input type="text" name="sender_phone" value="{{ old('sender_phone', $method->sender_phone ?? '') }}" class="input-custom !py-1.5 !text-sm" dir="ltr" placeholder="021-XXXXXXXX">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.shipping_methods.sender_mobile') }}</label>
                    <input type="text" name="sender_mobile" value="{{ old('sender_mobile', $method->sender_mobile ?? '') }}" class="input-custom !py-1.5 !text-sm" dir="ltr" placeholder="09XXXXXXXXX">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.shipping_methods.sender_email') }}</label>
                    <input type="email" name="sender_email" value="{{ old('sender_email', $method->sender_email ?? '') }}" class="input-custom !py-1.5 !text-sm" dir="ltr">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.shipping_methods.sender_city') }}</label>
                    <select name="sender_city_id" class="input-custom !py-1.5 !text-sm">
                        <option value="">{{ __('admin.shipping_methods.select_city') }}</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}" {{ old('sender_city_id', $method->sender_city_id ?? '') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.shipping_methods.sender_address') }}</label>
                    <textarea name="sender_address" rows="1" class="input-custom !py-1.5 !text-sm">{{ old('sender_address', $method->sender_address ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.shipping_methods.sender_postal_code') }}</label>
                    <input type="text" name="sender_postal_code" value="{{ old('sender_postal_code', $method->sender_postal_code ?? '') }}" class="input-custom !py-1.5 !text-sm" dir="ltr" placeholder="XXXXXXXXXXXX">
                </div>
            </div>
        </div>
    </div>

    {{-- سایدبار --}}
    <div class="space-y-4">
        {{-- وضعیت --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-2">{{ __('admin.shipping_methods.status') }}</h3>
            <div class="space-y-2">
                <x-admin.toggle name="status" label="{{ __('admin.shipping_methods.active') }}" :checked="old('status', $method->status ?? 0)" />
                <x-admin.toggle name="freight_balance" label="{{ __('admin.shipping_methods.freight_balance') }}" :checked="old('freight_balance', $method->freight_balance ?? 0)" />
            </div>
        </div>

        {{-- شهرهای مقصد --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-1">{{ __('admin.shipping_methods.destination_cities') }}</h3>
            <p class="text-xs text-gray-400 mb-2">{{ __('admin.shipping_methods.active_cities') }}</p>
            <div class="flex items-center gap-1.5 mb-2 px-2 py-1 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700">
                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" id="citySearchInput" placeholder="{{ __('admin.shipping_methods.search_cities') }}"
                       class="w-full bg-transparent text-xs text-gray-700 dark:text-gray-300 placeholder-gray-400 focus:outline-none">
            </div>
            <div class="flex items-center justify-between mb-1">
                <button type="button" id="toggleAllCities" class="text-[11px] text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium transition-colors">
                    {{ __('admin.shipping_methods.select_all') }}
                </button>
                <span id="citiesSelectedCount" class="text-[10px] text-gray-400"></span>
            </div>
            <div id="citiesList" class="overflow-y-auto space-y-0.5" style="max-height: 7rem; scrollbar-width: thin;">
                @foreach($cities as $city)
                    <label class="city-item flex items-center gap-2 px-1.5 py-1 rounded hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors">
                        <input type="checkbox" name="cities[]" value="{{ $city->id }}"
                               {{ in_array($city->id, $selectedCityIds ?? []) ? 'checked' : '' }}
                               class="w-3.5 h-3.5 rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-xs text-gray-700 dark:text-gray-300">{{ $city->name }}</span>
                    </label>
                @endforeach
            </div>
            <p id="noCityResult" class="hidden text-xs text-gray-400 text-center py-2">{{ __('admin.shipping_methods.no_results') }}</p>
        </div>

        {{-- دکمه‌ها --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
            <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm px-4 py-2 rounded-lg shadow-sm transition-all hover:shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ __('admin.shipping_methods.save') }}
            </button>
            <a href="{{ route('admin.shipping-methods.index') }}" class="mt-2 w-full inline-flex items-center justify-center px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 font-medium transition-all text-xs">
                {{ __('admin.shipping_methods.cancel') }}
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle estimated days
        var toggle = document.querySelector('input[name="show_estimated_days"]');
        var fields = document.getElementById('estimatedDaysFields');
        if (toggle && fields) {
            toggle.addEventListener('change', function() {
                fields.classList.toggle('hidden', !this.checked);
            });
        }

        // City search filter
        var searchInput = document.getElementById('citySearchInput');
        var citiesList = document.getElementById('citiesList');
        var noResult = document.getElementById('noCityResult');
        if (searchInput && citiesList) {
            searchInput.addEventListener('input', function() {
                var query = this.value.trim().toLowerCase();
                var items = citiesList.querySelectorAll('.city-item');
                var visibleCount = 0;
                items.forEach(function(item) {
                    var text = item.querySelector('span').textContent.toLowerCase();
                    var match = text.indexOf(query) !== -1;
                    item.style.display = match ? '' : 'none';
                    if (match) visibleCount++;
                });
                if (noResult) noResult.classList.toggle('hidden', visibleCount > 0);
            });
        }

        // Select All / Deselect All cities
        var toggleBtn = document.getElementById('toggleAllCities');
        var counterEl = document.getElementById('citiesSelectedCount');
        var selectAllText = @json(__('admin.shipping_methods.select_all'));
        var deselectAllText = @json(__('admin.shipping_methods.deselect_all'));
        function updateCityCount() {
            if (!counterEl || !citiesList) return;
            var total = citiesList.querySelectorAll('.city-item').length;
            var checked = citiesList.querySelectorAll('input[type="checkbox"]:checked').length;
            counterEl.textContent = checked + ' / ' + total;
            if (toggleBtn) toggleBtn.textContent = checked === total ? deselectAllText : selectAllText;
        }
        if (toggleBtn && citiesList) {
            toggleBtn.addEventListener('click', function() {
                var allItems = citiesList.querySelectorAll('.city-item');
                var allChecked = true;
                allItems.forEach(function(item) { if (item.style.display === 'none') return; if (!item.querySelector('input').checked) allChecked = false; });
                allItems.forEach(function(item) {
                    if (item.style.display === 'none') return;
                    item.querySelector('input').checked = !allChecked;
                });
                updateCityCount();
            });
            citiesList.addEventListener('change', updateCityCount);
            updateCityCount();
        }
    });
</script>
@endpush
