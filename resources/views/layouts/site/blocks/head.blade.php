<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    @php
        use App\Models\MetaData;
        $currentPath = request()->path();
        $staticSeo = MetaData::where('url', $currentPath)
            ->orWhere('url', '/' . $currentPath)
            ->first();

        $defaultTitle = $staticSeo->title ?? ($setting_site->title ?? 'شرکت پخش دارویی اکسیر');
        $defaultDesc = $staticSeo->description ?? ($setting_site->description ?? 'شرکت پخش دارویی اکسیر');

        $defaultRobots = isset($staticSeo) && $staticSeo->noIndex == 1 ? 'noindex,nofollow' : 'index,follow';
    @endphp
    <title>
        @yield('seo_title', $defaultTitle)
    </title>
    <meta name="description" content="@yield('seo_desc', $defaultDesc)">

     <meta name="robots" content="@yield('seo_robots', $defaultRobots)"> 
     <meta name="googlebot" content="@yield('seo_robots', $defaultRobots)"> 
    <link rel="canonical" href="{{ url()->current() }}" />
    <meta property="og:locale" content="fa_IR" />
    <meta property="og:title" content="@yield('seo_title', $defaultTitle)" />
    <meta property="og:description" content="@yield('seo_desc', $defaultDesc)" />
    <meta property="og:url" content="{{ url()->current() }}" />

    <link rel="shortcut icon" href="{{ $setting->imageFile('favicon') }}">


    <link rel="stylesheet" href="{{ asset('assets/site/css/swiper-bundle.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/site/css/bootstrap.rtl.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/site/css/bootstrap-icons.min.css') }}" />
    @include('layouts.site.blocks.font')
    <link rel="stylesheet" href="{{ asset('assets/site/css/style.css?v0.101') }}" />
    <link rel="stylesheet" href="{{ asset('assets/site/css/app.css?v0.101') }}" />

    <link href="{{ asset('assets/admin/js/sweetalert2.min.css') }}" rel="stylesheet">
    <script src="{{ asset('assets/admin/js/sweetalert2.all.min.js?v0.101') }}"></script>
    {{-- favicon --}}
    @stack('css')
    {!! @$setting_site->head !!}
</head>
