@csrf
<div class="container-fluid">
    <div class="card-block row w-100 m-0">

        <div class="col-md-6 col-12 p-2">
            <div class="form-group">
                <label for="title">عنوان</label>
                <span style="color: red;">*</span>
                <input type="text" class="form-control bg-light rounded-custom" id="title" name="title"
                       value="{{ old('title', $item->title ?? '') }}"
                       data-required="true">
            </div>
        </div>

        <div class="col-md-6 col-12 p-2">
            <div class="form-group">
                <label for="phone">شماره تماس</label>
                <input type="text" class="form-control bg-light rounded-custom" id="phone" name="phone"
                       value="{{ old('phone', $item->phone ?? '') }}">
            </div>
        </div>

        <div class="col-md-6 col-12 p-2">
            <div class="form-group">
                <label for="email">ایمیل</label>
                <input type="email" class="form-control bg-light rounded-custom" id="email" name="email"
                       value="{{ old('email', $item->email ?? '') }}">
            </div>
        </div>

        <div class="col-md-6 col-12 p-2 d-flex align-items-center">
            <div class="form-group w-100">
                <div class="form-check form-switch ps-0">
                    <label class="form-check-label ms-5" for="is_active">
                        فعال باشد؟
                    </label>
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input me-2" type="checkbox" id="is_active" name="is_active" value="1"
                           {{ (old('is_active', $item->is_active ?? true)) ? 'checked' : '' }} style="float: right;">
                </div>
            </div>
        </div>

        <div class="col-12 ms-auto p-2 mt-3">
            <button type="submit" class="btn btn-custom rounded-custom w-fit px-5">
                ذخیره
            </button>
        </div>
    </div>
</div>
