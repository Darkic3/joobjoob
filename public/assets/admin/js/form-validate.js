    let requiredInputs = document.querySelectorAll("[requiredCms]");
    let submitButton = document.getElementById('submitFormCms');

    submitButton.addEventListener('click', validateForm);

    function validateForm(e) {
    e.preventDefault();
    let isValid = true;

    requiredInputs.forEach(x => {
    const closestLabel = x.previousElementSibling;
    if (closestLabel) {
    const labelName = closestLabel.innerText || closestLabel.textContent;
    x.labelName = labelName.trim();
}
    const isEmpty = x.value.trim() === '';
    if (isEmpty) {
    isValid = false;
    Swal.fire({
    icon: 'error',
    text: `${x.labelName} نمی‌تواند خالی باشد!`,
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 5000
});
    console.log(x)
    x.style.border = '2px solid red';
} else {
    x.style.border = '';
}
});

    if (isValid) {
    // If all inputs are valid, submit the form
    document.getElementById("cms-form").submit();
}
}
