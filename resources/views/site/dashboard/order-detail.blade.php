<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>جزئیات سفارش #{{ $order->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>📦 جزئیات سفارش #{{ $order->id }}</h2>
            <div>
                <a href="{{ route('dashboard.orders') }}" class="btn btn-outline-secondary btn-sm">بازگشت</a>
                @if($order->order_status === 'paid')
                    <a href="{{ route('dashboard.order-factor', $order->id) }}" class="btn btn-outline-success btn-sm" target="_blank">چاپ فاکتور</a>
                @endif
            </div>
        </div>

        <div class="row">
            <!-- اطلاعات سفارش -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header"><h5 class="mb-0">اطلاعات سفارش</h5></div>
                    <div class="card-body">
                        <table class="table table-sm mb-0">
                            <tr><td>شماره سفارش</td><td><strong>#{{ $order->id }}</strong></td></tr>
                            <tr><td>تاریخ ثبت</td><td>{{ jdate('d F Y - H:i', $order->created_at->timestamp) }}</td></tr>
                            <tr>
                                <td>وضعیت سفارش</td>
                                <td>
                                    @if($order->status)
                                        <span class="badge bg-{{ $order->status['badge'] }}">{{ $order->status['title'] }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>وضعیت ارسال</td>
                                <td>
                                    @if($order->shippingStatus)
                                        <span class="badge" style="background-color: {{ $order->shippingStatus->color }}">
                                            {{ $order->shippingStatus->title }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            <tr><td>روش ارسال</td><td>{{ $order->shippingMethod?->title ?? '—' }}</td></tr>
                            <tr><td>کد پیگیری پستی</td><td>{{ $order->post_code ?? '—' }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- آدرس و پرداخت -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header"><h5 class="mb-0">آدرس تحویل</h5></div>
                    <div class="card-body">
                        <p><strong>تحویل‌گیرنده:</strong> {{ $order->receiptor_full_name }}</p>
                        @if($order->address)
                            <p><strong>استان:</strong> {{ $order->state?->name ?? ($order->address['state'] ?? '—') }}</p>
                            <p><strong>شهر:</strong> {{ $order->city?->name ?? ($order->address['city'] ?? '—') }}</p>
                            <p><strong>آدرس:</strong> {{ $order->address['address'] ?? $order->address }}</p>
                            <p><strong>کدپستی:</strong> {{ $order->address['postal_code'] ?? '—' }}</p>
                            <p><strong>موبایل:</strong> {{ $order->address['receiptor_mobile'] ?? '—' }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- اقلام سفارش -->
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">اقلام سفارش</h5></div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>محصول</th>
                            <th>تعداد</th>
                            <th>وزن</th>
                            <th>قیمت واحد</th>
                            <th>جمع</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>
                                    {{ $item->product?->trans('name') ?? $item->product?->name ?? 'محصول حذف شده' }}
                                    @if($item->productVariant)
                                        <br><small class="text-muted">واریانت: {{ $item->productVariant->label ?? '' }}</small>
                                    @endif
                                </td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ $item->weight ? number_format($item->weight) . ' گرم' : '—' }}</td>
                                <td>{{ number_format($item->price) }} تومان</td>
                                <td>{{ number_format($item->price * $item->quantity) }} تومان</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- خلاصه مالی -->
        <div class="row justify-content-end">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header"><h5 class="mb-0">خلاصه مالی</h5></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-1">
                            <span>جمع کل:</span>
                            <span>{{ number_format($order->total_price) }} تومان</span>
                        </div>
                        @if($order->discount_price > 0)
                            <div class="d-flex justify-content-between mb-1 text-success">
                                <span>تخفیف:</span>
                                <span>-{{ number_format($order->discount_price) }} تومان</span>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between mb-1">
                            <span>هزینه ارسال:</span>
                            <span>{{ $order->shipping_price > 0 ? number_format($order->shipping_price) . ' تومان' : 'رایگان' }}</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>مبلغ پرداختی:</strong>
                            <strong class="text-primary">{{ number_format($order->payment_price) }} تومان</strong>
                        </div>
                        @if($order->bankTrackingCode)
                            <hr>
                            <small class="text-muted">کد پیگیری بانکی: {{ $order->bankTrackingCode }}</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- تاریخچه -->
        @if($order->histories->count())
            <div class="card mt-4">
                <div class="card-header"><h5 class="mb-0">📜 تاریخچه سفارش</h5></div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr><th>تاریخ</th><th>وضعیت سفارش</th><th>وضعیت ارسال</th></tr>
                        </thead>
                        <tbody>
                            @foreach($order->histories as $history)
                                <tr>
                                    <td>{{ jdate('d/m/Y H:i', $history->created_at->timestamp) }}</td>
                                    <td>{{ $history->status_name }}</td>
                                    <td>
                                        @if($history->shippingStatus)
                                            <span class="badge" style="background-color: {{ $history->shippingStatus->color }}">
                                                {{ $history->shippingStatus->title }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
