<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiteAuthController extends Controller
{
    /**
     * صفحه ورود/ثبت‌نام — فرم شماره موبایل (OTP).
     * کاربر شماره را وارد می‌کند:
     *   - اگر وجود داشته باشد → کد OTP ارسال می‌شود
     *   - اگر وجود نداشته باشد → فیلد نام ظاهر شده و کاربر ثبت‌نام می‌شود
     */
    public function showLogin(Request $request)
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        if ($request->boolean('change')) {
            $request->session()->forget('otp_mobile');
            $request->session()->forget('otp_new_user');
        }

        return view('site.auth.otp', [
            'otpMobile' => $request->session()->get('otp_mobile'),
            'isNewUser' => $request->session()->get('otp_new_user', false),
            'showNameField' => $request->session()->get('otp_new_user', false),
        ]);
    }

    /**
     * خروج از حساب کاربری.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
