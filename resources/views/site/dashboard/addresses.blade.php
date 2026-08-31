<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>آدرس‌های من</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>📍 آدرس‌های من</h2>
            <div>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">+ افزودن آدرس جدید</button>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">بازگشت</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($addresses->isEmpty())
            <div class="alert alert-info">هنوز آدرسی ذخیره نکرده‌اید.</div>
        @else
            <div class="row">
                @foreach($addresses as $address)
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5>{{ $address->receiptor_full_name }}</h5>
                                <p class="mb-1">{{ $address->address }}</p>
                                <p class="mb-1"><small class="text-muted">{{ $address->state?->name }} - {{ $address->city?->name }}</small></p>
                                <p class="mb-1"><small class="text-muted">کدپستی: {{ $address->postal_code }} | موبایل: {{ $address->receiptor_mobile }}</small></p>
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-outline-warning" onclick="editAddress({{ $address }})">ویرایش</button>
                                    <form action="{{ route('dashboard.addresses.delete', $address->id) }}" method="POST" class="d-inline" onsubmit="return confirm('آیا مطمئن هستید؟')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">حذف</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- مودال افزودن -->
    <div class="modal fade" id="addModal" tabindex="-1">
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
                            <select name="state_id" class="form-select" id="addStateSelect" onchange="loadCities(this.value, 'addCitySelect')" required>
                                <option value="">انتخاب استان</option>
                                @foreach($states as $state)
                                    <option value="{{ $state->id }}">{{ $state->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">شهر</label>
                            <select name="city_id" class="form-select" id="addCitySelect" required>
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
                        <button type="submit" class="btn btn-primary">ذخیره</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- مودال ویرایش -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editForm" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-header">
                        <h5>ویرایش آدرس</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">نام تحویل‌گیرنده</label>
                            <input type="text" name="receiptor_full_name" id="editName" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">موبایل</label>
                            <input type="text" name="receiptor_mobile" id="editMobile" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">استان</label>
                            <select name="state_id" class="form-select" id="editStateSelect" onchange="loadCities(this.value, 'editCitySelect')" required>
                                <option value="">انتخاب استان</option>
                                @foreach($states as $state)
                                    <option value="{{ $state->id }}">{{ $state->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">شهر</label>
                            <select name="city_id" class="form-select" id="editCitySelect" required>
                                <option value="">انتخاب شهر</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">آدرس کامل</label>
                            <textarea name="address" id="editAddress" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">کدپستی</label>
                            <input type="text" name="postal_code" id="editPostalCode" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                        <button type="submit" class="btn btn-primary">بروزرسانی</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function loadCities(stateId, targetId) {
            fetch('{{ route("dashboard.addresses.cities") }}?state_id=' + stateId)
                .then(r => r.json())
                .then(data => {
                    let select = document.getElementById(targetId);
                    select.innerHTML = '<option value="">انتخاب شهر</option>';
                    data.cities.forEach(city => {
                        select.innerHTML += `<option value="${city.id}">${city.name}</option>`;
                    });
                });
        }

        function editAddress(address) {
            document.getElementById('editForm').action = '/account/addresses/' + address.id;
            document.getElementById('editName').value = address.receiptor_full_name;
            document.getElementById('editMobile').value = address.receiptor_mobile;
            document.getElementById('editStateSelect').value = address.state_id;
            document.getElementById('editAddress').value = address.address;
            document.getElementById('editPostalCode').value = address.postal_code;

            loadCities(address.state_id, 'editCitySelect');
            setTimeout(() => {
                document.getElementById('editCitySelect').value = address.city_id;
            }, 300);

            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
    </script>
</body>
</html>
