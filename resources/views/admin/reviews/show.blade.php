@extends('layouts.admin.app')

@section('tab-name', __('admin.reviews.tab'))

@section('content')
    <div class="max-w-3xl space-y-6 animate-slide-up">

        {{-- هدر --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <a href="{{ route('admin.reviews.index') }}"
                   class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400 hover:text-primary-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    {{ __('admin.reviews.back') }}
                </a>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mt-2">{{ __('admin.reviews.detail') }}</h1>
            </div>
            <div class="flex items-center gap-2">
                {{-- دکمه‌های تأیید/رد/بازگشت --}}
                <form action="{{ route('admin.reviews.approval') }}" method="PATCH" class="inline-flex gap-2 flex-wrap"
                      onsubmit="event.preventDefault(); toggleApprovalDetail(this);">
                    @csrf
                    <input type="hidden" name="id" value="{{ $review->id }}">
                    @if($review->is_approved != 1)
                        <input type="hidden" name="is_approved" value="1">
                        <button type="submit" class="btn-primary inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ __('admin.reviews.approve_btn') }}
                        </button>
                    @endif
                    @if($review->is_approved != 2)
                        <input type="hidden" name="is_approved" value="2">
                        <button type="submit" class="btn-danger inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            {{ __('admin.reviews.reject_btn') }}
                        </button>
                    @endif
                    @if($review->is_approved != 0)
                        <input type="hidden" name="is_approved" value="0">
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            {{ __('admin.reviews.back_to_pending') }}
                        </button>
                    @endif
                </form>

                {{-- دکمه حذف --}}
                @if(auth()->user()->canAccess('admin.reviews.destroy'))
                    <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" class="delete-form inline-flex">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            {{ __('admin.common.delete') }}
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- جزئیات نظر --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">

            {{-- هدر کارت --}}
            <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center justify-center w-11 h-11 rounded-xl bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 font-bold">
                            {{ mb_substr($review->display_name, 0, 1) }}
                        </span>
                        <div>
                            <div class="font-bold text-gray-800 dark:text-gray-100">{{ $review->display_name }}</div>
                            @if($review->guest_phone)
                                <div class="text-sm text-gray-500 dark:text-gray-400 mt-0.5" dir="ltr">{{ $review->guest_phone }}</div>
                            @endif
                            @if($review->user)
                                <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $review->user->email }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="ms-auto flex flex-wrap items-center gap-2">
                        @if($review->is_approved == 1)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ __('admin.reviews.status_approved') }}
                            </span>
                        @elseif($review->is_approved == 2)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                {{ __('admin.reviews.status_rejected') }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                {{ __('admin.reviews.status_pending') }}
                            </span>
                        @endif
                        <span class="text-xs text-gray-400 dark:text-gray-500">{{ show_date($review->created_at, 'Y/m/d - H:i') }}</span>
                    </div>
                </div>
            </div>

            {{-- اطلاعات نظر --}}
            <div class="p-6 grid sm:grid-cols-2 gap-4 border-b border-gray-100 dark:border-gray-700">
                {{-- محصول --}}
                <div>
                    <div class="text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">{{ __('admin.reviews.product') }}</div>
                    @if($review->product)
                        <a href="{{ localizedRoute('products.show', ['product' => $review->product->slug]) }}" target="_blank"
                           class="text-sm font-medium text-primary-600 dark:text-primary-400 hover:underline">
                            {{ $review->product->trans('name') }}
                            <svg class="w-3 h-3 inline-block ms-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                        </a>
                    @else
                        <span class="text-sm text-gray-400">—</span>
                    @endif
                </div>

                {{-- امتیاز --}}
                <div>
                    <div class="text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">{{ __('admin.reviews.rating') }}</div>
                    <div class="flex items-center gap-1">
                        @for($i = 1; $i <= 5; $i++)
                            <span class="text-xl {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-300 dark:text-gray-600' }}">★</span>
                        @endfor
                        <span class="text-sm text-gray-500 dark:text-gray-400 ms-2">({{ $review->rating }}/{{ 5 }})</span>
                    </div>
                </div>
            </div>

            {{-- متن نظر --}}
            <div class="p-6">
                <div class="text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">{{ __('admin.reviews.comment') }}</div>
                <p class="text-sm leading-7 text-gray-700 dark:text-gray-200 whitespace-pre-line">{{ $review->comment }}</p>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function toggleApprovalDetail(form) {
                var input = form.querySelector('input[name="is_approved"]');
                var isApproved = input.value === '1';

                fetch('{{ route("admin.reviews.approval") }}', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ id: '{{ $review->id }}', is_approved: isApproved }),
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(function(err) {
                    console.error(err);
                    alert('{{ __("admin.common.request_error") }}');
                });
            }
        </script>
    @endpush
@endsection
