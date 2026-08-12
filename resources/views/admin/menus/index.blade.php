@extends('layouts.admin.master')
@section('tab-name', 'تنظیمات منوی سایت')

@section('content')
    <div class="body d-flex py-3" dir="rtl">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-4 col-lg-5">
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
                        <div class="card-header bg-white py-3 border-bottom-0">
                            <h6 class="fw-bold mb-0 text-primary"><i class="bi bi-plus-circle-dotted me-2"></i>افزودن
                                آیتم جدید</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.menu.store') }}" method="POST" id="addMenuForm">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">عنوان منو</label>
                                    <input type="text" name="name" class="form-control rounded-pill"
                                           placeholder="مثلاً: خدمات ما" required>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input toggle-drop" type="checkbox" name="drop"
                                               id="is_dropdown" value="1">
                                        <label class="form-check-label small fw-bold" for="is_dropdown">دارای زیرمنو
                                            (Dropdown)</label>
                                    </div>
                                </div>

                                <div class="mb-3 url-field">
                                    <label class="form-label small fw-bold">لینک (URL)</label>
                                    <input type="text" name="url" class="form-control rounded-pill"
                                           placeholder="https://..." dir="ltr">
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="new_tab" id="new_tab"
                                               value="1">
                                        <label class="form-check-label small" for="new_tab">باز شدن در تب جدید</label>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 shadow-sm">
                                    <i class="bi bi-save me-1"></i> ذخیره آیتم
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-xl-8 col-lg-7">
                    <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                        <div class="card-header bg-white py-3 border-bottom-0">
                            <h6 class="fw-bold mb-0 text-primary"><i class="bi bi-diagram-3 me-2"></i>ساختار چیدمان منو
                            </h6>
                        </div>
                        <div class="card-body bg-light-custom" style="min-height: 500px;">
                            <div class="dd w-100" id="nestable">
                                <ol class="dd-list">
                                    @foreach($menus as $menu)
                                        <li class="dd-item" data-id="{{ $menu->id }}">
                                            <div class="dd-content-wrapper shadow-sm">
                                                <div class="dd-handle-custom"><i class="bi bi-grid-3x3-gap-fill fs-5"></i></div>

                                                <div class="dd-content-body">
                                                    <span class="fw-bolder">{{ $menu->name }}</span>
                                                    <div class="action-btns">
                                                        @if(is_null($menu->route_name))
                                                            <button class="btn edit-menu-btn shadow-none"
                                                                    data-id="{{ $menu->id }}"
                                                                    data-name="{{ $menu->name }}"
                                                                    data-url="{{ $menu->url }}"
                                                                    data-drop="{{ $menu->drop }}"
                                                                    data-tab="{{ $menu->new_tab }}">
                                                                <i class="bi bi-pencil-square"></i>
                                                            </button>
                                                            <button class="btn delete-menu shadow-none"
                                                                    data-id="{{ $menu->id }}">
                                                                <i class="bi bi-trash3-fill"></i>
                                                            </button>
                                                        @else
                                                            <span class="system-badge"><i class="bi bi-shield-lock me-1"></i>سیستمی</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            @if($menu->children->count() > 0)
                                                <ol class="dd-list">
                                                    @foreach($menu->children as $child)
                                                        <li class="dd-item" data-id="{{ $child->id }}">
                                                            <div class="dd-content-wrapper child-node">
                                                                <div class="dd-handle-custom"><i class="bi bi-grip-vertical"></i></div>
                                                                <div class="dd-content-body">
                                                                    <span>{{ $child->name }}</span>
                                                                    <div class="action-btns">
                                                                        @if(is_null($child->route_name))
                                                                            <button class="btn edit-menu-btn shadow-none p-0 me-2"
                                                                                    data-id="{{ $child->id }}"
                                                                                    data-name="{{ $child->name }}"
                                                                                    data-url="{{ $child->url }}"
                                                                                    data-drop="0"
                                                                                    data-tab="{{ $child->new_tab }}">
                                                                                <i class="bi bi-pencil-square"></i>
                                                                            </button>
                                                                            <button class="btn delete-menu shadow-none p-0"
                                                                                    data-id="{{ $child->id }}">
                                                                                <i class="bi bi-trash3"></i>
                                                                            </button>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ol>
                                            @endif
                                        </li>
                                    @endforeach
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold">ویرایش آیتم منو</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">عنوان منو</label>
                            <input type="text" name="name" id="edit_name" class="form-control rounded-pill" required>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input toggle-drop" type="checkbox" name="drop" id="edit_drop"
                                       value="1">
                                <label class="form-check-label small fw-bold" for="edit_drop">دارای زیرمنو
                                    (Dropdown)</label>
                            </div>
                        </div>
                        <div class="mb-3 url-field">
                            <label class="form-label small fw-bold">لینک (URL)</label>
                            <input type="text" name="url" id="edit_url" class="form-control rounded-pill" dir="ltr">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="new_tab" id="edit_tab" value="1">
                                <label class="form-check-label small fw-bold" for="edit_tab">باز شدن در تب جدید</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">انصراف
                        </button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">به‌روزرسانی</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        /* رفع رنگ زرد بدشکل در هنگام Autofill مرورگر */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 30px white inset !important;
            -webkit-text-fill-color: #334155 !important;
        }

        .bg-light-custom {
            background-color: #f8fafc;
            border-radius: 20px;
            padding: 30px !important;
        }

        /* کانتینر اصلی Nestable */
        .dd { max-width: 100% !important; }
        .dd-list { list-style: none; margin: 0; padding: 0; }
        .dd-list .dd-list { padding-right: 45px; padding-left: 0; } /* مخصوص RTL */

        /* استایل کارت‌های هر ردیف */
        .dd-content-wrapper {
            display: flex;
            align-items: center;
            background: #ffffff;
            margin-bottom: 15px;
            border-radius: 16px;
            border: 1px solid #f1f5f9;
            overflow: hidden;
            min-height: 60px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .dd-content-wrapper:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            border-color: #e2e8f0;
        }

        /* متمایز کردن فرزندان */
        .child-node {
            background-color: #fcfdfe;
            border-style: dashed;
            border-color: #cbd5e1;
        }

        /* بخش هندل جابجایی */
        .dd-handle-custom {
            padding: 10px 20px;
            background: #f8fafc;
            border-left: 1px solid #f1f5f9;
            cursor: grab;
            color: #94a3b8;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .dd-content-wrapper:hover .dd-handle-custom {
            background: #eff6ff;
            color: #1271fd;
        }

        /* بدنه محتوا */
        .dd-content-body {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            padding: 0 20px;
        }

        .dd-content-body span {
            font-size: 14px;
            color: #1e293b;
            letter-spacing: -0.2px;
        }

        .child-node span {
            font-size: 13px;
            color: #475569;
        }

        /* دکمه‌های عملیاتی */
        .action-btns .btn {
            width: 35px;
            height: 35px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            border: none;
            background: transparent;
        }

        .edit-menu-btn { color: #0ea5e9 !important; }
        .edit-menu-btn:hover { background: #f0f9ff !important; }
        .delete-menu { color: #f43f5e !important; }
        .delete-menu:hover { background: #fff1f2 !important; }

        .system-badge {
            font-size: 10px;
            background: #f1f5f9;
            color: #64748b;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 500;
        }

        /* --- استایل مدرن دکمه‌های باز و بسته (+ / -) --- */
        .dd-item > button {
            display: block;
            position: absolute;
            right: 12px;
            top: 15px;
            width: 32px;
            height: 32px;
            margin: 0;
            padding: 0;
            cursor: pointer;
            border: none;
            background: #ffffff;
            color: #1271fd !important;
            border-radius: 50% !important; /* دایره کامل */
            display: flex !important;
            align-items: center;
            justify-content: center;
            z-index: 25;
            box-shadow: 0 4px 10px rgba(18, 113, 253, 0.12);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid #f1f5f9;
        }

        /* آیکون فلش در حالت بسته (Expand) */
        .dd-item > button[data-action="expand"]:before {
            content: '\F284'; /* bi-chevron-left (نشان‌دهنده وجود زیرمنو در حالت بسته) */
            font-family: "bootstrap-icons" !important;
            font-size: 14px;
            font-weight: bold;
            transition: transform 0.3s ease;
        }

        /* آیکون فلش در حالت باز (Collapse) */
        .dd-item > button[data-action="collapse"]:before {
            content: '\F282'; /* bi-chevron-down (نشان‌دهنده باز بودن لیست) */
            font-family: "bootstrap-icons" !important;
            font-size: 14px;
            font-weight: bold;
        }

        /* افکت هاور حرفه‌ای */
        .dd-item > button:hover {
            background: #1271fd;
            color: #ffffff !important;
            transform: scale(1.08);
            box-shadow: 0 6px 15px rgba(18, 113, 253, 0.25);
        }

        /* ایجاد انیمیشن ورود دکمه‌ها برای حس Toggle بهتر */
        .dd-expand, .dd-collapse {
            animation: chevron-in 0.25s ease-out;
        }

        @keyframes chevron-in {
            from { opacity: 0; transform: scale(0.5); }
            to { opacity: 1; transform: scale(1); }
        }

        /* اصلاح پدینگ بدنه برای جلوگیری از تداخل با فلش */
        .dd-content-body {
            padding-right: 50px !important;
        }

        /* استایل متمایز برای نودهای فرزند در دیتابیس */
        .child-node {
            border-right: 3px solid #1271fd !important; /* اضافه کردن یک خط آبی کوچک در سمت راست فرزندان */
            background-color: #fcfdfe;
        }
    </style>
@stop

@push('scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nestable2/1.6.0/jquery.nestable.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/nestable2/1.6.0/jquery.nestable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function () {
            // تنظیم Nestable
            $('#nestable').nestable({
                maxDepth: 2,
                handleClass: 'dd-handle-custom'
            });

            // مدیریت چک‌باکس Dropdown در فرم‌ها
            $(document).on('change', '.toggle-drop', function () {
                const form = $(this).closest('form');
                const urlField = form.find('.url-field');
                if ($(this).is(':checked')) {
                    urlField.slideUp();
                    urlField.find('input').val('#');
                } else {
                    urlField.slideDown();
                }
            });

            // حذف با SweetAlert2
            $(document).on('click', '.delete-menu', function () {
                const id = $(this).data('id');
                Swal.fire({
                    title: @json(__('admin.common.delete_confirm_title'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    text: @json(__('admin.common.delete_confirm_text'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: @json(__('admin.common.delete_yes'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    cancelButtonText: @json(__('admin.common.cancel'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/admin/menu/${id}`,
                            type: 'DELETE',
                            data: {_token: "{{ csrf_token() }}"},
                            success: function () {
                                Swal.fire(@json(__('admin.common.deleted'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), '', 'success');
                                $(`li[data-id="${id}"]`).fadeOut(400, function () {
                                    $(this).remove();
                                });
                            },
                            error: function () {
                                Swal.fire(@json(__('admin.common.error'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), @json(__('admin.common.system_item_protected'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'error');
                            }
                        });
                    }
                });
            });

            // ویرایش آیتم
            $(document).on('click', '.edit-menu-btn', function () {
                const data = $(this).data();
                $('#editForm').attr('action', `/admin/menu/${data.id}`);
                $('#edit_name').val(data.name);
                $('#edit_url').val(data.url);
                $('#edit_drop').prop('checked', parseInt(data.drop) === 1).trigger('change');
                $('#edit_tab').prop('checked', parseInt(data.tab) === 1);
                $('#editModal').modal('show');
            });

            // ذخیره خودکار ترتیب
            const Toast = Swal.mixin({
                toast: true, position: 'bottom-start',
                showConfirmButton: false, timer: 1500
            });

            $('#nestable').on('change', function () {
                const data = $('#nestable').nestable('serialize');
                $.post("{{ route('admin.menu.updateOrder') }}", {
                    _token: "{{ csrf_token() }}",
                    nestable_output: JSON.stringify(data)
                }, function () {
                    Toast.fire({icon: 'success', title: @json(__('admin.common.order_saved'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)});
                });
            });
        });
    </script>
@endpush
