{{--
    SwalUI — تزریق رشته‌های ترجمهٔ دیالوگ‌ها به window.SwalUI_I18N
    این اسکریپت باید پیش از اجرای ماژول resources/js/swal-ui.js اجرا شود،
    بنابراین در انتهای <body> قرار می‌گیرد (ماژول‌های Vite به‌صورت deferred اجرا می‌شوند).
--}}
@once
    @if(!isset($swalUiStrings))
        @php($swalUiStrings = \Illuminate\Support\Facades\Lang::get('swal', [], $currentLocale ?? app()->getLocale()))
    @endif
    <script>
        window.SwalUI_I18N = @json($swalUiStrings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    </script>
@endonce