<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.body.addEventListener('click', function (e) {
            const deleteBtn = e.target.closest('.delete-faq-btn');
            if (deleteBtn) {
                e.preventDefault();
                const faqId = deleteBtn.getAttribute('data-faq-id');
                if (!faqId) {
                    SwalUI.error(@json(__('admin.common.invalid_id'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                    return;
                }

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (!csrfToken) {
                    SwalUI.error(@json(__('admin.common.security_token_missing'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                    return;
                }

                SwalUI.confirm({
                    title: @json(__('admin.common.delete_faq_title'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    text: @json(__('admin.common.delete_faq_text'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    danger: true,
                    confirmText: @json(__('admin.common.delete_yes'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                }).then((confirmed) => {
                    if (confirmed) {
                        fetch(`/admin/faqs/${faqId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            }
                        })
                            .then(response => {
                                if (response.ok) {
                                    return response.json().catch(() => ({}));
                                }
                                throw new Error(@json(__('admin.common.delete_failed'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                            })
                            .then(data => {
                                SwalUI.success(@json(__('admin.common.deleted'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), @json(__('admin.common.deleted_redirecting'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
                                    .then(() => {
                                        if (data.redirect_url) {
                                            window.location.href = data.redirect_url;
                                        } else {
                                            window.history.back();
                                        }
                                    });
                            })
                            .catch(error => {
                                console.error(@json(__('admin.common.delete_failed'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), error);
                                SwalUI.error(@json(__('admin.common.error'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), @json(__('admin.common.delete_failed'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                            });
                    }
                });
            }
        });
    });
</script>
