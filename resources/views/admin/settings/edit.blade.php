@php use App\Models\Setting; @endphp
@extends('layouts.admin.app')

@section('tab-name', 'تنظیمات سایت')

@section('content')
    <div class="body d-flex py-4">
        <div class="container-fluid">
            <!-- هدر صفحه -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h2 class="fw-bold mb-1">تنظیمات سایت</h2>
                    <p class="text-muted mb-0">مدیریت محتوای سایت و تنظیمات ظاهری</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-0">
                    <form action="{{ route('admin.settings.update', ['group' => $activeGroup]) }}" method="POST" enctype="multipart/form-data" id="settingsForm">
                        @csrf
                        @method('PUT')

                        <!-- تب‌های مدرن -->
                        <div class="border-bottom bg-light px-4 pt-3">
                            <ul class="nav nav-tabs border-0" id="settingsTabs" role="tablist">
                                @foreach ($tabs as $tabKey => $tabLabel)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link rounded-top-3 px-4 py-3 fw-semibold @if ($activeGroup === $tabKey) active @endif"
                                                id="tab-{{ $tabKey }}-btn"
                                                data-bs-toggle="tab"
                                                data-bs-target="#tab-{{ $tabKey }}"
                                                type="button"
                                                role="tab">
                                            {{ $tabLabel }}
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <!-- محتوای تب‌ها -->
                        <div class="tab-content p-4 p-md-5" id="settingsTabsContent">
                            @foreach ($tabs as $tabKey => $tabLabel)
                                <div class="tab-pane fade @if ($activeGroup === $tabKey) show active @endif" id="tab-{{ $tabKey }}" role="tabpanel">

                                    {{-- بخش ویژه: آیتم‌های منو --}}
                                    @if ($tabKey == 'menu_items')
                                        @include('admin.settings.menu_item')
                                    @else
                                        <div class="row g-4">
                                            @foreach ($fields as $fieldKey => $config)
                                                @if (($config['tab'] ?? 'general') === $tabKey)
                                                    @php
                                                        $value = old("settings.$fieldKey", $currentValues[$fieldKey] ?? null);
                                                        $type = $config['type'] ?? 'text';
                                                        $ckeditor = $config['ckeditor'] ?? false;
                                                        $isRequired = $config['required'] ?? false;
                                                        $guide = $config['guide'] ?? false;
                                                        $cropper = $config['cropper'] ?? false;
                                                        $colClass = ($type === 'textarea' || $type === 'ckeditor') ? 'col-12' : 'col-md-6';
                                                    @endphp

                                                    <div class="{{ $colClass }}">
                                                        <div class="mb-3">
                                                            <label for="{{ $fieldKey }}" class="form-label fw-semibold mb-2">
                                                                {{ $config['label'] }}
                                                                @if ($isRequired) <span class="text-danger">*</span> @endif
                                                            </label>

                                                            @if($guide)
                                                                <div class="alert alert-light border d-flex align-items-start py-2 mb-3" role="alert">
                                                                    <i class="bi bi-info-circle-fill text-primary me-2 mt-1"></i>
                                                                    <small class="text-muted">{{ $guide }}</small>
                                                                </div>
                                                            @endif

                                                            {{-- آپلود فایل / ویدیو --}}
                                                            @if ($type === 'file' || $type === 'video')
                                                                <div class="border rounded-3 p-3 bg-light bg-opacity-50">
                                                                    <div class="mb-3">
                                                                        <label class="custom-file-upload-btn w-100 text-center py-3 rounded-3 border border-dashed cursor-pointer hover-bg-light transition-all">
                                                                            <input type="file" name="settings[{{ $fieldKey }}]" id="{{ $fieldKey }}" class="d-none"
                                                                                   accept="{{ $type === 'video' ? 'video/mp4,video/x-m4v,video/*' : 'image/*' }}"
                                                                                   @if ($isRequired && empty($value)) required @endif
                                                                                   onchange="updateFileName(this)">
                                                                            <i class="bi bi-cloud-arrow-up fs-4 text-primary mb-2 d-block"></i>
                                                                            <span class="fw-semibold text-dark">انتخاب فایل جدید</span>
                                                                            <div class="file-name-display mt-2 text-muted small" id="name_{{ $fieldKey }}">فایلی انتخاب نشده است</div>
                                                                        </label>
                                                                    </div>

                                                                    @if ($value)
                                                                        <div class="mt-3 p-2 bg-white rounded-3 border">
                                                                            <p class="small fw-bold text-muted mb-2">پیش‌نمایش فایل فعلی:</p>
                                                                            @if ($type === 'video')
                                                                                @php $videoUrl = Setting::videoFile($fieldKey); @endphp
                                                                                @if ($videoUrl)
                                                                                    <video controls class="w-100 rounded-2" style="max-height: 200px;">
                                                                                        <source src="{{ $videoUrl }}" type="video/mp4">
                                                                                    </video>
                                                                                @endif
                                                                            @else
                                                                                <img src="{{ Setting::imageFile($fieldKey) }}" class="img-fluid rounded-2" style="max-height: 150px; object-fit: contain;" alt="{{ $config['label'] }}">
                                                                            @endif

                                                                            @if($type === 'file' && $cropper)
                                                                                @include('admin.image-croper.modal-croper', [
                                                                                    'inputId' => $fieldKey,
                                                                                    'aspectWidth' => 500,
                                                                                    'aspectHeight' => 738,
                                                                                    'outputWidth' => 500,
                                                                                    'outputHeight' => 738
                                                                                ])
                                                                            @endif
                                                                        </div>
                                                                    @endif
                                                                </div>

                                                                {{-- تکست ایریا --}}
                                                            @elseif($type === 'textarea' || $type === 'ckeditor')
                                                                <textarea name="settings[{{ $fieldKey }}]" id="{{ $fieldKey }}"
                                                                          class="form-control rounded-3 @if ($ckeditor) ckeditor @endif"
                                                                          rows="5"
                                                                          @if ($isRequired) required @endif>{{ $value }}</textarea>

                                                                {{-- اینپوت متنی --}}
                                                            @else
                                                                <input type="{{ $type }}" name="settings[{{ $fieldKey }}]" id="{{ $fieldKey }}"
                                                                       value="{{ $value }}" class="form-control rounded-3"
                                                                       @if ($isRequired) required @endif>
                                                                @endif
                                                                </div>
                                                        </div>
                                                        @endif
                                                        @endforeach
                                                    </div>
                                                @endif
                                        </div>
                                        @endforeach
                                </div>

                                <!-- دکمه ذخیره -->
                                <div class="card-footer bg-light border-top p-4 d-flex justify-content-end gap-3">
                                    <a href="{{ route('admin.dashboard.index') }}" class="btn btn-light rounded-3 px-4">انصراف</a>
                                    <button type="submit" class="btn btn-primary rounded-3 px-5 shadow-sm">
                                        <i class="bi bi-check-lg me-2"></i> ذخیره تغییرات
                                    </button>
                                </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            /* استایل‌های سفارشی برای زیباتر شدن فرم */
            .nav-tabs .nav-link {
                color: #6c757d;
                border: none;
                border-bottom: 3px solid transparent;
                transition: all 0.3s ease;
            }
            .nav-tabs .nav-link.active {
                color: #0d6efd;
                background-color: transparent;
                border-bottom-color: #0d6efd;
                font-weight: 700;
            }
            .nav-tabs .nav-link:hover:not(.active) {
                color: #0d6efd;
                border-bottom-color: #e9ecef;
            }
            .custom-file-upload-btn {
                border: 2px dashed #ced4da !important;
                background-color: #f8f9fa;
                transition: all 0.3s ease;
            }
            .custom-file-upload-btn:hover {
                border-color: #0d6efd !important;
                background-color: #e9ecef;
            }
            .border-dashed {
                border-style: dashed !important;
            }
            .cursor-pointer {
                cursor: pointer;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            // نمایش نام فایل انتخاب شده
            function updateFileName(input) {
                const fileName = input.files[0] ? input.files[0].name : 'فایلی انتخاب نشده است';
                const displayElement = document.getElementById('name_' + input.id);
                if (displayElement) {
                    displayElement.textContent = fileName;
                    displayElement.classList.add('text-primary', 'fw-semibold');
                }
            }

            // اعتبارسنجی حجم ویدیو
            document.addEventListener('DOMContentLoaded', function () {
                const videoInputs = document.querySelectorAll('input[type="file"][accept*="video"]');
                const MAX_SIZE = 50 * 1024 * 1024; // 50MB

                videoInputs.forEach(input => {
                    input.addEventListener('change', function () {
                        const file = this.files[0];
                        if (!file) return;

                        if (file.size > MAX_SIZE) {
                            Swal.fire({
                                icon: 'error',
                                title: 'حجم فایل بسیار زیاد است',
                                text: 'حجم ویدیو نباید بیشتر از ۵۰ مگابایت باشد.',
                                confirmButtonText: 'متوجه شدم',
                                confirmButtonColor: '#dc3545'
                            });
                            this.value = '';
                            const displayElement = document.getElementById('name_' + this.id);
                            if (displayElement) displayElement.textContent = 'فایلی انتخاب نشده است';
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
