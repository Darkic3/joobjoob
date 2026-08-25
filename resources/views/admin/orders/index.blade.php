<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت سفارشات</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid py-4">
        <h2 class="mb-4">📋 مدیریت سفارشات</h2>

        <!-- فیلترها -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-2">
                    <div class="col-md-2">
                        <input type="text" name="id" class="form-control form-control-sm" placeholder="شماره سفارش" value="{{ request('id') }}">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="mobile" class="form-control form-control-sm" placeholder="موبایل" value="{{ request('mobile') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="order_status" class="form-select form-select-sm">
                            <option value="">همه وضعیت‌ها</option>
                            <option value="paying" {{ request('order_status') === 'paying' ? 'selected' : '' }}>در حال پرداخت</option>
                            <option value="paid" {{ request('order_status') === 'paid' ? 'selected' : '' }}>پرداخت شده</option>
                            <option value="deposit_paid" {{ request('order_status') === 'deposit_paid' ? 'selected' : '' }}>بیعانه</option>
                            <option value="unpaid" {{ request('order_status') === 'unpaid' ? 'selected' : '' }}>پرداخت نشده</option>
                            <option value="cancelled" {{ request('order_status') === 'cancelled' ? 'selected' : '' }}>لغو شده</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="shipping_status_id" class="form-select form-select-sm">
                            <option value="">وضعیت ارسال</option>
                            @foreach($shippingStatuses as $status)
                                <option value="{{ $status->id }}" {{ request('shipping_status_id') == $status->id ? 'selected' : '' }}>{{ $status->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
                    </div>
                    <div class="col-md-12 mt-2">
                        <button type="submit" class="btn btn-primary btn-sm">🔍 فیلتر</button>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary btn-sm">پاک کردن</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- جدول -->
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>تاریخ</th>
                        <th>نام کاربر</th>
                        <th>موبایل</th>
                        <th>وضعیت سفارش</th>
                        <th>وضعیت ارسال</th>
                        <th>روش ارسال</th>
                        <th>مبلغ</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>{{ $order->id }}</td>
                            <td>{{ jdate('d/m', $order->created_at->timestamp) }}</td>
                            <td>{{ $order->user?->name ?? '—' }}</td>
                            <td>{{ $order->user?->mobile ?? '—' }}</td>
                            <td>
                                @if($order->status)
                                    <span class="badge bg-{{ $order->status['badge'] }}">{{ $order->status['title'] }}</span>
                                @endif
                            </td>
                            <td>
                                @if($order->shippingStatus)
                                    <span class="badge" style="background-color: {{ $order->shippingStatus->color }}">{{ $order->shippingStatus->title }}</span>
                                @endif
                            </td>
                            <td>{{ $order->shippingMethod?->title ?? '—' }}</td>
                            <td>{{ number_format($order->payment_price) }}</td>
                            <td>
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary">مشاهده</a>
                                <a href="{{ route('admin.orders.factor', $order->id) }}" class="btn btn-sm btn-outline-success" target="_blank">فاکتور</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted">سفارشی یافت نشد</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $orders->withQueryString()->links() }}
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
