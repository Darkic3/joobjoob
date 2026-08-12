@props([
    'promoBanner' => null,
    'bannerIcons' => \App\Models\PromoBanner::ICONS,
    'bannerTones' => \App\Models\PromoBanner::TONES,
])

@php($currentIcon = old('icon', $promoBanner->icon ?? 'megaphone'))

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">

        {{-- پیش‌نمایش زنده — همان کلاس‌های سایت (promo-preview.css در پنل) --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="text-base font-bold text-gray-800 dark:text-gray-100 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                {{ __('admin.promo_banners.preview') }}
            </h3>
            <div class="promo-banner-preview">
                <div id="pb-live-preview"
                     class="promo-banner promo-banner--{{ old('style', $promoBanner->style ?? 'gradient') }} promo-banner--{{ old('tone', $promoBanner->tone ?? 'accent') }}">
                    <span class="promo-banner-icon" id="pb-preview-icon" aria-hidden="true">
                        <x-promo-banner-icon :icon="$currentIcon" :size="22"/>
                    </span>
                    <span class="promo-banner-body">
                        <span class="promo-banner-title" data-digit-raw>{{ old('title.fa', $promoBanner->title['fa'] ?? '') ?: __('admin.promo_banners.title_ph') }}</span>
                        <span class="promo-banner-subtitle">{{ old('subtitle.fa', $promoBanner->subtitle['fa'] ?? '') ?: __('admin.promo_banners.subtitle_ph') }}</span>
                    </span>
                </div>
            </div>
        </div>

        {{-- عنوان (چندزبانه) --}}
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ __('admin.promo_banners.field_title') }} <span class="text-red-500">*</span>
            </label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <input type="text" id="pb_title_fa" name="title[fa]" value="{{ old('title.fa', $promoBanner->title['fa'] ?? '') }}"
                           class="input-custom" placeholder="{{ __('admin.promo_banners.title_ph') }}" required>
                    @error('title.fa')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <input type="text" id="pb_title_en" name="title[en]" value="{{ old('title.en', $promoBanner->title['en'] ?? '') }}"
                           class="input-custom" placeholder="Title (English)" dir="ltr">
                </div>
            </div>
        </div>

        {{-- زیرنویس (چندزبانه) --}}
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ __('admin.promo_banners.field_subtitle') }}
            </label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <input type="text" id="pb_subtitle_fa" name="subtitle[fa]" value="{{ old('subtitle.fa', $promoBanner->subtitle['fa'] ?? '') }}"
                           class="input-custom" placeholder="{{ __('admin.promo_banners.subtitle_ph') }}">
                </div>
                <div>
                    <input type="text" id="pb_subtitle_en" name="subtitle[en]" value="{{ old('subtitle.en', $promoBanner->subtitle['en'] ?? '') }}"
                           class="input-custom" placeholder="Subtitle (English)" dir="ltr">
                </div>
            </div>
        </div>

        {{-- لینک --}}
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ __('admin.promo_banners.field_url') }}
            </label>
            <input type="url" name="url" value="{{ old('url', $promoBanner->url ?? '') }}"
                   class="input-custom font-mono" dir="ltr"
                   placeholder="{{ __('admin.promo_banners.url_ph') }}">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                {{ __('admin.promo_banners.url_help') }}
            </p>
            @error('url')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="space-y-5">

        {{-- کارت تنظیمات نمایش --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="text-base font-bold text-gray-800 dark:text-gray-100 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                {{ __('admin.promo_banners.display') }}
            </h3>

            <div class="space-y-4">
                {{-- آیکون (از پیش‌طراحی‌شده) --}}
                <div class="space-y-1.5">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('admin.promo_banners.field_icon') }}</label>
                    <input type="hidden" name="icon" id="pb_icon" value="{{ $currentIcon }}">
                    <div class="grid grid-cols-5 gap-2" id="pb-icon-picker" role="radiogroup" aria-label="{{ __('admin.promo_banners.field_icon') }}">
                        @foreach($bannerIcons as $iconKey)
                            <button type="button" role="radio" data-banner-icon="{{ $iconKey }}"
                                    aria-checked="{{ $currentIcon === $iconKey ? 'true' : 'false' }}"
                                    title="{{ __('admin.promo_banners.icons.'.$iconKey) }}"
                                    class="flex items-center justify-center w-full aspect-square rounded-xl border transition-all duration-150 {{ $currentIcon === $iconKey
                                        ? 'border-primary-500 text-primary-600 bg-primary-50 dark:bg-primary-900/20 ring-2 ring-primary-500/30'
                                        : 'border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 hover:border-primary-400 hover:text-primary-600' }}">
                                <x-promo-banner-icon :icon="$iconKey" :size="20"/>
                            </button>
                        @endforeach
                    </div>
                    @error('icon')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- سبک طراحی --}}
                <div class="space-y-1.5">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('admin.promo_banners.field_style') }}</label>
                    <select id="pb_style" name="style" class="input-custom w-full">
                        @foreach(['gradient', 'neumorphic', 'minimal', 'dark', 'coupon'] as $styleKey)
                            <option value="{{ $styleKey }}" @selected(old('style', $promoBanner->style ?? 'gradient') === $styleKey)>
                                {{ __('admin.promo_banners.style_options.'.$styleKey) }}
                            </option>
                        @endforeach
                    </select>
                    @error('style')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- رنگ --}}
                <div class="space-y-1.5">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('admin.promo_banners.field_tone') }}</label>
                    <select id="pb_tone" name="tone" class="input-custom w-full">
                        @foreach($bannerTones as $toneKey)
                            <option value="{{ $toneKey }}" @selected(old('tone', $promoBanner->tone ?? 'accent') === $toneKey)>
                                {{ __('admin.promo_banners.tone_options.'.$toneKey) }}
                            </option>
                        @endforeach
                    </select>
                    @error('tone')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Toggle: فعال --}}
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('admin.promo_banners.active') }}</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active', $promoBanner->is_active ?? true) ? 'checked' : '' }}
                               class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- دکمه‌های اکشن --}}
