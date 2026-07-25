@php
    $isEdit = isset($faq) && $faq->exists;
    $faqId = $isEdit ? $faq->id : 'new';
@endphp

<div class="card border-0">
    <div class="card-header py-2 bg-light d-flex justify-content-between align-items-center">
        <h6 class="m-0 fw-bold">
            @if($isEdit)
                <i class="bi bi-pencil me-1"></i> ویرایش سوال
            @else
                <i class="bi bi-plus-circle me-1"></i> افزودن سوال جدید
            @endif
        </h6>
        @if($isEdit)
            <span class="badge bg-{{ $faq->is_active ? 'success' : 'secondary' }}">
                {{ $faq->is_active ? 'فعال' : 'غیرفعال' }}
            </span>
        @endif
    </div>
    <div class="card-body">
        <form id="faqForm{{ $data->id }}" method="POST"
              action="{{ $isEdit ? route('admin.faqs.update', $faq) : route('admin.faqs.store') }}"
              class="row g-3">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <input type="hidden" name="faqable_id" value="{{ $data->id }}">
            <input type="hidden" name="faqable_type" value="{{ get_class($data) }}">
            @if($isEdit)
                <input type="hidden" name="faq_id" value="{{ $faq->id }}">
            @endif

            <div class="col-12">
                <label class="form-label fw-bold">سؤال <span class="text-danger">*</span></label>
                <input type="text" name="question" class="form-control bg-light rounded-custom"
                       value="{{ old('question', $faq->question ?? '') }}" required maxlength="500">
                <small id="faqQuestionCounter{{ $data->id }}" class="text-muted small float-end">
                    <span class="current">0</span>/500
                </small>
            </div>

            <div class="col-12">
                <label class="form-label fw-bold">پاسخ <span class="text-danger">*</span></label>
                <textarea name="answer" class="form-control bg-light rounded-custom"
                          rows="4" required>{{ old('answer', $faq->answer ?? '') }}</textarea>
                <small id="faqAnswerCounter{{ $data->id }}" class="text-muted small float-end mt-1">
                    <span class="current">0</span>/2000
                </small>
            </div>

            <div class="col-12">
                <div class="form-check form-switch">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" id="faqActive{{ $data->id }}" name="is_active" value="1"
                        {{ ($isEdit ? $faq->is_active : true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="faqActive{{ $data->id }}">
                        فعال باشد؟
                    </label>
                </div>
            </div>

            <div class="col-12 text-end mt-2">
                @if($isEdit)
                    <button type="button"
                            class="btn btn-outline-danger btn-sm rounded-custom px-3 me-2 delete-faq-btn"
                            data-faq-id="{{ $faq->id }}"
                            title="حذف">
                        <i class="bi bi-trash me-1"></i> حذف
                    </button>
                @endif
                <button type="submit" class="btn btn-custom rounded-custom px-4">
                    @if($isEdit)
                        <i class="bi bi-check-lg me-1"></i> به‌روزرسانی
                    @else
                        <i class="bi bi-plus-circle me-1"></i> افزودن
                    @endif
                </button>
            </div>
        </form>
    </div>
</div>
