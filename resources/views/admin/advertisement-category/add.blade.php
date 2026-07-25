@extends('layouts.admin.master')
@section('tab-name','افزودن دسته آگهی')
@section('content')
    <div class="body d-flex py-3">
        <div class="container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="border-0 mb-4">
                        <div
                            class="card-header py-3 no-bg bg-transparent d-flex align-items-center px-0 justify-content-between border-bottom flex-wrap">
                            <h3 class="fw-bolder mb-0">
                                اضافه کردن دسته بندی آگهی
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="card border p-2">
                <form action="{{route('admin.advertisement-category.store')}}" method="POST"  enctype="multipart/form-data" id="my-main-form">
                    @include('admin.advertisement-category.form')
                </form>
            </div>
        </div>
    </div>
@stop
