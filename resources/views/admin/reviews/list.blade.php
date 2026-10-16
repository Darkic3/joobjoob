@extends('layouts.admin.app')

@section('tab-name', __('admin.reviews.tab'))

@section('content')
    <div class="space-y-6 animate-slide-up">

        {{-- هدر صفحه --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('admin.reviews.title') }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('admin.reviews.subtitle') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300">
                    {{ $reviews->total() }} {{ __('admin.reviews.count') }}
                </span>
            </div>
        </div>

        {{-- فیلتر و جستجو --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
            <form action="{{ route('admin.reviews.index') }}" method="GET" class="flex flex-col sm:flex-row sm:flex-wrap gap-3">
                <div class="flex-1 sm:min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="{{ __('admin.reviews.search_ph') }}"
                           class="input-custom w-full">
                </div>
                <select name="status" class="input-custom w-full sm:w-auto sm:min-w-[140px]">
                    <option value="">{{ __('admin.common.all_statuses') }}</option>
                    <option value="0" @selected(request('status') === '0')>{{ __('admin.reviews.status_pending') }}</option>
                    <option value="1" @selected(request('status') === '1')>{{ __('admin.reviews.status_approved') }}</option>
                    <option value="2" @selected(request('status') === '2')>{{ __('admin.reviews.status_rejected') }}</option>
                </select>
                <select name="rating" class="input-custom w-full sm:w-auto sm:min-w-[120px]">
                    <option value="">{{ __('admin.reviews.all_ratings') }}</option>
                    @for($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}" @selected(request('rating') == $i)>{{ $i }} ★</option>
                    @endfor
                </select>
                <button type="submit" class="btn-primary px-6 flex items-center justify-center gap-2 w-full sm:w-auto">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span>{{ __('admin.common.search') }}</span>
                </button>
            </form>
        </div>

        {{-- ═══════════════════════════════════════════
             جدول نظرات — دسکتاپ (≥ 768px)
             ═══════════════════════════════════════════ --}}
        <div class="hidden md:block bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/40">
                    <tr>
                        <th class="px-5 py-3 text-start text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">#</th>
                        <th class="px-5 py-3 text-start text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.reviews.author') }}</th>
                        <th class="px-5 py-3 text-start text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.reviews.product') }}</th>
                        <th class="px-5 py-3 text-start text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.reviews.rating') }}</th>
                        <th class="px-5 py-3 text-start text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.reviews.comment') }}</th>
                        <th class="px-5 py-3 text-start text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.reviews.status') }}</th>
                        <th class="px-5 py-3 text-start text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.reviews.date') }}</th>
                        <th class="px-5 py-3 text-start text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.common.actions') }}</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($reviews as $review)
                        @php
                            $rowBg = match($review->is_approved) {
                                0 => 'bg-amber-50/30 dark:bg-amber-900/5',
                                2 => 'bg-red-50/30 dark:bg-red-900/5',
                                default => '',
                            };
                            $statusColors = [
                                0 => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
                                1 => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
                                2 => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
                            ];
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors {{ $rowBg }}">
                            <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $reviews->firstItem() + $loop->index }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-xs font-bold">
                                        {{ mb_substr($review->display_name, 0, 1) }}
                                    </span>
                                    <div>
                                        <span class="font-medium text-gray-800 dark:text-gray-100 text-sm">{{ $review->display_name }}</span>
                                        @if($review->parent)
                                            <div class="text-[11px] text-blue-500 dark:text-blue-400 mt-0.5 flex items-center gap-1">
                                                <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                                {{ __('admin.reviews.reply_to') }}: {{ $review->parent->display_name }}
                                            </div>
                                        @endif
                                        @if($review->guest_phone)
                                            <div class="text-xs text-gray-400 dark:text-gray-500" dir="ltr">{{ $review->guest_phone }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                @if($review->product)
                                    <a href="{{ localizedRoute('products.show', ['product' => $review->product->slug]) }}" target="_blank"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400 hover:bg-primary-100 dark:hover:bg-primary-900/40 transition-colors">
                                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        <span class="line-clamp-1">{{ $review->product->trans('name') }}</span>
                                    </a>
                                @else
                                    <span class="text-sm text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-0.5">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span class="text-sm {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-300 dark:text-gray-600' }}">★</span>
                                    @endfor
                                    <span class="text-xs text-gray-500 dark:text-gray-400 mr-1">({{ $review->rating }})</span>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-2 max-w-xs">{{ $review->comment }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <select class="review-status-select text-xs rounded-full px-2.5 py-1 border-0 cursor-pointer font-semibold dark:text-white {{ $statusColors[$review->is_approved] ?? $statusColors[0] }}"
                                        data-id="{{ $review->id }}"
                                        onchange="toggleApproval(this)">
                                    <option value="0" {{ $review->is_approved == 0 ? 'selected' : '' }}>{{ __('admin.reviews.status_pending') }}</option>
                                    <option value="1" {{ $review->is_approved == 1 ? 'selected' : '' }}>{{ __('admin.reviews.status_approved') }}</option>
                                    <option value="2" {{ $review->is_approved == 2 ? 'selected' : '' }}>{{ __('admin.reviews.status_rejected') }}</option>
                                </select>
                                <div class="spinner-border spinner-border-sm text-primary d-none mt-1" role="status" id="spinner-{{ $review->id }}"></div>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ show_date($review->created_at, 'Y/m/d - H:i') }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-1.5">
                                    <button type="button"
                                            onclick="toggleFeatured({{ $review->id }}, this)"
                                            class="p-2 rounded-lg transition-all {{ $review->is_featured ? 'text-amber-500 bg-amber-50 dark:bg-amber-900/20 hover:text-amber-600 hover:bg-amber-100 dark:hover:bg-amber-900/30' : 'text-gray-400 dark:text-gray-500 hover:text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20' }}"
                                            title="{{ __('admin.reviews.show_home') }}">
                                        <svg class="w-4 h-4" fill="{{ $review->is_featured ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                        </svg>
                                    </button>
                                    <button type="button"
                                            onclick="openReplyModal({{ $review->id }}, '{{ addslashes($review->display_name) }}')"
                                            class="p-2 text-gray-400 dark:text-gray-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-all"
                                            title="{{ __('admin.reviews.reply') }}">                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                        </svg>
                                    </button>
                                    @if(auth()->user()->canAccess('admin.reviews.index'))
                                        <a href="{{ route('admin.reviews.show', $review) }}"
                                           class="p-2 text-gray-400 dark:text-gray-500 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg transition-all"
                                           title="{{ __('admin.common.show') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12z"/>
                                            </svg>
                                        </a>
                                    @endif
                                    @if(auth()->user()->canAccess('admin.reviews.destroy'))
                                        <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="p-2 text-gray-400 dark:text-gray-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all"
                                                    title="{{ __('admin.common.delete') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-16 text-center">
                                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gray-100 dark:bg-gray-700 mb-4">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('admin.reviews.empty') }}</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════
             کارت نظرات — موبایل (< 768px)
             ═══════════════════════════════════════════ --}}
        <div class="md:hidden space-y-3">
            @forelse($reviews as $review)
                @php
                    $cardBorder = match($review->is_approved) {
                        0 => 'border-r-4 border-r-amber-400',
                        2 => 'border-r-4 border-r-red-400',
                        default => 'border-r-4 border-r-green-400',
                    };
                    $statusColors = [
                        0 => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
                        1 => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
                        2 => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
                    ];
                    $statusLabels = [
                        0 => __('admin.reviews.status_pending'),
                        1 => __('admin.reviews.status_approved'),
                        2 => __('admin.reviews.status_rejected'),
                    ];
                @endphp
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm {{ $cardBorder }} overflow-hidden">

                    {{-- هدر کارت: نویسنده + وضعیت --}}
                    <div class="flex items-center justify-between p-4 pb-2">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-sm font-bold flex-shrink-0">
                                {{ mb_substr($review->display_name, 0, 1) }}
                            </span>
                            <div class="min-w-0">
                                <p class="font-semibold text-gray-800 dark:text-gray-100 text-sm truncate">{{ $review->display_name }}</p>
                                @if($review->parent)
                                    <p class="text-[11px] text-blue-500 dark:text-blue-400 mt-0.5 flex items-center gap-1">
                                        <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                        {{ __('admin.reviews.reply_to') }}: {{ $review->parent->display_name }}
                                    </p>
                                @endif
                                @if($review->guest_phone)
                                    <p class="text-xs text-gray-400 dark:text-gray-500" dir="ltr">{{ $review->guest_phone }}</p>
                                @endif
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold flex-shrink-0 {{ $statusColors[$review->is_approved] ?? $statusColors[0] }}">
                            {{ $statusLabels[$review->is_approved] ?? $statusLabels[0] }}
                        </span>
                    </div>

                    {{-- محصول --}}
                    @if($review->product)
                        <div class="px-4 pb-2">
                            <a href="{{ localizedRoute('products.show', ['product' => $review->product->slug]) }}" target="_blank"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400 hover:bg-primary-100 dark:hover:bg-primary-900/40 transition-colors">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                <span class="line-clamp-1">{{ $review->product->trans('name') }}</span>
                            </a>
                        </div>
                    @endif

                    {{-- امتیاز + تاریخ --}}
                    <div class="px-4 pb-2 flex items-center justify-between">
                        <div class="flex items-center gap-0.5">
                            @for($i = 1; $i <= 5; $i++)
                                <span class="text-sm {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-300 dark:text-gray-600' }}">★</span>
                            @endfor
                        </div>
                        <span class="text-[11px] text-gray-400 dark:text-gray-500">{{ show_date($review->created_at, 'Y/m/d - H:i') }}</span>
                    </div>

                    {{-- متن نظر --}}
                    <div class="px-4 pb-3">
                        <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">{{ $review->comment }}</p>
                    </div>

                    {{-- فوتر: تغییر وضعیت + اقدامات --}}
                    <div class="flex items-center justify-between gap-2 px-4 py-3 bg-gray-100 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                        {{-- انتخاب وضعیت --}}
                        <div class="flex items-center gap-2">
                            <label class="text-[11px] font-medium text-gray-500 dark:text-gray-400">{{ __('admin.reviews.status') }}:</label>
                            <select class="review-status-select text-xs rounded-lg px-2 py-1.5 border border-gray-200 dark:border-gray-600 cursor-pointer font-semibold {{ $statusColors[$review->is_approved] ?? $statusColors[0] }}"
                                    data-id="{{ $review->id }}"
                                    onchange="toggleApproval(this)">
                                <option value="0" {{ $review->is_approved == 0 ? 'selected' : '' }}>{{ __('admin.reviews.status_pending') }}</option>
                                <option value="1" {{ $review->is_approved == 1 ? 'selected' : '' }}>{{ __('admin.reviews.status_approved') }}</option>
                                <option value="2" {{ $review->is_approved == 2 ? 'selected' : '' }}>{{ __('admin.reviews.status_rejected') }}</option>
                            </select>
                            <div class="spinner-border spinner-border-sm text-primary d-none" role="status" id="spinner-mobile-{{ $review->id }}"></div>
                        </div>

                        {{-- دکمه‌های اقدام --}}
                        <div class="flex items-center gap-1">
                            <button type="button"
                                    onclick="openReplyModal({{ $review->id }}, '{{ addslashes($review->display_name) }}')"
                                    class="p-2.5 rounded-lg transition-all text-gray-400 dark:text-gray-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                    title="{{ __('admin.reviews.reply') }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                </svg>
                            </button>
                            <button type="button"
                                    onclick="toggleFeatured({{ $review->id }}, this)"
                                    class="p-2.5 rounded-lg transition-all {{ $review->is_featured ? 'text-amber-500 bg-amber-50 dark:bg-amber-900/20' : 'text-gray-400 dark:text-gray-500 hover:text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20' }}"
                                    title="{{ __('admin.reviews.show_home') }}">
                                <svg class="w-5 h-5" fill="{{ $review->is_featured ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                </svg>
                            </button>
                            @if(auth()->user()->canAccess('admin.reviews.index'))
                                <a href="{{ route('admin.reviews.show', $review) }}"                                    class="p-2.5 text-gray-400 dark:text-gray-500 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg transition-all"
                                   title="{{ __('admin.common.show') }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12z"/>
                                    </svg>
                                </a>
                            @endif
                            @if(auth()->user()->canAccess('admin.reviews.destroy'))
                                <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" class="delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"                                             class="p-2.5 text-gray-400 dark:text-gray-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all"
                                            title="{{ __('admin.common.delete') }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-12 text-center">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gray-100 dark:bg-gray-700 mb-4">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('admin.reviews.empty') }}</p>
                </div>
            @endforelse
        </div>

        {{-- صفحه‌بندی --}}
        @if($reviews->hasPages())
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm px-4 py-3">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>

    @push('styles')
        <style>
            /* اجبار رنگ متن select در دارک مود — مرورگرها گزینه‌ها رو از parent ارث نمی‌برن */
            .dark .review-status-select {
                color: #f3f4f6 !important;
            }
            .dark .review-status-select option[value="0"] {
                background: #422006;
                color: #fbbf24;
            }
            .dark .review-status-select option[value="1"] {
                background: #052e16;
                color: #4ade80;
            }
            .dark .review-status-select option[value="2"] {
                background: #450a0a;
                color: #f87171;
            }
            /* رنگ دکمه‌های اقدام در دارک مود */
            .dark .review-status-select:hover {
                opacity: 0.85;
            }
        </style>
    @endpush

    {{-- ═══ مودال پاسخ به نظر ═══ --}}
    <div id="replyModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm admin-backdrop" onclick="closeReplyModal()"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg pointer-events-auto admin-modal-panel">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">{{ __('admin.reviews.reply_to_review') }}</h3>
                    <p id="replyModalAuthor" class="text-sm text-gray-500 dark:text-gray-400 mt-0.5"></p>
                </div>
                <button onclick="closeReplyModal()" class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="replyForm" class="p-6">
                @csrf
                <input type="hidden" name="review_id" id="replyReviewId">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ __('admin.reviews.comment') }}</label>
                    <textarea name="comment" id="replyComment" rows="4" required
                              class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
                              placeholder="{{ __('admin.reviews.reply_placeholder') }}"></textarea>
                </div>
                <div id="replyError" class="hidden mt-3 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 text-sm"></div>
                <div class="flex items-center justify-end gap-3 mt-5">
                    <button type="button" onclick="closeReplyModal()"
                            class="px-5 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-colors">
                        {{ __('admin.common.cancel') }}
                    </button>
                    <button type="submit" id="replySubmitBtn"
                            class="px-6 py-2.5 text-sm font-bold text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition-all shadow-sm hover:shadow-md">
                        <span id="replySubmitText">{{ __('admin.reviews.reply') }}</span>
                        <span id="replySubmitSpinner" class="hidden">
                            <svg class="animate-spin h-4 w-4 inline-block" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            // ── Reply Modal ──
            function openReplyModal(reviewId, authorName) {
                document.getElementById('replyReviewId').value = reviewId;
                document.getElementById('replyModalAuthor').textContent = '{{ __('admin.reviews.reply_to') }}: ' + authorName;
                document.getElementById('replyComment').value = '';
                document.getElementById('replyError').classList.add('hidden');
                var modal = document.getElementById('replyModal');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';
                setTimeout(function() { document.getElementById('replyComment').focus(); }, 200);
            }

            function closeReplyModal() {
                var modal = document.getElementById('replyModal');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = '';
            }

            document.getElementById('replyForm').addEventListener('submit', function(e) {
                e.preventDefault();
                var comment = document.getElementById('replyComment').value.trim();
                var errorEl = document.getElementById('replyError');
                var submitBtn = document.getElementById('replySubmitBtn');
                var submitText = document.getElementById('replySubmitText');
                var submitSpinner = document.getElementById('replySubmitSpinner');

                if (!comment) {
                    errorEl.textContent = '{{ __('admin.reviews.reply_empty') }}';
                    errorEl.classList.remove('hidden');
                    return;
                }

                errorEl.classList.add('hidden');
                submitBtn.disabled = true;
                submitText.classList.add('hidden');
                submitSpinner.classList.remove('hidden');

                fetch('{{ route('admin.reviews.reply') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        review_id: document.getElementById('replyReviewId').value,
                        comment: comment,
                    }),
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (data.success) {
                        closeReplyModal();
                        // Show success toast
                        SwalUI.toast.success('{{ __("admin.reviews.reply_sent") }}');
                    } else {
                        errorEl.textContent = data.message || '{{ __("admin.common.request_error") }}';
                        errorEl.classList.remove('hidden');
                    }
                })
                .catch(function() {
                    errorEl.textContent = '{{ __("admin.common.request_error") }}';
                    errorEl.classList.remove('hidden');
                })
                .finally(function() {
                    submitBtn.disabled = false;
                    submitText.classList.remove('hidden');
                    submitSpinner.classList.add('hidden');
                });
            });

            function toggleFeatured(reviewId, button) {
                button.disabled = true;

                fetch('{{ route("admin.reviews.featured") }}', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ id: reviewId }),
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (data.success) {
                        var svg = button.querySelector('svg');
                        if (data.is_featured) {
                            button.classList.remove('text-gray-400', 'dark:text-gray-500', 'hover:text-amber-500');
                            button.classList.add('text-amber-500', 'bg-amber-50', 'dark:bg-amber-900/20');
                            svg.setAttribute('fill', 'currentColor');
                        } else {
                            button.classList.remove('text-amber-500', 'bg-amber-50', 'dark:bg-amber-900/20');
                            button.classList.add('text-gray-400', 'dark:text-gray-500', 'hover:text-amber-500');
                            svg.setAttribute('fill', 'none');
                        }
                    }
                })
                .catch(function(err) {
                    console.error(err);
                    SwalUI.error('{{ __("admin.common.request_error") }}');
                })
                .finally(function() {
                    button.disabled = false;
                });
            }

            function toggleApproval(element) {
                var reviewId = element.getAttribute('data-id');
                var isApproved = parseInt(element.value, 10);
                var spinner = document.getElementById('spinner-' + reviewId)
                    || document.getElementById('spinner-mobile-' + reviewId);

                element.disabled = true;
                if (spinner) spinner.classList.remove('d-none');

                fetch('{{ route("admin.reviews.approval") }}', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ id: reviewId, is_approved: isApproved }),
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (data.success) {
                        var colorMap = {
                            '1': 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
                            '0': 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
                            '2': 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
                        };
                        // حذف کلاس‌های رنگ قبلی
                        var allColorClasses = Object.values(colorMap).join(' ').split(' ');
                        allColorClasses.forEach(function(c) { if (c) element.classList.remove(c); });
                        // اضافه کردن کلاس رنگ جدید
                        var newClasses = colorMap[element.value] || colorMap['0'];
                        newClasses.split(' ').forEach(function(c) { if (c) element.classList.add(c); });
                    }
                })
                .catch(function(err) {
                    console.error(err);
                    SwalUI.error('{{ __("admin.common.request_error") }}');
                })
                .finally(function() {
                    element.disabled = false;
                    if (spinner) spinner.classList.add('d-none');
                });
            }
        </script>
    @endpush
@endsection
