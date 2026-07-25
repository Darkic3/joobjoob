
<br><br><br><br>

<div class="col-12">
    <h6 class="border-bottom pb-2 mb-3">اطلاعات سئو</h6>
</div>

<div class="col-md-6 p-2">
    <label for="meta_title" class="form-label">عنوان سئو</label>
    <input type="text" class="form-control" id="meta_title" name="meta_title"
           value="{{ old('meta_title', isset($meta) ? $meta->title : '') }}">
</div>

<div class="col-md-12 p-2">
    <label for="meta_description" class="form-label">توضیحات سئو</label>
    <textarea class="form-control ckeditor" id="meta_description" name="meta_description"
              rows="2">{!!   old('meta_description', isset($meta) ? $meta->description : '') !!}</textarea>
</div>
