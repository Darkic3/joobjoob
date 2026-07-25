@extends('layouts.admin.master')
@section('tab-name', 'ویرایش : ' . $item->title)
@section('content')
    <div class="body d-flex py-3">
        <div class="container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="border-0 mb-4">
                        <div class="card-header py-3 no-bg bg-transparent d-flex align-items-center px-0 justify-content-between border-bottom flex-wrap">
                            <h3 class="fw-bolder mb-0">
                                ویرایش اطلاعات تماس
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="card border p-2">
                <form action="{{route('admin.contact_infos.update', $item->id)}}" method="POST" id="my-main-form">
                    @method('PUT')
                    @include('admin.contact_info.form')
                </form>
            </div>
        </div>
    </div>
@stop
