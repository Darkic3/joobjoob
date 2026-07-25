@csrf
<div class="container-fluid">
    <div class="card-block row w-100 m-0">

        <div class="col-12 col-md-12 p-2">
            <div class="form-group">
                <label for="title">عنوان</label>
                <input type="text" class="form-control bg-light rounded-custom" id="title" name="title"
                       value="{{ old('title', $item->title ?? '') }}" data-required="true">
            </div>
        </div>

        <div class="col-12 p-2">
            <div class="form-group">
                <label for="description">توضیحات</label>
                <div class="ckeditor-div">
                    <textarea id="description" name="description" class="des form-control bg-light rounded-custom"
                              rows="5">{{ old('description', $item->description ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 p-2 d-flex align-items-center">
            <div class="form-group w-100">
                <div class="form-check form-switch mt-2 ps-0">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input me-2" type="checkbox" id="is_active" name="is_active" value="1"
                           {{ (old('is_active', $item->is_active ?? true)) ? 'checked' : '' }} style="float: none; margin-left: 10px;">
                    <label class="form-check-label" for="is_active">
                        فعال باشد؟ (نمایش در سایت)
                    </label>
                </div>
            </div>
        </div>

        <div class="col-12 ms-auto p-2 mt-3">
            <button type="submit" class="btn btn-custom rounded-custom w-fit px-5">
                ذخیره تغییرات
            </button>
        </div>
    </div>
</div>
