@extends('layouts.admin.master')
@section('tab-name','تامین کنندگان')
@section('content')
    <div class="body d-flex py-3">
        <div class="container-fluid">
            <div id="response" class="alert alert-success rounded-lg"
                 style="display:none; position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 9999; direction: rtl;">
                <i class="fa fa-check-circle me-1"></i>
                <span>ترتیب تامین‌کنندگان با موفقیت به‌روزرسانی شد.</span>
            </div>

            <div class="page-header">
                <div class="card-header py-3 no-bg bg-transparent border-0 px-0 flex-wrap">
                    <h3 class="fw-bolder ms-1">تامین کنندگان</h3>
                    <div class="d-flex align-items-center">
                        @include('admin.search-modal.search', [
                            'route' => route('admin.suppliers.index'),
                            'hasTitle' => true,
                            'hasUrl' => true,
                            'hasStatus' => true,
                            'hasDate' => false,
                            'hasShowInFirstPage' => true,
                        ])
                        <a href="{{ route('admin.suppliers.create') }}"
                           class="btn ms-2 my-2 btn-custom-b rounded-custom d-flex align-items-center w-fit">
                            <i class="bi bi-plus-square-dotted d-flex h5 my-0 me-2"></i>
                            افزودن تامین کننده
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <div class="card-block row">
                <div class="col-sm-12">
                    <div class="table-responsive d-flex align-items-center">
                        <table id="myDataTable" class="table align-middle border-custom mb-0">
                            <thead class="text-center text-light">
                            <tr>
                                <th style="width: 50px;">#</th> <th class="fw-bolder">لوگو</th>
                                <th class="fw-bolder" style=" width: 20%;">عنوان</th>
                                <th class="fw-bolder">وضعیت</th>
                                <th class="fw-bolder">لینک</th>
                                <th class="fw-bolder">تاریخ ایجاد</th>
                                <th class="fw-bolder">عملیات</th>
                            </tr>
                            </thead>
                            <tbody class="text-center text-light" id="sortable-suppliers">
                            @forelse($suppliers as $item)
                                <tr id="item_{{ $item->id }}" style="cursor: move; background-color: rgba(255,255,255,0.02)">
                                    <td class="handle">
                                        <i class="bi bi-grip-vertical fs-5 text-muted"></i>
                                        {{ $loop->iteration }}
                                    </td>
                                    <td>
                                        <img src="{{ $item->getLogoFile()}}"
                                             alt="{{ $item->title }}"
                                             style="width: 80px; height: 60px; object-fit: contain; border-radius: 5px;">
                                    </td>
                                    <td>{{ Str::limit($item->title, 60, '...') }}</td>
                                    <td>
                                        @if($item->is_active)
                                            <span class="badge bg-success">فعال</span>
                                        @else
                                            <span class="badge bg-danger">غیرفعال</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->link)
                                            <a href="{{ $item->link }}" target="_blank" rel="noreferrer nofollow">مشاهده</a>
                                        @else - @endif
                                    </td>
                                    <td>{{ jdate_format('Y/m/d', strtotime($item->created_at)) }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            @include('admin.seo.form',['data'=>$item])
                                            <a class="list-inline-item btn-custom-b border-0 rounded p-2 m-1"
                                               href="{{ route('admin.suppliers.edit', $item) }}">
                                                <i class="d-flex bi bi-pen fs-6"></i>
                                            </a>
                                            <form action="{{ route('admin.suppliers.destroy', $item) }}" method="POST"
                                                  class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="list-inline-item btn-custom-b border-0 rounded p-2 m-1 bg-transparent">
                                                    <i class="d-flex bi bi-trash fs-6"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-3">هیچ تامین کننده‌ای یافت نشد.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{asset('assets/admin/js/jquery-ui.min.js')}}"></script>

        <script>
            $(document).ready(function () {
                function hideResponse() {
                    setTimeout(function () {
                        $("#response").fadeOut("slow");
                    }, 3000);
                }

                $("#sortable-suppliers").sortable({
                    handle: '.handle',
                    opacity: 0.7,
                    cursor: 'move',
                    placeholder: "ui-state-highlight",
                    update: function (event, ui) {
                        let order = $(this).sortable("serialize") + '&_token={{ csrf_token() }}';
                        let url = "{{ route('admin.suppliers.sort') }}";

                        $.post(url, order, function (response) {
                            $("#response").fadeIn('slow');
                            hideResponse();
                        }).fail(function() {
                            alert('خطایی در ذخیره‌سازی ترتیب رخ داد.');
                        });
                    }
                });
            });
        </script>

        <style>
            .ui-state-highlight {
                height: 60px;
                background-color: rgba(18, 113, 253, 0.1);
                border: 1px dashed #1271fd;
                visibility: visible !important;
            }
            .handle {
                cursor: grab;
            }
            .handle:active {
                cursor: grabbing;
            }
        </style>
    @endpush
@stop
