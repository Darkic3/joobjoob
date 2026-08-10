@extends('layouts.admin.app')

@section('tab-name', __('admin.hero_slides.tab'))

@section('content')
    <div class="space-y-6 animate-slide-up">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('admin.hero_slides.title') }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('admin.hero_slides.subtitle') }}</p>
            </div>
            @if(auth()->user()->canAccess('admin.hero-slides.create'))
                <a href="{{ route('admin.hero-slides.create') }}" class="btn-primary flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>{{ __('admin.hero_slides.add_new') }}</span>
                </a>
            @endif
        </div>

        {{-- درگ‌اندروپ ترتیب اسلایدها — نوار ذخیره + جدول در یک Alpine scope --}}
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
                    fetch('{{ route('admin.hero-slides.reorder') }}', {
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
                            this.notify(data.message || '{{ __('admin.hero_slides.order_saved') }}');
                            return;
                        }
                        this.notify(data.message || '{{ __('admin.hero_slides.order_invalid') }}', 'error');
                    }).catch(() => {
                        this.saving = false;
                        this.notify('{{ __('admin.hero_slides.order_invalid') }}', 'error');
                    });
                }
            }">

        @if(auth()->user()->canAccess('admin.hero-slides.reorder'))
            {{-- نوار ذخیره ترتیب — فقط بعد از تغییر ردیف‌ها ظاهر می‌شود --}}
            <div class="mb-3 flex items-center justify-between gap-3 px-1" x-show="changed" x-cloak>
                <p class="text-xs text-gray-500 dark:text-gray-400 inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    {{ __('admin.hero_slides.sort_hint') }}
                </p>
                <button type="button" @click="saveOrder($root)" :disabled="saving"
                        class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-4 py-2 rounded-xl shadow-sm transition-all duration-200 hover:shadow-md disabled:opacity-50 disabled:cursor-wait">
                    <svg x-show="saving" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                    {{ __('admin.hero_slides.save_order') }}
                </button>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-start">
                    <thead class="bg-gray-50/80 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 font-semibold text-xs uppercase tracking-wider">
                    <tr>
                        @if(auth()->user()->canAccess('admin.hero-slides.reorder'))
                            <th class="px-3 py-4 w-10 text-center">{{ __('admin.hero_slides.order_col') }}</th>
                        @endif
                        <th class="px-6 py-4 w-32">{{ __('admin.hero_slides.image') }}</th>
                        <th class="px-6 py-4">{{ __('admin.common.title') }}</th>
                        <th class="px-6 py-4">{{ __('admin.hero_slides.badge') }}</th>
                        <th class="px-6 py-4">{{ __('admin.common.status') }}</th>
                        <th class="px-6 py-4 text-end w-24">{{ __('admin.common.actions') }}</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700"
                           @dragover.prevent @drop.prevent="onDragEnd($root)">
                    @forelse($slides as $slide)
                        <tr data-id="{{ $slide->id }}"
                            class="hover:bg-gray-50/80 dark:hover:bg-gray-700/40 transition-colors group"
                            :class="{ 'opacity-50': dragId === {{ $slide->id }}, 'bg-indigo-50/60 dark:bg-indigo-500/10': overId === {{ $slide->id }} }"
                            @dragover.prevent="onDragOver({{ $slide->id }}, $event, $root); overId = {{ $slide->id }}"
                            @dragleave="overId = null">
                            @if(auth()->user()->canAccess('admin.hero-slides.reorder'))
                                <td class="px-3 py-4 w-12 text-center cursor-grab active:cursor-grabbing text-gray-300 dark:text-gray-600 hover:text-gray-500 dark:hover:text-gray-400 transition-colors select-none"
                                    draggable="true"
                                    title="{{ __('admin.hero_slides.sort_hint') }}"
                                    @dragstart="onDragStart({{ $slide->id }})"
                                    @dragend="onDragEnd($root)">
                                    <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9h8M8 15h8"/></svg>
                                </td>
                            @endif
                            <td class="px-6 py-4">
                                @if($slide->image)
                                    <img src="{{ asset(config('paths.upload', 'uploads') . '/hero-slides/' . $slide->image) }}"
                                         class="w-16 h-10 rounded-lg object-cover border border-gray-200 dark:border-gray-600" alt="Slide">
                                @else
                                    <div class="w-16 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-800 dark:text-gray-100 truncate max-w-[200px]" title="{{ $slide->trans('title') }}">
                                    {{ Str::limit($slide->trans('title'), 40) }}
                                </div>
                                <span class="mt-1.5 inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-full border {{ count($slide->trustItems()) > 0 ? 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400 border-amber-100 dark:border-amber-800' : 'bg-gray-50 text-gray-400 dark:bg-gray-700/40 dark:text-gray-500 border-gray-100 dark:border-gray-700' }}">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    {{ count($slide->trustItems()) }} {{ __('admin.hero_slides.trust_items') }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg bg-purple-50 text-purple-700 dark:bg-purple-900/20 dark:text-purple-400 border border-purple-100 dark:border-purple-800">
                                    {{ $slide->trans('badge') ?: __('admin.hero_slides.no_badge') }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-lg {{ $slide->is_active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800' : 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400 border border-red-100 dark:border-red-800' }}">
                                    <span class="w-1.5 h-1.5 {{ $slide->is_active ? 'bg-emerald-500' : 'bg-red-500' }} rounded-full"></span>
                                    {{ $slide->is_active ? __('admin.common.active') : __('admin.common.inactive') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-end">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.hero-slides.edit', $slide) }}" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-all" title="{{ __('admin.common.edit') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form action="{{ route('admin.hero-slides.destroy', $slide) }}" method="POST" class="delete-form">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all" title="{{ __('admin.common.delete') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->canAccess('admin.hero-slides.reorder') ? 6 : 5 }}" class="px-6 py-16 text-center">
                                <p class="text-gray-500 dark:text-gray-400">{{ __('admin.hero_slides.empty') }}</p>
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
