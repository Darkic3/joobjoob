<!doctype html>
<html class="no-js" lang="fa" dir="rtl">

@include('layouts.admin.blocks.head')
<body class="rtl_mode" style>
<style>
    .swal2-container {
        z-index: 11111111111 !important;
    }
</style>
@include('layouts.admin.blocks.massage-swal')
<div id="ebazar-layout" class="theme-blue">
    @include('layouts.admin.blocks.sidebar')
    {{--    TODO: Menu , Sidebar and everything view in admin should be active --}}

    <div class="main px-lg-4 px-md-4">
        @include('layouts.admin.blocks.menu_mobile')

        @include('layouts.admin.blocks.header')
        @yield('content')
    </div>
</div>
@include('layouts.admin.blocks.script')
</body>
</html>
