<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Models\Setting;
use App\Services\SettingService; // ۱. ایمپورت کردن سرویس
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    protected $settingService; // ۲. تعریف پراپرتی

    // ۳. تزریق وابستگی (Dependency Injection) از طریق کانستراکتور
    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    public function edit(string $group = 'general')
    {
        $config = config('settings');

        if (!isset($config['tabs'][$group])) {
            abort(404, 'گروه تنظیمات یافت نشد.');
        }

        // حالا این خط بدون خطا کار می‌کند
        $currentValues = $this->settingService->getGroup($group);

        // دریافت لیست تم‌ها برای بخش تنظیمات ظاهر
        $themes = \App\Models\Theme::all();
        $defaultThemeSlug = \App\Models\Theme::where('is_default', true)->value('slug') ?? 'light-neumorphism';

        return view('admin.settings.edit', [
            'activeGroup'      => $group,
            'tabs'             => $config['tabs'],
            'fields'           => $config['fields'],
            'currentValues'    => $currentValues,
            'themes'           => $themes,
            'defaultThemeSlug' => $defaultThemeSlug,
        ]);
    }

    public function update(UpdateSettingsRequest $request, string $group)
    {
        $validatedData = $request->validated()['settings'] ?? [];
        $files = $request->file('settings') ?? [];

        // ۱. اگر تم پیش‌فرض تغییر کرده بود، آن را به‌روز کن
        if ($request->has('default_theme')) {
            \App\Models\Theme::query()->update(['is_default' => false]);
            \App\Models\Theme::where('slug', $request->input('default_theme'))->update(['is_default' => true]);

            // پاک کردن کش تم برای اعمال فوری
            \Illuminate\Support\Facades\Cache::forget('active_theme_variables');
        }

        // ۲. ذخیره سایر تنظیمات (این خط هم حالا بدون خطا کار می‌کند)
        $this->settingService->updateGroup($group, $validatedData, $files);

        return redirect()->route('admin.settings.edit', $group)
            ->with('success', 'تنظیمات با موفقیت و با بالاترین سرعت به‌روزرسانی شد.');
    }
}
