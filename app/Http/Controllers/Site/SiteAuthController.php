<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiteAuthController extends Controller
{
    public function __construct(private readonly OtpService $otpService) {}

    public function showLogin()
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        return view('site.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        // شماره موبایل را قبل از اعتبارسنجی نرمال‌سازی می‌کنیم (۰۹۱۲۳۴۵۶۷۸۹)
        // تا ورودی‌هایی مثل +98، 0098 یا ارقام فارسی/عربی هم درست شناسایی شوند.
        $mobile = $this->otpService->normalizeMobile((string) $request->input('mobile', ''));
        $request->merge(['mobile' => $mobile]);

        $credentials = $request->validate([
            'mobile' => ['required', 'string', function (string $attribute, string $value, callable $fail): void {
                if (! $this->otpService->isValidMobile($value)) {
                    $fail(__('messages.otp_mobile_invalid'));
                }
            }],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials + ['is_active' => true], $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(localizedRoute('dashboard'));
        }

        return back()->withErrors(['mobile' => __('messages.login_failed')])->onlyInput('mobile');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
