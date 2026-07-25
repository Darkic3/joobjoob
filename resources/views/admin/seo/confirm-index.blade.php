<script type="text/javascript">
    function confirmIndex(url) {
        Swal.fire({
            icon: 'warning',
            text: "آیا از تغییر وضعیت تمامی صفحات داخلی از نوایندکس به ایندکس مطمئن هستید؟",
            showCancelButton: true,
            confirmButtonText: 'تایید و ادامه',
            cancelButtonText: 'لغو',
        }).then((result) => {
            if (result.isConfirmed) {
                location.href = url;
            }
        });
    }
</script>
