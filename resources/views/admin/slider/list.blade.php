@extends('layouts.admin.master')

@section('content')
    <div class="body d-flex py-3">
        <div class="container-fluid">
            <div class="page-header">
                <div
                    class="card-header py-3 no-bg bg-transparent border-0 px-0 flex-wrap">
                    <h3 class="fw-bolder ms-1">
                        اسلایدرها
                    </h3>
                    <div class="d-flex align-items-center">
                        @include('admin.search-modal.search', [
                            'route' => route('admin.sliders.index'),
                            'hasTitle' => true,
                            'hasUrl' => false,
                            'hasStatus' => true,
                            'hasDate' => false,
                            'hasShowInFirstPage' => false,
                        ])
                        <a href="{{ route('admin.sliders.create') }}"
                           class="btn ms-2 my-2 btn-custom-b rounded-custom d-flex align-items-center w-fit">
                            <i class="bi bi-plus-square-dotted d-flex h5 my-0 me-2"></i>
                            افزودن اسلایدر
                        </a>
                    </div>
                    @if($activeCounts > 5)
                        <span class="text-danger small font-weight-bold">
                            <i class="fa fa-exclamation-triangle"></i>
                            هشدار: بیش از ۵ اسلایدر فعال است. فقط ۵ مورد اخیر نمایش داده می‌شوند؛ لطفاً موارد اضافی را غیرفعال کنید.
                        </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="card-block row">
                <div class="col-sm-12 col-lg-12 col-xl-12">
                    <div class="table-responsive d-flex align-items-center">
                        <table id="myDataTable"
                               class="table align-middle border-custom mb-0">
                            <thead class="text-center text-light">
                            <tr>
                                <th class="fw-bolder">#</th>
                                <th class="fw-bolder">تصویر</th>
                                <th class="fw-bolder">وضعیت</th>
                                <th class="fw-bolder">تاریخ ایجاد</th>
                                <th class="fw-bolder">عملیات</th>
                            </tr>
                            </thead>
                            <tbody class="text-center text-light">
                            @forelse($sliders as $item)
                                <tr>
                                    <td>
                                        {{ $loop->iteration }}
                                    </td>
                                    <td>
                                        <img src="{{ $item->getImageFile()}}"
                                             alt="{{ $item->title }}"
                                             style="width: 100px; height: 60px; object-fit: cover; border-radius: 5px;">
                                    </td>
                                    <td>
                                        @if($item->is_active)
                                            <span class="badge" style="background-color: #28a745;">فعال</span>
                                        @else
                                            <span class="badge" style="background-color: #dc3545;">غیرفعال</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ jdate_format('Y/m/d', strtotime($item->created_at)) }}
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a class="list-inline-item btn-custom-b border-0 rounded p-2 m-1"
                                               href="{{ route('admin.sliders.edit', $item) }}">
                                                <i class="d-flex bi bi-pen fs-6"></i>
                                            </a>

                                            <form action="{{ route('admin.sliders.destroy', $item) }}" method="POST"
                                                  class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="list-inline-item btn-custom-b border-0 rounded p-2 m-1"
                                                        style="background-color: transparent; cursor: pointer;">
                                                    <i class="d-flex bi bi-trash fs-6"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-3">
                                        هیچ اسلایدری یافت نشد.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $sliders->links() }}

                </div>
            </div>
        </div>
    </div>
@stop
