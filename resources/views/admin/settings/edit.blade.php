{{-- داده‌ها (fieldsByTab/groupsValues/…) در SettingController آماده می‌شود؛ ترجمه با fallback از هلمر trans_or() --}}
@extends('layouts.admin.app')

@section('tab-name', __('admin.settings.title'))

@section('content')
    <div class="body d-flex py-4">
        <div class="container-fluid">
            <!-- هدر صفحه -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h2 class="fw-bold mb-1">{{ __('admin.settings.title') }}</h2>
                    <p class="text-muted mb-0">{{ __('admin.settings.subtitle') }}</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-0">
                    <!-- تب‌ها — سوییچ سمت کلاینت بدون رفرش (Bootstrap tab) -->
                    <div class="border-bottom bg-light dark:bg-gray-800 px-4 pt-3">
                        <ul class="nav nav-tabs border-0 flex-wrap" id="settingsTabs" role="tablist">
                            @foreach ($tabs as $tabKey => $tabLabel)
                                <li class="nav-item" role="presentation">
                                    <a href="#settings-tab-{{ $tabKey }}" role="tab"
                                       data-bs-toggle="tab" data-bs-target="#settings-tab-{{ $tabKey }}"
                                       data-edit-url="{{ route('admin.settings.edit', $tabKey) }}"
                                       aria-controls="settings-tab-{{ $tabKey }}"
                                       aria-selected="{{ $activeGroup === $tabKey ? 'true' : 'false' }}"
                                       class="nav-link rounded-top-3 px-4 py-3 fw-semibold @if ($activeGroup === $tabKey) active @endif">
                                        {{ trans_or('admin.settings.tabs.'.$tabKey, $tabLabel) }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- محتوای همه گروه‌ها — پنل‌ها با JS نمایش داده می‌شوند -->
                    <div class="tab-content" id="settingsTabsContent">
                        @foreach ($tabs as $tabKey => $tabLabel)
                            <div class="tab-pane fade @if ($activeGroup === $tabKey) show active @endif"
                                 id="settings-tab-{{ $tabKey }}" role="tabpanel" aria-labelledby="settings-tab-{{ $tabKey }}">
                                <form action="{{ route('admin.settings.update', ['group' => $tabKey]) }}" method="POST"
                                      enctype="multipart/form-data" class="settings-group-form">
                                    @csrf
                                    @method('PUT')

                                    <div class="row g-4 p-4 p-md-5">
                                        @forelse ($fieldsByTab[$tabKey] ?? [] as $fieldKey => $config)
                                                <div class="{{ in_array($config['type'] ?? 'text', ['textarea', 'ckeditor']) ? 'col-12' : 'col-md-6' }}">
                                                    @include('admin.settings._field', compact('fieldKey', 'config', 'tabKey', 'groupsValues'))
                                                </div>
                                            @empty
                                                <div class="col-12 text-center text-muted py-4">
                                                    <i class="bi bi-sliders fs-3 d-block mb-2"></i>
                                                    {{ __('admin.settings.no_fields_in_group') }}
                                                </div>
                                            @endforelse
                                    </div>

                                    <!-- دکمه ذخیره این گروه -->
                                    <div class="card-footer bg-light border-top p-4 d-flex justify-content-end gap-3 dark:bg-gray-800 dark:border-gray-700">
                                        <a href="{{ route('admin.dashboard.index') }}" class="btn btn-light rounded-3 px-4">{{ __('admin.settings.cancel') }}</a>
                                        <button type="submit" class="btn btn-primary rounded-3 px-5 shadow-sm">
                                            <i class="bi bi-check-lg me-2"></i> {{ __('admin.settings.save') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
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

            /* ===== حالت تاریک برای باکس آپلود فایل ===== */
            [data-bs-theme="dark"] .custom-file-upload-btn {
                background-color: #212529;
                border-color: #495057 !important;
            }
            [data-bs-theme="dark"] .custom-file-upload-btn:hover {
                border-color: #0d6efd !important;
                background-color: #2b3035;
            }
            [data-bs-theme="dark"] .upload-label-text {
                color: #f8f9fa;
            }
            [data-bs-theme="dark"] .file-name-display {
                color: #adb5bd;
            }

            /* ===== پیش‌نمایش زندهٔ سبک‌های بارش جشن ===== */
            .festive-preview-item canvas {
                display: block;
                width: 96px;
                height: 64px;
                border-radius: 12px;
                border: 1px solid rgba(13, 110, 253, .18);
                background: rgba(127, 127, 127, .1);
                box-shadow: inset 2px 2px 5px rgba(0, 0, 0, .08), inset -2px -2px 5px rgba(255, 255, 255, .5);
            }
            .festive-preview-item {
                cursor: pointer;
                transition: transform .2s ease;
            }
            .festive-preview-item:hover {
                transform: translateY(-2px);
            }
            .festive-preview-item.playing canvas {
                border-color: rgba(13, 110, 253, .6);
                box-shadow: 0 0 0 2px rgba(13, 110, 253, .18), inset 2px 2px 5px rgba(0, 0, 0, .08), inset -2px -2px 5px rgba(255, 255, 255, .5);
            }
            .festive-preview-item.playing small {
                color: #0d6efd;
                font-weight: 600;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            // نمایش نام فایل انتخاب شده
            function updateFileName(input) {
                const fileName = input.files[0] ? input.files[0].name : '{{ __('admin.settings.no_file') }}';
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
                            SwalUI.error('{{ __('admin.settings.file_too_large') }}', '{{ __('admin.settings.video_size_limit') }}', {
                                confirmText: '{{ __('admin.settings.got_it') }}'
                            });
                            this.value = '';
                            const displayElement = document.getElementById('name_' + this.id);
                            if (displayElement) displayElement.textContent = '{{ __('admin.settings.no_file') }}';
                        }
                    });
                });

                // سوییچ تب بدون رفرش + همگام‌سازی URL (برای رفرش/لینک مستقیم) + رفرش CKEditor
                const tabsEl = document.getElementById('settingsTabs');
                if (tabsEl && window.bootstrap) {
                    tabsEl.addEventListener('shown.bs.tab', function (e) {
                        const link = e.target;
                        const url = link.getAttribute('data-edit-url');
                        if (url && history.replaceState) {
                            history.replaceState(null, '', url);
                        }

                        // اگر داخل پنل ادیتور مخفی بود، بعد از نمایش اندازه‌اش را درست کن
                        const pane = document.querySelector(link.getAttribute('data-bs-target'));
                        if (pane && typeof CKEDITOR !== 'undefined') {
                            pane.querySelectorAll('.ckeditor').forEach(function (el) {
                                const instance = CKEDITOR.instances[el.id];
                                if (instance) {
                                    setTimeout(function () {
                                        instance.resize('100%', instance.config.height || 400);
                                    }, 50);
                                }
                            });
                        }
                    });
                }
            });

        </script>
    @endpush
@endsection
