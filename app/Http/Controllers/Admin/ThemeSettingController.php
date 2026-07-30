<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ThemeSettingController extends Controller
{
    public function edit()
    {
        // فقط تم پیش‌فرض را برای ویرایش می‌گیریم
        $theme = Theme::where('is_default', true)->first();
        return view('admin.theme-settings', compact('theme'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'bg' => 'required|string',
            'accent' => 'required|string',
            'text_primary' => 'required|string',
            'danger' => 'required|string',
        ]);

        $theme = Theme::where('is_default', true)->first();

        // نگاشت نام inputها به متغیرهای CSS
        $customColors = [
            '--bg' => $request->bg,
            '--bg-elevated' => $request->bg, // در نئومورفیسم معمولاً یکی هستند
            '--accent' => $request->accent,
            '--text-primary' => $request->text_primary,
            '--danger' => $request->danger,
        ];

        $theme->update([
            'preview_color' => $request->bg,
            'css_variables' => $customColors,
        ]);

        // پاک کردن کش تم برای اعمال فوری
        Cache::forget('active_theme_variables');

        return back()->with('success', 'رنگ‌بندی سایت با موفقیت به‌روزرسانی شد. (برای مشاهده تغییرات، صفحه را رفرش کنید)');
    }
}
