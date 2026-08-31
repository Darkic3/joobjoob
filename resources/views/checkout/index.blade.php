<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سبد خرید</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        <h2 class="mb-4">🛒 سبد خرید</h2>

        @if($items->isEmpty())
            <div class="alert alert-info">سبد خرید شما خالی است.</div>
            <a href="{{ route('products.index') }}" class="btn btn-primary">مشاهده محصولات</a>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>محصول</th>
                            <th>قیمت واحد</th>
                            <th>تعداد</th>
                            <th>جمع</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($item['image'])
                                            <img src="{{ asset('uploads/products/' . $item['image']) }}" alt="" width="60" height="60" class="rounded me-2"
                                                 onerror="this.onerror=null;this.src='{{ asset('assets/admin/images/image-not-found.png') }}'">
                                        @endif
                                        <div>
                                            <strong>{{ $item['name'] }}</strong>
                                            @if($item['variant_label'])
                                                <br><small class="text-muted">{{ $item['variant_label'] }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ number_format($item['price']) }} تومان</td>
                                <td>{{ $item['qty'] }}</td>
                                <td>{{ number_format($item['price'] * $item['qty']) }} تومان</td>
                                <td>
                                    <form action="{{ route('cart.remove', $item['rowId']) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">حذف</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5>خلاصه سفارش</h5>
                            <hr>
                            <div class="d-flex justify-content-between mb-2">
                                <span>جمع کل:</span>
                                <strong>{{ number_format($subtotal) }} تومان</strong>
                            </div>
                            @if($discount > 0)
                                <div class="d-flex justify-content-between mb-2 text-success">
                                    <span>تخفیف:</span>
                                    <strong>-{{ number_format($discount) }} تومان</strong>
                                </div>
                            @endif
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <span>قابل پرداخت:</span>
                                <strong class="text-primary fs-5">{{ number_format($total) }} تومان</strong>
                            </div>
                            <a href="{{ route('checkout.address') }}" class="btn btn-primary w-100">ادامه فرآیند خرید</a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
