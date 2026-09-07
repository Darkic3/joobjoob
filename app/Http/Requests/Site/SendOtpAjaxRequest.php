<?php

namespace App\Http\Requests\Site;

use App\Services\OtpService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\App;

/**
 * ولیدیشن فرم ثبت‌نام AJAX (باکس «همین حالا ثبت‌نام کن و ۱۰٪ تخفیف بگیر»).
 *
 * چون فرانت‌اند منتظر فرمت JSON خاصی است:
 *   { success: false, field: 'mobile'|'name', error: '...' }
 * پاسخ خطای validation را به‌صورت سفارشی برمی‌گردانیم.
 */
class SendOtpAjaxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $mobile = $this->otpService()->normalizeMobile((string) $this->input('mobile', ''));
        $this->merge(['mobile' => $mobile]);

        return [
            'mobile' => ['required', 'string', function (string $attribute, string $value, \Closure $fail): void {
                if (! $this->otpService()->isValidMobile($value)) {
                    $fail(__('validation.requests.otp.mobile_invalid'));
                }
                if ($this->otpService()->userExists($value)) {
                    $fail(__('validation.requests.otp.mobile_registered'));
                }
            }],
            'name' => ['required', 'string', 'min:2', 'max:120'],
        ];
    }

    public function messages(): array
    {
        return [
            'mobile.required' => __('validation.requests.otp.mobile_required'),
            'name.required'   => __('validation.requests.otp.name_required'),
            'name.min'        => __('validation.requests.otp.name_min'),
            'name.max'        => __('validation.requests.otp.name_max'),
        ];
    }

    /**
     * پاسخ خطای validation سفارشی — فرمت JSON مورد انتظار فرانت‌اند.
     */
    protected function failedValidation(Validator $validator): void
    {
        $errors = $validator->errors();

        // اولویت: خطای موبایل → خطای نام
        if ($errors->has('mobile')) {
            $mobile = $this->otpService()->normalizeMobile((string) $this->input('mobile', ''));
            $isRegistered = $mobile !== '' && $this->otpService()->userExists($mobile);

            throw new HttpResponseException(response()->json([
                'success'    => false,
                'field'      => 'mobile',
                'registered' => $isRegistered,
                'error'      => $errors->first('mobile'),
            ]));
        }

        if ($errors->has('name')) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'field'   => 'name',
                'error'   => $errors->first('name'),
            ]));
        }

        // fallback
        throw new HttpResponseException(response()->json([
            'success' => false,
            'error'   => __('validation.requests.otp.general_error'),
        ]));
    }

    /**
     * نرمالایز شماره موبایل و برگرداندن آن.
     */
    public function normalizedMobile(): string
    {
        return $this->otpService()->normalizeMobile((string) $this->input('mobile', ''));
    }

    private function otpService(): OtpService
    {
        return App::make(OtpService::class);
    }
}
