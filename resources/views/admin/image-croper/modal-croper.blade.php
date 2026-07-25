@php
    // تنظیم مقادیر پیش‌فرض و ایجاد IDهای منحصربه‌فرد
    $inputId = $inputId ?? 'image';
    $aspectWidth = $aspectWidth ?? 1;
    $aspectHeight = $aspectHeight ?? 1;
    $outputWidth = $outputWidth ?? 1920;
    $outputHeight = $outputHeight ?? 1920;
    $aspectRatio = $aspectWidth / $aspectHeight;

    // ایجاد شناسه‌های یکتا برای هر اینپوت
    $modalId = "ac-modal-" . $inputId;
    $imgId = "ac-img-" . $inputId;
    $confirmBtnId = "ac-confirm-" . $inputId;
    $cancelBtnId = "ac-cancel-" . $inputId;
@endphp

@once
    @push('styles')
        <link rel="stylesheet" href="{{asset('assets/admin/cropper/cropper.min.css')}}" />
        <style>
            .ac-cropper-modal-custom {
                display: none;
                position: fixed;
                inset: 0;
                background-color: rgba(0, 0, 0, 0.85);
                z-index: 9999999;
                justify-content: center;
                align-items: center;
                padding: 20px;
                backdrop-filter: blur(4px);
            }
            .ac-content-box {
                background: #ffffff;
                padding: 25px;
                border-radius: 12px;
                width: 100%;
                max-width: 850px;
                display: flex;
                flex-direction: column;
            }
            .ac-image-wrapper {
                width: 100%;
                max-height: 60vh;
                overflow: hidden;
                background: #f8f8f8;
            }
            .ac-buttons { margin-top: 20px; display: flex; justify-content: flex-end; gap: 12px; }
            .ac-btn { padding: 10px 25px; border-radius: 6px; border: none; cursor: pointer; font-weight: bold; }
            .ac-btn-confirm { background-color: #28a745; color: white; }
            .ac-btn-cancel { background-color: #6c757d; color: white; }
        </style>
    @endpush

    @push('scripts')
        <script src="{{asset('assets/admin/cropper/cropper.min.js')}}"></script>
    @endpush
@endonce

{{-- بخش مدال نباید داخل @once باشد تا برای هر اینپوت یک مدال جداگانه ساخته شود --}}
@push('modals')
    <div id="{{ $modalId }}" class="ac-cropper-modal-custom">
        <div class="ac-content-box">
            <h3>برش تصویر ({{ $aspectWidth }}x{{ $aspectHeight }})</h3>
            <div class="ac-image-wrapper">
                <img id="{{ $imgId }}" style="max-width: 100%;">
            </div>
            <div class="ac-buttons">
                <button type="button" class="ac-btn ac-btn-confirm" id="{{ $confirmBtnId }}">تایید و جایگزینی</button>
                <button type="button" class="ac-btn ac-btn-cancel" id="{{ $cancelBtnId }}">انصراف</button>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        (function() {
            document.addEventListener('DOMContentLoaded', () => {
                let current_cropper = null;
                let originalName = '';

                const inputEl = document.getElementById('{{ $inputId }}');
                const modalEl = document.getElementById('{{ $modalId }}');
                const imageEl = document.getElementById('{{ $imgId }}');
                const confirmBtn = document.getElementById('{{ $confirmBtnId }}');
                const cancelBtn = document.getElementById('{{ $cancelBtnId }}');

                if (!inputEl) return;

                inputEl.addEventListener('change', function(e) {
                    const files = e.target.files;
                    if (files && files.length > 0) {
                        originalName = files[0].name;
                        const reader = new FileReader();
                        reader.onload = (event) => {
                            imageEl.src = event.target.result;
                            modalEl.style.display = 'flex';

                            if (current_cropper) {
                                current_cropper.destroy();
                            }

                            // ایجاد کراپ‌ر جدید بعد از لود شدن تصویر
                            imageEl.onload = () => {
                                current_cropper = new Cropper(imageEl, {
                                    aspectRatio: {{ $aspectRatio }},
                                    viewMode: 2,
                                    autoCropArea: 1
                                });
                            };
                        };
                        reader.readAsDataURL(files[0]);
                    }
                });

                confirmBtn.onclick = () => {
                    if (!current_cropper) return;

                    current_cropper.getCroppedCanvas({
                        width: {{ $outputWidth }},
                        height: {{ $outputHeight }},
                        imageSmoothingQuality: 'high',
                    }).toBlob((blob) => {
                        const fileName = originalName.split('.').slice(0, -1).join('.') + '.webp';
                        const croppedFile = new File([blob], fileName, { type: 'image/webp' });

                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(croppedFile);
                        inputEl.files = dataTransfer.files;

                        closeModal();
                    }, 'image/webp', 0.9);
                };

                cancelBtn.onclick = () => {
                    closeModal();
                    inputEl.value = ''; // ریست کردن اینپوت در صورت انصراف
                };

                function closeModal() {
                    modalEl.style.display = 'none';
                    if (current_cropper) {
                        current_cropper.destroy();
                        current_cropper = null;
                    }
                }
            });
        })();
    </script>
@endpush
