@php use App\Models\Setting; @endphp
@extends('layouts.admin.master')
@section('tab-name', 'تنظیمات')
@section('content')

    <div class="body d-flex py-3">
        <div class="container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="border-0 mb-4">
                        <div
                            class="card-header py-3 no-bg bg-transparent d-flex align-items-center px-0 justify-content-between border-bottom flex-wrap">
                            <h3 class="fw-bolder mb-0">تنظیمات سایت</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border p-2">
                <form id="my-main-form" action="{{ route('admin.settings.update') }}" method="POST"
                      enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <ul class="nav nav-tabs mb-4" role="tablist">
                        @foreach ($tabs as $tabKey => $tabLabel)
                            <li class="nav-item">
                                <a class="nav-link @if ($loop->first) active @endif" data-bs-toggle="tab"
                                   href="#tab-{{ $tabKey }}">
                                    {{ $tabLabel }}
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    <div class="tab-content">
                        @foreach ($tabs as $tabKey => $tabLabel)
                            <div class="tab-pane fade @if ($loop->first) show active @endif"
                                 id="tab-{{ $tabKey }}">
                                <div class="row">
                                    @if ($tabKey == 'menu_items')
                                        @include('admin.setting.menu_item')
                                    @else
                                        @foreach ($fields as $fieldKey => $config)
                                            @if ($config['tab'] === $tabKey)
                                                @php
                                                    $value = old(
                                                        "settings.$fieldKey",
                                                        $currentValues[$fieldKey] ?? null,
                                                    );
                                                    $type = $config['type'];
                                                    $ckeditor = $config['ckeditor'] ?? false;

                                                    $isRequiredConfig = $config['required'] ?? false;
                                                    $shouldInputBeRequired = $isRequiredConfig && empty($value);

                                                    $guide = $config['guide'] ?? false;
                                                    $cropper = $config['cropper'] ?? false;
                                                @endphp

                                                <div
                                                    class="@if ($type === 'textarea') col-12 @else col-md-6 @endif col-12 p-2">
                                                    <div class="form-group">
                                                        <label for="{{ $fieldKey }}" class="form-label fw-bold">
                                                            {{ $config['label'] }}
                                                            @if ($isRequiredConfig)
                                                                <span class="text-danger">*</span>
                                                            @endif
                                                        </label>

                                                        @if($guide)
                                                            <small class="text-muted d-block mb-3"
                                                                   style="font-size: 14px;">
                                                                <i class="bi bi-info-circle me-2"></i>{{$guide}}
                                                            </small>
                                                        @endif

                                                        {{-- =========================== --}}
                                                        {{-- File - Video --}}
                                                        {{-- =========================== --}}
                                                        @if ($type === 'file' || $type === 'video')

                                                            <div class="card bg-light border-0 p-2">
                                                                <input type="file" name="settings[{{ $fieldKey }}]"
                                                                       id="{{ $fieldKey }}"
                                                                       class="form-control bg-white"
                                                                       accept="{{ $type === 'video' ? 'video/mp4,video/x-m4v,video/*' : 'image/*' }}"
                                                                       @if ($shouldInputBeRequired) required
                                                                       data-required="true" @endif>


                                                                @if ($value)
                                                                    <div class="mt-3">
                                                                        @if ($type === 'video')
                                                                            @php $videoUrl = Setting::videoFile($fieldKey); @endphp
                                                                            @if ($videoUrl)
                                                                                <div
                                                                                    class="ratio ratio-16x9 rounded overflow-hidden shadow-sm border">
                                                                                    <video controls preload="metadata">
                                                                                        <source src="{{ $videoUrl }}"
                                                                                                type="video/mp4">
                                                                                        مرورگر شما از پخش ویدیو پشتیبانی
                                                                                        نمی‌کند.
                                                                                    </video>
                                                                                </div>
                                                                            @else
                                                                                <div
                                                                                    class="alert alert-warning py-2 small">
                                                                                    <i
                                                                                        class="bi bi-exclamation-triangle"></i>
                                                                                    فایل ویدیو در سرور پیدا نشد. لطفاً
                                                                                    مجدد آپلود کنید.
                                                                                </div>
                                                                            @endif
                                                                        @else
                                                                            {{-- Image --}}
                                                                            <div
                                                                                class="border rounded p-1 bg-white d-inline-block">
                                                                                <img
                                                                                    src="{{ Setting::imageFile($fieldKey) }}"
                                                                                    style="max-height: 150px; max-width: 100%; object-fit: contain;"
                                                                                    class="rounded"
                                                                                    alt="{{ $config['label'] }}">
                                                                            </div>

                                                                            @if($type === 'file' && $cropper)
                                                                                @include('admin.image-croper.modal-croper', [
                                                                                    'inputId' => $fieldKey,
                                                                                    'aspectWidth' =>  500,
                                                                                    'aspectHeight' =>  738,
                                                                                    'outputWidth' => 500,
                                                                                    'outputHeight' =>  738
                                                                                ])
                                                                            @endif

                                                                        @endif
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            {{-- =========================== --}}
                                                            {{-- بخش TextArea --}}
                                                            {{-- =========================== --}}
                                                        @elseif($type === 'textarea')
                                                            <textarea name="settings[{{ $fieldKey }}]"
                                                                      id="{{ $fieldKey }}"
                                                                      class="form-control bg-light rounded-custom @if ($ckeditor) ckeditor @endif"
                                                                      rows="4"
                                                                      @if ($shouldInputBeRequired) required
                                                                      data-required="true" @endif>{{ $value }}</textarea>

                                                            {{-- =========================== --}}
                                                            {{-- بخش Input های متنی --}}
                                                            {{-- =========================== --}}
                                                        @else
                                                            <input type="{{ $type }}"
                                                                   name="settings[{{ $fieldKey }}]"
                                                                   id="{{ $fieldKey }}" value="{{ $value }}"
                                                                   class="form-control bg-light rounded-custom"
                                                                   @if ($shouldInputBeRequired) required
                                                                   data-required="true" @endif>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 pb-5">
                        <button type="submit" class="btn btn-primary btn-lg rounded-custom px-5 shadow-sm">
                            <i class="bi bi-save ms-2"></i>
                            ذخیره تغییرات
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const videoInput = document.getElementById('video_about');
                if (!videoInput) return;

                const MAX_SIZE = 50 * 1024 * 1024; // 50MB

                videoInput.addEventListener('change', function () {
                    const file = this.files[0];
                    if (!file) return;

                    if (file.size > MAX_SIZE) {
                        Swal.fire({
                            icon: 'error',
                            title: 'خطا',
                            text: 'حجم ویدیو نباید بیشتر از ۵۰ مگابایت باشد.',
                            showCancelButton: false,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-right',
                            timer: 3000
                        });
                        this.value = '';
                    }
                });
            });
        </script>
    @endpush

@endsection
