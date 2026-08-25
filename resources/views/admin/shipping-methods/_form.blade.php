{{-- فرم مشترک ایجاد/ویرایش روش ارسال --}}

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    {{-- اطلاعات پایه --}}
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">اطلاعات پایه</h5></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">عنوان <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control"
                               value="{{ old('title', $method->title ?? '') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">نوع ارسال <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="fixed_price" {{ old('type', $method->type ?? '') === 'fixed_price' ? 'selected' : '' }}>قیمت ثابت</option>
                            <option value="with_weight" {{ old('type', $method->type ?? '') === 'with_weight' ? 'selected' : '' }}>بر اساس وزن</option>
                            <option value="chapar" {{ old('type', $method->type ?? '') === 'chapar' ? 'selected' : '' }}>چاپار</option>
                            <option value="cod24" {{ old('type', $method->type ?? '') === 'cod24' ? 'selected' : '' }}>Cod24</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">هزینه ارسال (تومان)</label>
                        <input type="number" name="price" class="form-control" min="0"
                               value="{{ old('price', $method->price ?? '') }}">
                        <small class="text-muted">برای وزنی: هزینه هر ۱۰۰ گرم</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">سقف ارسال رایگان (تومان)</label>
                        <input type="number" name="price_ceiling" class="form-control" min="0"
                               value="{{ old('price_ceiling', $method->price_ceiling ?? 0) }}">
                        <small class="text-muted">۰ = غیرفعال</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">نوع سرویس چاپار</label>
                        <input type="text" name="chapar_type" class="form-control"
                               value="{{ old('chapar_type', $method->chapar_type ?? '') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">توضیحات</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description', $method->description ?? '') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- زمان ارسال --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">⏱️ زمان تخمینی ارسال</h5>
                <div class="form-check form-switch mb-0">
                    <input type="hidden" name="show_estimated_days" value="0">
                    <input type="checkbox" name="show_estimated_days" class="form-check-input" role="switch"
                           value="1" {{ old('show_estimated_days', $method->show_estimated_days ?? 1) ? 'checked' : '' }}
                           onchange="document.getElementById('estimatedDaysFields').style.display = this.checked ? '' : 'none'">
                    <label class="form-check-label">نمایش در سایت</label>
                </div>
            </div>
            <div class="card-body" id="estimatedDaysFields" style="display: {{ old('show_estimated_days', $method->show_estimated_days ?? 1) ? '' : 'none' }};">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">حداقل زمان ارسال (روز کاری) <span class="text-danger">*</span></label>
                        <input type="number" name="estimated_days_min" class="form-control"
                               min="1" max="30"
                               value="{{ old('estimated_days_min', $method->estimated_days_min ?? 1) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">حداکثر زمان ارسال (روز کاری) <span class="text-danger">*</span></label>
                        <input type="number" name="estimated_days_max" class="form-control"
                               min="1" max="30"
                               value="{{ old('estimated_days_max', $method->estimated_days_max ?? 3) }}">
                    </div>
                    <div class="col-12">
                        <div class="alert alert-info mb-0">
                            <strong>پیش‌نمایش:</strong>
                            زمان تخمینی ارسال: <strong>{{ old('estimated_days_min', $method->estimated_days_min ?? 1) }} تا {{ old('estimated_days_max', $method->estimated_days_max ?? 3) }} روز کاری</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- اطلاعات فرستنده --}}
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">👤 اطلاعات فرستنده</h5></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">نام فرستنده</label>
                        <input type="text" name="sender_name" class="form-control"
                               value="{{ old('sender_name', $method->sender_name ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">شرکت فرستنده</label>
                        <input type="text" name="sender_company" class="form-control"
                               value="{{ old('sender_company', $method->sender_company ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">تلفن فرستنده</label>
                        <input type="text" name="sender_phone" class="form-control" dir="ltr"
                               value="{{ old('sender_phone', $method->sender_phone ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">موبایل فرستنده</label>
                        <input type="text" name="sender_mobile" class="form-control" dir="ltr"
                               value="{{ old('sender_mobile', $method->sender_mobile ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">ایمیل فرستنده</label>
                        <input type="email" name="sender_email" class="form-control" dir="ltr"
                               value="{{ old('sender_email', $method->sender_email ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">شهر فرستنده</label>
                        <select name="sender_city_id" class="form-select">
                            <option value="">انتخاب شهر</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ old('sender_city_id', $method->sender_city_id ?? '') == $city->id ? 'selected' : '' }}>
                                    {{ $city->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">آدرس فرستنده</label>
                        <textarea name="sender_address" class="form-control" rows="2">{{ old('sender_address', $method->sender_address ?? '') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">کدپستی فرستنده</label>
                        <input type="text" name="sender_postal_code" class="form-control" dir="ltr"
                               value="{{ old('sender_postal_code', $method->sender_postal_code ?? '') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- سایدبار --}}
    <div class="col-md-4">
        {{-- وضعیت --}}
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">وضعیت</h5></div>
            <div class="card-body">
                <div class="form-check form-switch">
                    <input type="hidden" name="status" value="0">
                    <input type="checkbox" name="status" class="form-check-input" role="switch"
                           value="1" {{ old('status', $method->status ?? 0) ? 'checked' : '' }}>
                    <label class="form-check-label">فعال</label>
                </div>
                <div class="form-check mt-2">
                    <input type="hidden" name="freight_balance" value="0">
                    <input type="checkbox" name="freight_balance" class="form-check-input"
                           value="1" {{ old('freight_balance', $method->freight_balance ?? 0) ? 'checked' : '' }}>
                    <label class="form-check-label">پس کرایه (ارسال رایگان)</label>
                </div>
            </div>
        </div>

        {{-- شهرهای مقصد --}}
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">🏙️ شهرهای مقصد</h5></div>
            <div class="card-body">
                <small class="text-muted d-block mb-2">شهرهایی که این روش ارسال برای آن‌ها فعال است:</small>
                <div style="max-height: 300px; overflow-y: auto;">
                    @foreach($cities as $city)
                        <div class="form-check">
                            <input type="checkbox" name="cities[]" class="form-check-input"
                                   value="{{ $city->id }}"
                                   {{ in_array($city->id, $selectedCityIds ?? []) ? 'checked' : '' }}>
                            <label class="form-check-label">{{ $city->name }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- دکمه ذخیره --}}
        <div class="card">
            <div class="card-body">
                <button type="submit" class="btn btn-primary w-100">💾 ذخیره</button>
                <a href="{{ route('admin.shipping-methods.index') }}" class="btn btn-secondary w-100 mt-2">انصراف</a>
            </div>
        </div>
    </div>
</div>
