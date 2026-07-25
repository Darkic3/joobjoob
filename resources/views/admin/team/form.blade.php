@csrf
<div class="container-fluid">
    <div class="card-block row w-100 m-0">

        <div class="col-md-6 col-12 p-2">
            <div class="form-group">
                <label for="full_name">نام و نام خانوادگی</label>
                <span style="color: red;">*</span>
                <input type="text" class="form-control bg-light rounded-custom" id="full_name" name="full_name"
                       value="{{ old('full_name', $item->full_name ?? '') }}"
                       data-required="true">
            </div>
        </div>

        <div class="col-md-6 col-12 p-2">
            <div class="form-group">
                <label for="position">سمت / عنوان شغلی</label>
                <span style="color: red;">*</span>
                <input type="text" class="form-control bg-light rounded-custom" id="position" name="position"
                       value="{{ old('position', $item->position ?? '') }}"
                       data-required="true">
            </div>
        </div>

        <div class="col-md-6 col-12 p-2">
            <div class="form-group">
                <label for="image">تصویر پرسنلی</label>
                <input type="file" class="form-control bg-light rounded-custom" id="image" name="image">

                @if(isset($item) && $item->image)
                    <div class="mt-2">
                        <img src="{{ $item->getImageFile() }}" width="400" alt="{{ $item->full_name }}"
                             style="object-fit: cover; border-radius: 5px;">
                    </div>
                @endif
            </div>
        </div>

        <div class="col-md-6 col-12 p-2 d-flex align-items-center">
            <div class="form-group mt-4">
                <div class="form-check form-switch">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                        {{ (old('is_active', $item->is_active ?? true)) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        فعال باشد؟
                    </label>
                </div>
            </div>
        </div>

         @include('admin.image-croper.modal-croper', [
            'inputId' => 'image',
            'aspectWidth' => 750,
            'aspectHeight' => 500,
            'outputWidth' => 750,
            'outputHeight' => 500
        ])

        <div class="col-12 ms-auto p-2">
            <button type="submit" class="btn btn-custom rounded-custom w-fit px-5">
                ذخیره
            </button>
        </div>
    </div>
</div>
