@extends('layouts.admin.master')
@section('tab-name','شعبه ها')
@section('content')
    <div class="body d-flex py-3">
        <div class="container-fluid">
            <div class="page-header">
                <div class="card-header py-3 no-bg bg-transparent border-0 px-0 flex-wrap">
                    <h3 class="fw-bolder ms-1">
                        شعبه‌ها
                    </h3>
                    <div class="d-flex align-items-center">
                        {{-- جستجوی ساده (بدون فیلترهای بلاگ، چون شعبه آن‌ها را ندارد) --}}
                        <form action="{{ route('admin.branches.index') }}" method="GET" class="d-flex me-3">
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="جستجو در عنوان یا مدیر..."
                                   class="form-control bg-light rounded-custom me-2" style="width: 200px;">
                            <button type="submit" class="btn btn-custom rounded-custom">جستجو</button>
                        </form>

                        <a href="{{ route('admin.branches.create') }}"
                           class="btn ms-2 my-2 btn-custom-b rounded-custom d-flex align-items-center w-fit">
                            <i class="bi bi-plus-square-dotted d-flex h5 my-0 me-2"></i>
                            افزودن شعبه
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <div class="card-block row">
                <div class="col-sm-12">
                    <div class="table-responsive">
                        <table id="myDataTable" class="table align-middle border-custom mb-0">
                            <thead class="text-center text-light">
                            <tr>
                                <th class="fw-bolder">#</th>
                                <th class="fw-bolder">استان</th>
                                <th class="fw-bolder" style="width: 15%;">عنوان</th>
                                <th class="fw-bolder">مدیر</th>
                                <th class="fw-bolder">تلفن ۱</th>
                                <th class="fw-bolder">آدرس</th>
                                <th class="fw-bolder">عملیات</th>
                            </tr>
                            </thead>
                            <tbody class="text-center text-light">
                            @forelse($branches as $branch)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        @if($branch->state)
                                            <span class="badge bg-primary">{{ $branch->state->name }}</span>
                                        @else
                                            <span class="badge bg-secondary">بدون استان</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ Str::limit($branch->title, 60, '...') }}
                                    </td>
                                    <td>{{ $branch->manager }}</td>
                                    <td>{{ $branch->phone_one ?? '—' }}</td>
                                    <td>{{ Str::limit($branch->address, 30, '...') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a class="list-inline-item btn-custom-b border-0 rounded p-2 m-1"
                                               href="{{ route('admin.branches.edit', $branch) }}">
                                                <i class="d-flex bi bi-pen fs-6"></i>
                                            </a>

                                            <form action="{{ route('admin.branches.destroy', $branch) }}" method="POST"
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
                                    <td colspan="7" class="text-center py-3">
                                        هیچ شعبه‌ای یافت نشد.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $branches->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
@stop
