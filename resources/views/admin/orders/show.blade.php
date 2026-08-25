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
            <h2>📦 سفارش #{{ $order->id }}</h2>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm">بازگشت</a>
        </div>

        <div class="row">
            <!-- اطلاعات سفارش -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header"><h5 class="mb-0">اطلاعات سفارش</h5></div>
                    <div class="card-body">
                        <table class="table table-sm mb-0">
                            <tr><td>کاربر</td><td>{{ $order->user?->name }} ({{ $order->user?->mobile }})</td></tr>
                            <tr><td>تاریخ</td><td>{{ jdate('d F Y - H:i', $order->created_at->timestamp) }}</td></tr>
                            <tr>
                                <td>وضعیت سفارش</td>
                                <td>
                                    <form action="{{ route('admin.orders.change-order-status', $order->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <select name="order_status" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                                            <option value="paying" {{ $order->order_status === 'paying' ? 'selected' : '' }}>در حال پرداخت</option>
                                            <option value="paid" {{ $order->order_status === 'paid' ? 'selected' : '' }}>پرداخت شده</option>
                                            <option value="deposit_paid" {{ $order->order_status === 'deposit_paid' ? 'selected' : '' }}>بیعانه</option>
                                            <option value="unpaid" {{ $order->order_status === 'unpaid' ? 'selected' : '' }}>پرداخت نشده</option>
                                            <option value="cancelled" {{ $order->order_status === 'cancelled' ? 'selected' : '' }}>لغو شده</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                            <tr>
                                <td>وضعیت ارسال</td>
                                <td>
                                    <form action="{{ route('admin.orders.change-shipping-status', $order->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <select name="shipping_status_id" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                                            @foreach($shippingStatuses as $status)
                                                <option value="{{ $status->id }}" {{ $order->shipping_status_id == $status->id ? 'selected' : '' }}>
                                                    {{ $status->title }} {{ $status->for_send ? '✓' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                            </tr>
                            <tr><td>روش ارسال</td><td>{{ $order->shippingMethod?->title ?? '—' }}</td></tr>
                            <tr><td>درگاه پرداخت</td><td>{{ $order->bank?->title ?? '—' }}</td></tr>
                            <tr><td>کد پیگیری پستی</td><td>{{ $order->post_code ?? '—' }}</td></tr>
                        </table>

                        <!-- ثبت کد پستی -->
                        <form action="{{ route('admin.orders.post-code') }}" method="POST" class="mt-3">
                            @csrf
                            <input type="hidden" name="order_id" value="{{ $order->id }}">
                            <div class="input-group input-group-sm">
                                <input type="text" name="post_code" class="form-control" placeholder="کد پیگیری پستی" value="{{ $order->post_code }}">
                                <button class="btn btn-outline-primary">ثبت</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- آدرس -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header"><h5 class="mb-0">آدرس تحویل</h5></div>
                    <div class="card-body">
                        <p><strong>نام:</strong> {{ $order->receiptor_full_name }}</p>
                        @if($order->address)
                            <p><strong>استان:</strong> {{ $order->address['state'] ?? '—' }}</p>
                            <p><strong>شهر:</strong> {{ $order->address['city'] ?? '—' }}</p>
                            <p><strong>آدرس:</strong> {{ $order->address['address'] ?? '—' }}</p>
                            <p><strong>کدپستی:</strong> {{ $order->address['postal_code'] ?? '—' }}</p>
                            <p><strong>موبایل:</strong> {{ $order->address['receiptor_mobile'] ?? '—' }}</p>
                        @endif
                        @if($order->user_description)
                            <hr>
                            <p><strong>توضیحات کاربر:</strong> {{ $order->user_description }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- اقلام -->
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">اقلام سفارش</h5></div>
            <div class="card-body">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr><th>#</th><th>محصول</th><th>واریانت</th><th>تعداد</th><th>وزن</th><th>قیمت</th><th>جمع</th></tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->product?->trans('name') ?? $item->product?->name ?? '—' }}</td>
                                <td>{{ $item->productVariant?->label ?? '—' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ $item->weight ? number_format($item->weight) . 'g' : '—' }}</td>
                                <td>{{ number_format($item->price) }}</td>
                                <td>{{ number_format($item->price * $item->quantity) }}</td>
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
                        <div class="d-flex justify-content-between mb-1"><span>جمع کل:</span><span>{{ number_format($order->total_price) }} تومان</span></div>
                        @if($order->discount_price > 0)
                            <div class="d-flex justify-content-between mb-1 text-success"><span>تخفیف:</span><span>-{{ number_format($order->discount_price) }} تومان</span></div>
                        @endif
                        <div class="d-flex justify-content-between mb-1"><span>ارسال:</span><span>{{ $order->shipping_price > 0 ? number_format($order->shipping_price) : 'رایگان' }} تومان</span></div>
                        <hr>
                        <div class="d-flex justify-content-between"><strong>نهایی:</strong><strong class="text-primary">{{ number_format($order->payment_price) }} تومان</strong></div>
                        @if($order->bankTrackingCode)
                            <hr><small class="text-muted">کد پیگیری: {{ $order->bankTrackingCode }}</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- تاریخچه -->
        @if($order->histories->count())
            <div class="card mt-4">
                <div class="card-header"><h5 class="mb-0">📜 تاریخچه</h5></div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead><tr><th>تاریخ</th><th>وضعیت سفارش</th><th>وضعیت ارسال</th></tr></thead>
                        <tbody>
                            @foreach($order->histories as $h)
                                <tr>
                                    <td>{{ jdate('d/m/Y H:i', $h->created_at->timestamp) }}</td>
                                    <td>{{ $h->status_name }}</td>
                                    <td>
                                        @if($h->shippingStatus)
                                            <span class="badge" style="background-color:{{ $h->shippingStatus->color }}">{{ $h->shippingStatus->title }}</span>
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
