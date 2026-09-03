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
 * جریان: کاربر شماره موبایل می‌دهد → بررسی وجود کاربر
 *   - اگر کاربر وجود داشته باشد: کد ارسال می‌شود
 *   - اگر کاربر وجود نداشته باشد و نام وارد شده باشد: کاربر ساخته شده و کد ارسال می‌شود
 *   - اگر کاربر وجود نداشته باشد بدون نام: نیاز به ورود نام
 * → کاربر کد را وارد می‌کند → کد چک و لاگین می‌شود.
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
     * بررسی وجود کاربر با شماره موبایل.
     */
    public function userExists(string $mobile): bool
    {
        return User::where('mobile', $mobile)->exists();
    }

    /**
     * ساخت کد ۴ رقمی، ذخیره روی کاربر و ارسال پیامک.
     *
     * @param  string  $mobile  شماره موبایل نرمال‌شده
     * @param  string|null  $name  نام کاربر (فقط برای کاربران جدید)
     * @return array{success: bool, message: string, code?: string}
     */
    public function send(string $mobile, ?string $name = null): array
    {
        $mobile = $this->normalizeMobile($mobile);

        if (! $this->isValidMobile($mobile)) {
            return ['success' => false, 'message' => __('messages.otp_mobile_invalid')];
        }

        $user = User::where('mobile', $mobile)->first();

        if ($user && ! $user->is_active) {
            return ['success' => false, 'message' => __('messages.otp_mobile_blocked')];
        }

        // کاربر وجود ندارد → ساخت با نام ارائه‌شده
        if (! $user) {
            if (! $name || trim($name) === '') {
                return ['success' => false, 'message' => __('messages.otp_name_required')];
            }

            $user = User::create([
                'name' => trim($name),
                'mobile' => $mobile,
                'password' => Str::random(32),
                'is_active' => true,
            ]);

            // ایجاد کد تخفیف خوشامدگویی ۱۰٪ برای کاربر جدید
            app(\App\Services\WelcomeDiscountService::class)->createForUser($user);
        }


        $code = $this->generateCode();
        $user->update([
            'confirm_code' => $code,
            'confirm_code_expires_at' => now()->addMinutes((int) config('kavenegar.otp_ttl_minutes', 2)),
        ]);

        $template = setting('otp_template') ?: 'verify';

        try {
            // token = کد OTP (فقط عدد — بدون فاصله)
            // token2 = نام سایت (پاکسازی‌شده برای سازگاری با قالب کاوه‌نگار)
            $siteName = $this->cleanSiteName(setting('site_name', config('app.name', 'Shopora')));
            $response = $this->sms->sendLookup($template, ['token' => $code, 'token2' => $siteName], $mobile);

            if ($response instanceof RedirectResponse) {
                return $this->result(false, __('messages.otp_sms_not_configured'));
            }

            if ($response === false) {
                return $this->result(false, __('messages.otp_sms_failed'));
            }

            $decoded = json_decode((string) $response, true);

            if (($decoded['return']['status'] ?? null) === 200) {
                return $this->result(true, __('messages.otp_code_sent'));
            }

            $apiMessage = $decoded['return']['message'] ?? 'API error';
            Log::error("Kavenegar OTP failed for {$mobile}: {$apiMessage}");

            return $this->result(false, __('messages.otp_sms_failed'));
        } catch (\Throwable $e) {
            Log::error("Kavenegar OTP exception for {$mobile}: ".$e->getMessage());

            return $this->result(false, __('messages.otp_sms_failed'));
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
     * پاکسازی نام سایت برای ارسال در token2 قالب کاوه‌نگار.
     *
     *  - اگر بیش از یک کلمه باشد و کلمه‌ی اول «فروشگاه» باشد → کلمه‌ی دوم
     *  - اگر بیش از یک کلمه باشد و کلمه‌ی اول «فروشگاه» نباشد → کلمه‌ی اول
     *  - اگر یک کلمه باشد → همان کلمه
     */
    private function cleanSiteName(string $raw): string
    {
        $parts = preg_split('/\s+/', trim($raw));

        if (count($parts) >= 2 && $parts[0] === 'فروشگاه') {
            return $parts[1];
        }

        return $parts[0] ?? $raw;
    }

    /**
     * @return array{success: bool, message: string}
     */
    private function result(bool $success, string $message): array
    {
        return ['success' => $success, 'message' => $message];
    }
}
