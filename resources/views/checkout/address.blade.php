<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>انتخاب آدرس و روش ارسال</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>
        .address-card { cursor: pointer; border: 2px solid #dee2e6; transition: all 0.2s; }
        .address-card.selected { border-color: #0d6efd; background: #f8f9ff; }
        .shipping-method { cursor: pointer; border: 2px solid #dee2e6; transition: all 0.2s; }
        .shipping-method.selected { border-color: #198754; background: #f0fff4; }
    </style>
</head>
<body>
    <div class="container py-4">
        <h2 class="mb-4">📍 انتخاب آدرس و روش ارسال</h2>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row">
            <!-- آدرس‌ها -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">آدرس‌های ذخیره شده</h5>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addAddressModal">افزودن آدرس جدید</button>
                    </div>
                    <div class="card-body">
                        @if($addresses->isEmpty())
                            <div class="text-muted">هنوز آدرسی ذخیره نکرده‌اید. لطفاً ابتدا آدرس اضافه کنید.</div>
                        @else
                            <div class="row" id="addressList">
                                @foreach($addresses as $address)
                                    <div class="col-md-6 mb-3">
                                        <div class="card address-card" data-address-id="{{ $address->id }}" onclick="selectAddress({{ $address->id }})">
                                            <div class="card-body">
                                                <h6>{{ $address->receiptor_full_name }}</h6>
                                                <p class="mb-1">{{ $address->address }}</p>
                                                <small class="text-muted">
                                                    {{ $address->state?->name }} - {{ $address->city?->name }}
                                                    <br>کدپستی: {{ $address->postal_code }}
                                                    <br>موبایل: {{ $address->receiptor_mobile }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- روش‌های ارسال -->
                <div class="card mb-4" id="shippingMethodsCard" style="display:none;">
                    <div class="card-header">
                        <h5 class="mb-0">🚀 روش ارسال</h5>
                    </div>
                    <div class="card-body">
                        <div id="shippingMethodsList"></div>
                    </div>
                </div>
            </div>

            <!-- خلاصه -->
            <div class="col-md-4">
                <div class="card sticky-top" style="top: 20px;">
                    <div class="card-body">
                        <h5>خلاصه سفارش</h5>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span>جمع کل:</span>
                            <strong>{{ number_format($cart->subtotal()) }} تومان</strong>
                        </div>
                        <div id="shippingPriceDisplay" class="d-flex justify-content-between mb-2" style="display:none !important;">
                            <span>هزینه ارسال:</span>
                            <strong id="shippingPriceText">0 تومان</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span>قابل پرداخت:</span>
                            <strong class="text-primary fs-5" id="totalPrice">{{ number_format($cart->total()) }} تومان</strong>
                        </div>
                        <button class="btn btn-primary w-100" id="continueBtn" disabled onclick="goToPayment()">ادامه و پرداخت</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- مودال افزودن آدرس -->
    <div class="modal fade" id="addAddressModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('dashboard.addresses.create') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5>افزودن آدرس جدید</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">نام تحویل‌گیرنده</label>
                            <input type="text" name="receiptor_full_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">موبایل</label>
                            <input type="text" name="receiptor_mobile" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">استان</label>
                            <select name="state_id" class="form-select" id="stateSelect" onchange="loadCities(this.value)" required>
                                <option value="">انتخاب استان</option>
                                @foreach($states as $state)
                                    <option value="{{ $state->id }}">{{ $state->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">شهر</label>
                            <select name="city_id" class="form-select" id="citySelect" required>
                                <option value="">انتخاب شهر</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">آدرس کامل</label>
                            <textarea name="address" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">کدپستی</label>
                            <input type="text" name="postal_code" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                        <button type="submit" class="btn btn-primary">ذخیره آدرس</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedAddressId = null;
        let selectedShippingMethodId = null;

        function selectAddress(addressId) {
            selectedAddressId = addressId;
            selectedShippingMethodId = null;

            document.querySelectorAll('.address-card').forEach(c => c.classList.remove('selected'));
            document.querySelector(`[data-address-id="${addressId}"]`).classList.add('selected');

            // دریافت روش‌های ارسال
            fetch('{{ route("checkout.shipments") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ address_id: addressId })
            })
            .then(r => r.json())
            .then(data => {
                let html = '';
                data.shipping_methods.forEach(method => {
                    let priceText = method.price === 'dynamic' ? 'قیمت لحظه‌ای' : (method.price > 0 ? numberFormat(method.price) + ' تومان' : 'رایگان');
                    html += `
                        <div class="card shipping-method mb-2" data-method-id="${method.id}" data-price="${method.price}" onclick="selectShipping(${method.id}, ${method.price === 'dynamic' ? 0 : method.price})">
                            <div class="card-body d-flex justify-content-between">
                                <div>
                                    <strong>${method.title}</strong>
                                    <small class="text-muted ms-2">${method.type === 'with_weight' ? '(بر اساس وزن)' : method.type === 'fixed_price' ? '(قیمت ثابت)' : ''}</small>
                                </div>
                                <div><strong>${priceText}</strong></div>
                            </div>
                        </div>`;
                });

                document.getElementById('shippingMethodsList').innerHTML = html;
                document.getElementById('shippingMethodsCard').style.display = 'block';
            });
        }

        function selectShipping(methodId, price) {
            selectedShippingMethodId = methodId;

            document.querySelectorAll('.shipping-method').forEach(c => c.classList.remove('selected'));
            document.querySelector(`[data-method-id="${methodId}"]`).classList.add('selected');

            // ذخیره در سرور
            fetch('{{ route("checkout.set-shipment") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ shipping_method_id: methodId, shipping_price: price })
            });

            let shippingPrice = parseInt(price) || 0;
            let subtotal = {{ $cart->subtotal() }};
            let total = subtotal + shippingPrice;

            document.getElementById('shippingPriceDisplay').style.display = 'flex';
            document.getElementById('shippingPriceText').textContent = numberFormat(shippingPrice) + ' تومان';
            document.getElementById('totalPrice').textContent = numberFormat(total) + ' تومان';
            document.getElementById('continueBtn').disabled = false;
        }

        function goToPayment() {
            if (selectedAddressId && selectedShippingMethodId) {
                window.location.href = '{{ route("checkout.payment") }}';
            }
        }

        function loadCities(stateId) {
            fetch('{{ route("checkout.cities") }}?state_id=' + stateId)
                .then(r => r.json())
                .then(data => {
                    let select = document.getElementById('citySelect');
                    select.innerHTML = '<option value="">انتخاب شهر</option>';
                    data.cities.forEach(city => {
                        select.innerHTML += `<option value="${city.id}">${city.name}</option>`;
                    });
                });
        }

        function numberFormat(num) {
            return new Intl.NumberFormat('fa-IR').format(num);
        }
    </script>
</body>
</html>
