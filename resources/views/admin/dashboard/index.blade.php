@extends('layouts.admin.master')
@section('tab-name','داشبورد')
@section('content')
    <div class="body d-flex py-3">
        <div class="px-md-5 px-1 py-md-3 py-2 w-100">
            <div class="container text-center">

                <div class="row w-100 m-0 px-0 dash-content mb-4">
                    <div class="w-100 d-flex align-items-center justify-content-between mb-2">
                        <p class="text-start fs-5 text-nowrap me-2 mb-0" style="color: #6f6b7d">
                            📊 آمار سیستم
                        </p>
                        <hr class="w-100">
                    </div>
                    <div class="col-xxl-2 col-lg-3 col-sm-4 col-6 p-md-2 p-1">
                        <a href="{{ route('admin.user.index') }}"
                           class="dashbtn stat-box d-flex flex-column align-items-start justify-content-between p-3 h-100">
                            <i class="p-2 rounded bi bi-people fs-3 mb-2" style="color: #6f6b7d;"></i>
                            <span class="fs-4 fw-bold" style="color: #5d596c;">{{ $content['totalUsers'] }}</span>
                            <span class="title"> ادمین ها</span>
                        </a>
                    </div>
                    <div class="col-xxl-2 col-lg-3 col-sm-4 col-6 p-md-2 p-1">
                        <a href="{{ route('admin.blogs.index') }}"
                           class="dashbtn stat-box d-flex flex-column align-items-start justify-content-between p-3 h-100">
                            <i class="p-2 rounded bi bi-journal-text fs-3 mb-2" style="color: #6f6b7d;"></i>
                            <span class="fs-4 fw-bold" style="color: #5d596c;">{{ $content['totalBlogs'] }}</span>
                            <span class="title">مقاله فعال</span>
                        </a>
                    </div>
                    <div class="col-xxl-2 col-lg-3 col-sm-4 col-6 p-md-2 p-1">
                        <a href="{{ route('admin.advertisement.index') }}"
                           class="dashbtn stat-box d-flex flex-column align-items-start justify-content-between p-3 h-100">
                            <i class="p-2 rounded bi bi-megaphone fs-3 mb-2" style="color: #6f6b7d;"></i>
                            <span class="fs-4 fw-bold" style="color: #5d596c;">{{ $content['totalAdvertisements'] }}</span>
                            <span class="title">آگهی فعال</span>
                        </a>
                    </div>
                    <div class="col-xxl-2 col-lg-3 col-sm-4 col-6 p-md-2 p-1">
                        <a href="{{ route('admin.suppliers.index') }}"
                           class="dashbtn stat-box d-flex flex-column align-items-start justify-content-between p-3 h-100">
                            <i class="p-2 rounded bi bi-truck fs-3 mb-2" style="color: #6f6b7d;"></i>
                            <span class="fs-4 fw-bold" style="color: #5d596c;">{{ $content['totalSuppliers'] }}</span>
                            <span class="title">تامین کننده</span>
                        </a>
                    </div>
                    <div class="col-xxl-2 col-lg-3 col-sm-4 col-6 p-md-2 p-1">
                        <a href="{{ route('admin.news.index') }}"
                           class="dashbtn stat-box d-flex flex-column align-items-start justify-content-between p-3 h-100">
                            <i class="p-2 rounded bi bi-newspaper fs-3 mb-2" style="color: #6f6b7d;"></i>
                            <span class="fs-4 fw-bold" style="color: #5d596c;">{{ $content['totalNews'] }}</span>
                            <span class="title">اخبار منتشر شده</span>
                        </a>
                    </div>
                    <div class="col-xxl-2 col-lg-3 col-sm-4 col-6 p-md-2 p-1">
                        <a href="{{ route('admin.permissions.index') }}"
                           class="dashbtn stat-box d-flex flex-column align-items-start justify-content-between p-3 h-100">
                            <i class="p-2 rounded bi bi-file-lock fs-3 mb-2" style="color: #6f6b7d;"></i>
                            <span class="fs-4 fw-bold" style="color: #5d596c;">{{ $content['totalRoles'] }}</span>
                            <span class="title">پرمیشن ها</span>
                        </a>
                    </div>

                </div>
                <div class="row w-100 m-0 px-0 dash-content">
                    <div class="w-100 d-flex align-items-center justify-content-between mb-2">
                        <p class="text-start fs-5 text-nowrap me-2 mb-0" style="color: #6f6b7d">
                            ⚡️ دسترسی سریع
                        </p>
                        <hr class="w-100">
                    </div>
                    <div class="dash-box col-xxl-2 col-lg-3 col-sm-4 col-6 p-md-2 p-1">
                        <a href="{{route('admin.blogs.index')}}"
                           class="dashbtn d-flex align-items-center justify-content-between p-3">
                            مقالات
                            <i class="p-2 rounded bi bi-journal-text d-flex "></i>
                        </a>
                    </div>
                    <div class="dash-box col-xxl-2 col-lg-3 col-sm-4 col-6 p-md-2 p-1">
                        <a href="{{route('admin.sliders.index')}}"
                           class="dashbtn d-flex align-items-center justify-content-between p-3">
                            اسلایدرها
                            <i class=" p-2 rounded bi bi-images d-flex "></i>
                        </a>
                    </div>
                    <div class="dash-box col-xxl-2 col-lg-3 col-sm-4 col-6 p-md-2 p-1">
                        <a href="{{route('admin.settings.edit')}}"
                           class="dashbtn d-flex align-items-center justify-content-between p-3">
                            تنظیمات سایت
                            <i class="p-2 rounded bi bi-gear-wide-connected d-flex "></i>
                        </a>
                    </div>
                    <div class="dash-box col-xxl-2 col-lg-3 col-sm-4 col-6 p-md-2 p-1">
                        <a href="{{route('admin.seo.index')}}"
                           class="dashbtn d-flex align-items-center justify-content-between p-3">
                            اطلاعات سئو
                            <i class="p-2 rounded bi bi-google d-flex "></i>
                        </a>
                    </div>
                    <div class="dash-box col-xxl-2 col-lg-3 col-sm-4 col-6 p-md-2 p-1">
                        <a href="{{route('admin.suppliers.index')}}"
                           class="dashbtn d-flex align-items-center justify-content-between p-3">
                            تامین کنندگان
                            <i class="p-2 rounded bi bi-truck d-flex "></i>
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
@stop
