<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    {{-- ستون اصلی --}}
    <div class="lg:col-span-2 space-y-4">
        {{-- اطلاعات پایه --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-3">{{ __('admin.banks.basic_info') }}</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.banks.field_title') }} <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $bank->title ?? '') }}" class="input-custom !py-1.5 !text-sm" placeholder="{{ __('admin.banks.field_title_placeholder') }}" required>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.banks.field_bank_type') }} <span class="text-rose-500">*</span></label>
                    <select name="bank_type" class="input-custom !py-1.5 !text-sm" required id="bankTypeSelect">
                        <option value="zarinPal" {{ old('bank_type', $bank->bank_type ?? '') === 'zarinPal' ? 'selected' : '' }}>ZarinPal</option>
                        <option value="card_to_card" {{ old('bank_type', $bank->bank_type ?? '') === 'card_to_card' ? 'selected' : '' }}>{{ __('admin.banks.type_card_to_card') }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.banks.field_icon') }}</label>
                    <input type="file" name="icon" accept="image/*" class="input-custom !py-1.5 !text-sm">
                    @if(!empty($bank->icon))
                        <div class="mt-1.5"><img src="{{ $bank->item_image }}" class="h-6 w-auto"></div>
                    @endif
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.banks.field_config') }}</label>
                    <textarea name="config" rows="3" class="input-custom !py-1.5 !text-sm font-mono text-xs" placeholder='{"merchant_id": "...", "api_key": "..."}'>{{ old('config', is_array($bank->config ?? null) ? json_encode($bank->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : ($bank->config ?? '')) }}</textarea>
                </div>
            </div>
        </div>

        {{-- اطلاعات کارت به کارت --}}
        <div id="cardToCardFields" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 {{ (old('bank_type', $bank->bank_type ?? '') !== 'card_to_card') ? 'hidden' : '' }}">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-3">{{ __('admin.banks.card_to_card_info') }}</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.banks.field_card_number') }} <span class="text-rose-500">*</span></label>
                    <input type="text" name="card_number" value="{{ format_card_number(old('card_number', $bank->card_number ?? '')) }}" class="input-custom !py-1.5 !text-sm" dir="ltr" placeholder="6037-XXXX-XXXX-XXXX" id="cardNumberInput" data-card-format>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.banks.field_card_holder_name') }}</label>
                    <input type="text" name="card_holder_name" value="{{ old('card_holder_name', $bank->card_holder_name ?? '') }}" class="input-custom !py-1.5 !text-sm" placeholder="{{ __('admin.banks.field_card_holder_placeholder') }}">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.banks.field_card_description') }}</label>
                    <textarea name="card_description" rows="2" class="input-custom !py-1.5 !text-sm" placeholder="{{ __('admin.banks.field_card_description_placeholder') }}">{{ old('card_description', $bank->card_description ?? '') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- سایدبار --}}
    <div class="space-y-4">
        {{-- وضعیت --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-2">{{ __('admin.banks.status') }}</h3>
            <div class="space-y-2">
                <x-admin.toggle name="status" label="{{ __('admin.banks.active') }}" :checked="old('status', $bank->status ?? 0)" />
                <x-admin.toggle name="admin_test" label="{{ __('admin.banks.admin_test') }}" :checked="old('admin_test', $bank->admin_test ?? 0)" />
            </div>
        </div>

        {{-- دکمه‌ها --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
            <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm px-4 py-2 rounded-lg shadow-sm transition-all hover:shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ __('admin.banks.save') }}
            </button>
            <a href="{{ route('admin.banks.index') }}" class="mt-2 w-full inline-flex items-center justify-center px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 font-medium transition-all text-xs">
                {{ __('admin.banks.cancel') }}
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var bankTypeSelect = document.getElementById('bankTypeSelect');
        var cardToCardFields = document.getElementById('cardToCardFields');

        if (bankTypeSelect && cardToCardFields) {
            bankTypeSelect.addEventListener('change', function() {
                cardToCardFields.classList.toggle('hidden', this.value !== 'card_to_card');
            });
        }


        // Show validation errors as toasts
        @if(isset($errors) && $errors->any())
            var toastEl = document.getElementById('admin-toast');
            if (toastEl && toastEl.__x) {
                @foreach($errors->all() as $error)
                    toastEl.__x.$data.show(@json($error), 'error');
                @endforeach
            }
        @endif
    });
</script>
@endpush
