@extends('layouts.admin.app')

@section('tab-name', __('admin.promo_banners.tab'))

@section('content')
    <div class="space-y-6 animate-slide-up">

        {{-- هدر صفحه --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('admin.promo_banners.title') }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('admin.promo_banners.subtitle') }}</p>
            </div>
            @if(auth()->user()->canAccess('admin.promo-banners.create'))
                <a href="{{ route('admin.promo-banners.create') }}" class="btn-primary flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>{{ __('admin.promo_banners.add_new') }}</span>
                </a>
            @endif
        </div>

        {{-- فیلتر و جستجو --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
            <form action="{{ route('admin.promo-banners.index') }}" method="GET" class="flex flex-wrap gap-3">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="{{ __('admin.promo_banners.search_ph') }}"
                           class="input-custom w-full">
                </div>
                <select name="status" class="input-custom w-auto min-w-[120px]">
                    <option value="">{{ __('admin.common.all_statuses') }}</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>{{ __('admin.common.active') }}</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>{{ __('admin.common.inactive') }}</option>
                </select>
                <button type="submit" class="btn-primary px-6 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span class="hidden sm:inline">{{ __('admin.common.search') }}</span>
                </button>
            </form>
        </div>

        {{-- درگ‌اندروپ ترتیب بنرها — نوار ذخیره + جدول در یک Alpine scope --}}
        <div x-data="{
                dragId: null,
                overId: null,
                changed: false,
                saving: false,
                onDragStart(id) { this.dragId = id; },
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
                    fetch('{{ route('admin.promo-banners.reorder') }}', {
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
                            this.notify(data.message || '{{ __('admin.promo_banners.order_saved') }}');
                            return;
                        }
                        this.notify(data.message || '{{ __('admin.promo_banners.order_invalid') }}', 'error');
                    }).catch(() => {
                        this.saving = false;
                        this.notify('{{ __('admin.promo_banners.order_invalid') }}', 'error');
                    });
                }
            }">

        @if(auth()->user()->canAccess('admin.promo-banners.reorder'))
            {{-- نوار ذخیره ترتیب — فقط بعد از تغییر ردیف‌ها ظاهر می‌شود --}}
            <div class="mb-3 flex items-center justify-between gap-3 px-1" x-show="changed" x-cloak>
                <p class="text-xs text-gray-500 dark:text-gray-400 inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    {{ __('admin.promo_banners.sort_hint') }}
                </p>
                <button type="button" @click="saveOrder($root)" :disabled="saving"
                        class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-4 py-2 rounded-xl shadow-sm transition-all duration-200 hover:shadow-md disabled:opacity-50 disabled:cursor-wait">
                    <svg x-show="saving" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                    {{ __('admin.promo_banners.save_order') }}
                </button>
            </div>
        @endif

        {{-- جدول بنرها --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-start">
                    <thead class="bg-gray-50/80 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 font-semibold text-xs uppercase tracking-wider">
                    <tr>
                        @if(auth()->user()->canAccess('admin.promo-banners.reorder'))
                            <th class="px-3 py-4 w-10 text-center">{{ __('admin.promo_banners.order_col') }}</th>
                        @endif
                        <th class="px-6 py-4">{{ __('admin.promo_banners.title_subtitle') }}</th>
                        <th class="px-6 py-4 w-32">{{ __('admin.promo_banners.style') }}</th>
                        <th class="px-6 py-4 w-28">{{ __('admin.promo_banners.tone') }}</th>
                        <th class="px-6 py-4 w-28">{{ __('admin.common.status') }}</th>
                        <th class="px-6 py-4 w-32">{{ __('admin.common.date') }}</th>
                        <th class="px-6 py-4 text-end w-24">{{ __('admin.common.actions') }}</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700"
                           @dragover.prevent @drop.prevent="onDragEnd($root)">
                    @forelse($banners as $banner)
                        <tr data-id="{{ $banner->id }}"
                            class="hover:bg-gray-50/80 dark:hover:bg-gray-700/40 transition-colors group"
                            :class="{ 'opacity-50': dragId === {{ $banner->id }}, 'bg-indigo-50/60 dark:bg-indigo-500/10': overId === {{ $banner->id }} }"
                            @dragover.prevent="onDragOver({{ $banner->id }}, $event, $root); overId = {{ $banner->id }}"
                            @dragleave="overId = null">
                            @if(auth()->user()->canAccess('admin.promo-banners.reorder'))
                                <td class="px-3 py-4 w-12 text-center cursor-grab active:cursor-grabbing text-gray-300 dark:text-gray-600 hover:text-gray-500 dark:hover:text-gray-400 transition-colors select-none"
                                    draggable="true"
                                    title="{{ __('admin.promo_banners.sort_hint') }}"
                                    @dragstart="onDragStart({{ $banner->id }})"
                                    @dragend="onDragEnd($root)">
                                    <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9h8M8 15h8"/></svg>
                                </td>
                            @endif

                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-800 dark:text-gray-100 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                                    {{ Str::limit($banner->trans('title'), 40) }}
                                </div>
                                @if($banner->trans('subtitle'))
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-1">
                                        {{ $banner->trans('subtitle') }}
                                    </div>
                                @endif
                            </td>

                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                    {{ __('admin.promo_banners.style_options.'.$banner->style) }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-lg {{ $banner->tone === 'gold' ? 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400' : 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-400' }}">
                                    <span class="w-2 h-2 rounded-full {{ $banner->tone === 'gold' ? 'bg-amber-500' : 'bg-indigo-500' }}"></span>
                                    {{ __('admin.promo_banners.tone_options.'.$banner->tone) }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-lg {{ $banner->is_active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800' : 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400 border border-red-100 dark:border-red-800' }}">
                                    <span class="w-1.5 h-1.5 {{ $banner->is_active ? 'bg-emerald-500' : 'bg-red-500' }} rounded-full"></span>
                                    {{ $banner->is_active ? __('admin.common.active') : __('admin.common.inactive') }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400 whitespace-nowrap text-xs">
                                <div>{{ show_date($banner->created_at, 'Y/m/d') }}</div>
                                <div class="text-[10px] text-gray-400 mt-0.5">{{ show_date($banner->created_at, 'H:i') }}</div>
                            </td>

                            <td class="px-6 py-4 text-end">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.promo-banners.edit', $banner) }}"
                                       class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-all" title="{{ __('admin.common.edit') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <form action="{{ route('admin.promo-banners.destroy', $banner) }}" method="POST" class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all" title="{{ __('admin.common.delete') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->canAccess('admin.promo-banners.reorder') ? 7 : 6 }}" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </div>
                                    <p class="text-gray-500 dark:text-gray-400 font-medium">{{ __('admin.promo_banners.empty') }}</p>
                                    @if(auth()->user()->canAccess('admin.promo-banners.create'))
                                        <a href="{{ route('admin.promo-banners.create') }}" class="mt-3 text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400">{{ __('admin.promo_banners.create_first') }} &larr;</a>
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
