<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Http\Request;

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

        if (!isset($config['tabs'][$group])) {
            abort(404, 'گروه تنظیمات یافت نشد.');
        }

        $currentValues = $this->settingService->getGroup($group);

        return view('admin.settings.edit', [
            'activeGroup'      => $group,
            'tabs'             => $config['tabs'],
            'fields'           => $config['fields'],
            'currentValues'    => $currentValues,
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
