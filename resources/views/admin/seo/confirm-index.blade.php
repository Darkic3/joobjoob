<script type="text/javascript">
    function confirmIndex(url) {
        Swal.fire({
            icon: 'warning',
            text: @json(__('admin.common.index_confirm_text'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            showCancelButton: true,
            confirmButtonText: @json(__('admin.common.confirm_continue'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            cancelButtonText: @json(__('admin.common.cancel'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        }).then((result) => {
            if (result.isConfirmed) {
                location.href = url;
            }
        });
    }
</script>
