<script type="text/javascript">
    function confirmIndex(url) {
        SwalUI.confirm({
            title: @json(__('admin.common.delete_title'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            text: @json(__('admin.common.index_confirm_text'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            icon: 'warning',
            danger: true,
            confirmText: @json(__('admin.common.confirm_continue'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        }).then((confirmed) => {
            if (confirmed) {
                location.href = url;
            }
        });
    }
</script>
