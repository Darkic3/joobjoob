@extends('layouts.admin.app')

@section('tab-name', __('admin.languages.tab'))

@section('content')
    <div class="px-4 sm:px-6 lg:px-8 py-8 max-w-7xl mx-auto">

        {{-- هدر صفحه --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 dark:text-white tracking-tight">{{ __('admin.languages.title') }}</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">{{ __('admin.languages.subtitle') }}</p>
            </div>
            @if(auth()->user()->canAccess('admin.languages.create'))
                <a href="{{ route('admin.languages.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2.5 rounded-xl shadow-sm transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ __('admin.languages.add_new') }}
                </a>
            @endif
        </div>

        {{-- هشدار حالت تک‌زبانه — فقط وقتی بیش از یک زبان فعال نیست --}}
        @if(count(locale()->active()) <= 1)
            <div class="mb-6 flex items-start gap-3 rounded-xl border border-amber-200 dark:border-amber-500/30 bg-amber-50 dark:bg-amber-500/10 px-4 py-3 text-sm text-amber-700 dark:text-amber-300">
                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                <div>{{ __('admin.languages.single_mode_notice') }}</div>
            </div>
        @endif

        {{-- درگ‌اندروپ ترتیب زبان‌ها — نوار ذخیره + جدول در یک Alpine scope --}}
        <div x-data="{
                dragId: null,
                overId: null,
                changed: false,
                saving: false,
                onDragStart(id) { this.dragId = id; },
                // root = $root (ریشهٔ کامپوننت) — استفاده از this.$el در x-on ناپایدار است
                // الگوریتم پایدار بدون نوسان: محل درج مستقیماً از Y موش نسبت به وسطِ هر ردیف محاسبه می‌شود.
                // dragover در مرورگر پیاپی (حتی بدون حرکت موش) شلیک می‌شود — پس بعد از هر جابه‌جایی اگر
                // ردیف از قبل در جای درست باشد هیچ کاری نمی‌کنیم تا بین دو موقعیت نچرخد و ترتیبِ رها
                // شده همان چیزی باشد که ذخیره می‌شود.
                onDragOver(id, e, root) {
                    if (this.dragId === null) return;
                    const rows = [...root.querySelectorAll('tbody tr[data-id]')];
                    const fromIdx = rows.findIndex(r => r.dataset.id == this.dragId);
                    if (fromIdx < 0) return;
                    const from = rows[fromIdx];
                    // ایندکس درج: اولین ردیفی که موش بالایِ وسطِ آن است (پیش‌فرض: انتهای جدول).
                    // insertBefore خودش «حذف و درج» انجام می‌دهد، پس همین ایندکسِ ساده درست است —
                    // بدون هیچ decrement (decrement باعث می‌شد ردیف یک‌جا بالاتر/پایین‌تر از موش بیفتد).
                    let insertIdx = rows.length;
                    for (let i = 0; i < rows.length; i++) {
                        const rect = rows[i].getBoundingClientRect();
                        if (e.clientY < rect.top + rect.height / 2) { insertIdx = i; break; }
                    }
                    // ردیف از قبل در جای درست است → هیچ کاری نکن (جلوگیری از نوسان)
                    const anchor = rows[insertIdx];
                    if (fromIdx === insertIdx || (anchor && anchor === from)) return;
                    if (anchor) from.parentNode.insertBefore(from, anchor);
                    else from.parentNode.appendChild(from);
                    this.changed = true;
                },
                // پایان درگ → ذخیرهٔ خودکار AJAX بدون رفرش (مثل الگوی صفحهٔ منوها)
                onDragEnd(root) {
                    this.dragId = null;
                    this.overId = null;
                    if (this.changed && !this.saving) this.saveOrder(root);
                },
                notify(message, type = 'success') {
                    const el = document.getElementById('admin-toast');
                    if (el && window.Alpine) {
                        window.Alpine.$data(el).show(message, type);
                    } else {
                        console.warn('[languages] toast unavailable:', message);
                    }
                },
                saveOrder(root) {
                    if (this.saving) return;
                    this.saving = true;
                    const ids = [...root.querySelectorAll('tbody tr[data-id]')].map(r => Number(r.dataset.id));
                    fetch('{{ route('admin.languages.reorder') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                        },
                        body: JSON.stringify({ ids }),
                    }).then(r => r.json()).then(data => {
                        this.saving = false;
                        if (data.ok) {
                            this.changed = false;
                            this.notify(data.message || '{{ __('admin.languages.order_saved') }}');
                            return;
                        }
                        // شکست: چک‌باکس changed می‌ماند تا نوار ذخیره برای تلاش دوباره دیده شود
                        this.notify(data.message || '{{ __('admin.languages.order_invalid') }}', 'error');
                    }).catch(() => {
                        this.saving = false;
                        this.notify('{{ __('admin.languages.order_invalid') }}', 'error');
                    });
                }
            }">

        @if(auth()->user()->canAccess('admin.languages.reorder'))
            {{-- نوار ذخیره ترتیب — فقط بعد از تغییر ردیف‌ها ظاهر می‌شود --}}
            <div class="mb-3 flex items-center justify-between gap-3 px-1" x-show="changed" x-cloak>
                <p class="text-xs text-gray-500 dark:text-gray-400 inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    {{ __('admin.languages.sort_hint') }}
                </p>
                <button type="button" @click="saveOrder($root)" :disabled="saving"
                        class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-4 py-2 rounded-xl shadow-sm transition-all duration-200 hover:shadow-md disabled:opacity-50 disabled:cursor-wait">
                    <svg x-show="saving" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                    {{ __('admin.languages.save_order') }}
                </button>
            </div>
        @endif

        {{-- جدول زبان‌ها --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-start">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        @if(auth()->user()->canAccess('admin.languages.reorder'))
                            <th class="px-3 py-4 w-10 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.languages.order_col') }}</th>
                        @endif
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.languages.name_col') }}</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.languages.code_col') }}</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.languages.dir_col') }}</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.languages.files_col') }}</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.languages.status_col') }}</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.languages.default_col') }}</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center">{{ __('admin.common.actions') }}</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700"
                           @dragover.prevent @drop.prevent="onDragEnd($root)">
                    @forelse($languages as $language)
                        <tr data-id="{{ $language->id }}"
                            class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150"
                            :class="{ 'opacity-50': dragId === {{ $language->id }}, 'bg-indigo-50/60 dark:bg-indigo-500/10': overId === {{ $language->id }} }"
                            @dragover.prevent="onDragOver({{ $language->id }}, $event, $root); overId = {{ $language->id }}"
                            @dragleave="overId = null">
                            @if(auth()->user()->canAccess('admin.languages.reorder'))
                                {{-- دستگیره درگ — شروع کشیدن از اینجا --}}
                                <td class="px-3 py-4 w-12 text-center cursor-grab active:cursor-grabbing text-gray-300 dark:text-gray-600 hover:text-gray-500 dark:hover:text-gray-400 transition-colors select-none"
                                    draggable="true"
                                    title="{{ __('admin.languages.sort_hint') }}"
                                    @dragstart="onDragStart({{ $language->id }})"
                                    @dragend="onDragEnd($root)">
                                    <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9h8M8 15h8"/></svg>
                                </td>
                            @endif
                            {{-- نام زبان --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400 flex-shrink-0 font-bold text-sm">
                                        {{ strtoupper(mb_substr($language->native_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $language->native_name }}</div>
                                        <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $language->localizedName() }}</div>
                                    </div>
                                </div>
                            </td>
                            {{-- کد --}}
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-1 rounded-md bg-gray-100 dark:bg-gray-700 text-xs font-mono font-semibold text-gray-600 dark:text-gray-300">{{ $language->code }}</span>
                            </td>
                            {{-- جهت --}}
                            <td class="px-6 py-4">
                                @if($language->isRtl())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-violet-50 text-violet-700 dark:bg-violet-500/10 dark:text-violet-400">RTL</span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-600/50 dark:text-gray-300">LTR</span>
                                @endif
                            </td>
                            {{-- وضعیت فایل‌های ترجمه --}}
                            <td class="px-6 py-4">
                                @if(is_dir(resource_path('lang/'.$language->code)))
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        {{ __('admin.languages.files_ok') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                                        {{ __('admin.languages.files_missing') }}
                                    </span>
                                @endif
                            </td>
                            {{-- وضعیت فعال/غیرفعال — سوییچ سریع (با پرمیشن خودش) --}}
                            <td class="px-6 py-4">
                                @if(auth()->user()->canAccess('admin.languages.toggle'))
                                    <form action="{{ route('admin.languages.toggle', $language) }}" method="POST" class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                                title="{{ $language->is_active ? __('admin.languages.inactive') : __('admin.languages.active') }}"
                                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 {{ $language->is_active ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-gray-600' }}">
                                            <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform duration-200 {{ $language->is_active ? 'translate-x-6 rtl:-translate-x-6' : 'translate-x-1 rtl:-translate-x-1' }}"></span>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs font-semibold {{ $language->is_active ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400' }}">{{ $language->is_active ? __('admin.languages.active') : __('admin.languages.inactive') }}</span>
                                @endif
                            </td>
                            {{-- پیش‌فرض --}}
                            <td class="px-6 py-4">
                                @if($language->is_default)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        {{ __('admin.languages.default') }}
                                    </span>
                                @elseif($language->is_active && auth()->user()->canAccess('admin.languages.make-default'))
                                    <form action="{{ route('admin.languages.make-default', $language) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors inline-flex items-center gap-1">
                                            {{ __('admin.languages.make_default') }}
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-300 dark:text-gray-600">—</span>
                                @endif
                            </td>
                            {{-- عملیات --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-2">
                                    @if(auth()->user()->canAccess('admin.languages.edit'))
                                        <a href="{{ route('admin.languages.edit', $language) }}" class="text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors p-1.5 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-500/10" title="{{ __('admin.common.edit') }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                    @endif
                                    @if(! $language->is_default && auth()->user()->canAccess('admin.languages.delete'))
                                        <form action="{{ route('admin.languages.destroy', $language) }}" method="POST" class="delete-form" onsubmit="return confirm('{{ __('admin.languages.delete_confirm', ['name' => $language->native_name]) }}')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-gray-400 hover:text-rose-600 dark:hover:text-rose-400 transition-colors p-1.5 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-500/10" title="{{ __('admin.common.delete') }}">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->canAccess('admin.languages.reorder') ? 8 : 7 }}" class="px-6 py-16 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21a9 9 0 100-18 9 9 0 000 18zM8 9h8m-8 3h5m-5 3h2"/></svg>
                                    </div>
                                    <p class="font-semibold text-gray-700 dark:text-gray-300">{{ __('admin.languages.empty') }}</p>
                                    @if(auth()->user()->canAccess('admin.languages.create'))
                                        <a href="{{ route('admin.languages.create') }}" class="mt-3 text-sm font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">{{ __('admin.languages.create_first') }}</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        </div>{{-- /درگ‌اندروپ ترتیب --}}

        <p class="mt-4 text-xs text-gray-400 dark:text-gray-500">{{ __('admin.languages.calendar_hint') }}</p>
    </div>
@endsection
