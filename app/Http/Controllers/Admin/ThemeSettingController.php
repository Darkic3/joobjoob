<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use Illuminate\Http\Request;

class ThemeSettingController extends Controller
{
    public function edit()
    {
        $themes = Theme::all();
        return view('admin.theme-settings', compact('themes'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'theme_id' => 'required|exists:themes,id',
            'colors' => 'nullable|array',
            'colors.bg' => 'nullable|string',
            'colors.accent' => 'nullable|string',
            // سایر رنگ‌ها را هم می‌توانید ولیدیت کنید
        ]);

        // ۱. تنظیم تم پیش‌فرض
        Theme::query()->update(['is_default' => false]);
        $theme = Theme::findOrFail($request->theme_id);
        $theme->update(['is_default' => true]);

        // ۲. ذخیره رنگ‌های سفارشی (اگر ارسال شده باشد)
        if ($request->has('colors')) {
            $currentVars = $theme->css_variables ?? [];
            $newVars = array_merge($currentVars, array_filter($request->colors));
            $theme->update(['css_variables' => $newVars]);
        }

        // ۳. پاک کردن کش برای اعمال فوری تغییرات
        \Illuminate\Support\Facades\Cache::forget('themes.all');

        return back()->with('success', 'تنظیمات تم با موفقیت و به صورت آنی ذخیره شد!');
    }
}
