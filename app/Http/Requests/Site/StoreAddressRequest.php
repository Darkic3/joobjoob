<?php

namespace App\Http\Requests\Site;

use App\Helpers\NumberHelper;
use Illuminate\Foundation\Http\FormRequest;

/**
 * اعتبارسنجی حرفه‌ای آدرس (ایجاد + ویرایش).
 *
 * اهداف:
 *   ۱. اگر فرانت disable/تغییر شده باشد و فیلدی نرسد، کاربر ۵۰۰ نمی‌گیرد —
 *      ولیدیشن سمت سرور با پیام فارسی به همان فرم برمی‌گردد (back with errors)
 *      و ورودی‌ها با old() حفظ می‌شوند.
 *   ۲. ارقام فارسی/عربی موبایل و کدپستی قبل از ولیدیشن به لاتین نرمال می‌شوند.
 *   ۳. شماره موبایل با فرمت واقعی ایران (09xxxxxxxxx) اعتبارسنجی می‌شود نه فقط max طول رشته.
 */
class StoreAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        // هر دو اکشن (create/update) داخل middleware('auth') هستند
        return true;
    }

    /**
     * نرمال‌سازی ارقام قبل از اعتبارسنجی.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'receiptor_mobile' => $this->normalizeDigits((string) $this->input('receiptor_mobile', '')),
            'postal_code'      => $this->normalizeDigits((string) $this->input('postal_code', '')),
            'receiptor_full_name' => trim((string) $this->input('receiptor_full_name', '')),
            'address'             => trim((string) $this->input('address', '')),
        ]);
    }

    public function rules(): array
    {
        return [
            'state_id'            => ['required', 'integer', 'exists:states,id'],
            'city_id'             => ['required', 'integer', 'exists:cities,id'],
            'address'             => ['required', 'string', 'min:5', 'max:1000'],
            'receiptor_full_name' => ['required', 'string', 'min:2', 'max:120'],
            // فرمت واقعی شماره موبایل ایران
            'receiptor_mobile'    => ['required', 'string', 'regex:/^09\d{9}$/'],
            // کدپستی ایران: دقیقاً ۱۰ رقم
            'postal_code'         => ['required', 'string', 'digits:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'state_id.required'           => 'انتخاب استان الزامی است.',
            'state_id.exists'             => 'استان انتخاب‌شده معتبر نیست.',
            'city_id.required'            => 'انتخاب شهر الزامی است.',
            'city_id.exists'              => 'شهر انتخاب‌شده معتبر نیست.',
            'address.required'            => 'آدرس کامل الزامی است.',
            'address.min'                 => 'آدرس باید حداقل ۵ کاراکتر باشد.',
            'address.max'                 => 'آدرس نمی‌تواند بیشتر از ۱۰۰۰ کاراکتر باشد.',
            'receiptor_full_name.required' => 'نام تحویل‌گیرنده الزامی است.',
            'receiptor_full_name.min'      => 'نام تحویل‌گیرنده باید حداقل ۲ کاراکتر باشد.',
            'receiptor_full_name.max'      => 'نام تحویل‌گیرنده نمی‌تواند بیشتر از ۱۲۰ کاراکتر باشد.',
            'receiptor_mobile.required'   => 'شماره موبایل تحویل‌گیرنده الزامی است.',
            'receiptor_mobile.regex'      => 'شماره موبایل معتبر نیست (مثال: 09123456789).',
            'postal_code.required'        => 'کدپستی الزامی است.',
            'postal_code.digits'          => 'کدپستی باید دقیقاً ۱۰ رقم باشد.',
        ];
    }

    public function attributes(): array
    {
        return [
            'state_id'            => 'استان',
            'city_id'             => 'شهر',
            'address'             => 'آدرس',
            'receiptor_full_name' => 'نام تحویل‌گیرنده',
            'receiptor_mobile'    => 'موبایل',
            'postal_code'         => 'کدپستی',
        ];
    }

    /**
     * ارقام فارسی/عربی → لاتین + حذف هر کاراکتر غیررقمی.
     */
    private function normalizeDigits(string $value): string
    {
        $latin = NumberHelper::persian2LatinDigit($value);

        return preg_replace('/\D/', '', $latin) ?? '';
    }
}
