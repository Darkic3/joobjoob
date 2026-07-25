@extends('layouts.admin.master')

@section('title','سئو')
@section('content')
<div class="body d-flex py-3">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="border-0 mb-4">
                    <div
                        class="card-header py-3 no-bg bg-transparent d-flex align-items-center px-0 justify-content-between border-bottom flex-wrap">
                        <h3 class="fw-bolder mb-0">
                            سئو
                        </h3>
                        <div
                            class="d-flex flex-md-row flex-column align-items-md-center justify-content-md-between w-100">
                            @if($index_count > 0)
                            <a onclick="confirmIndex('{{route('admin.seo.index-all')}}')" href="#"
                                class="btn ms-2 my-2 btn-custom-b rounded-custom d-flex align-items-center w-fit">
                                <i class="bi bi-google d-flex h5 my-0 me-2"></i>
                                ایندکس همگانی
                            </a>

                            @endif
{{--                            @component("admin.components.video-button")--}}
{{--                            @slot("type","seo_settings")--}}
{{--                            @endcomponent--}}
                        </div>
                        <div role="alert" class="alert alert-info d-block w-100 m-0">
                            توجه داشته باشید با نوایندکس کردن صفحه اصلی (/) تمامی صفحات سایت نوایندکس خواهند شد.
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="card-block row">
            <div class="col-sm-12 col-lg-12 col-xl-12">
                <div class="table-responsive">
                    <table id="myDataTable" class="table align-middle table-bordered border-custom table-striped mb-0">
                        <thead class="text-center text-light">
                            <tr>
                                <th class="fw-bolder">
                                    #
                                </th>
                                <th class="fw-bolder">
                                    آدرس صفحه
                                </th>
                                <th class="fw-bolder" style="width:100px">
                                    وضعیت
                                </th>

                                <th class="fw-bolder" style="width:250px">
                                    عملیات
                                </th>
                            </tr>
                        </thead>
                        <tbody class="text-center text-light">
                            @foreach($data as $key=>$row)
                            <tr>
                                <th>
                                    {{$key + 1}}
                                </th>
                                <th>
                                    <a href="{{url(@$row['url'])}}">
                                        <span class="badge bg-white" style="font-size: 11px">
                                            {{$row['url']}}
                                        </span>
                                    </a>
                                </th>
                                <th>
                                    <span class="badge bg-label-{{$row->noindex_status['badge']}}"
                                        style="font-size: 11px">
                                        {{@$row->noindex_status['title']}}
                                    </span>
                                </th>
                                <th>

                                    <div class="btn-group" role="group">
                                        <a data-target="tooltip" title="ویرایش"
                                           class="btn btn-sm btn-custom d-flex align-items-center justify-content-center"
                                           href="{{ route('admin.seo.edit', $row) }}">
                                            ویرایش
                                        </a>
                                    </div>
                                </th>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <br>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')
@include('admin.seo.confirm-index')
@endpush
