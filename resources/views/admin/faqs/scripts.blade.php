<script>
    document.addEventListener('DOMContentLoaded', function () {
        function initCharacterCounters(dataId) {
            const questionInput = document.querySelector(`#faqForm${dataId} input[name="question"]`);
            const answerTextarea = document.querySelector(`#faqForm${dataId} textarea[name="answer"]`);
            const questionCounter = document.getElementById(`faqQuestionCounter${dataId}`);
            const answerCounter = document.getElementById(`faqAnswerCounter${dataId}`);

            if (questionInput && questionCounter) {
                const updateQuestionCounter = () => {
                    const len = questionInput.value.length;
                    questionCounter.querySelector('.current').textContent = len;
                    questionCounter.style.color = len > 450 ? '#dc3545' : '#6c757d';
                };
                questionInput.addEventListener('input', updateQuestionCounter);
                updateQuestionCounter();
            }

            if (answerTextarea && answerCounter) {
                const updateAnswerCounter = () => {
                    const len = answerTextarea.value.length;
                    answerCounter.querySelector('.current').textContent = len;
                    answerCounter.style.color = len > 1800 ? '#dc3545' : '#6c757d';
                };
                answerTextarea.addEventListener('input', updateAnswerCounter);
                updateAnswerCounter();
            }
        }

        window.loadFaqForEdit = function(faqId, dataId) {
            location.reload();
        };

        window.resetFaqForm = function(dataId) {
            location.reload();
        };

        window.confirmFaqDelete = function(faqId, dataId) {
            Swal.fire({
                icon: 'warning',
                text: 'آیا از حذف این سوال پرتکرار اطمینان دارید؟',
                showCancelButton: true,
                confirmButtonText: 'بله، حذف شود',
                cancelButtonText: 'لغو',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/admin/faqs/${faqId}`;

                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    const method = document.createElement('input');
                    method.type = 'hidden';
                    method.name = '_method';
                    method.value = 'DELETE';

                    form.appendChild(csrf);
                    form.appendChild(method);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        };

        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('shown.bs.modal', function () {
                const dataId = this.id.match(/FaqModal(\d+)/)?.[1];
                if (dataId) initCharacterCounters(dataId);
            });
        });
    });
</script>
