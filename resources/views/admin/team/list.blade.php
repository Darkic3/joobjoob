@extends('layouts.admin.master')
@section('tab-name','لیست اعضای تیم')

@section('content')
    <div class="body d-flex py-3">
        <div class="container-fluid">
            <div id="response" class="alert alert-success rounded-lg"
                 style="display:none; position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 9999; direction: rtl;">
                <i class="fa fa-check-circle me-1"></i>
                <span>ترتیب اعضا با موفقیت به‌روزرسانی شد.</span>
            </div>

            <div class="page-header">
                <div class="card-header py-3 no-bg bg-transparent border-0 px-0 flex-wrap">
                    <h3 class="fw-bolder ms-1">مدیریت اعضای تیم</h3>
                    <div class="d-flex align-items-center">
                        <div class="alert alert-info py-2 px-3 mb-0 me-3 rounded-custom shadow-sm" style="font-size: 13px;">
                            <i class="bi bi-info-circle me-1"></i>
                            با جابجا کردن ردیف‌ها، ترتیب نمایش در سایت تغییر می‌کند.
                        </div>
                        <a href="{{ route('admin.teams.create') }}"
                           class="btn ms-2 my-2 btn-custom-b rounded-custom d-flex align-items-center w-fit">
                            <i class="bi bi-plus-square-dotted d-flex h5 my-0 me-2"></i>
                            افزودن عضو جدید
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
                                <th style="width: 50px;">#</th>
                                <th>تصویر</th>
                                <th>نام و نام خانوادگی</th>
                                <th>سمت</th>
                                <th>وضعیت</th>
                                <th>عملیات</th>
                            </tr>
                            </thead>
                            <tbody class="text-center text-light" id="sortable-teams">
                            @forelse($teams as $team)
                                <tr id="item_{{ $team->id }}" style="cursor: move; background-color: rgba(255,255,255,0.02)">
                                    <td class="handle">
                                        <i class="bi bi-grip-vertical fs-5 text-muted"></i>
                                        {{ $loop->iteration }}
                                    </td>
                                    <td>
                                        <img src="{{ $team->getImageFile()}}"
                                             alt="{{ $team->full_name }}"
                                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                                    </td>
                                    <td>{{ $team->full_name }}</td>
                                    <td>{{ $team->position }}</td>
                                    <td>
                                        @if($team->is_active)
                                            <span class="badge bg-success">فعال</span>
                                        @else
                                            <span class="badge bg-danger">غیرفعال</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a class="list-inline-item btn-custom-b border-0 rounded p-2 m-1"
                                               href="{{ route('admin.teams.edit', $team) }}">
                                                <i class="d-flex bi bi-pen fs-6"></i>
                                            </a>
                                            <form action="{{ route('admin.teams.destroy', $team) }}" method="POST"
                                                  class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="list-inline-item btn-custom-b border-0 rounded p-2 m-1 bg-transparent">
                                                    <i class="d-flex bi bi-trash fs-6"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-3">هیچ عضوی یافت نشد.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
{{--                    <div class="mt-4">--}}
{{--                        {{ $teams->links() }}--}}
{{--                    </div>--}}
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

                $("#sortable-teams").sortable({
                    handle: '.handle',
                    opacity: 0.7,
                    cursor: 'move',
                    placeholder: "ui-state-highlight",
                    update: function (event, ui) {
                        // سریالایز کردن آیدی‌ها (item[]=1&item[]=2...)
                        let order = $(this).sortable("serialize") + '&_token={{ csrf_token() }}';
                        let url = "{{ route('admin.teams.sort') }}"; // اطمینان حاصل کنید این روت تعریف شده است

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
                height: 80px;
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
