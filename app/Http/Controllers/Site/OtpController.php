<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OtpController extends Controller
{
    public function __construct(private readonly OtpService $otpService) {}

    public function show(Request $request)
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        // لینک «تغییر شماره» → پاک‌کردن شماره از سشن تا مرحله‌ی اول نمایش داده شود
        if ($request->boolean('change')) {
            $request->session()->forget('otp_mobile');
        }

        return view('site.auth.otp', [
            'otpMobile' => $request->session()->get('otp_mobile'),
        ]);
    }

    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mobile' => ['required', 'string'],
        ]);

        $result = $this->otpService->send($data['mobile']);

        if (! $result['success']) {
            return back()
                ->withErrors(['mobile' => $result['message']])
                ->onlyInput('mobile');
        }

        $request->session()->put('otp_mobile', $this->otpService->normalizeMobile($data['mobile']));

        // فقط در محیط debug — نمایش کد برای تست بدون ارسال واقعی
        if (isset($result['code'])) {
            $request->session()->flash('otp_dev_code', $result['code']);
        }

        return redirect()->route('login.otp')->with('success', $result['message']);
    }

    public function verify(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'digits:4'],
        ]);

        $mobile = $request->session()->get('otp_mobile');

        if (! $mobile) {
            return redirect()->route('login.otp')
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

    public function resend(Request $request): RedirectResponse
    {
        $mobile = $request->session()->get('otp_mobile');

        if (! $mobile) {
            return redirect()->route('login.otp');
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
