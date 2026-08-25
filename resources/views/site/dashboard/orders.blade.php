<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سفارشات من</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>📋 سفارشات من</h2>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">بازگشت به پنل</a>
        </div>

        @if($orders->isEmpty())
            <div class="alert alert-info">
                شما هنوز سفارشی ثبت نکرده‌اید.
                <a href="{{ route('products.index') }}" class="alert-link">مشاهده محصولات</a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>تاریخ</th>
                            <th>وضعیت سفارش</th>
                            <th>وضعیت ارسال</th>
                            <th>مبلغ قابل پرداخت</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td>{{ $order->id }}</td>
                                <td>{{ jdate('d/m/Y H:i', $order->created_at->timestamp) }}</td>
                                <td>
                                    @if($order->status)
                                        <span class="badge bg-{{ $order->status['badge'] }}">{{ $order->status['title'] }}</span>
                                    @else
                                        <span class="badge bg-secondary">نامشخص</span>
                                    @endif
                                </td>
                                <td>
                                    @if($order->shippingStatus)
                                        <span class="badge" style="background-color: {{ $order->shippingStatus->color }}">
                                            {{ $order->shippingStatus->title }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">—</span>
                                    @endif
                                </td>
                                <td>{{ number_format($order->payment_price) }} تومان</td>
                                <td>
                                    <a href="{{ route('dashboard.order-detail', $order->id) }}" class="btn btn-sm btn-outline-primary">جزئیات</a>
                                    @if($order->order_status === 'paid')
                                        <a href="{{ route('dashboard.order-factor', $order->id) }}" class="btn btn-sm btn-outline-success" target="_blank">فاکتور</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $orders->links() }}
        @endif
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
