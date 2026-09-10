<?php

namespace App\Http\Requests\Site;

use Illuminate\Foundation\Http\FormRequest;

/**
 * اعتبارسنجی حرفه‌ای فرم «تایید کد یکبارمصرف».
 *
 * اهداف:
 *   ۱. کاربر با هر زبانی (فارسی/عربی/انگلیسی) کد را وارد کند، قبل از اعتبارسنجی
 *      به ارقام لاتین نرمال می‌شود — پس هرگز خطای «کد باید ۴ رقم باشد» به‌خاطر
 *      خانوادهٔ ارقام رخ نمی‌دهد.
 *   ۲. فقط رقم پذیرفته می‌شود؛ حرف/فاصله/خط‌تیره دور ریخته می‌شود (مثل رفتار JS سمت کلاینت).
 *   ۳. پیام‌های خطا از validation.requests.otp خوانده می‌شود (ترجمه‌پذیر).
 */
class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        // اطمینان از اینکه شماره موبایل در سشن موجود است (جلوگیری از دور زدن مرحله اول یا انقضای سشن)
        return $this->session()->has('otp_mobile');
    }

    /**
     * ریدایرکت کاربر در صورت انقضای سشن یا دسترسی غیرمجاز.
     */
    protected function failedAuthorization()
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            redirect()->route('login')->withErrors(['mobile' => __('messages.otp_session_expired')])
        );
    }

    /**
     * نرمال‌سازی قبل از اعتبارسنجی: ارقام فارسی/عربی → لاتین + حذف هر کاراکتر غیررقمی.
     */
    protected function prepareForValidation(): void
    {
        $code = (string) $this->input('code', '');

        $this->merge([
            'code' => $this->toLatinDigits($code),
        ]);
    }

    public function rules(): array
    {
        return [
            // بعد از prepareForValidation مقدار فقط می‌تواند شامل ارقام لاتین باشد.
            // استفاده از 'digits:4' هم طول را چک می‌کند و هم عددی بودن را.
            'code' => ['required', 'string', 'digits:4'],
        ];
    }

    /**
     * سفارشی‌سازی نمایش خطاها برای اطمینان از نمایش Toast در فرانت‌اِند.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        if ($this->expectsJson()) {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
                'message' => $validator->errors()->first(),
            ], 422));
        }

        parent::failedValidation($validator);
    }

    public function messages(): array
    {
        return [
            'code.required' => __('validation.requests.otp.code_required'),
            'code.digits'   => __('validation.requests.otp.code_digits'),
        ];
    }

    /**
     * تبدیل ارقام فارسی/عربی به لاتین + دور ریختن هر کاراکتر غیررقمی.
     * (هوموگلیف‌های همهٔ خانواده‌ها را پوشش می‌دهد — همان منطق فقط-رقمِ JS)
     */
    private function toLatinDigits(string $value): string
    {
        $out = '';

        for ($i = 0, $len = mb_strlen($value); $i < $len; $i++) {
            $char = mb_substr($value, $i, 1);
            $code = mb_ord($char);

            if ($code >= 48 && $code <= 57) {           // 0-9 لاتین
                $out .= $char;
            } elseif ($code >= 1632 && $code <= 1641) { // ٠-٩ عربی
                $out .= (string) ($code - 1632);
            } elseif ($code >= 1776 && $code <= 1785) { // ۰-۹ فارسی
                $out .= (string) ($code - 1776);
            }
            // بقیهٔ کاراکترها (حرف، فاصله، +، - و…) دور ریخته می‌شوند
        }

        return $out;
    }
}
