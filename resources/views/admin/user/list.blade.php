@extends('layouts.admin.master')
@section('tab-name','کاربران')
@section('content')
    <div class="body d-flex py-3">
        <div class="container-fluid">
            <div class="page-header">
                <div
                    class="card-header py-3 no-bg bg-transparent border-0 px-0 flex-wrap">
                    <h3 class="fw-bolder ms-1">
                        ادمین ها
                    </h3>
                    <div class="d-flex align-items-center">
                        <a href="{{ route('admin.user.create') }}"
                           class="btn ms-2 my-2 btn-custom-b rounded-custom d-flex align-items-center w-fit">
                            <i class="bi bi-plus-square-dotted d-flex h5 my-0 me-2"></i>
                            افزودن ادمین
                        </a>
                    </div>
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
                                <th class="fw-bolder" style=" width: 20%;">نام کامل</th>
                                <th class="fw-bolder">ایمیل</th>
                                <th class="fw-bolder">موبایل</th>
                                <th class="fw-bolder">سطح (ادمین)</th>
                                <th class="fw-bolder">تاریخ ایجاد</th>
                                <th class="fw-bolder">عملیات</th>
                            </tr>
                            </thead>
                            <tbody class="text-center text-light">
                            @forelse($users as $item)
                                <tr>
                                    <td>
                                        {{ $loop->iteration }}
                                    </td>
                                    <td>
                                        {{ $item->full_name }}
                                    </td>
                                    <td>
                                        {{ $item->email }}
                                    </td>
                                    <td>
                                        {{ $item->phone ?? '-' }}
                                    </td>
                                    <td>
                                        @if($item->is_admin)
                                            <span class="badge" style="background-color: #28a745;">ادمین</span>
                                        @else
                                            <span class="badge" style="background-color: #6c757d;">ادمین</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ show_date($item->created_at, 'Y/m/d') }}
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
{{--                                            here--}}
                                            <a class="list-inline-item btn-custom-b border-0 rounded p-2 m-1"
                                               href="{{ route('admin.user.edit', $item) }}">
                                                <i class="d-flex bi bi-pen fs-6"></i>
                                            </a>

                                            <form action="{{ route('admin.user.destroy', $item) }}" method="POST"
                                                  class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type
                                                        ="submit"
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
                                    <td colspan="7" class="text-center py-3">
                                        هیچ ادمینی یافت نشد.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{$users->links()}}

                </div>
            </div>
        </div>
    </div>
@stop
