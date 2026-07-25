@extends('layouts.admin.master')
@section('content')
    <div class="body d-flex py-3">
        <div class="container-fluid">
            <div class="page-header">
                <div class="card-header py-3 no-bg bg-transparent d-flex align-items-center px-0">
                    <h3 class="fw-bolder mb-0">
                        ویرایش سوال: «{{ Str::limit($faq->question, 30) }}»
                    </h3>
                    <a href="{{ url()->previous() }}" class="btn btn-outline-light rounded-custom me-2">
                        <i class="bi bi-arrow-right me-1"></i> بازگشت
                    </a>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="card border p-3">
                <form action="{{ route('admin.faqs.update', $faq) }}" method="POST">
                    <meta name="csrf-token" content="{{ csrf_token() }}">
                    @method('PUT')
                    @include('admin.faqs._faq-form', ['data' => $item, 'faq' => $faq])
                </form>
            </div>
        </div>
    </div>


    @push('scripts')
        @include('admin.faqs._delete-script')
    @endpush
@stop
