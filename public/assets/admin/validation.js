function numberKeyUp(event) {
    const mobileRegex = /^[0-9]+$/gmi;
    const backspaceKeyCode = 8;
    if (!mobileRegex.test(event.target.value.trim()) && event.keyCode !== backspaceKeyCode) {
        event.target.value = '';
        Swal.fire({
            icon: 'error',
            title: 'خطا',
            text: 'داخل شماره موبایل فقط عدد وارد کنید',
            showCancelButton: false,
            showConfirmButton: false,
            toast: true,
            position: 'top-right',
            timer: 5000
        });
        return false;
    }
    return true;
}

function urlKeyUp(event) {
    const value = event.target.value;
    const unValidCharacters = /[\'^£$%&*()}{@#~?><>,|=:؟.]/g;
    // حذف اسپیس‌های ابتدای کلمه فقط
    const replacedSpacesAtStart = value.replace(/^\s+/, '');

    // جایگزینی یک یا بیشتر از یک اسپیس با یک خط تیره
    const replacedSpaces = replacedSpacesAtStart.replace(/\s+/g, '-');

    // جایگزینی یک یا بیشتر از یک خط تیره با یک خط تیره
    const sanitizedValue = replacedSpaces.replace(/-+/g, '-');

    event.target.value = sanitizedValue.replace(unValidCharacters, '').toLowerCase();
}

function desKeyUp(event) {
    let desText = event.target.value.trim();
    // گرفتن مقدار وارد شده توسط کاربر و حذف فاصله‌های اضافی از ابتدا و انتهای رشته
    if (desText.length > 250) {
        desText = desText.slice(0, 250);
        // حذف بخش‌های اضافی از متن
        event.target.value = desText;
        // اعمال مقدار تغییر یافته به textarea
        Swal.fire({
            icon: 'error',
            title: 'خطا',
            text: 'در توضیحات بالایی فقط باید 250 کاراکتر وارد کنید',
            showCancelButton: false,
            showConfirmButton: false,
            toast: true,
            position: 'top-right',
            timer: 5000
        });
        return false;
    }
    return true;
    // اگر طول متن کمتر از 280 کاراکتر باشد، اعتبارسنجی موفقیت‌آمیز است
}


function onDelete(route) {
    Swal.fire({
        title: 'آیا از حذف فیلد مطمئن هستید؟',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'تایید و حذف',
        cancelButtonText: 'لغو'
    }).then((result) => {
        if (result.isConfirmed) {
            location.href = route;
        }
    });
}
