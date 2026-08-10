<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LanguageRequest;
use App\Models\Language;
use App\Services\LocaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        return view('admin.languages.create', ['nameLocales' => app(LocaleService::class)->active()]);
    }

    public function store(LanguageRequest $request): RedirectResponse
    {
        $data = $this->normalizedData($request, 'store');

        // زبان جدید همیشه به انتهای ترتیب اضافه می‌شود (سورت از ایندکس با درگ‌اندروپ)
        $data['sort_order'] = (Language::max('sort_order') ?? 0) + 1;

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
        // کدهای زبانی که برای «نام ترجمه‌شده» ورودی می‌گیریم:
        // همهٔ زبان‌های فعال + کد خودِ زبانِ در حال ویرایش (حتی اگر غیرفعال باشد)
        $nameLocales = app(LocaleService::class)->active();
        if (! isset($nameLocales[$language->code])) {
            $nameLocales = [$language->code => $language] + $nameLocales;
        }

        return view('admin.languages.edit', ['language' => $language, 'nameLocales' => $nameLocales]);
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

    /**
     * اکشن سریع ایندکس: ذخیره‌ی ترتیب جدید (درگ‌اندروپ) — همه زبان‌ها با ترتیب جدید.
     * ترتیب روی sort_order نوشته می‌شود تا scopes (ordered) و سوییچرها آن را رعایت کنند.
     */
    public function reorder(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:languages,id'],
        ]);

        $ids = array_values(array_unique(array_map('intval', $validated['ids'])));

        // ترتیب باید شامل همه‌ی زبان‌ها باشد — در غیر این صورت ترتیب خراب می‌شود
        if (count($ids) !== Language::count()) {
            return $request->expectsJson()
                ? response()->json(['ok' => false, 'message' => __('admin.languages.order_invalid')], 422)
                : back()->with('error', __('admin.languages.order_invalid'));
        }

        foreach ($ids as $index => $id) {
            Language::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        app(LocaleService::class)->flush();

        // مسیر AJAX (استفادهٔ اصلی UI): پیام در پاسخ JSON برمی‌گردد و JS بدون رفرش toast نشان می‌دهد.
        // فلش فقط برای درخواست‌های فرم (غیر AJAX) ست می‌شود تا toast منسوخ روی صفحهٔ بعدی ظاهر نشود.
        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => __('admin.languages.order_saved')]);
        }

        session()->flash('success', __('admin.languages.order_saved'));

        return back();
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
