@csrf
<div class="container-fluid">
    <div class="card-block row w-100 m-0">

        <div class="col-md-6 col-12 p-2">
            <div class="form-group">
                <label for="title">عنوان</label>
                <span style="color: red;">*</span>
                <input type="text" class="form-control bg-light rounded-custom"
                       id="title" name="title"
                       value="{{ old('title', $item->title ?? '') }}"
                       data-required="true">
            </div>
        </div>

        <div class="col-md-6 col-12 p-2">
            <div class="form-group">
                <label for="url">url</label>
                <span style="color: red;">*</span>
                <input type="text" class="form-control bg-light rounded-custom"
                       id="url" name="url"
                       value="{{ old('url', $item->url ?? '') }}"
                       data-required="true">
            </div>
        </div>

        <div class="col-md-6 col-12 p-2">
            <div class="form-group">
                <label for="news_category_id">دسته‌بندی</label>
                <select class="form-control bg-light rounded-custom" id="news_category_id" name="news_category_id">
                    <option value="">-- انتخاب دسته‌بندی --</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}"
                            {{ (old('news_category_id', $item->news_category_id ?? '')) == $category->id ? 'selected' : '' }}>
                            {{ $category->title }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-md-6 col-12 p-2">
            <div class="form-group">
                <label class="d-block mb-2">نوع محتوا</label>
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check" name="type" id="type_news" value="news" autocomplete="off"
                        {{ old('type', $item->type ?? 'news') == 'news' ? 'checked' : '' }}>
                    <label class="btn btn-outline-primary" for="type_news">خبر</label>
                    {{--                    TODO : --ask-- can fix here tooooo ?--}}
                    <input type="radio" class="btn-check" name="type" id="type_visual" value="report"
                           autocomplete="off"
                        {{ old('type', $item->type ?? '') == 'report' ? 'checked' : '' }}>
                    <label class="btn btn-outline-warning" for="type_visual">گزارش تصویری</label>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-12 p-2">
            <div class="form-group">
                <label for="image">عکس</label>
                <input type="file" class="form-control bg-light rounded-custom" id="image" name="image">
                @if (isset($item) && $item->image)
                    <div class="mt-2">
                        <img src="{{ $item->getImageFile() }}" width="100" alt="{{ $item->title }}"
                             style="object-fit: cover; border-radius: 5px;">
                    </div>
                @endif
            </div>
        </div>

        <div class="col-md-6 col-12 p-2 d-flex align-items-center">
            <div class="row w-100 m-0">
                <div class="col-6">
                    <div class="form-group">
                        <div class="form-check form-switch mt-md-4">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                فعال باشد؟
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <div class="form-check form-switch mt-md-4">
                            <input type="hidden" name="show_in_first_page" value="0">
                            <input class="form-check-input" type="checkbox" id="show_in_first_page"
                                   name="show_in_first_page"
                                   value="1" {{ old('show_in_first_page', $item->show_in_first_page ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="show_in_first_page">
                                نمایش در صفحه اول؟
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 p-2">
            <div class="form-group">
                <label for="description">توضیحات</label>
                <div class="ckeditor-div">
                    <textarea id="description" name="description"
                              class="des form-control bg-light rounded-custom ckeditor"
                              rows="3">{{ old('description', $item->description ?? '') }}</textarea>
                </div>
            </div>
        </div>

        @include('admin.image-croper.modal-croper', [
            'inputId' => 'image',
            'aspectWidth' => 400,
            'aspectHeight' => 280,
            'outputWidth' => 400,
            'outputHeight' => 280
        ])

        <div class="col-12 ms-auto p-2 mt-3">
            <button type="submit" id="submitFormCms" class="btn btn-custom rounded-custom w-fit px-5">
                ذخیره
            </button>
        </div>
    </div>
</div>
