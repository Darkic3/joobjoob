@extends('layouts.admin.master')
@section('tab-name','اخبار')
@section('content')
    <div class="body d-flex py-3">
        <div class="container-fluid">
            <div class="page-header">
                <div class="card-header py-3 no-bg bg-transparent border-0 px-0 flex-wrap">
                    <h3 class="fw-bolder ms-1">اخبار</h3>
                    <div class="d-flex align-items-center">
                        @include('admin.search-modal.search', [
                            'route' => route('admin.news.index'),
                            'hasTitle' => true,
                            'hasUrl' => true,
                            'hasStatus' => true,
                            'hasDate' => true,
                            'hasShowInFirstPage' => true,
                            'hasCategory' => false,
                            'categories' => $categories,
                            'categoryRef' => 'category_url'
                        ])
                        <a href="{{ route('admin.news.create') }}"
                           class="btn ms-2 my-2 btn-custom-b rounded-custom d-flex align-items-center w-fit">
                            <i class="bi bi-plus-square-dotted d-flex h5 my-0 me-2"></i>
                            افزودن خبر
                        </a>
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
                                <th class="fw-bolder">تصویر</th>
                                <th class="fw-bolder" style=" width: 20%;">عنوان</th>
                                <th class="fw-bolder">نوع</th>
                                <th class="fw-bolder">وضعیت</th>
                                <th class="fw-bolder">دسته بندی</th>
                                <th class="fw-bolder">تاریخ ایجاد</th>
                                <th class="fw-bolder">عملیات</th>
                            </tr>
                            </thead>
                            <tbody class="text-center text-light">
                            @forelse($items as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <img src="{{ $item->getImageFile() }}" alt="{{ $item->title }}"
                                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                                    </td>
                                    <td>
                                        {{ Str::limit($item->title, 60, '...') }}
                                    </td>
                                    <td>
                                        @if($item->type == 'news')
                                            <span class="badge"
                                                  style="background-color: #2faf53;  font-size:small; color: #0e0e0e;">خبر</span>
                                        @elseif($item->type == 'report')
                                            <span class="badge"
                                                  style="background-color: #ae71d6; font-size:small; color: #0e0e0e;">گزارش تصویری</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($item->is_active)
                                            <span class="badge" style="background-color: #28a745;">فعال</span>
                                        @else
                                            <span class="badge" style="background-color: #dc3545;">غیرفعال</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($item->category)
                                            <span class="badge" style="background-color: #0d6efd;">
                                                    {{ $item->category->title }}
                                                </span>
                                        @else
                                            <span class="badge" style="background-color: #6c757d;">
                                                    بدون دسته‌بندی
                                                </span>
                                        @endif
                                    </td>
                                    <td>{{ jdate_format('Y/m/d', strtotime($item->created_at)) }}</td>
                                    <td>
                                        <div class="btn-group" role="group">

                                            @if($item->type === 'report')
                                                <a href="{{ route('admin.news-images.index', $item) }}"
                                                   class="list-inline-item btn-custom-b border-0 rounded p-2 m-1" title="تصاویر">
                                                    <i class="d-flex bi bi-images fs-6"></i>
                                                </a>
                                            @endif

                                            @include('admin.faqs.form', ['data' => $item])

                                            @include('admin.seo.form', ['data' => $item])

                                            <a class="list-inline-item btn-custom-b border-0 rounded p-2 m-1"
                                               href="{{ route('admin.news.edit', $item->id) }}">
                                                <i class="d-flex bi bi-pen fs-6"></i>
                                            </a>

                                            <form action="{{ route('admin.news.destroy', $item->id) }}" method="POST"
                                                  class="d-inline"
                                                  onsubmit="return confirm('آیا برای حذف این خبر مطمئن هستید؟');">
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
                                    <td colspan="7"> هیچ خبری یافت نشد.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $items->links() }}

                </div>
            </div>
        </div>
    </div>
@stop
