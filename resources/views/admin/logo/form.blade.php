@csrf
<div class="container-fluid">
    <div class="card-block row w-100 m-0">

        <div class="col-12 col-md-6 p-2">
            <div class="form-group">
                <label for="link">لینک</label>
                <input type="text" class="form-control bg-light rounded-custom" id="link" name="link"
                       value="{{ old('link', $item->link ?? '') }}">
            </div>
        </div>

        <div class="col-12 col-md-6 p-2">
            <div class="form-group">
                <label for="image">تصویر لوگو</label>
                @if(!isset($item)) <span style="color: red;">*</span> @endif
                <input type="file" class="form-control bg-light rounded-custom" id="image" name="image"
                       @if(!isset($item)) data-required="true" @endif>

                @if(isset($item) && $item->image)
                    <div class="mt-2">
                        <img src="{{ $item->getImageFile() }}" width="120" alt="logo"
                             style="object-fit: contain; border-radius: 5px; background: #eee; padding: 5px;">
                    </div>
                @endif
            </div>
        </div>

        <div class="col-12 col-md-6 p-2 d-flex align-items-center">
            <div class="form-group w-100">
                <div class="form-check form-switch mt-4 ps-0">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input me-2" type="checkbox" id="is_active" name="is_active" value="1"
                           {{ (old('is_active', $item->is_active ?? true)) ? 'checked' : '' }} style="float: none; margin-left: 10px;">
                    <label class="form-check-label" for="is_active">فعال باشد؟</label>
                </div>
            </div>
        </div>

        <div class="col-12 ms-auto p-2 mt-3">
            <button type="submit" class="btn btn-custom rounded-custom w-fit px-5">ذخیره</button>
        </div>
    </div>
</div>
