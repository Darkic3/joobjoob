<div class="container-fluid">
    <div class="card-block row w-100 m-0">

        <div class="col-12 p-2">
            <div class="form-group">
                <label for="title_seo">عنوان سئو</label>
                <input type="text"
                       name="title_seo"
                       id="title_seo"
                       class="form-control bg-light rounded-custom seoTitle{{$data->id}}"
                       value="{{ old('title_seo', $data->title ?? '') }}"
                       data-required="true">
                <small id="smallTitle{{$data->id}}"
                       style="color: #0a58ca; font-size: 1rem; float: left; margin-top: 5px;">
                </small>
            </div>
        </div>

        <div class="col-12 p-2">
            <div class="form-group">
                <label for="h1">H1</label>
                <input type="text"
                       name="h1"
                       id="h1"
                       class="form-control bg-light rounded-custom seoH1{{$data->id}}"
                       value="{{ old('h1', $data->H1 ?? '') }}"
                       data-required="true">
                <small id="smallH1{{$data->id}}"
                       style="color: #0a58ca; font-size: 1rem; float: left; margin-top: 5px;"></small>
            </div>
        </div>

        <div class="col-12 p-2">
            <div class="form-group">
                <label for="description_seo">توضیحات سئو</label>
                <textarea name="description_seo"
                          id="description_seo"
                          class="form-control bg-light rounded-custom seoDes{{$data->id}}"
                          rows="4"
                          data-required="true">{{ old('description_seo', $data->description ?? '') }}</textarea>
                <small id="smallDes{{$data->id}}"
                       style="color: #0a58ca; font-size: 1rem; float: left; margin-top: 5px;">
                </small>
            </div>
        </div>


        <div class="col-12 mt-2">
            <div class="form-group">
                <div class="form-check form-switch">
                    <input type="hidden" name="noIndex" value="0">
                    <input class="form-check-input"
                           type="checkbox"
                           id="noindex"
                           name="noIndex"
                           value="1"
                        {{ (old('noIndex', $data->noIndex ?? false)) ? 'checked' : '' }}>
                    <label class="form-check-label" for="noindex">
                        عدم انتشار در ابزارهای جستجو
                    </label>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-4 col-sm-6 col-12 ms-auto p-2">
            <button type="submit" id="submitFormCms" class="btn btn-custom rounded-custom w-fit px-3 py-2">
                ذخیره
            </button>
        </div>
    </div>
</div>
@push('scripts')
    @include('admin.seo.seo-character-counter')
@endpush
