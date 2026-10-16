<script>
    document.addEventListener('DOMContentLoaded', function () {

        {{-- CKEditor محلی پروژه (یک بار) --}}
        if (typeof CKEDITOR !== 'undefined' && document.querySelectorAll('.ckeditor').length > 0) {
            let uploadUrl = '';
            @if(Route::has('admin.ckeditor.upload'))
                uploadUrl = '{{ route("admin.ckeditor.upload", ["_token" => csrf_token()]) }}';
            @endif

            document.querySelectorAll('.ckeditor').forEach(function (el) {
                CKEDITOR.replace(el, {
                    language: 'fa',
                    contentsLangDirection: 'rtl',
                    allowedContent: true,
                    removePlugins: 'flash,smiley,iframe,forms,table,templates,pagebreak,anchor',
                    filebrowserUploadMethod: 'form',
                    height: 400,
                    ...(uploadUrl && {filebrowserUploadUrl: uploadUrl})
                });
            });
        }

        {{-- پیکر تاریخ شمسی + همگام‌سازی خودکار شمسی → میلادی --}}
        if (typeof jalaliDatepicker !== 'undefined') {
            jalaliDatepicker.startWatch({minDate: 'attr', maxDate: 'attr', time: true});

            // اینپوت شمسی (data-jdp) → اینپوت مخفی میلادی (data-target)
            // دیتابیس همیشه میلادی می‌ماند؛ فقط نمایش و ورودی شمسی است.
            if (typeof window.jalaali !== 'undefined') {
                var toEnDigits = function (s) {
                    return String(s).replace(/[۰-۹]/g, function (d) {
                        return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d);
                    });
                };
                var toGregorianValue = function (jalaliStr) {
                    var m = toEnDigits(jalaliStr || '').match(/(\d+)\/(\d+)\/(\d+)(?:\s+(\d+):(\d+))?/);
                    if (!m) return '';
                    var jy = +m[1], jm = +m[2], jd = +m[3];
                    // گارد اعتبارسنجی: تاریخ نامعتبر (مثل ۱۴۰۴/۱۳/۴۰) نباید ذخیره شود
                    if (!window.jalaali.isValidJalaaliDate(jy, jm, jd)) return '';
                    var g = window.jalaali.toGregorian(jy, jm, jd);
                    var pad = function (n) { return String(n).padStart(2, '0'); };
                    return g.gy + '-' + pad(g.gm) + '-' + pad(g.gd) + ' ' + pad(m[4] || 0) + ':' + pad(m[5] || 0);
                };

                document.querySelectorAll('[data-jdp][data-target]').forEach(function (el) {
                    var sync = function () {
                        var hidden = document.getElementById(el.getAttribute('data-target'));
                        if (hidden) hidden.value = toGregorianValue(el.value);
                    };
                    el.addEventListener('change', sync);
                    el.addEventListener('input', sync);
                    sync(); // همگام‌سازی اولیه (پرشدن مقدار هنگام ادیت)
                });
            }
        }

        if (typeof Chart !== 'undefined') {
            var chartEls = document.querySelectorAll('[data-chart]');
            chartEls.forEach(function (el) {
                var config = JSON.parse(el.getAttribute('data-chart'));
                new Chart(el, config);
            });
        }

        {{-- خطاهای اعتبارسنجی (پس از ریدایرکت بک) — نمایش به‌صورت دیالوگ زیبای SwalUI --}}
        @if($errors->any())
            <script>
                window.addEventListener('load', function () {
                    if (!window.SwalUI) return;
                    window.SwalUI.errors(@json(__('admin.common.save_error'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), @json($errors->all(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                });
            </script>
        @endif
    });
</script>
