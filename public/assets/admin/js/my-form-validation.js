document.addEventListener('DOMContentLoaded', function () {

    const fields = [
        { id: 'title', type: 'input' },
        { id: 'manager', type: 'input' },
        { id: 'state_id', type: 'select' },
        { id: 'url', type: 'input' },
        { id: 'description', type: 'ckeditor' }
    ];

    const form = document.getElementById('my-main-form');

    const urlInput = document.getElementById('url');
    if (urlInput) {
        urlInput.addEventListener('input', function() {
            this.value = this.value.replace(/\s+/g, '-').toLowerCase();
        });
    }

    fields.forEach(item => {
        const element = document.getElementById(item.id);

        if (!element) return;

        if (item.type === 'input') {
            element.addEventListener('input', function() {
                checkField(element);
            });
        }
        else if (item.type === 'select') {
            element.addEventListener('change', function() {
                checkField(element);
            });
        }
        else if (item.type === 'ckeditor') {
            const checkCK = setInterval(() => {
                if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances[item.id]) {
                    clearInterval(checkCK);
                    const editor = CKEDITOR.instances[item.id];

                    editor.on('key', function() {
                        setTimeout(() => {
                            editor.updateElement();
                            checkField(element, true);
                        }, 50);
                    });

                    editor.on('change', function() {
                        editor.updateElement();
                        checkField(element, true);
                    });
                }
            }, 500);
        }
    });

    if (form) {
        form.addEventListener('submit', function (e) {
            let hasError = false;

            fields.forEach(item => {
                const element = document.getElementById(item.id);
                if (element) {
                    if (item.type === 'ckeditor' && typeof CKEDITOR !== 'undefined') {
                        CKEDITOR.instances[item.id].updateElement();
                        if (!checkField(element, true)) hasError = true;
                    } else {
                        if (!checkField(element)) hasError = true;
                    }
                }
            });

            if (hasError) {
                e.preventDefault();
                Swal.fire({
                    title: 'خطا!',
                    text: 'لطفاً فیلدهای قرمز شده را پر کنید.',
                    icon: 'error',
                    showConfirmButton: false,
                    timer: 2000
                });
            }
        });
    }

    function checkField(element, isCkeditor = false) {
        const isRequired = element.getAttribute('data-required') === 'true';

        if (!isRequired) return true;

        let isEmpty = false;
        let value = element.value.trim();

        if (isCkeditor) {
            const cleanText = value.replace(/<[^>]*>/g, '').replace(/&nbsp;/g, '').trim();
            if (cleanText === '') isEmpty = true;
        } else {
            if (value === '') isEmpty = true;
        }

        const parentDiv = element.closest('.form-group');

        if (isEmpty) {
            if (isCkeditor) {
                const wrapper = element.closest('.ckeditor-div');
                if(wrapper) wrapper.style.border = '1px solid red';
            } else {
                element.classList.add('is-invalid');
                element.classList.remove('is-valid');
            }

            let errorMsg = parentDiv.querySelector('.text-danger-custom');
            if (!errorMsg) {
                errorMsg = document.createElement('small');
                errorMsg.className = 'text-danger-custom text-danger d-block mt-1';
                errorMsg.innerText = 'این فیلد الزامی است';
                parentDiv.appendChild(errorMsg);
            }
            return false;

        } else {
            if (isCkeditor) {
                const wrapper = element.closest('.ckeditor-div');
                if(wrapper) wrapper.style.border = '1px solid #d8d6de';
            } else {
                element.classList.remove('is-invalid');
                element.classList.add('is-valid');
            }

            const errorMsg = parentDiv.querySelector('.text-danger-custom');
            if (errorMsg) {
                errorMsg.remove();
            }
            return true;
        }
    }
});
