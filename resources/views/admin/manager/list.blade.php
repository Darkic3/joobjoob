@extends('layouts.admin.master')
@section('tab-name','لیست مدیران')

@section('content')
    <div class="body d-flex py-3">
        <div class="container-fluid">
            {{-- پیام موفقیت --}}
            <div id="response" class="alert alert-success rounded-lg"
                 style="display:none; position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 9999; direction: rtl;">
                <i class="fa fa-check-circle me-1"></i>
                <span>ترتیب با موفقیت به‌روزرسانی شد.</span>
            </div>

            <div class="page-header">
                <div class="card-header py-3 no-bg bg-transparent border-0 px-0 flex-wrap">
                    <h3 class="fw-bolder ms-1">مدیران و اعضا</h3>
                    <div class="d-flex align-items-center">
                        <div class="alert alert-info py-2 px-3 mb-0 me-3 rounded-custom shadow-sm"
                             style="font-size: 13px;">
                            <i class="bi bi-info-circle me-1"></i>
                            درگ و دراپ فقط در محدوده هر گروه امکان‌پذیر است.
                        </div>
                        <a href="{{ route('admin.managers.create') }}"
                           class="btn ms-2 my-2 btn-custom-b rounded-custom d-flex align-items-center w-fit">
                            <i class="bi bi-plus-square-dotted d-flex h5 my-0 me-2"></i>
                            افزودن مدیر جدید
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
                                <th>نام و سمت</th>
                                <th>نوع مدیریت</th>
                                <th>اطلاعات تماس</th>
                                <th>وضعیت</th>
                                <th>عملیات</th>
                            </tr>
                            </thead>

                            @foreach($allTypes as $typeKey => $typeName)
                                <tbody class="bg-dark bg-opacity-10">
                                <tr>
                                    <td colspan="7" class="py-2 px-4 text-center">
                                        <span class="badge bg-primary fs-6">{{ $typeName }}</span>
                                    </td>
                                </tr>
                                </tbody>

                                {{-- بدنه قابل درگ برای هر گروه --}}
                                <tbody class="text-center text-light sortable-group" data-type="{{ $typeKey }}">
                                @forelse($groupedManagers->get($typeKey, []) as $manager)
                                    <tr id="item_{{ $manager->id }}" class="manager-row"
                                        style="cursor: move; background-color: rgba(255,255,255,0.02)">
                                        <td class="handle">
                                            <i class="bi bi-grip-vertical fs-5 text-muted"></i>
                                            {{ $loop->iteration }}
                                        </td>
                                        <td>
                                            <img src="{{ $manager->getImageFile() }}"
                                                 alt="{{ $manager->full_name }}"
                                                 style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold">{{ $manager->full_name }}</span>
                                                <small class="text-muted">{{ $manager->position }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $typeName }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column small text-start" style="direction: ltr">
                                                @if($manager->email)
                                                    <span><i
                                                            class="bi bi-envelope me-1"></i>{{ $manager->email }}</span>
                                                @endif
                                                @if($manager->phone)
                                                    <span><i class="bi bi-phone me-1"></i>{{ $manager->phone }}</span>
                                                @endif
                                                @if($manager->internal_phone)
                                                    <span><i class="bi bi-telephone me-1"></i>داخلی: {{ $manager->internal_phone }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if($manager->is_active)
                                                <span class="badge bg-success">فعال</span>
                                            @else
                                                <span class="badge bg-danger">غیرفعال</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a class="list-inline-item btn-custom-b border-0 rounded p-2 m-1"
                                                   href="{{ route('admin.managers.edit', $manager) }}">
                                                    <i class="d-flex bi bi-pen fs-6"></i>
                                                </a>
                                                <form action="{{ route('admin.managers.destroy', $manager) }}"
                                                      method="POST"
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
                                    <tr class="no-sort">
                                        <td colspan="7" class="text-center py-3 text-muted small">در این گروه مدیری ثبت
                                            نشده است.
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@push('scripts')
    <script src="{{asset('assets/admin/js/jquery-ui.min.js')}}"></script>

    <script>
        $(document).ready(function () {
            function hideResponse() {
                setTimeout(function () {
                    $("#response").fadeOut("slow");
                }, 3000);
            }

            $(".sortable-group").sortable({
                handle: '.handle',
                items: "tr:not(.no-sort)",
                opacity: 0.8,
                cursor: 'move',
                placeholder: "ui-state-highlight",
                update: function (event, ui) {
                    let order = $(this).sortable("serialize") + '&_token={{ csrf_token() }}';
                    let url = "{{ route('admin.managers.sort') }}";

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
            height: 75px;
            background-color: rgba(18, 113, 253, 0.05) !important;
            border: 2px dashed #1271fd !important;
            display: table-row !important;
            visibility: visible !important;
        }
        .handle {
            cursor: grab;
            width: 40px;
        }
        .handle:active {
            cursor: grabbing;
        }
        .group-header-text {
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }
        .manager-row:hover {
            background-color: rgba(255,255,255,0.05) !important;
            transition: background 0.2s;
        }
    </style>
@endpush
