@extends('layouts.admin.master')
@section('tab-name', 'سطح دسترسی')
@section('role','active ')
@section('role-list','active ')
@section('role-collapse','show ')

@section('content')

        <div class="body d-flex py-3">
            <div class="container-fluid">
                <div class="page-header">
                    <div class="row align-items-center">
                        <div class="border-0 mb-4">
                            <div
                                class="card-header py-3 no-bg bg-transparent d-flex align-items-center px-0 justify-content-between border-bottom flex-wrap">
                                <h3 class="fw-bolder mb-0">لیست سطح دسترسی</h3>
                                <div class="d-flex align-items-center">
                                    <a href="{{route('admin.permissions.create')}}"
                                       class="btn ms-2 my-2 btn-custom-b rounded-custom d-flex align-items-center">
                                        <i class="bi bi-plus-square-dotted d-flex h5 my-0 me-2"></i>
                                        افزودن سطح دسترسی
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @if(isset($roles) && count($roles)>0)
            <div class="container-fluid">
                <div class="card-block row">
                    <div class="col-sm-12">
                        <div class="table-responsive">
                            <table id="myDataTable"
                                   class="table align-middle table-bordered border-custom table-striped mb-0">
                                <thead class="text-center text-light">
                                <tr>
                                    <th class="fw-bolder" style="width: 75px">#</th>
                                    <th class="fw-bolder">عنوان</th>
                                    <th class="fw-bolder" style="width:200px">عملیات</th>
                                </tr>
                                </thead>
                                @foreach($roles as $key => $role)
                                    <tbody class="text-center text-light">
                                    <tr>
                                        <th>{{$key+1}}</th>
                                        <th><span class="badge bg-white"
                                                  style="font-size: 11px">{{$role->role}}</span></th>
                                        <th>
                                            <div class="btn-group" role="group">
                                                <a type="button" class="btn btn-primary"
                                                   href="{{route('admin.permissions.edit',$role)}}">
                                                    <i class="bi bi-pencil-fill d-flex text-white"></i>
                                                </a>

                                                <form action="{{ route('admin.permissions.destroy', $role) }}"
                                                      method="POST" id="delete-form-permission-{{ $role->id }}"
                                                      class="d-inline" data-item-id="{{ $role->id }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-danger delete-btn">
                                                        <i class="bi bi-trash-fill d-flex text-white"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </th>
                                    </tr>
                                    </tbody>
                                @endforeach
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @else
                <div class="container-fluid">
                    <br><br>
                    <div class="alert alert-dark" role="alert">هیچ موردی یافت نشد !</div>
                </div>
            @endif
        </div>
@endsection
