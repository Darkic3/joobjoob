<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

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

        if (Auth::attempt($credentials + ['is_active' => true], $request->boolean('remember'))) {
            return redirect()->intended(localizedRoute('dashboard'));
        }

        return back()->withErrors(['email' => __('messages.login_failed')])->onlyInput('email');
    }

    /**
     * خروج از حساب کاربری.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $this->clearRecallerCookie();

        return redirect()->route('home');
    }

    private function clearRecallerCookie(): void
    {
        $guard = Auth::guard();
        $name  = 'remember_web_'.$guard->getName().'_'.md5($guard::class);

        if (Cookie::hasQueued($name)) {
            Cookie::queue(Cookie::forget($name));
        }
    }
}
