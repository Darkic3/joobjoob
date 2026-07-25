@extends('layouts.admin.master')
@section('tab-name', 'ویرایش سطح دسترسی')
@section('role-collapse','show ')
@section('role','active ')

@section('content')
        <div class="body d-flex py-3">
            <div class="container-fluid">
                <div class="page-header">
                    <div class="row align-items-center">
                        <div class="border-0 mb-4">
                            <div
                                class="card-header py-3 no-bg bg-transparent d-flex align-items-center px-0 justify-content-between border-bottom flex-wrap">
                                <h3 class="fw-bolder mb-0">
                                    ویرایش سطح دسترسی : {{$role->role}}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container-fluid">
                <div class="card border p-2">
                    <form action="{{route('admin.permissions.update',$role)}}" method="POST">
                        @csrf
                        @method('PUT')
                        @include('admin.permission.form')
                    </form>
                </div>
            </div>
        </div>
@endsection
