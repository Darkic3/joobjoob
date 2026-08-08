<div class="modal fade" id="commentModal-{{ $comment->id }}" tabindex="-1"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-dark text-start">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    جزئیات نظر
                </h5>
                <button type="button" class="btn-close m-0"
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">نویسنده:</label>
                        <p class="mb-0 fw-bold">{{ $comment->author_name }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">مربوط به:</label>
                        <span class="mb-0 py-2" dir="ltr"> {{ $comment->getSection() }} (شناسه: {{ $comment->commentable_id }})</span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="fw-bold text-muted small">شماره تلفن:</label>
                    <p class="mb-0 bg-light p-2 rounded">{{ $comment->phone ?? 'بدون شماره تلفن' }}</p>
                </div>

                <hr class="my-2">

                <div class="mb-3">
                    <label class="fw-bold text-muted small">متن کامل نظر:</label>
                    <div class="p-3 bg-light rounded border mt-1"
                         style="white-space: pre-wrap;">{{ $comment->message }}</div>
                </div>

                <div class="text-end text-muted small">
                    تاریخ ثبت: {{ show_date($comment->created_at, 'Y/m/d H:i') }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">بستن
                </button>
            </div>
        </div>
    </div>
</div>
