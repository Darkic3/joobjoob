<!DOCTYPE html>
<html lang="fa">
@include('layouts.site.blocks.head')
<body dir="rtl">
<style>
    .swal2-container {
        z-index: 11111111111 !important;
    }
</style>
@include('layouts.admin.blocks.massage-swal')
@include('layouts.site.blocks.menu-desktop')
@include('layouts.site.blocks.menu-mobile')
@yield('content')
@include('layouts.site.blocks.footer')
@include('layouts.site.blocks.script')
</body>
</html>
