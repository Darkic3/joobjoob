@php use Illuminate\Support\Facades\Auth; @endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="theme-color" content="#2a2a2a">
{{--    <link rel="shortcut icon" href="" type="image/x-icon"/>--}}
    <title>@yield('tab-name')</title>
    <meta name="robots" content="noindex nofollow"/>
    <link href="{{asset('assets/admin/css/bootstrap.rtl.min.css')}}" rel="stylesheet"/>
    <link rel="stylesheet" href="{{asset('assets/admin/css/admin.style.min.css?v0.01')}}"/>
    <link rel="stylesheet" href="{{asset('assets/admin/css/app.css?v0.08')}}"/>
    <link rel="stylesheet" href="{{asset('assets/admin/css/bootstrap-datepicker.min.css?v0.0')}}"/>
    <link rel="stylesheet" href="{{asset('assets/admin/css/perfect-scrollbar.css')}}"/>
    <script src="{{asset('assets/admin/js/axios.min.js')}}"></script>
    <script src="{{asset('assets/admin/js/sweetalert.min.js')}}" type="text/javascript"></script>
    <script src="{{asset('assets/admin/validation.js?v0.02')}}"></script>
    <script src="{{asset('assets/admin/js/js-deliver.js')}}"></script>

    <link rel="shortcut icon" href="{{$setting->imageFile('favicon')}}">

    <link rel="stylesheet" href="{{ asset('assets/admin/jalalidatepicker.min/jalalidatepicker.min.css') }}">
{{--    <link href="https://cdn.jsdelivr.net/gh/vazirmatn/Vazirmatn@latest/dist/font-face.css" rel="stylesheet">--}}

    <link href="{{asset('assets/admin/js/sweetalert2.min.css')}}" rel="stylesheet">
    <script src="{{asset('assets/admin/js/sweetalert2.all.min.js')}}"></script>

    <style>
        .pagination {
            justify-content: center;
            margin-top: 0.75rem
        }

        .pagination li:first-child {
            background: unset;
        }

        .pagination li:last-child {
            background: unset;
        }

        .pagination li {
            margin: 4px;
            background: #f0f0f0;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
        }

        .pagination li a {
            color: #000;
        }

        .pagination li.active {
            margin: 4px;
            background: #ff8d81;
            padding: 0.25rem 0.5rem;
            color: #fff;
            border-radius: 4px;
        }

        .pagination li.active a {
            color: #fff;
        }
    </style>
    @stack('styles')
</head>
