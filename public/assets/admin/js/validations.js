new Vue({
    el: "#cms-form",
    data: {
        listenersValidations: [
            'numberCms',
            'timeCms'
        ]
    },
    methods: {
        errorsGenerator(inputs) {
            const inputErrors = [];
            inputs.forEach(element => {
                const errors = [];
                const isEmpty = element.value.trim() === '';

                if (element.hasAttribute("requiredCms")) {
                    if (isEmpty) {
                        errors.push({message: "وارد کردن مقدار الزامیست", validation: "requiredCms"});
                    }
                }

                if (element.hasAttribute("minCms") && element.value) {
                    if (isEmpty || element.value.length < element.getAttribute("minCms")) {
                        errors.push({
                            message: `حداقل ${element.getAttribute("minCms")} کاراکتر وارد کنید.`,
                            validation: "minCms"
                        });
                    }
                }

                if (element.hasAttribute("mobileCms")) {
                    const mobilePattern = /^[0-9+۰-۹]+$/;
                    if (!isEmpty && !mobilePattern.test(element.value)) {
                        errors.push({message: "موبایل معتبر نمیباشد", validation: "mobileCms"});
                    }
                }

                if (element.hasAttribute("emailCms")) {
                    const emailPattern = /^[0-9+A-Z+a-z+@+.]+$/;
                    if (!isEmpty && !emailPattern.test(element.value)) {
                        errors.push({message: "ایمیل معتبر نمیباشد", validation: "emailCms"});
                    }
                }

                if (element.hasAttribute("numberCms")) {
                    var numberPattern = /^[\d۰۱۲۳۴۵۶۷۸۹]+$/;
                    if (!isEmpty && !numberPattern.test(element.value)) {
                        errors.push({message: "فقط عدد وارد کنید", validation: "numberCms"});
                    }
                }
                if (element.hasAttribute("urlCms")) {
                    var urlPattern = /^[\'^£$%&*()}{@#~?><>,|=:]+$/;
                    console.log(urlPattern.test(element.value))
                    if (!isEmpty && urlPattern.test(element.value)) {
                        errors.push({message: "از کاراکترهای اضافی (؟/[\\'^£$%&*:()}{@#~?><>,|=]/)پرهیز کنید", validation: "urlCms"});
                    }
                }
                if (element.hasAttribute("timeCms")) {
                    var numberPattern = /^[\d۰۱۲۳۴۵۶۷۸۹:]+$/;
                    if (!isEmpty && !numberPattern.test(element.value)) {
                        errors.push({message: "به صورت عدد و ۱۲:۰۰ ورد کنید", validation: "timeCms"});
                    }
                }

                if (errors.length > 0) {
                    inputErrors.push({element: element, errors: errors});
                }
            });
            return inputErrors;
        },
        resetErrorElements(inputs) {
            inputs.forEach(element => {
                element.style.border = '';
                const previousError = element.nextSibling;
                if (previousError && previousError.tagName === 'P') {
                    previousError.parentNode.removeChild(previousError);
                }
            })
            return true;
        },
        clearError(element) {
            console.log(element);
            element.style.border = '';
            const previousError = element.nextSibling;
            if (previousError && previousError.tagName === 'P') {
                previousError.parentNode.removeChild(previousError);
            }
        },
        addListenerInput(element) {
            const vm = this;
            element.addEventListener('input', function () {
                vm.clearError(element);
            });
            element.addEventListener('change', function () {
                vm.clearError(element);
            });
            return true;
        },
        validateForm(submitEvent) {
            const inputs = submitEvent.target.querySelectorAll('input,select,textarea');
            this.resetErrorElements(inputs);
            const errors = this.errorsGenerator(inputs);
            if (errors.length > 0) {
                console.log(errors);
                errors.forEach(error => {
                    const errorElement = document.createElement('p');
                    errorElement.textContent = error.errors[0].message;
                    errorElement.style.color = "red";
                    errorElement.style.display = 'none';
                    error.element.style.border = '1px solid red';
                    errorElement.style.display = 'block';
                    error.element.parentNode.insertBefore(errorElement, error.element.nextSibling);
                    this.addListenerInput(error.element);
                })
                Swal.fire({
                    icon: 'error',
                    text: "کاربر گرامی اطلاعات فرم را به درستی پر کنید",
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 5000
                });

                return false;
            } else {
                submitEvent.target.submit();
            }
        },
        sanitizeValueNumber(value) {
            const validCharacters = /[۰-۹0-9]/g;
            return value.match(validCharacters)?.join('') || '';
        },
        sanitizeValueUrl(value) {
            const unValidCharacters = /[\'^£$%&*()}{@#~?><>,|=:؟.]/g;

            // حذف اسپیس‌های ابتدای کلمه فقط
            const replacedSpacesAtStart = value.replace(/^\s+/, '');

            // جایگزینی یک یا بیشتر از یک اسپیس با یک خط تیره
            const replacedSpaces = replacedSpacesAtStart.replace(/\s+/g, '-');

            // جایگزینی یک یا بیشتر از یک خط تیره با یک خط تیره
            const sanitizedValue = replacedSpaces.replace(/-+/g, '-');

            return sanitizedValue.replace(unValidCharacters, '').toLowerCase(); // حذف کاراکترهای نامعتبر
        }
    },
    mounted() {
        let vm = this;
        let numberInputs = document.querySelectorAll("[numberCms], [mobileCms],[timeCms]");
        numberInputs.forEach(item => {
            item.addEventListener('input', function (event){
                event.target.value = vm.sanitizeValueNumber(event.target.value);
            });
        });
        let urlInput = document.querySelectorAll("[urlCms]");
        urlInput.forEach(item => {
            item.addEventListener('input', function (event){
                event.target.value = vm.sanitizeValueUrl(event.target.value);
            });
        });
    }
});
