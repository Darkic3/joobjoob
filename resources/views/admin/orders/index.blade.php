@extends('layouts.admin.app')

@section('tab-name', __('admin.orders.title'))

@section('content')
    <div class="px-4 sm:px-6 lg:px-8 py-8 max-w-7xl mx-auto">

        {{-- هدر صفحه --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 dark:text-white tracking-tight">{{ __('admin.orders.title') }}</h1>
                    @if($newOrdersCount > 0)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-bold bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                            <svg class="w-4 h-4 animate-pulse" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/></svg>
                            {{ $newOrdersCount }} {{ __('admin.orders.new_orders') }}
                        </span>
                    @endif
                </div>
                <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">{{ __('admin.orders.subtitle') }}</p>
            </div>
        </div>

        {{-- فیلترها --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 mb-6">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">{{ __('admin.orders.filter_order_id') }}</label>
                    <input type="text" name="id" value="{{ request('id') }}" placeholder="#"
                           class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl px-3 py-2 text-sm text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">{{ __('admin.orders.filter_mobile') }}</label>
                    <input type="text" name="mobile" value="{{ request('mobile') }}" placeholder="09..."
                           class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl px-3 py-2 text-sm text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" dir="ltr">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">{{ __('admin.orders.order_status') }}</label>
                    <select name="order_status" class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl px-3 py-2 text-sm text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all">
                        <option value="">{{ __('admin.orders.all_statuses') }}</option>
                        @foreach(['paying','pending_confirmation','paid','deposit_paid','unpaid','cancelled'] as $status)
                            <option value="{{ $status }}" {{ request('order_status') === $status ? 'selected' : '' }}>{{ __('admin.orders.' . $status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">{{ __('admin.orders.shipping_status') }}</label>
                    <select name="shipping_status_id" class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl px-3 py-2 text-sm text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all">
                        <option value="">{{ __('admin.orders.all_shipping') }}</option>
                        @foreach($shippingStatuses as $status)
                            <option value="{{ $status->id }}" {{ request('shipping_status_id') == $status->id ? 'selected' : '' }}>{{ $status->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">{{ __('admin.orders.from_date') }}</label>
                    <input type="text" name="from_date" data-jdp data-jdp-only-date
                           value="{{ request('from_date') }}"
                           placeholder="۱۴۰۳/۰۵/۱۴"
                           class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl px-3 py-2 text-sm text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" dir="ltr">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 block">{{ __('admin.orders.to_date') }}</label>
                    <input type="text" name="to_date" data-jdp data-jdp-only-date
                           value="{{ request('to_date') }}"
                           placeholder="۱۴۰۳/۰۵/۱۴"
                           class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl px-3 py-2 text-sm text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" dir="ltr">
                </div>
                <div class="sm:col-span-2 lg:col-span-6 flex gap-2">
                    <button type="submit" class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm px-5 py-2 rounded-xl shadow-sm transition-all duration-200 hover:shadow-md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        {{ __('admin.orders.filter') }}
                    </button>
                    <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold text-sm px-5 py-2 rounded-xl transition-all duration-200">
                        {{ __('admin.orders.clear') }}
                    </a>
                </div>
            </form>
        </div>

        {{-- نوار اقدامات گروهی --}}
        <div id="bulkActions"
             class="hidden fixed bottom-6 left-1/2 -translate-x-1/2 z-50 bg-gray-900 dark:bg-gray-100 rounded-2xl shadow-2xl px-5 py-3 flex items-center gap-3 transition-all duration-300">
            <button onclick="clearSelection()" class="text-gray-400 dark:text-gray-600 hover:text-white dark:hover:text-gray-900 transition-colors shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <div class="w-px h-6 bg-gray-600 dark:bg-gray-400 shrink-0"></div>
            <span id="selectedCount" class="text-sm font-bold text-white dark:text-gray-900 whitespace-nowrap">۰ سفارش انتخاب شده</span>
            <div class="w-px h-6 bg-gray-600 dark:bg-gray-400 shrink-0"></div>
            <button onclick="printSelected()" class="inline-flex items-center gap-1.5 bg-indigo-500 hover:bg-indigo-600 text-white dark:bg-indigo-600 dark:hover:bg-indigo-700 text-sm font-semibold px-4 py-2 rounded-xl transition-colors shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                چاپ فاکتورها
            </button>
        </div>
        {{-- جدول --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-start">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-4 w-10">
                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                                   class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">#</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.orders.date') }}</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.orders.customer') }}</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.orders.mobile') }}</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.orders.order_status') }}</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.orders.shipping_status') }}</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.orders.total') }}</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('admin.orders.actions') }}</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($orders as $order)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-6 py-4 w-10">
                                <input type="checkbox" name="order_ids[]" value="{{ $order->id }}"
                                       onchange="updateBulkActions()"
                                       class="order-checkbox w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-800 dark:text-gray-200">{{ $order->id }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ show_date($order->created_at, 'd/m') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $order->user?->name ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400" dir="ltr">{{ $order->user?->mobile ?? '—' }}</td>
                            <td class="px-6 py-4">
                                @if($order->status)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold
                                        @if($order->status['badge'] === 'success') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400
                                        @elseif($order->status['badge'] === 'warning') bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400
                                        @elseif($order->status['badge'] === 'danger') bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400
                                        @else bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 @endif">
                                        {{ $order->status['title'] }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($order->shippingStatus)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold text-white" style="background: {{ $order->shippingStatus->color }}">
                                        {{ $order->shippingStatus->title }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-800 dark:text-gray-200" dir="ltr">{{ number_format($order->payment_price) }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        {{ __('admin.orders.view') }}
                                    </a>
                                    <a href="{{ route('admin.orders.factor', $order->id) }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        {{ __('admin.orders.factor') }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    </div>
                                    <p class="text-gray-500 dark:text-gray-400 font-semibold">{{ __('admin.orders.no_results') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- صفحه‌بندی --}}
        <div class="mt-6">
            {{ $orders->withQueryString()->links() }}
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleSelectAll(el) {
            document.querySelectorAll('.order-checkbox').forEach(function(cb) {
                cb.checked = el.checked;
            });
            updateBulkActions();
        }

        function updateBulkActions() {
            var checked = document.querySelectorAll('.order-checkbox:checked');
            var bar = document.getElementById('bulkActions');
            var count = document.getElementById('selectedCount');
            if (checked.length > 0) {
                bar.classList.remove('hidden');
                count.textContent = checked.length + ' سفارش انتخاب شده';
            } else {
                bar.classList.add('hidden');
            }
        }

        function clearSelection() {
            document.querySelectorAll('.order-checkbox').forEach(function(cb) {
                cb.checked = false;
            });
            document.getElementById('selectAll').checked = false;
            updateBulkActions();
        }

        function printSelected() {
            var ids = [];
            document.querySelectorAll('.order-checkbox:checked').forEach(function(cb) {
                ids.push(cb.value);
            });
            if (ids.length === 0) return;
            window.open('{{ route("admin.orders.bulk-factor") }}?ids=' + ids.join(','), '_blank');
        }
    </script>
    @endpush
@endsection
