@extends('layouts.admin.master')
@section('tab-name','مخاطبین')
@section('content')
    <div class="body d-flex py-3">
        <div class="container-fluid">
            <div class="page-header">
                <div class="card-header py-3 no-bg bg-transparent border-0 px-0 flex-wrap">
                    <h3 class="fw-bolder ms-1">
                        درخواست‌های تماس
                    </h3>
                    <div class="d-flex align-items-center">

                        @include('admin.search-modal.search', [
                            'route' => route('admin.contact.index'),
                            'hasTitle' => true,
                            'hasName' => true,
                            'email' => true,
                            'hasDepartment' => true,
                            'hasReadStatus' => true,

                            'hasStatus' => false,
                            'hasUrl' => false,
                            'hasShowInFirstPage' => false,
                            'hasCategory' => false,
                            'hasDate' => true,
                        ])
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="card-block row">
                <div class="col-sm-12 col-lg-12 col-xl-12">
                    <div class="table-responsive d-flex align-items-center">
                        <table id="myDataTable" class="table align-middle border-custom mb-0">
                            <thead class="text-center text-light">
                            <tr>
                                <th class="fw-bolder">#</th>
                                <th class="fw-bolder">نام فرستنده</th>
                                <th class="fw-bolder">ایمیل</th>
                                <th class="fw-bolder" style="width: 30%;">پیام</th>
                                <th class="fw-bolder">دپارتمان</th>
                                <th class="fw-bolder">وضعیت</th>
                                <th class="fw-bolder">تاریخ ارسال</th>
                                <th class="fw-bolder">عملیات</th>
                            </tr>
                            </thead>
                            <tbody class="text-center text-light">
                            @forelse($contacts as $request)
                                <tr>
                                    <td>
                                        {{ $loop->iteration }}
                                    </td>
                                    <td>
                                        {{ $request->name }}
                                    </td>
                                    <td>
                                        {{ $request->email }}
                                    </td>
                                    <td>
                                        <span class="d-inline-block text-truncate" style="max-width: 250px;"
                                              title=" {{ Str::limit($request->message, 60, '...') }}">
                                                {{ Str::limit($request->message, 60, '...') }}
                                        </span>
                                    </td>
                                    <td>
                                        @switch($request->department)
                                            @case('management')
                                                <span
                                                    class="badge bg-danger bg-opacity-75 text-white rounded-pill px-3 py-2 fw-normal">
                                                    <i class="bi bi-shield-lock me-1"></i> مدیریت
                                                </span>
                                                @break

                                            @case('support')
                                                <span
                                                    class="badge bg-info bg-opacity-75 text-dark rounded-pill px-3 py-2 fw-normal">
                                                    <i class="bi bi-headset me-1"></i> پشتیبانی
                                                </span>
                                                @break

                                            @default
                                                <span class="badge bg-secondary rounded-pill px-3 py-2 fw-normal">
                                                    {{ $request->department ?? 'عمومی' }}
                                                </span>
                                        @endswitch
                                    </td>
                                    <td id="status-cell-contact-{{ $request->id }}">
                                        <span class="badge {{ $request->is_read ? 'bg-success' : 'bg-danger' }}">
                                            {{ $request->is_read ? 'خوانده شده' : 'جدید' }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ jdate_format('Y/m/d', strtotime($request->created_at)) }}
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">

                                            {{--                                            <button type="button"--}}
                                            {{--                                                    class="list-inline-item btn-custom-b border-0 rounded p-2 m-1"--}}
                                            {{--                                                    data-bs-toggle="modal"--}}
                                            {{--                                                    data-bs-target="#messageModal-{{ $request->id }}">--}}
                                            {{--                                                <i class="d-flex bi bi-eye fs-6"></i>--}}
                                            {{--                                            </button>--}}

                                            <button type="button"
                                                    class="list-inline-item btn-custom-b border-0 rounded p-2 m-1"
                                                    onclick="openContactModal('{{ route('admin.contact.show', $request->id) }}', {{ $request->id }})">
                                                <i class="d-flex bi bi-eye fs-6"></i>
                                            </button>

                                            <form action="{{ route('admin.contact.destroy', $request) }}" method="POST"
                                                  class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="list-inline-item btn-custom-b border-0 rounded p-2 m-1"
                                                        style="background-color: transparent;">
                                                    <i class="d-flex bi bi-trash fs-6"></i>
                                                </button>
                                            </form>

                                            {{--                                            <div class="modal fade" id="messageModal-{{ $request->id }}" tabindex="-1"--}}
                                            {{--                                                 aria-hidden="true">--}}
                                            {{--                                                <div class="modal-dialog modal-dialog-centered">--}}
                                            {{--                                                    <div class="modal-content text-dark text-start">--}}
                                            {{--                                                        <div class="modal-header">--}}
                                            {{--                                                            <h5 class="modal-title fw-bold">پیام--}}
                                            {{--                                                                از: {{ $request->name }}</h5>--}}
                                            {{--                                                            <button type="button" class="btn-close m-0"--}}
                                            {{--                                                                    data-bs-dismiss="modal" aria-label="Close"></button>--}}
                                            {{--                                                        </div>--}}
                                            {{--                                                        <div class="modal-body">--}}
                                            {{--                                                            <div class="mb-3">--}}
                                            {{--                                                                <label class="fw-bold text-muted small">ایمیل:</label>--}}
                                            {{--                                                                <p class="mb-0">{{ $request->email }}</p>--}}
                                            {{--                                                            </div>--}}
                                            {{--                                                            <hr class="my-2">--}}
                                            {{--                                                            <div class="mb-3">--}}
                                            {{--                                                                <label class="fw-bold text-muted small">متن کامل--}}
                                            {{--                                                                    پیام:</label>--}}
                                            {{--                                                                <div class="p-3 bg-light rounded border mt-1"--}}
                                            {{--                                                                     style="white-space: pre-wrap;">{{ $request->message }}</div>--}}
                                            {{--                                                            </div>--}}
                                            {{--                                                            <div class="text-end text-muted small">--}}
                                            {{--                                                                ارسال شده--}}
                                            {{--                                                                در: {{ jdate_format('Y/m/d H:i', strtotime($request->created_at)) }}--}}
                                            {{--                                                            </div>--}}
                                            {{--                                                        </div>--}}
                                            {{--                                                        <div class="modal-footer">--}}
                                            {{--                                                            <button type="button" class="btn btn-secondary"--}}
                                            {{--                                                                    data-bs-dismiss="modal">بستن--}}
                                            {{--                                                            </button>--}}
                                            {{--                                                        </div>--}}
                                            {{--                                                    </div>--}}
                                            {{--                                                </div>--}}
                                            {{--                                            </div>--}}
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-3">
                                        هیچ درخواست تماسی یافت نشد.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $contacts->links() }}

                </div>
            </div>
        </div>
    </div>
    {{-- Modal with Show - Js --}}
    <div class="modal fade" id="contactDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-dark text-start">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">جزئیات پیام: <span id="modal-contact-name"></span></h5>
                    <button type="button" class="btn-close m-0" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div id="modal-contact-loading" class="modal-body text-center">
                    <div class="spinner-border text-primary"></div>
                </div>
                <div id="modal-contact-content" class="modal-body d-none">
                    <div class="mb-3">
                        <label class="fw-bold text-muted small">ایمیل:</label>
                        <p id="modal-contact-email" class="mb-0"></p>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold text-muted small">دپارتمان:</label>
                        <p id="modal-contact-dept" class="mb-0"></p>
                    </div>
                    <hr class="my-2">
                    <div class="mb-3">
                        <label class="fw-bold text-muted small">متن کامل پیام:</label>
                        <div id="modal-contact-message" class="p-3 bg-light rounded border mt-1"
                             style="white-space: pre-wrap;"></div>
                    </div>
                    <div class="text-end text-muted small">
                        ارسال شده در: <span id="modal-contact-date"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بستن</button>
                </div>
            </div>
        </div>
    </div>

@stop

@push('scripts')
    <script>
        function openContactModal(url, id) {
            const modalElement = document.getElementById('contactDetailsModal');
            const modal = new bootstrap.Modal(modalElement);

            // ریست کردن نمایش
            document.getElementById('modal-contact-loading').classList.remove('d-none');
            document.getElementById('modal-contact-content').classList.add('d-none');
            modal.show();

            // ۱. دریافت اطلاعات برای نمایش
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modal-contact-name').innerText = data.name;
                    document.getElementById('modal-contact-email').innerText = data.email;
                    document.getElementById('modal-contact-dept').innerText = data.department;
                    document.getElementById('modal-contact-message').innerText = data.message;
                    document.getElementById('modal-contact-date').innerText = data.created_at;

                    document.getElementById('modal-contact-loading').classList.add('d-none');
                    document.getElementById('modal-contact-content').classList.remove('d-none');
                });

            // ۲. آپدیت وضعیت به خوانده شده
            fetch(`/admin/contact/${id}/mark-read`, {
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
                        const statusCell = document.getElementById(`status-cell-contact-${id}`);
                        if (statusCell) {
                            statusCell.innerHTML = '<span class="badge bg-success">خوانده شده</span>';
                        }
                    }
                });
        }
    </script>
@endpush
