{{-- رندر یک فیلد تنظیمات — پارامترها: $fieldKey, $config, $tabKey, $groupsValues --}}
<div class="mb-3">
    <label for="{{ $fieldKey }}" class="form-label fw-semibold mb-2">
        {{ trans_or('admin.settings.fields.'.$fieldKey, $config['label'] ?? '') }}
        @if ($config['required'] ?? false) <span class="text-danger">*</span> @endif
    </label>

    @if($config['guide'] ?? false)
        <div class="alert alert-light border d-flex align-items-start py-2 mb-3" role="alert">
            <i class="bi bi-info-circle-fill text-primary me-2 mt-1"></i>
            <small class="text-muted">{{ $config['guide'] }}</small>
        </div>
    @endif

    {{-- آپلود فایل / ویدیو --}}
    @if (($config['type'] ?? 'text') === 'file' || ($config['type'] ?? 'text') === 'video')
        <div class="border rounded-3 p-3 bg-light bg-opacity-50 dark:bg-gray-800 dark:border-gray-600">
            <div class="mb-3">
                <label class="custom-file-upload-btn w-100 text-center py-3 rounded-3 border border-dashed cursor-pointer hover-bg-light transition-all">
                    <input type="file" name="settings[{{ $fieldKey }}]" id="{{ $fieldKey }}" class="d-none"
                           accept="{{ ($config['type'] ?? 'text') === 'video' ? 'video/mp4,video/x-m4v,video/*' : 'image/*' }}"
                           @if (($config['required'] ?? false) && empty(old("settings.$fieldKey", $groupsValues[$tabKey][$fieldKey] ?? null))) required @endif
                           onchange="updateFileName(this)">
                    <i class="bi bi-cloud-arrow-up fs-4 text-primary mb-2 d-block"></i>
                    <span class="fw-semibold upload-label-text">{{ __('admin.settings.choose_file') }}</span>
                    <div class="file-name-display mt-2 text-muted small" id="name_{{ $fieldKey }}">{{ __('admin.settings.no_file') }}</div>
                </label>
            </div>

            @if (old("settings.$fieldKey", $groupsValues[$tabKey][$fieldKey] ?? null))
                <div class="mt-3 p-2 bg-white rounded-3 border dark:bg-gray-900 dark:border-gray-600">
                    <p class="small fw-bold text-muted mb-2">{{ __('admin.settings.current_file') }}:</p>
                    @if (($config['type'] ?? 'text') === 'video')
                        @if (\App\Models\Setting::videoFile($fieldKey))
                            <video controls class="w-100 rounded-2" style="max-height: 200px;">
                                <source src="{{ \App\Models\Setting::videoFile($fieldKey) }}" type="video/mp4">
                            </video>
                        @endif
                    @else
                        <img src="{{ \App\Models\Setting::imageFile($fieldKey) }}" class="img-fluid rounded-2" style="max-height: 150px; object-fit: contain;" alt="{{ trans_or('admin.settings.fields.'.$fieldKey, $config['label'] ?? '') }}">
                    @endif

                    {{-- کراپ تصویر به‌صورت سراسری (هندلر تغییر فایل در app-admin.js) انجام می‌شود --}}
                </div>
            @endif
        </div>

        {{-- تکست ایریا / ادیتور --}}
    @elseif(in_array($config['type'] ?? 'text', ['textarea', 'ckeditor']))
        <textarea name="settings[{{ $fieldKey }}]" id="{{ $fieldKey }}"
                  class="input-custom @if ($config['ckeditor'] ?? false) ckeditor @endif"
                  rows="5"
                  @if ($config['required'] ?? false) required @endif>{{ old("settings.$fieldKey", $groupsValues[$tabKey][$fieldKey] ?? null) }}</textarea>

        {{-- سوییچ بولی --}}
    @elseif(($config['type'] ?? 'text') === 'boolean')
        <div class="form-check form-switch fs-4">
            <input type="checkbox" class="form-check-input" name="settings[{{ $fieldKey }}]" id="{{ $fieldKey }}"
                   value="1"
                   @checked(old("settings.$fieldKey", $groupsValues[$tabKey][$fieldKey] ?? false))>
        </div>

        {{-- سلکت (گزینه‌ها از کانفیگ فیلد) --}}
    @elseif(($config['type'] ?? 'text') === 'select')
        <select name="settings[{{ $fieldKey }}]" id="{{ $fieldKey }}"
                class="input-custom form-select"
                @if ($config['required'] ?? false) required @endif>
            @foreach ($config['options'] ?? [] as $optKey => $optLabel)
                <option value="{{ $optKey }}"
                        @selected((string) old("settings.$fieldKey", $groupsValues[$tabKey][$fieldKey] ?? $config['default'] ?? null) === (string) $optKey)>
                    {{ trans_or('admin.settings.fields.'.$fieldKey.'_options.'.$optKey, $optLabel) }}
                </option>
            @endforeach
        </select>

        {{-- پیش‌نمایش زندهٔ گزینه‌ها (فقط اگر فیلد preview داشته باشد) --}}
        @if (($config['preview'] ?? false) && !empty($config['options']))
            <div class="festive-preview-row d-flex gap-2 mt-3 flex-wrap">
                @foreach ($config['options'] as $optKey => $optLabel)
                    <div class="festive-preview-item">
                        <canvas data-festive-preview data-style="{{ $optKey }}" width="96" height="64" aria-hidden="true"></canvas>
                        <small class="d-block text-muted text-center mt-1" style="font-size: .68rem;">{{ trans_or('admin.settings.fields.'.$fieldKey.'_options.'.$optKey, $optLabel) }}</small>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- اینپوت متنی --}}
    @else
        <input type="{{ $config['type'] ?? 'text' }}" name="settings[{{ $fieldKey }}]" id="{{ $fieldKey }}"
               value="{{ old("settings.$fieldKey", $groupsValues[$tabKey][$fieldKey] ?? null) }}" class="input-custom"
               @if ($config['required'] ?? false) required @endif>
    @endif
</div>