<div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100 dark:border-gray-700 mt-6">
    <a href="{{ route('admin.promo-banners.index') }}"
       class="px-6 py-2.5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-colors font-medium">
        {{ __('admin.common.cancel') }}
    </a>
    <button type="submit" class="btn-primary flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ $promoBanner ? __('admin.promo_banners.edit_btn') : __('admin.promo_banners.create_btn') }}
    </button>
</div>

@push('scripts')
    <script>
        // پیش‌نمایش زنده — همگام‌سازی متن، آیکون و کلاس‌های سبک/رنگ با فرم
        document.addEventListener('DOMContentLoaded', function () {
            const preview = document.getElementById('pb-live-preview');
            if (!preview) return;

            const titleFa = document.getElementById('pb_title_fa');
            const titleEn = document.getElementById('pb_title_en');
            const subFa = document.getElementById('pb_subtitle_fa');
            const subEn = document.getElementById('pb_subtitle_en');
            const styleSel = document.getElementById('pb_style');
            const toneSel = document.getElementById('pb_tone');
            const iconInput = document.getElementById('pb_icon');
            const iconPicker = document.getElementById('pb-icon-picker');
            const previewIcon = document.getElementById('pb-preview-icon');

            const titleEl = preview.querySelector('.promo-banner-title');
            const subEl = preview.querySelector('.promo-banner-subtitle');
            const titleFallback = titleEl?.textContent.trim();
            const subFallback = subEl?.textContent.trim();

            const sync = function () {
                if (titleEl) {
                    const v = (titleFa?.value || titleEn?.value || '').trim();
                    titleEl.textContent = v || titleFallback || '';
                }
                if (subEl) {
                    const v = (subFa?.value || subEn?.value || '').trim();
                    subEl.textContent = v || subFallback || '';
                }
                if (styleSel) {
                    preview.classList.remove('promo-banner--gradient', 'promo-banner--neumorphic', 'promo-banner--minimal', 'promo-banner--dark', 'promo-banner--coupon');
                    preview.classList.add('promo-banner--' + styleSel.value);
                }
                if (toneSel) {
                    preview.classList.remove('promo-banner--accent', 'promo-banner--gold', 'promo-banner--emerald', 'promo-banner--rose', 'promo-banner--blue', 'promo-banner--orange', 'promo-banner--violet');
                    preview.classList.add('promo-banner--' + toneSel.value);
                }
            };

            [titleFa, titleEn, subFa, subEn].forEach(function (el) {
                if (el) el.addEventListener('input', sync);
            });
            if (styleSel) styleSel.addEventListener('change', sync);
            if (toneSel) toneSel.addEventListener('change', sync);

            // انتخابگر آیکون — کلیک روی هر دکمه: انتخاب + تعویض آیکون پیش‌نمایش
            if (iconPicker && iconInput && previewIcon) {
                const buttons = [...iconPicker.querySelectorAll('[data-banner-icon]')];
                const select = function (btn) {
                    buttons.forEach(function (b) {
                        b.classList.remove('border-primary-500', 'text-primary-600', 'bg-primary-50', 'dark:bg-primary-900/20', 'ring-2', 'ring-primary-500/30');
                        b.classList.add('border-gray-200', 'dark:border-gray-700', 'text-gray-500', 'dark:text-gray-400', 'hover:border-primary-400', 'hover:text-primary-600');
                        b.setAttribute('aria-checked', 'false');
                    });
                    btn.classList.remove('border-gray-200', 'dark:border-gray-700', 'text-gray-500', 'dark:text-gray-400', 'hover:border-primary-400', 'hover:text-primary-600');
                    btn.classList.add('border-primary-500', 'text-primary-600', 'bg-primary-50', 'dark:bg-primary-900/20', 'ring-2', 'ring-primary-500/30');
                    btn.setAttribute('aria-checked', 'true');
                    iconInput.value = btn.dataset.bannerIcon;
                    const svg = btn.querySelector('svg');
                    if (svg && previewIcon) previewIcon.innerHTML = svg.outerHTML;
                };
                buttons.forEach(function (btn) {
                    btn.addEventListener('click', function () { select(btn); });
                });
            }
        });
    </script>
@endpush
