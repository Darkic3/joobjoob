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
@endphp

<div class="grid grid-cols-1 gap-6">
    {{-- نام نقش --}}
    <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
            نام نقش <span class="text-rose-500">*</span>
        </label>
        <input type="text" name="name" value="{{ $isEdit ? old('name', $role->name) : old('name') }}"
               class="w-full rounded-xl border-gray-200 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
               placeholder="مثال: content-manager" dir="ltr" required>
        @error('name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- دسترسی‌ها --}}
    <div x-data="{ all: false }">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">دسترسی‌های نقش</label>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">بخش‌هایی که این نقش می‌تواند ببیند و مدیریت کند را انتخاب کنید.</p>
            </div>
            <label class="inline-flex items-center gap-2 cursor-pointer select-none bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/20 rounded-xl px-4 py-2.5">
                <input type="checkbox" x-model="all"
                       @change="$el.closest('form').querySelectorAll('.perm-check').forEach(c => c.checked = $el.checked)"
                       class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                <span class="text-sm font-semibold text-indigo-700 dark:text-indigo-400">انتخاب همه</span>
            </label>
        </div>

        <div class="space-y-4">
            @forelse($normalized as $groupKey => $group)
                <div class="rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ __($group['title']) }}</span>
                        <span class="text-[11px] text-gray-400 dark:text-gray-500">{{ count($group['items']) }} دسترسی</span>
                    </div>
                    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                        @foreach($group['items'] as $label => $names)
                            @php $primary = $names[0]; @endphp
                            <label class="flex items-center gap-2.5 cursor-pointer select-none rounded-xl px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors">
                                <input type="checkbox" name="permissions[]" value="{{ $primary }}" class="perm-check w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                                       {{ $isChecked($names) ? 'checked' : '' }}>
                                <span class="text-sm text-gray-700 dark:text-gray-200">{{ __($label) }}</span>
                                @if(count($names) > 1)
                                    <span class="text-[10px] text-gray-400 dark:text-gray-500">(شامل ذخیره)</span>
                                @endif
                            </label>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="text-center py-10 text-gray-400 dark:text-gray-500 text-sm">درخت دسترسی‌ها خالی است — به config/permissions.php مراجعه کنید.</div>
            @endforelse
        </div>
        @error('permissions') <p class="text-rose-500 text-xs mt-2">{{ $message }}</p> @enderror
    </div>
</div>
