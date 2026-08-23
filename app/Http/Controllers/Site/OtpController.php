<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OtpController extends Controller
{
    public function __construct(private readonly OtpService $otpService) {}

    /**
     * برگرداندن به صفحه اصلی ورود/ثبت‌نام (backward compatibility).
     */
    public function show(Request $request)
    {
        return redirect()->route('login', $request->query());
    }

    /**
     * بررسی AJAX وجود شماره موبایل.
     * فرانت‌اند قبل از ارسال فرم، شماره را چک می‌کند تا فیلد نام را نشان/پنهان کند.
     */
    public function checkPhone(Request $request): JsonResponse
    {
        $mobile = $this->otpService->normalizeMobile((string) $request->input('mobile', ''));

        if (! $this->otpService->isValidMobile($mobile)) {
            return response()->json(['exists' => null, 'valid' => false]);
        }

        return response()->json([
            'exists' => $this->otpService->userExists($mobile),
            'valid'   => true,
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
            $name = $request->input('name');

            // اگر کاربر جدید است ولی نام وارد نشده → به صفحه ورود برگردان با فلگ
            if (! $name || trim($name) === '') {
                $request->session()->put('otp_mobile', $mobile);
                $request->session()->put('otp_new_user', true);

                return redirect()->route('login')
                    ->withInput(['mobile' => $mobile])
                    ->withErrors(['name' => __('messages.otp_name_required')]);
            }

            $result = $this->otpService->send($mobile, $name);
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
