<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OtpController extends Controller
{
    public function __construct(private readonly OtpService $otpService) {}

    /**
     * صفحه اصلی ورود/ثبت‌نام — فقط شماره موبایل.
     * اگر کاربر جدید باشد (otp_new_user در سشن) فیلد نام هم نمایش داده می‌شود.
     */
    public function show(Request $request)
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
        ]);
    }

    /**
     * دریافت شماره (+ نام در صورت کاربر جدید) و ارسال کد تایید.
     */
    public function send(Request $request): RedirectResponse
    {
        $mobile = $this->otpService->normalizeMobile((string) $request->input('mobile', ''));
        $request->merge(['mobile' => $mobile]);

        // ابتدا فرمت شماره موبایل را اعتبارسنجی می‌کنیم
        $request->validate([
            'mobile' => ['required', 'string', function (string $attribute, string $value, callable $fail): void {
                if (! $this->otpService->isValidMobile($value)) {
                    $fail(__('messages.otp_mobile_invalid'));
                }
            }],
        ]);

        $isNewUser = ! $this->otpService->userExists($mobile);

        if ($isNewUser) {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:100'],
            ]);

            $result = $this->otpService->send($mobile, $data['name']);
        } else {
            $result = $this->otpService->send($mobile);
        }

        $normalizedMobile = $this->otpService->normalizeMobile($mobile);
        $request->session()->put('otp_mobile', $normalizedMobile);
        $request->session()->forget('otp_new_user');

        if (isset($result['code'])) {
            $request->session()->flash('otp_dev_code', $result['code']);
        }

        if (! $result['success']) {
            return redirect()->route('login.verify')->with('error', $result['message']);
        }

        return redirect()->route('login.verify')->with('success', $result['message']);
    }

    /**
     * صفحه جداگانه وارد کردن کد تایید.
     */
    public function showVerify(Request $request)
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        $mobile = $request->session()->get('otp_mobile');

        if (! $mobile) {
            return redirect()->route('login');
        }

        return view('site.auth.verify', [
            'otpMobile' => $mobile,
        ]);
    }

    /**
     * تایید کد و ورود.
     */
    public function verify(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'digits:4'],
        ]);

        $mobile = $request->session()->get('otp_mobile');

        if (! $mobile) {
            return redirect()->route('login')
                ->withErrors(['mobile' => __('messages.otp_session_expired')]);
        }

        $result = $this->otpService->verify($mobile, $data['code']);

        if (! $result['success']) {
            return back()->withErrors(['code' => $result['message']]);
        }

        $request->session()->forget('otp_mobile');
        $request->session()->regenerate();

        return redirect()->intended(localizedRoute('dashboard'));
    }

    /**
     * ارسال مجدد کد.
     */
    public function resend(Request $request): RedirectResponse
    {
        $mobile = $request->session()->get('otp_mobile');

        if (! $mobile) {
            return redirect()->route('login');
        }

        $result = $this->otpService->send($mobile);

        if (! $result['success']) {
            return back()->withErrors(['code' => $result['message']]);
        }

        if (isset($result['code'])) {
            $request->session()->flash('otp_dev_code', $result['code']);
        }

        return back()->with('success', __('messages.otp_resent'));
    }
}
