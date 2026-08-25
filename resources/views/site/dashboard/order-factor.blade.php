<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فاکتور سفارش #{{ $order->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>
        @media print {
            body { font-size: 12px; }
            .no-print { display: none !important; }
            .card { border: 1px solid #000 !important; }
        }
        .factor-header { border-bottom: 3px double #333; padding-bottom: 15px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="no-print text-center mb-3">
            <button onclick="window.print()" class="btn btn-primary btn-sm">🖨️ چاپ فاکتور</button>
            <button onclick="window.close()" class="btn btn-secondary btn-sm">بستن</button>
        </div>

        <div class="card">
            <div class="card-body">
                <!-- هدر -->
                <div class="factor-header text-center">
                    <h3>فاکتور خرید</h3>
                    <p class="mb-0">شماره سفارش: #{{ $order->id }} | تاریخ: {{ jdate('d F Y', $order->created_at->timestamp) }}</p>
                </div>

                <div class="row mb-4">
                    <div class="col-6">
                        <h6>فروشنده:</h6>
                        <p class="mb-0">{{ config('app.name', 'فروشگاه') }}</p>
                    </div>
                    <div class="col-6">
                        <h6>تحویل‌گیرنده:</h6>
                        <p class="mb-0">{{ $order->receiptor_full_name }}</p>
                        @if($order->address)
                            <small>{{ $order->address['address'] ?? '' }}</small><br>
                            <small>{{ $order->address['state'] ?? '' }} - {{ $order->address['city'] ?? '' }}</small>
                        @endif
                    </div>
                </div>

                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>محصول</th>
                            <th>تعداد</th>
                            <th>قیمت واحد</th>
                            <th>جمع</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->product?->trans('name') ?? $item->product?->name ?? '—' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->price) }}</td>
                                <td>{{ number_format($item->price * $item->quantity) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="row justify-content-end">
                    <div class="col-4">
                        <table class="table table-sm">
                            <tr><td>جمع کل:</td><td class="text-end">{{ number_format($order->total_price) }} تومان</td></tr>
                            @if($order->discount_price > 0)
                                <tr class="text-success"><td>تخفیف:</td><td class="text-end">-{{ number_format($order->discount_price) }} تومان</td></tr>
                            @endif
                            <tr><td>ارسال:</td><td class="text-end">{{ $order->shipping_price > 0 ? number_format($order->shipping_price) . ' تومان' : 'رایگان' }}</td></tr>
                            <tr class="table-dark"><td><strong>مبلغ نهایی:</strong></td><td class="text-end"><strong>{{ number_format($order->payment_price) }} تومان</strong></td></tr>
                        </table>
                    </div>
                </div>

                @if($order->post_code)
                    <div class="mt-3 p-3 bg-light rounded">
                        <strong>کد پیگیری پستی:</strong> {{ $order->post_code }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
