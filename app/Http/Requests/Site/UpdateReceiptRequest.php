<?php

namespace App\Http\Requests\Site;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'receipt_tracking_code' => 'required|digits_between:6,16',
            'receipt_payer_card' => 'nullable|string|regex:/^(\d{4}-?){3}\d{4}$/',
        ];
    }

    public function messages(): array
    {
        return [
            'receipt_tracking_code.required' => 'کد پیگیری واریزی الزامی است.',
            'receipt_tracking_code.numeric' => 'کد پیگیری باید عدد باشد.',
            'receipt_tracking_code.min' => 'کد پیگیری حداقل ۶ رقم باشد.',
            'receipt_tracking_code.max' => 'کد پیگیری حداکثر ۱۶ رقم باشد.',
            'receipt_payer_card.regex' => 'شماره کارت معتبر نیست (مثال: 6037-xxxx-xxxx-xxxx).',
        ];
    }
}
