<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use Illuminate\Http\Request;

/**
 * پنل ادمین: انتخاب تم پیش‌فرض سایت از بین تم‌های از پیش تعریف‌شده.
 */
class ThemeSettingController extends Controller
{
    public function edit()
    {
        $themes = Theme::all();

        return view('admin.theme-settings', compact('themes'));
    }

    public function update(Request $request)
    {
        $request->validate(['theme_id' => 'required|exists:themes,id']);

        Theme::query()->update(['is_default' => false]);
        Theme::where('id', $request->theme_id)->update(['is_default' => true]);

        return back()->with('success', __('messages.theme_updated'));
    }
}
