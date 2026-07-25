<div class="container-fluid">
    <div class="card-block row w-100 m-0">
        <div class="col-md-12 p-2">
            <div class="form-group">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="not_active" name="not_active" value="1"
                        {{ old('not_active', $branch->not_active ?? '') ? 'checked' : '' }}>
                    <label class="custom-control-label" for="not_active">این استان شعبه ندارد (اتصال به شعبه دیگر)</label>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-12 p-2">
            <div class="form-group">
                <label for="state_location_id">استان</label>
                <span style="color: red;">*</span>
                <select class="form-control bg-light rounded-custom" id="state_location_id" name="state_location_id">
                    <option value="">-- انتخاب استان --</option>
                    @foreach ($states as $state)
                        <option value="{{ $state->id }}" {{ old('state_location_id', $branch->state_location_id ?? '') == $state->id ? 'selected' : '' }}>
                            {{$state->name}}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div id="alternate_branch_section" class="col-md-6 col-12 p-2" style="display: none;">
            <div class="form-group">
                <label for="alternate_branch_id">شعبه جایگزین</label>
                <span style="color: red;">*</span>
                <select class="form-control bg-light rounded-custom" id="alternate_branch_id" name="alternate_branch_id">
                    <option value="">-- انتخاب شعبه فعال --</option>
                    @foreach ($activeBranches as $activeBranch)
                        <option value="{{ $activeBranch->id }}" {{ old('alternate_branch_id', $branch->alternate_branch_id ?? '') == $activeBranch->id ? 'selected' : '' }}>
                            {{ $activeBranch->title }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div id="main_info_fields" class="row w-100 m-0">
            <div class="col-md-6 col-12 p-2">
                <div class="form-group">
                    <label for="title">عنوان شعبه</label> <span style="color: red;">*</span>
                    <input type="text" class="form-control bg-light rounded-custom" id="title" name="title" value="{{ old('title', $branch->title ?? '') }}">
                </div>
            </div>

            <div class="col-md-6 col-12 p-2">
                <div class="form-group">
                    <label for="manager">نام مدیر</label> <span style="color: red;">*</span>
                    <input type="text" class="form-control bg-light rounded-custom" id="manager" name="manager" value="{{ old('manager', $branch->manager ?? '') }}">
                </div>
            </div>

            <div class="col-12 p-2">
                <div class="form-group">
                    <label for="address">آدرس</label>
                    <textarea id="address" name="address" class="form-control bg-light rounded-custom" rows="2">{{ old('address', $branch->address ?? '') }}</textarea>
                </div>
            </div>

            <div class="col-md-3 col-6 p-2">
                <div class="form-group">
                    <label for="postal_code">کد پستی</label>
                    <input type="text" class="form-control bg-light rounded-custom" id="postal_code" name="postal_code"
                           value="{{ old('postal_code', $branch->postal_code ?? '') }}">
                </div>
            </div>

            <div class="col-md-3 col-6 p-2">
                <div class="form-group">
                    <label for="phone_one">تلفن 1</label>
                    <input type="text" class="form-control bg-light rounded-custom" id="phone_one" name="phone_one"
                           value="{{ old('phone_one', $branch->phone_one ?? '') }}">
                </div>
            </div>

            <div class="col-md-3 col-6 p-2">
                <div class="form-group">
                    <label for="phone_two">تلفن 2</label>
                    <input type="text" class="form-control bg-light rounded-custom" id="phone_two" name="phone_two"
                           value="{{ old('phone_two', $branch->phone_two ?? '') }}">
                </div>
            </div>
            <div class="col-md-3 col-6 p-2">
                <div class="form-group">
                    <label for="phone_three">تلفن 3</label>
                    <input type="text" class="form-control bg-light rounded-custom" id="phone_three" name="phone_three"
                           value="{{ old('phone_three', $branch->phone_three ?? '') }}">
                </div>
            </div>


            <div class="col-md-3 col-6 p-2">
                <div class="form-group">
                    <label for="email">ایمیل</label>
                    <input type="text" class="form-control bg-light rounded-custom" id="email" name="email"
                           value="{{ old('email', $branch->email ?? '') }}">
                </div>
            </div>


            <div class="col-md-3 col-6 p-2">
                <div class="form-group">
                    <label for="mobile">موبایل</label>
                    <input type="text" class="form-control bg-light rounded-custom" id="mobile" name="mobile" value="{{ old('mobile', $branch->mobile ?? '') }}">
                </div>
            </div>
        </div>

        <div class="col-12 ms-auto p-2">
            <button type="submit" class="btn btn-custom rounded-custom w-fit px-5">ذخیره</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const notActiveCheckbox = document.getElementById('not_active');
        const alternateSection = document.getElementById('alternate_branch_section');
        const mainInfoFields = document.getElementById('main_info_fields');

        function toggleFields() {
            if (notActiveCheckbox.checked) {
                alternateSection.style.display = 'block';
                mainInfoFields.style.display = 'none';
            } else {
                alternateSection.style.display = 'none';
                mainInfoFields.style.display = 'flex';
            }
        }

        notActiveCheckbox.addEventListener('change', toggleFields);
        toggleFields(); // برای زمان لود (Edit)
    });
</script>
