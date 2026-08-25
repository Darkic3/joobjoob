<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پرداخت ناموفق</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <div class="mb-4" style="font-size: 80px;">❌</div>
                <h2 class="text-danger mb-3">پرداخت ناموفق بود</h2>

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <p class="text-muted mb-4">
                    پرداخت شما تکمیل نشد. لطفاً مجدداً تلاش کنید.
                </p>

                <div class="d-flex gap-2 justify-content-center">
                    <a href="{{ route('checkout.index') }}" class="btn btn-primary">بازگشت به سبد خرید</a>
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary">بازگشت به صفحه اصلی</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
