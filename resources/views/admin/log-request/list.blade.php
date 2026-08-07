@extends('layouts.admin.master')
@section('tab-name', 'لاگ درخواست‌ها')
@section('content')
    <div class="body d-flex py-3">
        <div class="container-fluid">
            <div class="page-header">
                <div class="card-header py-3 no-bg bg-transparent border-0 px-0 flex-wrap">
                    <h3 class="fw-bolder ms-1">لیست لاگ درخواست‌ها</h3>
                    <div class="d-flex align-items-center">
                        @include('admin.search-modal.search', [
                            'route' => route('admin.request-logs.index'),
                            'hasTitle' => false,
                            'hasName' => true,
                            'hasUrl' => true,
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
                                <th class="fw-bolder">کاربر</th>
                                <th class="fw-bolder">IP</th>
                                <th class="fw-bolder">متد</th>
                                <th class="fw-bolder" style="width: 30%;">آدرس (URL)</th>
                                <th class="fw-bolder">کد وضعیت</th>
                                <th class="fw-bolder">تاریخ</th>
                                <th class="fw-bolder">عملیات</th>
                            </tr>
                            </thead>
                            <tbody class="text-center text-light">
                            @forelse($logs as $log)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $log->user->name ?? 'کاربر مهمان/حذف شده' }}</td>
                                    <td>{{ $log->ip }}</td>
                                    <td>
                                        @php
                                            $methodColor = match($log->method) {
                                                'GET' => '#07ddef',
                                                'POST' => '#198754',
                                                'PUT', 'PATCH' => '#ffc107',
                                                'DELETE' => '#dc3545',
                                                default => '#6c757d',
                                            };
                                            $textColor = ($log->method == 'POST' || $log->method == 'DELETE') ? '#fff' : '#373535';
                                        @endphp
                                        <span class="d-inline-block rounded-pill px-3 py-2"
                                              style="font-size: 0.85rem; font-weight: 600; background-color: {{ $methodColor }}; color: {{ $textColor }}; font-family: 'Vazirmatn', sans-serif;">
                                            {{ $log->method }}
                                        </span>
                                    </td>
                                    <td dir="ltr" class="text-end">
                                        <span class="d-inline-block text-truncate" style="max-width: 300px;"
                                              title="{{ $log->url }}">
                                                {{ Str::limit($log->url, 50, '...') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span
                                            class="badge {{ $log->status_code >= 200 && $log->status_code < 300 ? 'bg-success' : ($log->status_code >= 400 ? 'bg-danger' : 'bg-secondary') }}">
                                            {{ $log->status_code }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ show_date($log->created_at, 'Y/m/d H:i') }}
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button"
                                                    class="list-inline-item btn-custom-b border-0 rounded p-2 m-1"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#logModal-{{ $log->id }}">
                                                <i class="d-flex bi bi-eye fs-6"></i>
                                            </button>

                                            {{-- دکمه حذف (اختیاری اگر نیاز دارید لاگ‌ها حذف شوند) --}}
                                            {{--
                                            <form action="{{ route('admin.request-logs.destroy', $log->id) }}" method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="list-inline-item btn-custom-b border-0 rounded p-2 m-1" style="background-color: transparent;">
                                                    <i class="d-flex bi bi-trash fs-6"></i>
                                                </button>
                                            </form>
                                            --}}
                                        </div>

                                        <div class="modal fade" id="logModal-{{ $log->id }}" tabindex="-1"
                                             aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                <div class="modal-content"
                                                     style="background-color: #2a2a2a; color: #fff;">
                                                    <div class="modal-header border-bottom border-secondary">
                                                        <h5 class="modal-title">جزئیات درخواست #{{ $log->id }}</h5>
                                                        <button type="button" class="btn-close btn-close-white"
                                                                data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body text-start" dir="ltr">
                                                        <div class="mb-3">
                                                            <strong class="d-block text-info mb-2">Full URL:</strong>
                                                            <code class="text-light">{{ $log->url }}</code>
                                                        </div>
                                                        <div class="mb-3">
                                                            <strong class="d-block text-warning mb-2">Payload
                                                                (Body):</strong>
                                                            <pre class="bg-dark p-3 rounded"
                                                                 style="max-height: 300px; overflow-y: auto; color: #adbac7;">
                                                            @php
                                                                $payloadData = json_decode($log->payload, true);
                                                                echo $payloadData ? json_encode($payloadData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : 'No Data';
                                                            @endphp
                                                            </pre>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-top border-secondary">
                                                        <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">بستن
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-3">
                                        هیچ لاگی ثبت نشده است.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
