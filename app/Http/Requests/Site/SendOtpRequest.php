<?php

namespace App\Http\Requests\Site;

use App\Services\OtpService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;

/**
 * اعتبارسنجی حرفه‌ای فرم ارسال کد تایید (OTP).
 *
 * اهداف:
 *   ۱. نرمال‌سازی شماره موبایل (تبدیل ارقام و حذف کاراکترهای اضافه).
 *   ۲. ولیدیشن دقیق فرمت شماره موبایل ایران.
 *   ۳. ولیدیشن نام (در صورت لزوم).
 */
class SendOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $mobile = $this->otpService()->normalizeMobile((string) $this->input('mobile', ''));
        $this->merge(['mobile' => $mobile]);
    }

    public function rules(): array
    {
        return [
            'mobile' => ['required', 'string', function (string $attribute, string $value, \Closure $fail): void {
                if (! $this->otpService()->isValidMobile($value)) {
                    $fail(__('validation.requests.otp.mobile_invalid'));
                }
            }],
            'name' => ['nullable', 'string', 'min:2', 'max:120'],
        ];
    }

    public function messages(): array
    {
        return [
            'mobile.required' => __('validation.requests.otp.mobile_required'),
            'name.min'        => __('validation.requests.otp.name_min'),
            'name.max'        => __('validation.requests.otp.name_max'),
        ];
    }

    private function otpService(): OtpService
    {
        return App::make(OtpService::class);
    }
}
