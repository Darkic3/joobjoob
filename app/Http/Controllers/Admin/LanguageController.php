<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LanguageRequest;
use App\Models\Language;
use App\Services\LocaleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LanguageController extends Controller
{
    public function index(): View
    {
        $languages = Language::ordered()->get();

        return view('admin.languages.index', compact('languages'));
    }

    public function create(): View
    {
        return view('admin.languages.create');
    }

    public function store(LanguageRequest $request): RedirectResponse
    {
        $data = $this->normalizedData($request, 'store');

        $language = Language::create($data);

        // اگر این زبان پیش‌فرض شد → بقیه از پیش‌فرض خارج شوند
        if ($data['is_default']) {
            Language::where('is_default', true)->where('id', '!=', $language->id)->update(['is_default' => false]);
        }

        $this->ensureActiveDefault();

        app(LocaleService::class)->flush();

        return redirect()->route('admin.languages.index')->with('success', __('admin.languages.created'));
    }

    public function edit(Language $language): View
    {
        return view('admin.languages.edit', compact('language'));
    }

    public function update(LanguageRequest $request, Language $language): RedirectResponse
    {
        $data = $this->normalizedData($request, 'update');
        $wantActive = $data['is_active'];
        $wantDefault = $data['is_default'];

        // محافظت ۱: آخرین زبان فعال را نمی‌توان غیرفعال کرد
        if ($language->is_active && ! $wantActive && $this->activeCount() <= 1) {
            return back()->with('error', __('admin.languages.last_active_error'));
        }

        // محافظت ۲: زبان غیرفعال نمی‌تواند پیش‌فرض باشد
        if ($wantDefault && ! $wantActive) {
            return back()->with('error', __('admin.languages.inactive_default_error'));
        }

        $language->update($data);

        // اگر این زبان پیش‌فرض شد → بقیه از پیش‌فرض خارج شوند
        if ($wantDefault) {
            Language::where('is_default', true)->where('id', '!=', $language->id)->update(['is_default' => false]);
        }

        // اگر پیش‌فرضی معتبر نماند → به اولین زبان فعال منتقل شود
        $this->ensureActiveDefault();

        app(LocaleService::class)->flush();

        return redirect()->route('admin.languages.edit', $language)->with('success', __('admin.languages.updated'));
    }

    /**
     * اکشن سریع ایندکس: تغییر وضعیت فعال/غیرفعال (بدون ترک صفحه).
     */
    public function toggle(Language $language): RedirectResponse
    {
        // آخرین زبان فعال را نمی‌توان غیرفعال کرد
        if ($language->is_active && $this->activeCount() <= 1) {
            return back()->with('error', __('admin.languages.last_active_error'));
        }

        $language->update(['is_active' => ! $language->is_active]);

        // اگر زبان پیش‌فرض غیرفعال شد → پیش‌فرض به اولین زبان فعال دیگر منتقل شود
        $this->ensureActiveDefault();

        app(LocaleService::class)->flush();

        return back()->with('success', __('admin.languages.updated'));
    }

    /**
     * اکشن سریع ایندکس: تعیین پیش‌فرض (فقط برای زبان فعال).
     */
    public function makeDefault(Language $language): RedirectResponse
    {
        abort_unless($language->is_active, 403);

        Language::where('is_default', true)->update(['is_default' => false]);
        $language->update(['is_default' => true]);

        app(LocaleService::class)->flush();

        return back()->with('success', __('admin.languages.updated'));
    }

    public function destroy(Language $language): RedirectResponse
    {
        // زبان پیش‌فرض حذف نمی‌شود — اول پیش‌فرض را تغییر دهید
        if ($language->is_default) {
            return back()->with('error', __('admin.languages.default_not_deletable'));
        }

        // آخرین زبان فعال حذف نمی‌شود
        if ($language->is_active && $this->activeCount() <= 1) {
            return back()->with('error', __('admin.languages.last_active_error'));
        }

        $language->delete();

        $this->ensureActiveDefault();

        app(LocaleService::class)->flush();

        return redirect()->route('admin.languages.index')->with('success', __('admin.languages.deleted'));
    }

    /**
     * داده‌ی تمیزشده + تبدیل چک‌باکس‌ها به boolean.
     */
    private function normalizedData(LanguageRequest $request, string $mode): array
    {
        $data = $request->validated();

        // کد در ویرایش فقط‌خواندنی است (شناسه‌ی زبان)
        if ($mode === 'update') {
            unset($data['code']);
        }

        $data['is_active'] = $request->boolean('is_active');
        $data['is_default'] = $request->boolean('is_default');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        // نام‌های خالی را حذف کن تا localizedName() به fallback بیفتد
        if (isset($data['name'])) {
            $data['name'] = array_filter($data['name'], fn ($v) => $v !== null && trim($v) !== '');
        }

        return $data;
    }

    private function activeCount(): int
    {
        return Language::where('is_active', true)->count();
    }

    /**
     * تضمین: همیشه یک زبانِ پیش‌فرضِ فعال وجود دارد.
     * اگر نه، پیش‌فرض به اولین زبان فعال منتقل می‌شود.
     */
    private function ensureActiveDefault(): void
    {
        if (Language::where('is_default', true)->where('is_active', true)->exists()) {
            return;
        }

        $next = Language::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->first();

        if ($next) {
            Language::where('is_default', true)->update(['is_default' => false]);
            $next->update(['is_default' => true]);
        }
    }
}
