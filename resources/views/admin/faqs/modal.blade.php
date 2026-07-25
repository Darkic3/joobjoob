<div id="FaqModal{{ $data->id }}" class="modal fade" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <div class="modal-content rounded-custom border-custom shadow bg-white">
            <div class="modal-header px-3 py-2">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-question-circle me-2 text-primary"></i>
                    سوالات پرتکرار: {{ $data->title ?? 'بدون عنوان' }}
                </h5>
                <button type="button" class="close btn px-0" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x-lg d-flex" aria-hidden="true"></i>
                </button>
            </div>
            <div class="modal-body p-3">
                <div class="row">
                    <div class="col-lg-5 border-end">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="m-0 fw-bold">لیست سوالات</h6>
                            <span class="badge bg-info text-dark">
                                {{ $data->faqs->count() }} سوال
                            </span>
                        </div>

                        @if($data->faqs->isNotEmpty())
                            <div class="list-group list-group-flush faq-list" id="faqList{{ $data->id }}">
                                @foreach($data->faqs as $faq)
                                    <a href="{{ route('admin.faqs.edit-inline', [
                                        'model' => class_basename($data),
                                        'id' => $data->id,
                                        'faq' => $faq->id]) }}"
                                       class="list-group-item list-group-item-action py-2 px-3 d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1 me-2">
                                            <div class="d-flex align-items-baseline">
                                                <span class="badge bg-{{ $faq->is_active ? 'success' : 'secondary' }} me-2">
                                                    {{ $faq->is_active ? 'فعال' : 'غیرفعال' }}
                                                </span>
                                                <strong>{{ $loop->iteration }}.</strong>
                                            </div>
                                            <div class="small text-truncate" style="max-width: 200px;">
                                                {{ $faq->question }}
                                            </div>
                                        </div>
                                        <div>
                                            <i class="bi bi-pencil-square text-muted"></i>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4 text-muted small">
                                <i class="bi bi-exclamation-circle d-block mb-2"></i>
                                سوالی ثبت نشده است.
                            </div>
                        @endif

{{--                        <div class="mt-3">--}}
{{--                            <button type="button" class="btn btn-sm btn-custom-b w-100 reset-faq-form-btn"--}}
{{--                                    data-faqable-id="{{ $data->id }}">--}}
{{--                                <i class="bi bi-plus-circle me-1"></i> سوال جدید--}}
{{--                            </button>--}}
{{--                        </div>--}}
                    </div>

                    <div class="col-lg-7">
                        <div id="faqFormContainer{{ $data->id }}">
                            @include('admin.faqs._faq-form', [
                                'data' => $data,
                                'faq' => null
                            ])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
