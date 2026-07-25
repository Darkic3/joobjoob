<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Services\SettingService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(
        protected SettingService $settingService
    ) {}

    public function edit(string $group = 'general')
    {
        // دریافت ساختار فرم از کانفیگ
        $config = config('settings');

        // اگر گروه وجود نداشت، خطای ۴۰۴
        if (!isset($config[$group])) {
            abort(404, 'گروه تنظیمات یافت نشد.');
        }

        // دریافت مقادیر فعلی (از کش یا دیتابیس)
        $currentValues = $this->settingService->getGroup($group);

        return view('admin.settings.edit', [
            'activeGroup' => $group,
            'groups' => $config,
            'fields' => $config[$group]['fields'],
            'values' => $currentValues,
        ]);
    }

    public function update(UpdateSettingsRequest $request, string $group)
    {
        $validatedData = $request->validated()['settings'] ?? [];
        $files = $request->file('settings') ?? [];

        $this->settingService->updateGroup($group, $validatedData, $files);

        return redirect()->route('admin.settings.edit', $group)
            ->with('success', 'تنظیمات با موفقیت و با بالاترین سرعت به‌روزرسانی شد.');
    }
}
