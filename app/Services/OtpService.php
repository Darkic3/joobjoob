<?php

namespace App\Services;

use App\Helpers\Helper;
use App\Helpers\Sms;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * ورود/ثبت‌نام با کد یکبارمصرف (OTP) از طریق پیامک کاوه‌نگار.
 *
 * جریان: کاربر شماره موبایل می‌دهد → کد ۴ رقمی ساخته و روی کاربر ذخیره می‌شود
 * (اگر کاربر وجود نداشته باشد ساخته می‌شود) → پیامک الگودار ارسال می‌شود →
 * کاربر کد را وارد می‌کند → کد چک و لاگین می‌شود.
 */
class OtpService
{
    private const CODE_LENGTH = 4;

    public function __construct(private readonly Sms $sms) {}

    /**
     * نرمال‌سازی شماره موبایل به فرمت 09xxxxxxxxx
     * (پشتیبانی از ارقام فارسی/عربی، +98، 0098 و بدون صفر اول)
     */
    public function normalizeMobile(string $mobile): string
    {
        $digits = preg_replace('/\D/', '', Helper::persian2LatinDigit($mobile)) ?? '';

        if (str_starts_with($digits, '0098') && strlen($digits) === 14) {
            return '0'.substr($digits, 4);
        }

        if (str_starts_with($digits, '98') && strlen($digits) === 12) {
            return '0'.substr($digits, 2);
        }

        if (str_starts_with($digits, '9') && strlen($digits) === 10) {
            return '0'.$digits;
        }

        return $digits;
    }

    public function isValidMobile(string $mobile): bool
    {
        return (bool) preg_match('/^09\d{9}$/', $mobile);
    }

    /**
     * ساخت کد ۴ رقمی، ذخیره روی کاربر و ارسال پیامک.
     *
     * @return array{success: bool, message: string, code?: string}
     */
    public function send(string $mobile): array
    {
        $mobile = $this->normalizeMobile($mobile);

        if (! $this->isValidMobile($mobile)) {
            return ['success' => false, 'message' => __('messages.otp_mobile_invalid')];
        }

        $user = User::where('mobile', $mobile)->first();

        if ($user && ! $user->is_active) {
            return ['success' => false, 'message' => __('messages.otp_mobile_blocked')];
        }

        // کاربر وجود ندارد → ساخت (ثبت‌نام خودکار با OTP)؛
        // رمز عبور placeholder چون ورود فقط از طریق کد یکبارمصرف انجام می‌شود.
        if (! $user) {
            $user = User::create([
                'name' => null,
                'mobile' => $mobile,
                'password' => Str::random(32),
                'is_active' => true,
            ]);
        }

        $code = $this->generateCode();
        $user->update([
            'confirm_code' => $code,
            'confirm_code_expires_at' => now()->addMinutes((int) config('kavenegar.otp_ttl_minutes', 2)),
        ]);

        $template = setting('otp_template') ?: 'verify';

        try {
            $response = $this->sms->sendLookup($template, ['token' => $code], $mobile);

            if ($response instanceof RedirectResponse) {
                // کلید API تنظیم نشده است
                return $this->result(false, __('messages.otp_sms_not_configured'), $code);
            }

            if ($response === false) {
                return $this->result(false, __('messages.otp_sms_failed'), $code);
            }

            $decoded = json_decode((string) $response, true);

            if (($decoded['return']['status'] ?? null) === 200) {
                return $this->result(true, __('messages.otp_code_sent'), $code);
            }

            $apiMessage = $decoded['return']['message'] ?? 'API error';
            Log::error("Kavenegar OTP failed for {$mobile}: {$apiMessage}");

            return $this->result(false, __('messages.otp_sms_failed'), $code);
        } catch (\Throwable $e) {
            Log::error("Kavenegar OTP exception for {$mobile}: ".$e->getMessage());

            return $this->result(false, __('messages.otp_sms_failed'), $code);
        }
    }

    /**
     * بررسی کد واردشده و لاگین کاربر.
     *
     * @return array{success: bool, message: string}
     */
    public function verify(string $mobile, string $code): array
    {
        $user = User::where('mobile', $mobile)->first();

        if (! $user || ! $user->is_active) {
            return ['success' => false, 'message' => __('messages.otp_code_invalid')];
        }

        if (! $user->confirm_code || $user->confirm_code !== $code) {
            return ['success' => false, 'message' => __('messages.otp_code_invalid')];
        }

        if ($user->confirm_code_expires_at && $user->confirm_code_expires_at->isPast()) {
            return ['success' => false, 'message' => __('messages.otp_code_expired')];
        }

        // کد یکبارمصرف است — بعد از استفاده پاک می‌شود
        $user->update([
            'confirm_code' => null,
            'confirm_code_expires_at' => null,
        ]);

        Auth::login($user, true);

        return ['success' => true, 'message' => __('messages.otp_login_success')];
    }

    private function generateCode(): string
    {
        return str_pad((string) random_int(0, 9999), self::CODE_LENGTH, '0', STR_PAD_LEFT);
    }

    /**
     * کد فقط در محیط debug برگردانده می‌شود تا بدون ارسال واقعی هم تست شود.
     *
     * @return array{success: bool, message: string, code?: string}
     */
    private function result(bool $success, string $message, string $code): array
    {
        $result = ['success' => $success, 'message' => $message];

        if (config('app.debug')) {
            $result['code'] = $code;
        }

        return $result;
    }
}
