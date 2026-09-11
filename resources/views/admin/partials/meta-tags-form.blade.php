<div class="card card-default">
    <div class="card-header">
        <h3 class="card-title">تنظیمات SEO و متا تگ‌ها</h3>
    </div>
    <div class="card-body">
        <div class="form-group">
            <label for="meta_title">عنوان متا (Title)</label>
            <input type="text" name="meta_title" id="meta_title" class="form-control"
                   value="{{ old('meta_title', $meta?->title) }}" placeholder="عنوان متا" maxlength="255">
            <small class="form-text text-muted">حداکثر 60-70 کاراکتر برای نمایش بهتر در گوگل. <span id="meta_title_count">0</span> کاراکتر.</small>
            @error('meta_title')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="meta_description">توضیحات متا (Description)</label>
            <textarea name="meta_description" id="meta_description" class="form-control" rows="3"
                      placeholder="توضیحات متا" maxlength="500">{{ old('meta_description', $meta?->description) }}</textarea>
            <small class="form-text text-muted">حداکثر 150-160 کاراکتر برای نمایش بهتر در گوگل. <span id="meta_description_count">0</span> کاراکتر.</small>
            @error('meta_description')
                <div class="text-danger">{{ $message }}</div>
            @endror
        </div>

        <div class="form-group">
            <label for="meta_keywords">کلمات کلیدی (Keywords)</label>
            <input type="text" name="meta_keywords" id="meta_keywords" class="form-control"
                   value="{{ old('meta_keywords', $meta?->keywords) }}" placeholder="کلمات کلیدی (با کاما جدا کنید)" maxlength="255">
            @error('meta_keywords')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="meta_canonical_url">Canonical URL</label>
            <input type="url" name="meta_canonical_url" id="meta_canonical_url" class="form-control"
                   value="{{ old('meta_canonical_url', $meta?->canonical_url) }}" placeholder="https://example.com/canonical-page" maxlength="500">
            <small class="form-text text-muted">آدرس اصلی و ترجیحی این صفحه.</small>
            @error('meta_canonical_url')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="meta_robots">Robots</label>
            <input type="text" name="meta_robots" id="meta_robots" class="form-control"
                   value="{{ old('meta_robots', $meta?->robots ?? 'index, follow') }}" placeholder="index, follow" maxlength="255">
            <small class="form-text text-muted">دستورالعمل برای ربات‌های موتور جستجو.</small>
            @error('meta_robots')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <hr>
        <h5>Open Graph (برای اشتراک‌گذاری در شبکه‌های اجتماعی)</h5>

        <div class="form-group">
            <label for="meta_og_title">OG Title</label>
            <input type="text" name="meta_og_title" id="meta_og_title" class="form-control"
                   value="{{ old('meta_og_title', $meta?->og_title) }}" placeholder="OG Title" maxlength="255">
        </div>

        <div class="form-group">
            <label for="meta_og_description">OG Description</label>
            <textarea name="meta_og_description" id="meta_og_description" class="form-control" rows="3"
                      placeholder="OG Description" maxlength="500">{{ old('meta_og_description', $meta?->og_description) }}</textarea>
        </div>

        <div class="form-group">
            <label for="meta_og_image">OG Image URL</label>
            <input type="url" name="meta_og_image" id="meta_og_image" class="form-control"
                   value="{{ old('meta_og_image', $meta?->og_image) }}" placeholder="https://example.com/image.jpg" maxlength="500">
            <small class="form-text text-muted">لینک تصویر برای Open Graph.</small>
        </div>

        <div class="form-group">
            <label for="meta_og_type">OG Type</label>
            <input type="text" name="meta_og_type" id="meta_og_type" class="form-control"
                   value="{{ old('meta_og_type', $meta?->og_type ?? 'website') }}" placeholder="website" maxlength="255">
        </div>

        <div class="form-group">
            <label for="meta_og_url">OG URL</label>
            <input type="url" name="meta_og_url" id="meta_og_url" class="form-control"
                   value="{{ old('meta_og_url', $meta?->og_url) }}" placeholder="https://example.com/page-url" maxlength="500">
        </div>

        <hr>
        <h5>Twitter Cards</h5>

        <div class="form-group">
            <label for="meta_twitter_card">Twitter Card Type</label>
            <input type="text" name="meta_twitter_card" id="meta_twitter_card" class="form-control"
                   value="{{ old('meta_twitter_card', $meta?->twitter_card ?? 'summary_large_image') }}" placeholder="summary_large_image" maxlength="255">
        </div>

        <div class="form-group">
            <label for="meta_twitter_site">Twitter Site (@username)</label>
            <input type="text" name="meta_twitter_site" id="meta_twitter_site" class="form-control"
                   value="{{ old('meta_twitter_site', $meta?->twitter_site) }}" placeholder="@yoursite" maxlength="255">
        </div>

        <div class="form-group">
            <label for="meta_twitter_creator">Twitter Creator (@username)</label>
            <input type="text" name="meta_twitter_creator" id="meta_twitter_creator" class="form-control"
                   value="{{ old('meta_twitter_creator', $meta?->twitter_creator) }}" placeholder="@author" maxlength="255">
        </div>

        <div class="form-group">
            <label for="meta_twitter_title">Twitter Title</label>
            <input type="text" name="meta_twitter_title" id="meta_twitter_title" class="form-control"
                   value="{{ old('meta_twitter_title', $meta?->twitter_title) }}" placeholder="Twitter Title" maxlength="255">
        </div>

        <div class="form-group">
            <label for="meta_twitter_description">Twitter Description</label>
            <textarea name="meta_twitter_description" id="meta_twitter_description" class="form-control" rows="3"
                      placeholder="Twitter Description" maxlength="500">{{ old('meta_twitter_description', $meta?->twitter_description) }}</textarea>
        </div>

        <div class="form-group">
            <label for="meta_twitter_image">Twitter Image URL</label>
            <input type="url" name="meta_twitter_image" id="meta_twitter_image" class="form-control"
                   value="{{ old('meta_twitter_image', $meta?->twitter_image) }}" placeholder="https://example.com/image.jpg" maxlength="500">
            <small class="form-text text-muted">لینک تصویر برای Twitter Card.</small>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function updateCharCount(inputId, countId, maxLength) {
            const input = document.getElementById(inputId);
            const countSpan = document.getElementById(countId);

            if (input && countSpan) {
                function update() {
                    const currentLength = input.value.length;
                    countSpan.textContent = currentLength;
                    if (currentLength > maxLength) {
                        countSpan.style.color = 'red';
                    } else {
                        countSpan.style.color = 'inherit';
                    }
                }
                input.addEventListener('input', update);
                update(); // Initial update
            }
        }

        updateCharCount('meta_title', 'meta_title_count', 70); // Google recommends 60-70
        updateCharCount('meta_description', 'meta_description_count', 160); // Google recommends 150-160
    });
</script>
@endpush
