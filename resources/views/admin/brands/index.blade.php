@extends('layouts.admin.app')

@section('tab-name', __('admin.brands.tab'))

@section('content')
    <div class="px-4 sm:px-6 lg:px-8 py-8 max-w-7xl mx-auto">

        {{-- هدر صفحه --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 dark:text-white tracking-tight">{{ __('admin.brands.title') }}</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">{{ __('admin.brands.subtitle') }}</p>
            </div>
            @if(auth()->user()->canAccess('admin.brands.create'))
                <a href="{{ route('admin.brands.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2.5 rounded-xl shadow-sm transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ __('admin.brands.add_new') }}
                </a>
            @endif
        </div>

        {{-- فیلتر و جستجو --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 mb-6">
            <form action="{{ route('admin.brands.index') }}" method="GET" class="flex flex-wrap gap-3">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('admin.brands.search_ph') }}" class="input-custom">
                </div>
                <select name="status" class="input-custom w-auto min-w-[120px]">
                    <option value="">{{ __('admin.common.all_statuses') }}</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>{{ __('admin.common.active') }}</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>{{ __('admin.common.inactive') }}</option>
                </select>
                <button type="submit" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2.5 rounded-xl shadow-sm transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    {{ __('admin.common.search') }}
                </button>
            </form>
        </div>

        {{-- درگ‌اندروپ ترتیب برندها — نوار ذخیره + جدول در یک Alpine scope --}}
        <div x-data="{
                dragId: null,
                overId: null,
                changed: false,
                saving: false,
                onDragStart(id) { this.dragId = id; },
                // محل درج از Y موش نسبت به وسط هر ردیف — پایدار و بدون نوسان (dragover پیاپی)
                onDragOver(id, e, root) {
                    if (this.dragId === null) return;
                    const rows = [...root.querySelectorAll('tbody tr[data-id]')];
                    const fromIdx = rows.findIndex(r => r.dataset.id == this.dragId);
                    if (fromIdx < 0) return;
                    const from = rows[fromIdx];
                    let insertIdx = rows.length;
                    for (let i = 0; i < rows.length; i++) {
                        const rect = rows[i].getBoundingClientRect();
                        if (e.clientY < rect.top + rect.height / 2) { insertIdx = i; break; }
                    }
                    const anchor = rows[insertIdx];
                    if (fromIdx === insertIdx || (anchor && anchor === from)) return;
                    if (anchor) from.parentNode.insertBefore(from, anchor);
                    else from.parentNode.appendChild(from);
                    this.changed = true;
                },
                // پایان درگ → ذخیرهٔ خودکار AJAX بدون رفرش
                onDragEnd(root) {
                    this.dragId = null;
                    this.overId = null;
                    if (this.changed && !this.saving) this.saveOrder(root);
                },
                notify(message, type = 'success') {
                    const el = document.getElementById('admin-toast');
                    if (el && window.Alpine) window.Alpine.$data(el).show(message, type);
                    else console.warn('[admin] toast unavailable:', message);
                },
                saveOrder(root) {
                    if (this.saving) return;
                    this.saving = true;
                    const ids = [...root.querySelectorAll('tbody tr[data-id]')].map(r => Number(r.dataset.id));
                    fetch('{{ route('admin.brands.reorder') }}', {
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
                            this.notify(data.message || '{{ __('admin.brands.order_saved') }}');
                            return;
                        }
                        this.notify(data.message || '{{ __('admin.brands.order_invalid') }}', 'error');
                    }).catch(() => {
                        this.saving = false;
                        this.notify('{{ __('admin.brands.order_invalid') }}', 'error');
                    });
                }
            }">

        @if(auth()->user()->canAccess('admin.brands.reorder'))
            {{-- نوار ذخیره ترتیب — فقط بعد از تغییر ردیف‌ها ظاهر می‌شود --}}
            <div class="mb-3 flex items-center justify-between gap-3 px-1" x-show="changed" x-cloak>
                <p class="text-xs text-gray-500 dark:text-gray-400 inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    {{ __('admin.brands.sort_hint') }}
                </p>
                <button type="button" @click="saveOrder($root)" :disabled="saving"
                        class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-4 py-2 rounded-xl shadow-sm transition-all duration-200 hover:shadow-md disabled:opacity-50 disabled:cursor-wait">
                    <svg x-show="saving" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                    {{ __('admin.brands.save_order') }}
                </button>
            </div>
        @endif

        {{-- جدول برندها --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-start">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        @if(auth()->user()->canAccess('admin.brands.reorder'))
                            <th class="px-3 py-4 w-10 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.brands.order_col') }}</th>
                        @endif
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.brands.title') }}</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.brands.url') }}</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.brands.products') }}</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.common.featured') }}</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.common.status') }}</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center">{{ __('admin.common.actions') }}</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700"
                           @dragover.prevent @drop.prevent="onDragEnd($root)">
                    @forelse($brands as $brand)
                        <tr data-id="{{ $brand->id }}"
                            class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150"
                            :class="{ 'opacity-50': dragId === {{ $brand->id }}, 'bg-indigo-50/60 dark:bg-indigo-500/10': overId === {{ $brand->id }} }"
                            @dragover.prevent="onDragOver({{ $brand->id }}, $event, $root); overId = {{ $brand->id }}"
                            @dragleave="overId = null">
                            @if(auth()->user()->canAccess('admin.brands.reorder'))
                                <td class="px-3 py-4 w-12 text-center cursor-grab active:cursor-grabbing text-gray-300 dark:text-gray-600 hover:text-gray-500 dark:hover:text-gray-400 transition-colors select-none"
                                    draggable="true"
                                    title="{{ __('admin.brands.sort_hint') }}"
                                    @dragstart="onDragStart({{ $brand->id }})"
                                    @dragend="onDragEnd($root)">
                                    <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9h8M8 15h8"/></svg>
                                </td>
                            @endif
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-11 w-11 rounded-xl overflow-hidden shadow-sm border border-gray-100 dark:border-gray-700 bg-gray-100 dark:bg-gray-700">
                                        @if($brand->image)
                                            <img src="{{ asset(config('paths.upload', 'uploads') . '/brands/' . $brand->image) }}" class="w-full h-full object-cover" alt="{{ $brand->trans('title') }}">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-gray-400 font-bold">{{ mb_substr($brand->trans('title'), 0, 1) }}</div>
                                        @endif
                                    </div>
                                    <div class="ms-3">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $brand->trans('title') }}</div>
                                        @if($brand->trans('description'))
                                            <div class="text-xs text-gray-500 dark:text-gray-400 line-clamp-1">{{ $brand->trans('description') }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400" dir="ltr">{{ $brand->url }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{{ number_format($brand->products_count) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($brand->show_in_first_page)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400">{{ __('admin.common.show') }}</span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($brand->active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> {{ __('admin.common.active') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-600/50 dark:text-gray-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> {{ __('admin.common.inactive') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.brands.edit', $brand) }}" class="text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors p-1.5 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-500/10" title="{{ __('admin.common.edit') }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form action="{{ route('admin.brands.destroy', $brand) }}" method="POST" class="delete-form">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-gray-400 hover:text-rose-600 dark:hover:text-rose-400 transition-colors p-1.5 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-500/10" title="{{ __('admin.common.delete') }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->canAccess('admin.brands.reorder') ? 7 : 6 }}" class="px-6 py-16 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </div>
                                    <p class="font-semibold text-gray-700 dark:text-gray-300">{{ __('admin.brands.empty') }}</p>
                                    @if(auth()->user()->canAccess('admin.brands.create'))
                                        <a href="{{ route('admin.brands.create') }}" class="mt-3 text-sm font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">{{ __('admin.brands.create_first') }}</a>
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
    </div>
@endsection
