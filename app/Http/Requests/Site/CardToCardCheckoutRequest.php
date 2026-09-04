<?php

namespace App\Http\Requests\Site;

use App\Models\Bank;
use Illuminate\Foundation\Http\FormRequest;

class CardToCardCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * در صورت خطای validation، برگشت به صفحه سبد با حفظ مرحله ۴
     */
    protected function redirectUrl(): string
    {
        return route('cart.index') . '?step=4';
    }

    public function rules(): array
    {
        $bankId = $this->input('bank_id');
        $bank = $bankId ? Bank::find($bankId) : null;
        $isCardToCard = $bank && $bank->bank_type === 'card_to_card';

        $rules = [
            'bank_id' => 'required|exists:banks,id',
        ];

        if ($isCardToCard) {
            $rules['receipt_tracking_code'] = 'required|digits_between:6,16';
            $rules['receipt_payer_card'] = 'nullable|string|regex:/^(\d{4}-?){3}\d{4}$/';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'bank_id.required' => 'لطفاً روش پرداخت را انتخاب کنید.',
            'bank_id.exists' => 'روش پرداخت نامعتبر است.',
            'receipt_tracking_code.required' => 'کد پیگیری واریزی الزامی است.',
            'receipt_tracking_code.digits_between' => 'کد پیگیری باید بین ۶ تا ۱۶ رقم باشد.',
            'receipt_payer_card.regex' => 'شماره کارت معتبر نیست (مثال: 6037-xxxx-xxxx-xxxx).',
        ];
    }
}
