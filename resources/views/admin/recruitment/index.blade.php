@extends('layouts.admin.master')
@section('tab-name','استخدام')
@section('content')
    <div class="body d-flex py-3">
        <div class="container-fluid">
            <div class="page-header">
                <div class="card-header py-3 no-bg bg-transparent border-0 px-0 flex-wrap">
                    <h3 class="fw-bolder ms-1">درخواست‌های استخدام</h3>
                    <div class="d-flex align-items-center">
                        @include('admin.search-modal.search', [
                            'route' => route('admin.recruitment.index'),
                            'hasName' => true,
                            'hasReadStatus' => true,
                            'hasDate' => true,
                        ])
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="card-block row">
                <div class="col-sm-12">
                    <div class="table-responsive">
                        <table class="table align-middle border-custom mb-0 text-center text-light">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>نام متقاضی</th>
                                <th>آگهی مربوطه</th>
                                <th>تلفن</th>
                                <th>رزومه</th>
                                <th>وضعیت</th>
                                <th>تاریخ</th>
                                <th>عملیات</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($items as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>
                                        @if($item->advertisement)
                                            <span class="badge bg-secondary">{{ $item->advertisement->title }}</span>
                                        @else
                                            <span class="text-muted small">عمومی / نامشخص</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->phone }}</td>
                                    <td>
                                        @if($item->getPdfUrl())
                                            <a href="{{ $item->getPdfUrl() }}" target="_blank"
                                               class="btn btn-sm btn-outline-info">
                                                <i class="bi bi-file-earmark-pdf"></i> PDF
                                            </a>
                                        @else
                                            <span class="text-muted">بدون فایل</span>
                                        @endif
                                    </td>
                                    <td id="status-cell-recruitment-{{ $item->id }}">
                                        <span class="badge {{ $item->is_read ? 'bg-success' : 'bg-danger' }}">
                                            {{ $item->is_read ? 'خوانده شده' : 'جدید' }}
                                        </span>
                                    </td>
                                    <td>{{ jdate_format('Y/m/d', strtotime($item->created_at)) }}</td>
                                    <td>
                                        <button type="button" class="btn-custom-b border-0 rounded p-2"
                                                onclick="openRecruitmentModal({{ $item->id }})">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        {{--                                        <button class="btn-custom-b border-0 rounded p-2" data-bs-toggle="modal" data-bs-target="#modal-{{ $item->id }}">--}}
                                        {{--                                            <i class="bi bi-eye"></i>--}}
                                        {{--                                        </button>--}}
                                        <form action="{{ route('admin.recruitment.destroy', $item) }}" method="POST"
                                              class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-custom-b border-0 rounded p-2"><i
                                                    class="bi bi-trash"></i></button>
                                        </form>

                                        {{-- Modal Details --}}
                                        {{--                                        <div class="modal fade" id="modal-{{ $item->id }}" tabindex="-1">--}}
                                        {{--                                            <div class="modal-dialog modal-dialog-centered">--}}
                                        {{--                                                <div class="modal-content text-dark text-start">--}}
                                        {{--                                                    <div class="modal-header">--}}
                                        {{--                                                        <h5 class="modal-title">جزئیات درخواست: {{ $item->name }}</h5>--}}
                                        {{--                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>--}}
                                        {{--                                                    </div>--}}
                                        {{--                                                    <div class="modal-body">--}}
                                        {{--                                                        <div class="mb-3">--}}
                                        {{--                                                            <label class="fw-bold">مربوط به آگهی:</label>--}}
                                        {{--                                                            <p>{{ $item->advertisement->title ?? 'درخواست عمومی' }}</p>--}}
                                        {{--                                                        </div>--}}
                                        {{--                                                        <div class="mb-3">--}}
                                        {{--                                                            <label class="fw-bold text-muted">تلفن تماس:</label>--}}
                                        {{--                                                            <p>{{ $item->phone }}</p>--}}
                                        {{--                                                        </div>--}}
                                        {{--                                                        <hr>--}}
                                        {{--                                                        <div class="mb-3">--}}
                                        {{--                                                            <label class="fw-bold">توضیحات متقاضی:</label>--}}
                                        {{--                                                            <div class="p-3 bg-light rounded border mt-1" style="white-space: pre-wrap;">{{ $item->description }}</div>--}}
                                        {{--                                                        </div>--}}
                                        {{--                                                    </div>--}}
                                        {{--                                                </div>--}}
                                        {{--                                            </div>--}}
                                        {{--                                        </div>--}}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-4">هیچ درخواست استخدامی یافت نشد.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $items->links() }}
                </div>
            </div>
        </div>
    </div>
    {{-- my new js modal (show) --}}
    <div class="modal fade" id="detailsModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-dark text-start">
                <div class="modal-header">
                    <h5 class="modal-title">جزئیات درخواست: <span id="modal-user-name"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modal-loading-spinner">
                    <div class="text-center">
                        <div class="spinner-border text-primary"></div>
                    </div>
                </div>
                <div class="modal-body d-none" id="modal-content-area">
                    <div class="mb-3">
                        <label class="fw-bold">مربوط به آگهی:</label>
                        <p id="modal-ad-title"></p>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold text-muted">تلفن تماس:</label>
                        <p id="modal-phone"></p>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="fw-bold">توضیحات متقاضی:</label>
                        <div id="modal-description" class="p-3 bg-light rounded border mt-1"
                             style="white-space: pre-wrap;"></div>
                    </div>
                    <div id="modal-pdf-link" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
@stop

@push('scripts')
    <script>
        function openRecruitmentModal(id) {
            const modalElement = document.getElementById('detailsModal');
            const modal = new bootstrap.Modal(modalElement);
            const contentArea = document.getElementById('modal-content-area');
            const spinner = document.getElementById('modal-loading-spinner');

            contentArea.classList.add('d-none');
            spinner.classList.remove('d-none');
            modal.show();

            fetch(`/admin/recruitment/${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modal-user-name').innerText = data.name;
                    document.getElementById('modal-ad-title').innerText = data.ad_title;
                    document.getElementById('modal-phone').innerText = data.phone;
                    document.getElementById('modal-description').innerText = data.description || 'توضیحاتی ثبت نشده است.';

                    const pdfContainer = document.getElementById('modal-pdf-link');
                    if (data.pdf_url) {
                        pdfContainer.innerHTML = `<a href="${data.pdf_url}" target="_blank" class="btn btn-info w-100 text-white">مشاهده رزومه PDF</a>`;
                    } else {
                        pdfContainer.innerHTML = '<span class="text-muted">بدون فایل رزومه</span>';
                    }

                    spinner.classList.add('d-none');
                    contentArea.classList.remove('d-none');
                });

            fetch(`/admin/recruitment/${id}/mark-read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const statusCell = document.getElementById(`status-cell-recruitment-${id}`);
                        if (statusCell) {
                            statusCell.innerHTML = '<span class="badge bg-success">خوانده شده</span>';
                        }
                    }
                })
                .catch(err => console.error("Error marking read:", err));
        }
    </script>
@endpush
