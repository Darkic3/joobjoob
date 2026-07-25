@extends('layouts.admin.master')
@section('tab-name','نظرات')
@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="body d-flex py-3">
        <div class="container-fluid">
            <div class="page-header">
                <div class="card-header py-3 no-bg bg-transparent border-0 px-0 flex-wrap">
                    <h3 class="fw-bolder ms-1">لیست نظرات</h3>
                    <div class="d-flex align-items-center">
                        @include('admin.search-modal.search', [
                            'route' => route('admin.comments.index'),
                            'hasTitle' => false,
                            'hasName' => true,
                            'hasUrl' => false,
                            'hasStatus' => false,
                            'hasDate' => true,
                            'hasShowInFirstPage' => false,
                            'hasCategory' => false,
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
                                <th class="fw-bolder">نویسنده</th>
                                <th class="fw-bolder">شماره تلفن</th>
                                <th class="fw-bolder" style="width: 35%;">متن نظر</th>
                                <th class="fw-bolder">بخش مربوطه</th>
                                <th class="fw-bolder">وضعیت نظر</th>
                                <th class="fw-bolder">تاریخ ثبت</th>
                                <th class="fw-bolder">عملیات</th>
                            </tr>
                            </thead>
                            <tbody class="text-center text-light">
                            @forelse($comments as $comment)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $comment->author_name }}</td>
                                    <td>{{ $comment->phone ?? '---' }}</td>
                                    <td>
                                        <span class="d-inline-block text-truncate" style="max-width: 250px;"
                                              title="{{ $comment->message }}">
                                                {{ Str::limit($comment->message, 60, '...') }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ $comment->getPageUrl() }}" target="_blank"
                                           class="text-decoration-none">
                                            <span class="d-inline-block rounded-pill px-3 py-2"
                                                  style="font-size: 0.85rem; font-weight: 400; background-color: #07ddef; color: #373535; font-family: 'Vazirmatn', 'IRANSans', 'Segoe UI', Tahoma, sans-serif;">
                                                {{ $comment->getSection() }}
                                                 <i class="bi bi-box-arrow-up-right ms-1" style="font-size: 10px;"></i>
                                            </span>
                                        </a>
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm comment-status-select"
                                                data-id="{{ $comment->id }}"
                                                style="min-width: 110px; cursor: pointer;"
                                                onchange="changeStatus(this)">
                                            <option value="0"
                                                    {{ $comment->status == 'pending' ? 'selected' : '' }} class="text-warning">
                                                در انتظار
                                            </option>
                                            <option value="1"
                                                    {{ $comment->status == 'approved' ? 'selected' : '' }} class="text-success">
                                                تایید شده
                                            </option>
                                            <option value="2"
                                                    {{ $comment->status == 'rejected' ? 'selected' : '' }} class="text-danger">
                                                رد
                                                شده
                                            </option>
                                        </select>
                                        <div class="spinner-border spinner-border-sm text-primary d-none mt-1"
                                             role="status" id="spinner-{{ $comment->id }}"></div>
                                    </td>
                                    <td>
                                        {{ jdate_format('Y/m/d', strtotime($comment->created_at)) }}
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button"
                                                    class="list-inline-item btn-custom-b border-0 rounded p-2 m-1"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#commentModal-{{ $comment->id }}">
                                                <i class="d-flex bi bi-eye fs-6"></i>
                                            </button>

                                            <form action="{{ route('admin.comments.destroy', $comment) }}" method="POST"
                                                  class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="list-inline-item btn-custom-b border-0 rounded p-2 m-1"
                                                        style="background-color: transparent;">
                                                    <i class="d-flex bi bi-trash fs-6"></i>
                                                </button>
                                            </form>


                                            @include('admin.comment.partials.modal', ['comment' => $comment])
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-3">
                                        هیچ نظری یافت نشد.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $comments->links() }}
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
            axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');


            function changeStatus(element) {
                const commentId = element.getAttribute('data-id');
                const newStatus = element.value;
                const spinner = document.getElementById(`spinner-${commentId}`);

                element.disabled = true;
                spinner.classList.remove('d-none');

                const res = axios.post("{{ route('admin.comments.updateStatus') }}", {
                    id: commentId,
                    status: newStatus
                })
                    .then(response => {
                        console.log('Success:', response.data);
                        element.style.borderColor = '#198754';
                        setTimeout(() => {
                            element.style.borderColor = '';
                        }, 2000);
                    })
                    .catch(error => {
                        console.error('خطا در درخواست:', error);

                        if (error.response) {
                            const status = error.response.status;
                            const data = error.response.data;

                            let msg = `خطا ${status}`;
                            if (status === 419) {
                                msg = 'توکن امنیتی منقضی شده یا نامعتبر است. لطفاً صفحه را رفرش کنید.';
                            } else if (status === 401) {
                                msg = 'دسترسی غیرمجاز. لطفاً دوباره وارد شوید.';
                            } else if (status === 403) {
                                msg = 'شما دسترسی لازم برای این عملیات را ندارید.';
                            } else if (status === 500) {
                                msg = 'خطای داخلی سرور. لطفاً با پشتیبانی تماس بگیرید.';
                            } else if (data?.message) {
                                msg = data.message;
                            } else if (data?.error) {
                                msg = data.error;
                            }

                            alert(msg);
                            element.style.borderColor = 'red';
                        } else if (error.request) {
                            alert('اتصال برقرار نشد. ممکن است درخواست به صفحه‌ای غیر از API ریدایرکت شده باشد.');
                            console.log('Request object:', error.request);
                        } else {
                            alert('خطا در ارسال درخواست: ' + error.message);
                        }
                    })
                    .finally(() => {
                        element.disabled = false;
                        spinner.classList.add('d-none');
                    });
            }
        </script>
    @endpush
@stop
