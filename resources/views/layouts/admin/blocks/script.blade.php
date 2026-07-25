<script src="{{asset('assets/admin/js/jquery-3.3.1.min.js')}}"></script>
<script src="{{asset('assets/admin/js/libscripts.bundle.js')}}"></script>
<script src="{{asset('assets/admin/js/template.js')}}"></script>
<script src="{{asset('assets/admin/js/perfect-scrollbar.min.js')}}"></script>
<script src="{{asset('assets/admin/js/script.min.js')}}"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="{{asset('assets/admin/ckeditor/ckeditor.js')}}"></script>
<script>
    var cks = document.getElementsByClassName('ckeditor');
    Array.from(cks).forEach((el) => {
        CKEDITOR.replace(el, {
            language: 'fa',
            allowedContent: true, // اجازه می‌دهد تمام محتوای HTML مجاز باشد
            removePlugins: ',flash,smiley,iframe,forms,table,templates,pagebreak,anchor', // حذف پلاگین‌های اضافی از جمله فونت و تغییر رنگ
            filebrowserUploadUrl: "{{route('admin.ckeditor.upload', ['_token' => csrf_token() ])}}",
            filebrowserUploadMethod: 'form',
            contentsLangDirection: 'rtl'
        });
    });

</script>
<script>
    var cks = document.getElementsByClassName('ckeditor');
    Array.from(cks).forEach((el) => {
        // console.log(el)
        CKEDITOR.replace(el, {
            language: 'fa',
            content: 'fa',
            filebrowserUploadMethod: 'form'
        });
    });
    $(document).ready(function () {
        // Prevent alert from closing dropdown
        $('.alert-dismissible .btn-close').click(function (event) {
            $(this).closest('.alert-dismissible').alert('close');
            event.stopPropagation();
        });
    });

    function dropClose() {
        const alerts = document.getElementById('drop-alert');
        alerts.classList.remove('show')
    }

    function dropClose2() {
        const alerts2 = document.getElementById('drop-alert2');
        alerts2.classList.remove('show')
    }

    const input = document.getElementById('openMenu');
    const div = document.getElementById('sidebar');
    let titleElements = document.querySelectorAll('.sidebar .title');
    input.addEventListener('click', () => {
        if (input.classList.contains('bi-record-circle')) {
            input.classList.remove('bi-record-circle');
            input.classList.add('bi-circle');
            div.style.width = '100px';
            for (const titleElement of titleElements) {
                titleElement.style.display = 'none';
            }
            input.classList.remove('d-flex');
            input.classList.add('d-none');

        } else if (input.classList.contains('bi-circle')) {
            input.classList.remove('bi-circle');
            input.classList.add('bi-record-circle');
            div.style.width = '260px';
            for (const titleElement of titleElements) {
                titleElement.style.display = 'flex';
            }
            input.classList.remove('d-none');
            input.classList.add('d-flex');
        }
    });
    div.addEventListener('mouseout', () => {
        if (input.classList.contains('bi-circle')) {
            div.style.width = '100px';
            for (const titleElement of titleElements) {
                titleElement.style.display = 'none';
            }
            input.classList.remove('d-flex');
            input.classList.add('d-none');
        }
    });
    div.addEventListener('mouseover', () => {
        if (input.classList.contains('bi-circle')) {
            div.style.width = '260px';
            for (const titleElement of titleElements) {
                titleElement.style.display = 'flex';
            }
            input.classList.remove('d-none');
            input.classList.add('d-flex');
        }
    });
</script>
<script src="{{asset('assets/admin/js/chart.js')}}"></script>
<script>
    const xValues = [0, 5, 10, 15, 20, 25, 30];
    const yValues = [0, 2000000, 1500000, 2500000, 1000000, 3500000, 3000000];
    const myChart = new Chart("myChart", {
        type: "line",
        data: {
            labels: xValues,
            datasets: [{
                // Specify gradient using Chart.js's helper function:
                backgroundColor: "rgba(115,103,240,0.42)",
                borderColor: "#7367f0",
                data: yValues
            }]
        },
        options: {
            legend: {display: false}
        }
    });

    const xValues2 = [0, 5, 10, 15, 20, 25, 30];
    const yValues2 = [0, 1500000, 3500000, 3000000, 2000000, 1200000, 2000000];
    const myChart2 = new Chart("myChart2", {
        type: "line",
        data: {
            labels: xValues2,
            datasets: [{
                // Specify gradient using Chart.js's helper function:
                backgroundColor: "rgba(0,207,232,0.42)",
                borderColor: "#00cfe8",
                data: yValues2
            }]
        },
        options: {
            legend: {display: false},
        }
    });
</script>

{{-- my custom scripts--}}
<script src="{{asset('assets/admin/jalalidatepicker.min/jalalidatepicker.min.js')}}"></script>
<script>
    jalaliDatepicker.startWatch({
        minDate: "attr",
        maxDate: "attr"
    });
</script>

<script src="{{asset('assets/admin/js/my-form-validation.js')}}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.body.addEventListener('submit', function(e) {
            const form = e.target.closest('.delete-form');
            if (form) {
                e.preventDefault();
                Swal.fire({
                    title: 'آیا مطمئن هستید؟',
                    text: "این عمل قابل بازگشت نخواهد بود!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'بله، حذف کن!',
                    cancelButtonText: 'انصراف'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }
        });
    });
</script>

@stack('modals')
@stack('scripts')
