<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.body.addEventListener('click', function (e) {
            const deleteBtn = e.target.closest('.delete-faq-btn');
            if (deleteBtn) {
                e.preventDefault();
                const faqId = deleteBtn.getAttribute('data-faq-id');
                if (!faqId) {
                    alert('شناسه سوال یافت نشد!');
                    return;
                }

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (!csrfToken) {
                    alert('توکن امنیتی یافت نشد!');
                    return;
                }

                Swal.fire({
                    icon: 'warning',
                    title: 'حذف سوال',
                    text: 'آیا از حذف این سوال پرتکرار اطمینان دارید؟',
                    showCancelButton: true,
                    confirmButtonText: 'بله، حذف شود',
                    cancelButtonText: 'لغو',
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    reverseButtons: true,
                    focusCancel: true
                }).then((result) => {
                    if (result.isConfirmed) {
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
                                throw new Error('پاسخ ناموفق از سرور');
                            })
                            .then(data => {
                                Swal.fire('حذف شد!', 'در حال بازگشت...', 'success')
                                    .then(data => {
                                        if (data.redirect_url) {
                                            window.location.href = data.redirect_url;
                                        } else {
                                            window.history.back();
                                        }
                                    });
                            })
                            .catch(error => {
                                console.error('خطا در حذف:', error);
                                Swal.fire('خطا!', 'حذف با خطا مواجه شد.', 'error');
                            });
                    }
                });
            }
        });
    });
</script>
