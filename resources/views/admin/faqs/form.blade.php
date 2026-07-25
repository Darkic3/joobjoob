<a class="list-inline-item btn-custom-b border-0 rounded p-2 m-1" id="faqBtn{{ $data->id }}"
   data-bs-target="#FaqModal{{ $data->id }}" data-bs-toggle="modal"
   title="سوالات پرتکرار">
    <span data-bs-toggle="tooltip" data-bs-title="سوالات پرتکرار">
        <i class="d-flex bi bi-question-circle color-custom2 fs-5"></i>
    </span>
</a>

@push('modals')
    @include('admin.faqs.modal', ['data' => $data])
@endpush

@push('scripts')
    @include('admin.faqs.scripts')
@endpush
