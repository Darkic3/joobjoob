<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تست ایزوله کراپر</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css"/>
    <style>
        body { font-family: sans-serif; padding: 20px; background-color: #f4f4f4; }
        hr { margin: 30px 0; }
        .image-crop-window {
            display: none; position: fixed; z-index: 99999; left: 0; top: 0;
            width: 100%; height: 100%; overflow: auto; background-color: rgba(0, 0, 0, 0.6);
            justify-content: center; align-items: center;
        }
        .image-crop-window-content {
            background-color: #fefefe; padding: 20px; border: 1px solid #888;
            border-radius: 8px; max-width: 90vw; max-height: 90vh;
            position: relative; z-index: 1; box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        #image-to-crop { max-width: 100%; max-height: 70vh; }
        .image-crop-window-content .cropper-container { margin: 0 auto; }
        .cropper-view-box, .cropper-face { border-radius: 0 !important; }

        /* راه‌حل‌های باگ */
        .cropper-view-box img {
            max-width: none !important;
            transform-origin: center center !important;
            position: static !important;
        }
        .cropper-container img { transition: none !important; }
        .cropper-container *, .cropper-container *:before, .cropper-container *:after {
            box-sizing: content-box !important;
        }
    </style>
</head>
<body>

<h1>تست ایزوله کراپر</h1>

<form id="my-main-form" onsubmit="return false;">
    <div>
        <label for="image">عکس را انتخاب کنید:</label>
        <input type="file" id="image" name="image">
    </div>
    <br>
    <button type="submit" id="submitFormCms">ذخیره (تست)</button>
</form>

<hr>
<h3>پیش‌نمایش خروجی کراپ شده:</h3>
<div style="width: 400px; height: 400px; border: 2px solid #555; background-color: #fff;">
    <img id="cropped-preview" src="" style="width: 100%; height: 100%; object-fit: contain;">
</div>


<div id="cropper-modal" class="image-crop-window">
    <div class="image-crop-window-content">
        <h2>برش تصویر</h2>
        <img id="image-to-crop">
        <br>
        <button type="button" id="confirm-crop-button" style="padding: 10px 20px;">تایید برش</button>
        <button type="button" id="cancel-crop-button" style="padding: 10px 20px;">انصراف</button>
    </div>
</div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
<script src="{{ asset('assets/admin/js/sweetalert2.all.min.js') }}"></script>
@vite(['resources/css/swal.css', 'resources/js/swal-ui.js'])
<x-swal-ui />
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const fileInput = document.getElementById('image');
        const modal = document.getElementById('cropper-modal');
        const image = document.getElementById('image-to-crop');
        const confirmCropButton = document.getElementById('confirm-crop-button');
        const cancelCropButton = document.getElementById('cancel-crop-button');
        const mainForm = document.getElementById('my-main-form');

        // ۲. المان پیش‌نمایش را هم می‌گیریم
        const previewImage = document.getElementById('cropped-preview'); // <-- جدید

        let cropper = null;
        let croppedImageBlob = null;
        let originalFileName = '';

        image.onload = () => {
            if (cropper) cropper.destroy();
            modal.style.display = 'flex';
            cropper = new Cropper(image, {
                aspectRatio: 10 / 10, viewMode: 2, dragMode: 'crop',
                background: false, autoCropArea: 1, responsive: true,
            });
        };

        fileInput.addEventListener('change', (e) => {
            // ۳. اگر فایل عوض شد، پیش‌نمایش قبلی را پاک می‌کنیم
            if (previewImage.src) { // <-- جدید
                URL.revokeObjectURL(previewImage.src);
                previewImage.src = '';
            }
            croppedImageBlob = null; // <-- جدید

            const files = e.target.files;
            if (files && files.length > 0) {
                const file = files[0];
                originalFileName = file.name;
                const reader = new FileReader();
                reader.onload = (event) => {
                    image.src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        cancelCropButton.addEventListener('click', () => {
            modal.style.display = 'none';
            if (cropper) cropper.destroy();
            fileInput.value = '';
            croppedImageBlob = null;

            // اگر انصراف زد، پیش‌نمایش را هم پاک می‌کنیم
            if (previewImage.src) { // <-- جدید
                URL.revokeObjectURL(previewImage.src);
                previewImage.src = '';
            }
        });

        confirmCropButton.addEventListener('click', () => {
            if (!cropper) return;

            cropper.getCroppedCanvas({
                width: 1920, height: 1920, imageSmoothingQuality: 'high',
            }).toBlob((blob) => {
                croppedImageBlob = blob;
                modal.style.display = 'none';
                cropper.destroy();

                console.log('عکس با موفقیت کراپ شد و آماده ارسال است.');
                SwalUI.success('عکس کراپ شد!', 'پیش‌نمایش در پایین صفحه اضافه شد.');

                // ۴. آبجکت URL می‌سازیم و در پیش‌نمایش نمایش می‌دهیم
                if (previewImage.src) { // <-- جدید
                    URL.revokeObjectURL(previewImage.src);
                }
                const imageUrl = URL.createObjectURL(blob); // <-- جدید
                previewImage.src = imageUrl; // <-- جدید

            }, 'image/png');
        });

        mainForm.addEventListener('submit', (e) => {
            e.preventDefault();
            if (croppedImageBlob) {
                SwalUI.info('فرم آماده ارسال است (همراه با عکس کراپ شده).');
            } else if (fileInput.files.length > 0) {
                SwalUI.warning('لطفاً عکس را انتخاب و سپس برش دهید.');
            } else {
                SwalUI.warning('لطفاً یک عکس انتخاب کنید.');
            }
        });
    });
</script>
</body>
</html>
