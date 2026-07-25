<!doctype html>
<html class="no-js" lang="fa">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="theme-color" content="#2a2a2a">

    <link rel="shortcut icon" href="{{ asset('assets/admin/images/fav.jpg') }}" type="image/x-icon" />
    <title>ورود به پنل مدیریت سایت</title>
    <meta name="robots" content=" noindex nofollow" />

    <script src="{{ asset('assets/admin/js/sweetalert.min.js') }}"></script>
    <link href="{{ asset('assets/admin/css/bootstrap.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/admin/css/admin.style.min.css?v0.01') }}" />
    <link rel="stylesheet" href="{{ asset('assets/admin/css/app.css?v0.05') }}" />
    <link rel="stylesheet" href="{{ asset('assets/admin/css/bootstrap-datepicker.min.css?v0.0') }}" />
    <link rel="stylesheet" href="{{ asset('assets/admin/css/perfect-scrollbar.css') }}" />

</head>

<body dir="rtl" class="login-body vh-100">

@if(session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            text: "{{ session('error') }}",
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 5000
        });
    </script>
@endif

@if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            text: "{{ session('success') }}",
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 5000
        });
    </script>
@endif


<div id="ebazar-layout" class="login-page theme-blue vh-100 d-flex align-items-center">
    <div class="col-xl-4 col-lg-5 col-md-6 col-sm-9 col-12 py-4 px-xxl-5 px-xl-4 px-lg-3 px-md-2 px-3 mx-auto">
        <div
            class="card bg-white rounded-custom2 border shadow d-flex flex-column align-items-center justify-content-between px-xxl-5 px-xl-4 px-lg-3 px-md-2 px-3 py-xl-5 py-lg-4 py-md-3 py-4">
            <div class="col-12 px-0 pb-3 text-center">
                <img src="{{ asset('assets/admin/images/rahweb.svg') }}" width="150px">
            </div>

            <form id="cms-form" class="m-0 mx-auto"
                  action="{{ route('admin.logger') }}"
                  method="POST">
                @csrf

                <div class="row w-100 m-0">
                    <div class="col-12 text-center p-2">
                        <h1 class="h4">
                            ورود به پنل
                        </h1>
                    </div>
                    <div class="col-12 p-2">
                        <div class="form-floating">
                            <input type="email" name="email" class="form-control rounded-custom"
                                   id="floatingInput" placeholder="name@example.com" value="{{ old('email') }}">
                            <label for="floatingInput" style="color: #999; text-align: right !important;left: unset !important;right: 0;" >
                                <i class="bi bi-at"></i>
                                آدرس ایمیل
                            </label>
                        </div>
                    </div>
                    <div class="col-12 p-2">
                        <div class="form-floating">
                            <input
                                type="password" name="password" class="form-control rounded-custom"
                                id="floatingInput" placeholder="">
                            <label for="floatingInput" style="color: #999; text-align: right !important;left: unset !important;right: 0;" >
                                <i class="bi bi-shield-lock"></i>
                                رمز عبور
                            </label>
                        </div>
                    </div>
                    <div class="col-12 p-2">
                        <button type="submit"  id="submitFormCms" class="btn btn-lg w-100 btn-custom rounded-custom">
                            ورود
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="{{ asset('assets/admin/js/jquery-3.3.1.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/libscripts.bundle.js') }}"></script>
<script src="{{ asset('assets/admin/js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/bootstrap-datepicker.fa.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/template.js') }}"></script>
<script src="{{ asset('assets/admin/js/perfect-scrollbar.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/script.min.js') }}"></script>
<script src="{{ asset('assets/admin/ckeditor/ckeditor.js') }}"></script>
</body>

</html>
