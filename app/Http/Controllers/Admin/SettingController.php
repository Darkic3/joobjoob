<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Services\SettingService;

class SettingController extends Controller
{
    protected $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    public function edit(string $group = 'general')
    {
        $config = config('settings');

        if (! isset($config['tabs'][$group])) {
            abort(404, 'گروه تنظیمات یافت نشد.');
        }

        // مقادیر همه گروه‌ها — برای سوییچ تب بدون رفرش، همه پنل‌ها یکجا رندر می‌شوند
        $groupsValues = [];
        foreach ($config['tabs'] as $tabKey => $tabLabel) {
            $groupsValues[$tabKey] = $this->settingService->getGroup($tabKey);
        }

        // فیلدها گروه‌بندی‌شده بر اساس تب — کلید هر فیلد (مثل site_name) حفظ می‌شود
        // تا نام اینپوت‌ها (`settings[site_name]`) و برچسب‌ها درست رندر شوند.
        $fieldsByTab = [];
        foreach ($config['fields'] as $fieldKey => $field) {
            $fieldsByTab[$field['tab'] ?? 'general'][$fieldKey] = $field;
        }

        return view('admin.settings.edit', [
            'activeGroup' => $group,
            'tabs' => $config['tabs'],
            'fieldsByTab' => $fieldsByTab,
            'groupsValues' => $groupsValues,
        ]);
    }

    public function update(UpdateSettingsRequest $request, string $group)
    {
        $validatedData = $request->validated()['settings'] ?? [];
        $files = $request->file('settings') ?? [];

        $this->settingService->updateGroup($group, $validatedData, $files);

        return redirect()->route('admin.settings.edit', $group)
            ->with('success', __('admin.settings.saved'));
    }
}
