<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پرداخت</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        <h2 class="mb-4">💳 تأیید و پرداخت</h2>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row">
            <!-- خلاصه سفارش -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">📦 اقلام سفارش</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <thead>
                                <tr><th>محصول</th><th>تعداد</th><th>قیمت</th><th>جمع</th></tr>
                            </thead>
                            <tbody>
                                @foreach($items as $item)
                                    <tr>
                                        <td>{{ $item['name'] }}</td>
                                        <td>{{ $item['qty'] }}</td>
                                        <td>{{ number_format($item['price']) }}</td>
                                        <td>{{ number_format($item['price'] * $item['qty']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- کد تخفیف -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="input-group">
                            <input type="text" id="discountCode" class="form-control" placeholder="کد تخفیف را وارد کنید">
                            <button class="btn btn-outline-success" onclick="applyDiscount()">اعمال</button>
                        </div>
                        <div id="discountMessage" class="mt-2"></div>
                    </div>
                </div>

                <!-- درگاه پرداخت -->
                <form action="{{ route('checkout.create') }}" method="POST" id="paymentForm">
                    @csrf
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">🏦 انتخاب درگاه پرداخت</h5>
                        </div>
                        <div class="card-body">
                            @forelse($banks as $bank)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="bank_id" id="bank_{{ $bank->id }}" value="{{ $bank->id }}" required>
                                    <label class="form-check-label" for="bank_{{ $bank->id }}">
                                        @if($bank->icon)
                                            <img src="{{ $bank->item_image }}" alt="{{ $bank->title }}" class="w-10 h-7 object-contain me-2">
                                        @endif
                                        {{ $bank->title }}
                                    </label>
                                </div>
                            @empty
                                <div class="alert alert-warning">هیچ درگاه پرداختی فعال نیست.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- توضیحات -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <label class="form-label">توضیحات سفارش (اختیاری)</label>
                            <textarea name="user_description" class="form-control" rows="3" placeholder="مثلاً: ساعت ارسال، توضیحات خاص و ..."></textarea>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100" id="payBtn">💰 پرداخت {{ number_format($total) }} تومان</button>
                </form>
            </div>

            <!-- خلاصه قیمت -->
            <div class="col-md-4">
                <div class="card sticky-top" style="top: 20px;">
                    <div class="card-body">
                        <h5>💸 خلاصه پرداخت</h5>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span>جمع کل اقلام:</span>
                            <span>{{ number_format($subtotal) }} تومان</span>
                        </div>
                        @if($discount > 0)
                            <div class="d-flex justify-content-between mb-2 text-success">
                                <span>تخفیف:</span>
                                <span>-{{ number_format($discount) }} تومان</span>
                            </div>
                        @endif
                        @if($shipping_price > 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span>هزینه ارسال:</span>
                                <span>{{ number_format($shipping_price) }} تومان</span>
                            </div>
                        @else
                            <div class="d-flex justify-content-between mb-2 text-success">
                                <span>ارسال:</span>
                                <span>رایگان</span>
                            </div>
                        @endif
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>مبلغ قابل پرداخت:</strong>
                            <strong class="text-primary fs-4">{{ number_format($total) }} تومان</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function applyDiscount() {
            let code = document.getElementById('discountCode').value;
            if (!code) return;

            fetch('{{ route("checkout.apply-discount") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ discount_code: code })
            })
            .then(r => r.json())
            .then(data => {
                let msgEl = document.getElementById('discountMessage');
                msgEl.className = data.success ? 'alert alert-success mt-2' : 'alert alert-danger mt-2';
                msgEl.textContent = data.message;
                if (data.success) location.reload();
            });
        }
    </script>
</body>
</html>
