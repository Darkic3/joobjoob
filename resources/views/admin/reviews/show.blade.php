@extends('layouts.admin.app')

@section('tab-name', __('admin.reviews.detail'))

@section('content')
    <div class="px-4 sm:px-6 lg:px-8 py-8 max-w-5xl mx-auto">

        {{-- هدر --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
            <div>
                <a href="{{ route('admin.reviews.index') }}"
                   class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors mb-2">
                    <svg class="w-4 h-4 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    {{ __('admin.reviews.back') }}
                </a>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 dark:text-white tracking-tight">
                    {{ __('admin.reviews.detail') }} <span class="text-indigo-600">#{{ $review->id }}</span>
                </h1>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                @if($review->is_approved != 1)
                    <button onclick="changeApproval({{ $review->id }}, 1)"
                            class="inline-flex items-center gap-1.5 bg-emerald-50 dark:bg-emerald-900/30 hover:bg-emerald-100 dark:hover:bg-emerald-800/50 text-emerald-700 dark:text-emerald-400 font-semibold text-sm px-4 py-2 rounded-xl transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ __('admin.reviews.approve_btn') }}
                    </button>
                @endif
                @if($review->is_approved != 2)
                    <button onclick="changeApproval({{ $review->id }}, 2)"
                            class="inline-flex items-center gap-1.5 bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:hover:bg-red-800/50 text-red-700 dark:text-red-400 font-semibold text-sm px-4 py-2 rounded-xl transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        {{ __('admin.reviews.reject_btn') }}
                    </button>
                @endif
                @if($review->is_approved != 0)
                    <button onclick="changeApproval({{ $review->id }}, 0)"
                            class="inline-flex items-center gap-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold text-sm px-4 py-2 rounded-xl transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        {{ __('admin.reviews.back_to_pending') }}
                    </button>
                @endif
                @if(auth()->user()->canAccess('admin.reviews.destroy'))
                    <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" class="delete-form inline-flex"
                          data-swal-confirm data-swal-danger="1"
                          data-swal-title="{{ __('admin.common.confirm_delete') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white font-semibold text-sm px-4 py-2 rounded-xl transition-all duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            {{ __('admin.common.delete') }}
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ستون اصلی: اطلاعات نظر --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- اطلاعات نویسنده --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <h2 class="text-sm font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center">
                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        {{ __('admin.reviews.reviewer_info') ?? 'اطلاعات نویسنده' }}
                    </h2>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                            <span class="text-gray-500 dark:text-gray-400">نام</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $review->display_name }}</span>
                        </div>
                        @if($review->guest_phone)
                            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-gray-500 dark:text-gray-400">تلفن</span>
                                <span class="font-semibold text-gray-800 dark:text-gray-200" dir="ltr">{{ $review->guest_phone }}</span>
                            </div>
                        @endif
                        @if($review->user)
                            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-gray-500 dark:text-gray-400">ایمیل</span>
                                <span class="font-semibold text-gray-800 dark:text-gray-200" dir="ltr">{{ $review->user->email }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-500 dark:text-gray-400">تاریخ ثبت</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ show_date($review->created_at, 'd F Y - H:i') }}</span>
                        </div>
                    </div>
                </div>

                {{-- متن نظر --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <h2 class="text-sm font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center">
                            <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                        </div>
                        {{ __('admin.reviews.comment') }}
                    </h2>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4">
                        <p class="text-sm leading-7 text-gray-700 dark:text-gray-200 whitespace-pre-line">{{ $review->comment }}</p>
                    </div>
                </div>

                {{-- پاسخ‌ها --}}
                @if($review->replies && $review->replies->count())
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                        <h2 class="text-sm font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                            </div>
                            پاسخ‌ها ({{ $review->replies->count() }})
                        </h2>
                        <div class="space-y-3">
                            @foreach($review->replies as $reply)
                                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 border-{{ $reply->user_id ? 'l-4 border-l-indigo-500' : 'border border-gray-200 dark:border-gray-600' }}">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-xs font-bold">
                                            {{ mb_substr($reply->display_name, 0, 1) }}
                                        </span>
                                        <span class="text-xs font-bold text-gray-800 dark:text-gray-200">{{ $reply->display_name }}</span>
                                        @if($reply->user_id)
                                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 font-semibold">ادمین</span>
                                        @endif
                                        <span class="text-[10px] text-gray-400 dark:text-gray-500 mr-auto">{{ show_date($reply->created_at, 'd/m/Y H:i') }}</span>
                                    </div>
                                    <p class="text-sm text-gray-700 dark:text-gray-200 leading-6">{{ $reply->comment }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- سایدبار --}}
            <div class="space-y-6">

                {{-- وضعیت --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white mb-3">وضعیت</h3>
                    <div id="statusBadge">
                        @if($review->is_approved == 1)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ __('admin.reviews.status_approved') }}
                            </span>
                        @elseif($review->is_approved == 2)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                {{ __('admin.reviews.status_rejected') }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                {{ __('admin.reviews.status_pending') }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- محصول --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white mb-3 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center">
                            <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        {{ __('admin.reviews.product') }}
                    </h3>
                    @if($review->product)
                        <a href="{{ localizedRoute('products.show', ['product' => $review->product->slug]) }}" target="_blank"
                           class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors group">
                            @if($review->product->images?->first())
                                <div class="w-12 h-12 rounded-lg overflow-hidden shrink-0">
                                    <img src="{{ asset('storage/' . $review->product->images->first()->path) }}" alt="" class="w-full h-full object-cover">
                                </div>
                            @else
                                <div class="w-12 h-12 rounded-lg bg-gray-200 dark:bg-gray-600 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ $review->product->trans('name') }}</p>
                                <svg class="w-3 h-3 inline-block ms-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </div>
                        </a>
                    @else
                        <p class="text-sm text-gray-400 dark:text-gray-500">محصول حذف شده</p>
                    @endif
                </div>

                {{-- امتیاز --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white mb-3 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center">
                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        </div>
                        {{ __('admin.reviews.rating') }}
                    </h3>
                    <div class="flex items-center gap-1">
                        @for($i = 1; $i <= 5; $i++)
                            <span class="text-2xl {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-200 dark:text-gray-600' }}">★</span>
                        @endfor
                        <span class="text-sm text-gray-500 dark:text-gray-400 ms-2 font-bold">{{ $review->rating }}/5</span>
                    </div>
                </div>

                {{-- ثبت پاسخ --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white mb-3 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center">
                            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                        </div>
                        پاسخ به نظر
                    </h3>
                    <textarea id="replyComment" rows="3" placeholder="متن پاسخ..."
                              class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl px-3 py-2 text-sm text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 outline-none resize-none transition-all"></textarea>
                    <button onclick="sendReply()" id="replyBtn"
                            class="mt-2 w-full inline-flex items-center justify-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm px-4 py-2 rounded-xl shadow-sm transition-all hover:shadow-md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        ارسال پاسخ
                    </button>
                </div>

            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function changeApproval(id, status) {
                var url = '{{ route("admin.reviews.approval") }}';
                var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ id: id, is_approved: status }),
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (data.success) {
                        location.reload();
                    } else {
                        SwalUI.error(data.message || @json(__('admin.common.status_change_error'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                    }
                })
                .catch(function(err) {
                    console.error(err);
                    SwalUI.error(@json(__('admin.common.server_communication_error'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                });
            }

            function sendReply() {
                var comment = document.getElementById('replyComment').value.trim();
                if (!comment) {
                    document.getElementById('replyComment').style.borderColor = '#ef4444';
                    return;
                }

                var btn = document.getElementById('replyBtn');
                btn.disabled = true;
                btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg> در حال ارسال...';

                var url = '{{ route("admin.reviews.reply") }}';
                var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ review_id: {{ $review->id }}, comment: comment }),
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (data.success) {
                        location.reload();
                    } else {
SwalUI.error(data.message || @json(__('admin.common.reply_send_error'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                        btn.disabled = false;
                        btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg> ارسال پاسخ';
                    }
                })
                .catch(function(err) {
                    console.error(err);
                    SwalUI.error(@json(__('admin.common.server_communication_error'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                    btn.disabled = false;
                    btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg> ارسال پاسخ';
                });
            }
        </script>
    @endpush
@endsection
