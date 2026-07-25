@csrf
<div class="container-fluid">
    <div class="card-block row w-100 m-0">

        <div class="col-md-6 col-12 p-2">
            <div class="form-group">
                <label for="name">نام</label>
                <span style="color: red;">*</span>
                <input type="text" class="form-control bg-light rounded-custom" id="name" name="name"
                       value="{{ old('name', $item->name) }}"
                       data-required="true">
            </div>
        </div>

        <div class="col-md-6 col-12 p-2">
            <div class="form-group">
                <label for="family">نام خانوادگی</label>
                <input type="text" class="form-control bg-light rounded-custom" id="family" name="family"
                       value="{{ old('family', $item->family) }}">
            </div>
        </div>

        <div class="col-md-6 col-12 p-2">
            <div class="form-group">
                <label for="email">ایمیل</label><span style="color: red;">*</span>
                <input type="email" class="form-control bg-light rounded-custom" id="email" name="email"
                       value="{{ old('email', $item->email) }}"
                       data-required="true">
            </div>
        </div>

        <div class="col-md-6 col-12 p-2">
            <div class="form-group">
                <label for="phone">موبایل</label>
                <input type="text" class="form-control bg-light rounded-custom" id="phone" name="phone"
                       value="{{ old('phone', $item->phone) }}">
            </div>
        </div>

        <div class="col-md-6 col-12 p-2">
            <div class="form-group">
                <label for="password">رمز عبور</label>
                @if(!$item->exists)
                <span style="color: red;">*</span>
                @else
                    <small>(در صورت عدم تغییر، خالی بگذارید)</small>
                @endif
                <input type="password" class="form-control bg-light rounded-custom" id="password" name="password"
                       @if(!$item->exists) data-required="true" @endif
                >
            </div>
        </div>

        <div class="col-md-6 col-12 p-2">
            <div class="form-group">
                <label for="password_confirmation">تکرار رمز عبور</label>
                @if(!$item->exists)
                    <span style="color: red;">*</span>
                @endif
                <input type="password" class="form-control bg-light rounded-custom" id="password_confirmation"
                       name="password_confirmation"
                       @if(!$item->exists) data-required="true" @endif
                >
            </div>
        </div>

        <div class="col-md-3 col-6 p-2">
            <div class="form-group">
                <div class="form-check form-switch">
                    <input type="hidden" name="is_admin" value="0">
                    <input class="form-check-input" type="checkbox" id="is_admin" name="is_admin" value="1"
                        {{ (old('is_admin', $item->is_admin)) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_admin">
                        ادمین باشد؟
                    </label>
                </div>
            </div>
        </div>

        <div class="col-md-9 col-12 p-2">
            <div class="form-group">
                <label for="roles">نقش‌ها</label><span style="color: red;">*</span>

                <select name="roles[]" id="roles" class="form-control bg-light rounded-custom" multiple style="height: 150px;">
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}"
                            {{ in_array($role->id, $selectedRoles) ? 'selected' : '' }}>
                            {{ $role->role }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">برای انتخاب چندتایی، کلید Ctrl (یا Cmd در مک) را نگه دارید.</small>
            </div>
        </div>
        <div class="col-12 ms-auto p-2">
            <button type="submit" class="btn btn-custom rounded-custom w-fit px-5">
                ذخیره
            </button>
        </div>
    </div>
</div>
