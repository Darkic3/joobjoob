<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پرداخت موفق</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <div class="mb-4" style="font-size: 80px;">✅</div>
                <h2 class="text-success mb-3">پرداخت شما با موفقیت انجام شد!</h2>
                <p class="text-muted mb-4">
                    شماره سفارش شما: <strong>#{{ $order->id }}</strong>
                    <br>
                    تاریخ: {{ jdate('d F Y - H:i', $order->created_at->timestamp) }}
                </p>

                <div class="card mb-4">
                    <div class="card-body text-start">
                        <h6>جزئیات سفارش:</h6>
                        <ul class="list-unstyled mb-0">
                            <li>مبلغ کل: <strong>{{ number_format($order->payment_price) }} تومان</strong></li>
                            <li>هزینه ارسال: <strong>{{ number_format($order->shipping_price) }} تومان</strong></li>
                            <li>وضعیت: <span class="badge bg-success">پرداخت شده</span></li>
                        </ul>
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-center">
                    <a href="{{ route('dashboard.order-detail', $order->id) }}" class="btn btn-outline-primary">مشاهده جزئیات سفارش</a>
                    <a href="{{ route('home') }}" class="btn btn-primary">بازگشت به صفحه اصلی</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
