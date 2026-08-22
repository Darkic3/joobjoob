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
            <form action="{{ route('admin.reviews.index') }}" method="GET" class="flex flex-wrap gap-3">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="{{ __('admin.reviews.search_ph') }}"
                           class="input-custom w-full">
                </div>
                <select name="status" class="input-custom min-w-[140px]">
                    <option value="">{{ __('admin.common.all_statuses') }}</option>
                    <option value="0" @selected(request('status') === '0')>{{ __('admin.reviews.status_pending') }}</option>
                    <option value="1" @selected(request('status') === '1')>{{ __('admin.reviews.status_approved') }}</option>
                    <option value="2" @selected(request('status') === '2')>{{ __('admin.reviews.status_rejected') }}</option>
                </select>
                <select name="rating" class="input-custom min-w-[120px]">
                    <option value="">{{ __('admin.reviews.all_ratings') }}</option>
                    @for($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}" @selected(request('rating') == $i)>{{ $i }} ★</option>
                    @endfor
                </select>
                <button type="submit" class="btn-primary px-6 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span class="hidden sm:inline">{{ __('admin.common.search') }}</span>
                </button>
            </form>
        </div>

        {{-- جدول نظرات --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
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
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors {{ $review->is_approved == 0 ? 'bg-amber-50/30 dark:bg-amber-900/5' : ($review->is_approved == 2 ? 'bg-red-50/30 dark:bg-red-900/5' : '') }}">
                            <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $reviews->firstItem() + $loop->index }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-xs font-bold">
                                        {{ mb_substr($review->display_name, 0, 1) }}
                                    </span>
                                    <div>
                                        <span class="font-medium text-gray-800 dark:text-gray-100 text-sm">{{ $review->display_name }}</span>
                                        @if($review->guest_phone)
                                            <div class="text-xs text-gray-400 dark:text-gray-500" dir="ltr">{{ $review->guest_phone }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                @if($review->product)
                                    <a href="{{ localizedRoute('products.show', ['product' => $review->product->slug]) }}" target="_blank"
                                       class="text-sm text-primary-600 dark:text-primary-400 hover:underline line-clamp-1 max-w-[180px]">
                                        {{ $review->product->trans('name') }}
                                    </a>
                                @else
                                    <span class="text-sm text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-0.5">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $review->rating)
                                            <span class="text-amber-400 text-sm">★</span>
                                        @else
                                            <span class="text-gray-300 dark:text-gray-600 text-sm">★</span>
                                        @endif
                                    @endfor
                                    <span class="text-xs text-gray-500 dark:text-gray-400 mr-1">({{ $review->rating }})</span>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-2 max-w-xs">{{ $review->comment }}</p>
                            </td>
                            <td class="px-5 py-4">
                                @php
                                    $statusColors = [
                                        0 => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
                                        1 => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
                                        2 => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
                                    ];
                                @endphp
                                <select class="review-status-select text-xs rounded-full px-2.5 py-1 border-0 cursor-pointer font-semibold {{ $statusColors[$review->is_approved] ?? $statusColors[0] }}"
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
                                    @if(auth()->user()->canAccess('admin.reviews.index'))
                                        <a href="{{ route('admin.reviews.show', $review) }}"
                                           class="p-2 text-gray-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg transition-all"
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
                                                    class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all"
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

            @if($reviews->hasPages())
                <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700">
                    {{ $reviews->links() }}
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            function toggleApproval(element) {
                var reviewId = element.getAttribute('data-id');
                var isApproved = parseInt(element.value, 10);
                var spinner = document.getElementById('spinner-' + reviewId);

                element.disabled = true;
                spinner.classList.remove('d-none');

                fetch('{{ route("admin.reviews.approval") }}', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ id: reviewId, is_approved: isApproved }),
                })
                .then(function(res) { return res.json(); })                    .then(function(data) {
                    if (data.success) {
                        element.style.borderColor = '#198754';
                        var colorMap = {
                            '1': 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
                            '0': 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
                            '2': 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
                        };
                        var classes = (colorMap['1'] + ' ' + colorMap['0'] + ' ' + colorMap['2']).split(' ');
                        classes.forEach(function(c) { if (c) element.classList.remove(c); });
                        var newClasses = colorMap[element.value] || colorMap['0'];
                        newClasses.split(' ').forEach(function(c) { if (c) element.classList.add(c); });
                        setTimeout(function() { element.style.borderColor = ''; }, 2000);
                    }
                })
                .catch(function(err) {
                    console.error(err);
                    alert('{{ __("admin.common.request_error") }}');
                })
                .finally(function() {
                    element.disabled = false;
                    spinner.classList.add('d-none');
                });
            }
        </script>
    @endpush
@endsection
