@php
    $role = $role ?? null;
    $isEdit = $role !== null;

    // دسترسی‌های فعلی نقش (یا old برای بازگشت خطای اعتبارسنجی)
    $rolePerms = $isEdit ? $role->permissions->pluck('name')->all() : [];
    $oldPerms  = old('permissions', $rolePerms);

    // درخت کانفیگ → ساختار dedupe شده: هر لیبل یک بار، با همه نام‌هایش (مثل create+store)
    $tree = config('permissions.permissions', []);
    $normalized = [];
    foreach ($tree as $groupKey => $group) {
        $items = [];
        foreach ($group['access'] as $access => $label) {
            $items[$label][] = "admin.{$groupKey}.{$access}";
        }
        $normalized[$groupKey] = ['title' => $group['title'], 'items' => $items];
    }

    $isChecked = function (array $names) use ($oldPerms) {
        foreach ($names as $n) {
            if (in_array($n, $oldPerms, true)) return true;
        }
        return false;
    };

    // تعداد دسترسی‌های تیک‌خورده (برای شمارنده زنده)
    $checkedCount = 0;
    foreach ($normalized as $group) {
        foreach ($group['items'] as $names) {
            if ($isChecked($names)) $checkedCount++;
        }
    }
@endphp

<div x-data="{
        search: '',
        all: false,
        count: {{ $checkedCount }},
        updateCount() { this.count = document.querySelectorAll('.perm-check:checked').length; },
        toggleAll() {
            document.querySelectorAll('.perm-check').forEach(c => c.checked = this.all);
            this.updateCount();
        }
    }">

    {{-- نوار بالا: نام نقش + جستجو + انتخاب همه — یک ردیف جمع‌وجور --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-4 items-stretch sm:items-end">

        {{-- نام نقش --}}
        <div class="sm:w-60">
            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                نام نقش <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="name" value="{{ $isEdit ? old('name', $role->name) : old('name') }}"
                   class="input-custom text-sm"
                   placeholder="مثال: content-manager" dir="ltr" required>
            @error('name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- جستجوی زنده دسترسی‌ها --}}
        <div class="flex-1">
            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                <span class="inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    جستجوی دسترسی
                </span>
            </label>
            <input type="text" x-model="search" placeholder="مثلاً: محصول، حذف، تنظیمات..."
                   class="input-custom text-sm"
                   @keydown.esc.window="search = ''">
        </div>

        {{-- انتخاب همه + شمارنده --}}
        <div class="sm:w-52">
            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">عملیات سریع</label>
            <div class="flex items-center gap-2">
                <label class="flex-1 inline-flex items-center justify-center gap-1.5 cursor-pointer select-none bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/20 rounded-lg px-2.5 py-1.5">
                    <input type="checkbox" x-model="all" @change="toggleAll()"
                           class="w-3.5 h-3.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                    <span class="text-xs font-semibold text-indigo-700 dark:text-indigo-400">انتخاب همه</span>
                </label>
                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded-lg px-2 py-1.5 whitespace-nowrap tabular-nums"
                      x-text="count + ' تیک‌خورده'"></span>
            </div>
        </div>
    </div>

    {{-- شبکه گروه‌ها — ۳ ستونه، فشرده، با اسکرول داخلی محدود به ارتفاع صفحه --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-2.5 max-h-[calc(100vh-280px)] overflow-y-auto pe-1 pb-1"
         @change="updateCount()">

        @forelse($normalized as $groupKey => $group)
            <div class="perm-group rounded-xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden flex flex-col min-h-0">
                {{-- هدر گروه: عنوان + انتخاب همه‌گروه + تعداد --}}
                <div class="flex items-center justify-between px-2.5 py-1.5 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700 flex-shrink-0">
                    <span class="text-xs font-bold text-gray-800 dark:text-gray-100 truncate">{{ __($group['title']) }}</span>
                    <div class="flex items-center gap-1.5 flex-shrink-0">
                        <label class="inline-flex items-center gap-1 cursor-pointer select-none text-[10px] font-medium text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors" title="انتخاب همه این گروه">
                            <input type="checkbox" class="group-toggle w-3 h-3 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                                   @change="$el.closest('.perm-group').querySelectorAll('.perm-check').forEach(c => c.checked = $el.checked)">
                            همه
                        </label>
                        <span class="text-[10px] text-gray-400 dark:text-gray-500 tabular-nums">{{ count($group['items']) }}</span>
                    </div>
                </div>

                {{-- چک‌باکس‌های گروه — ردیف‌های بسیار فشرده --}}
                <div class="p-1.5 grid grid-cols-1 gap-0.5 overflow-y-auto min-h-0">
                    @foreach($group['items'] as $label => $names)
                        @php $primary = $names[0]; @endphp
                        <label x-show="!search || '{{ addslashes(__($label)) }}'.toLowerCase().includes(search.toLowerCase())"
                               class="flex items-center gap-1.5 cursor-pointer select-none px-1.5 py-[3px] rounded-md hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors">
                            <input type="checkbox" name="permissions[]" value="{{ $primary }}"
                                   class="perm-check w-3.5 h-3.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 flex-shrink-0"
                                   {{ $isChecked($names) ? 'checked' : '' }}>
                            <span class="text-xs text-gray-700 dark:text-gray-200 leading-tight truncate">{{ __($label) }}</span>
                            @if(count($names) > 1)
                                <span class="text-[9px] text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-700 rounded px-1 py-px flex-shrink-0">+ذخیره</span>
                            @endif
                        </label>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-10 text-gray-400 dark:text-gray-500 text-sm">درخت دسترسی‌ها خالی است — به config/permissions.php مراجعه کنید.</div>
        @endforelse
    </div>

    @error('permissions') <p class="text-rose-500 text-xs mt-2">{{ $message }}</p> @enderror
</div>
