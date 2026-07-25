@csrf
<div class="container-fluid">
    <div class="card-block row w-100 m-0">

        <div class="col-12 col-md-6 p-2">
            <div class="form-group">
                <label for="title">عنوان</label>
                <input type="text" class="form-control bg-light rounded-custom" id="title" name="title"
                       value="{{ old('title', $item->title ?? '') }}"
                       data-required="true">
            </div>
        </div>

{{--        <div class="col-12 col-md-6 p-2">--}}
{{--            <div class="form-group">--}}
{{--                <label for="link">لینک</label>--}}
{{--                <input type="text" class="form-control bg-light rounded-custom" id="link" name="link"--}}
{{--                       value="{{ old('link', $item->link ?? '') }}">--}}
{{--            </div>--}}
{{--        </div>--}}

        <div class="col-12 p-2">
            <div class="form-group">
                <label for="description">توضیحات</label>
                <div class="ckeditor-div">
                    <textarea id="description" name="description" class="des form-control bg-light rounded-custom"
                              rows="3">{{ old('description', $item->description ?? '') }}</textarea>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 p-2">

            <div class="form-group">
                <label for="image">عکس</label>
                @if(!isset($item))
                    <span style="color: red;">*</span>
                @endif

                <input type="file" class="form-control bg-light rounded-custom" id="image" name="image"
                       @if(!isset($item)) data-required="true" @endif
                >

                @if(isset($item) && $item->image)
                    <div class="mt-2">
                        <img src="{{ $item->getImageFile() }}" width="100" alt="{{ $item->title }}"
                             style="object-fit: cover; border-radius: 5px;">
                    </div>
                @endif
            </div>
        </div>
        <div class="col-12 col-md-6 p-2">

            <div class="form-group">
                <label for="phone_image">عکس موبایل</label>
                @if(!isset($item))
                    <span style="color: red;">*</span>
                @endif

                <input type="file" class="form-control bg-light rounded-custom" id="phone_image" name="phone_image"
                       @if(!isset($item)) data-required="true" @endif
                >

                @if(isset($item) && $item->phone_image)
                    <div class="mt-2">
                        <img src="{{ $item->getPhoneImageFile() }}" width="100" alt="{{ $item->title }}"
                             style="object-fit: cover; border-radius: 5px;">
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
                    <label class="form-check-label" for="is_active">
                        فعال باشد؟
                    </label>
                </div>
            </div>
        </div>

        @include('admin.image-croper.modal-croper', [
            'inputId' => 'image',
            'aspectWidth' => 2000,
            'aspectHeight' => 840,
            'outputWidth' => 2000,
            'outputHeight' => 840
        ])
        @include('admin.image-croper.modal-croper', [
            'inputId' => 'phone_image',
            'aspectWidth' => 750,
            'aspectHeight' => 650,
            'outputWidth' => 750,
            'outputHeight' => 650
        ])

        <div class="col-12 ms-auto p-2 mt-3">
            <button type="submit" class="btn btn-custom rounded-custom w-fit px-5">
                ذخیره
            </button>
        </div>
    </div>
</div>
