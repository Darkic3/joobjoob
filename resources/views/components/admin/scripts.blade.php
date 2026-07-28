<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (document.querySelector('.ckeditor')) {
            document.querySelectorAll('.ckeditor').forEach(function (el) {
                CKEDITOR.replace(el, {
                    language: 'fa',
                    contentsLangDirection: 'rtl',
                    allowedContent: true,
                    removePlugins: 'flash,smiley,iframe,forms,table,templates,pagebreak,anchor',
                    filebrowserUploadUrl: '{{ route("admin.ckeditor.upload", ["_token" => csrf_token()]) }}',
                    filebrowserUploadMethod: 'form'
                });
            });
        }

        if (typeof jalaliDatepicker !== 'undefined') {
            jalaliDatepicker.startWatch({minDate: 'attr', maxDate: 'attr'});
        }

        if (typeof Chart !== 'undefined') {
            var chartEls = document.querySelectorAll('[data-chart]');
            chartEls.forEach(function (el) {
                var config = JSON.parse(el.getAttribute('data-chart'));
                new Chart(el, config);
            });
        }

        document.addEventListener('submit', function (e) {
            var form = e.target.closest('.delete-form');
            if (form && typeof Swal !== 'undefined') {
                e.preventDefault();
                Swal.fire({
                    title: 'آیا مطمئن هستید؟',
                    text: 'این عمل قابل بازگشت نخواهد بود!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'بله، حذف کن!',
                    cancelButtonText: 'انصراف'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        // CKEditor
        if (typeof CKEDITOR !== 'undefined' && document.querySelectorAll('.ckeditor').length > 0) {
            document.querySelectorAll('.ckeditor').forEach(function (el) {
                let uploadUrl = '';
                @if(Route::has('admin.ckeditor.upload'))
                    uploadUrl = '{{ route("admin.ckeditor.upload", ["_token" => csrf_token()]) }}';
                @endif

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

    });
</script>
