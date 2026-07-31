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

    // Prepare Slug
    document.addEventListener('DOMContentLoaded', function() {
        const slugInputs = document.querySelectorAll('input[data-auto-slug]');

        slugInputs.forEach(input => {
            let isManualSlug = input.value !== '';

            input.addEventListener('input', function(e) {
                let value = e.target.value;

                // اگر کاربر چیزی تایپ کرد، حالت دستی فعال شود
                if (value !== '') {
                    isManualSlug = true;
                }

                // ذخیره موقعیت مکان‌نما برای جلوگیری از پریدن به انتهای متن
                const start = e.target.selectionStart;
                const end = e.target.selectionEnd;

                // 1. تبدیل هر نوع فاصله (فاصله معمولی، نیم‌فاصله، تب) به خط تیره
                value = value.replace(/\s+/g, '-');

                // 2. تبدیل حروف انگلیسی به کوچک
                value = value.toLowerCase();

                // 3. جلوگیری از ایجاد چندین خط تیره پشت سر هم
                value = value.replace(/-+/g, '-');

                // 4. حذف کاراکترهای غیرمجاز
                // (فقط حروف فارسی، انگلیسی، اعداد، خط تیره (-) و آندرلاین (_) مجاز هستند)
                value = value.replace(/[^a-z0-9_\u0600-\u06FF\-]/g, '');

                // اعمال مقدار جدید
                e.target.value = value;

                // بازگرداندن مکان‌نما به جایگاه درست (با در نظر گرفتن تغییرات طول رشته)
                const newLength = value.length;
                const oldLength = e.target.value.length; // قبل از تخصیص
                // یک تعدیل ساده برای زمانی که کاراکتری حذف شده است
                const offset = Math.max(0, start - (oldLength - newLength));
                e.target.setSelectionRange(offset, offset);
            });

            // 5. تمیزکاری نهایی: فقط زمانی که کاربر از فیلد خارج شد (Blur)
            // خط تیره‌های اضافی در ابتدا و انتهای متن را حذف می‌کند
            input.addEventListener('blur', function(e) {
                let value = e.target.value;
                value = value.replace(/^-+|-+$/g, '');
                e.target.value = value;
            });

            // تولید خودکار اسلاگ از روی عنوان (فقط اگر کاربر دستی چیزی ننوشته باشد)
            const titleInput = document.querySelector('input[name="title"]');
            if (titleInput) {
                titleInput.addEventListener('input', function() {
                    if (!isManualSlug) {
                        input.value = titleInput.value;
                        input.dispatchEvent(new Event('input'));
                    }
                });
            }

            // مدیریت هوشمند حالت دستی: اگر فیلد خالی شد، دوباره حالت خودکار فعال شود
            input.addEventListener('focus', function() {
                if (input.value === '') {
                    isManualSlug = false;
                } else {
                    isManualSlug = true;
                }
            });
        });
    });


</script>
