<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class DiscountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $discountId = $this->route('discount')?->id;

        return match ($this->method()) {
            'PUT', 'PATCH' => [
                'title' => 'required|string|max:255',
                'code' => "required|string|max:50|alpha_dash|unique:discounts,code,{$discountId},id,deleted_at,NULL",
                'amount' => 'required|numeric|min:0',
                'type' => 'required|in:cash,percent',
                'count' => 'required|integer|min:1',
                'basket_minimum_price' => 'nullable|integer|min:0',
                'first_purchase' => 'nullable|boolean',
                'with_discount' => 'nullable|boolean',
                'user_id' => 'nullable|exists:users,id',
                'max_usage_per_user' => 'required|integer|min:0',
                'pay_type' => 'nullable|in:category,brand,product',
                'is_active' => 'required|boolean',
                'starts_at' => 'nullable|date',
                'expires_at' => 'nullable|date|after_or_equal:starts_at',
            ],
            default => [
                'title' => 'required|string|max:255',
                'code' => 'required|string|max:50|alpha_dash|unique:discounts,code,NULL,id,deleted_at,NULL',
                'amount' => 'required|numeric|min:0',
                'type' => 'required|in:cash,percent',
                'count' => 'required|integer|min:1',
                'basket_minimum_price' => 'nullable|integer|min:0',
                'first_purchase' => 'nullable|boolean',
                'with_discount' => 'nullable|boolean',
                'user_id' => 'nullable|exists:users,id',
                'max_usage_per_user' => 'required|integer|min:0',
                'pay_type' => 'nullable|in:category,brand,product',
                'is_active' => 'required|boolean',
                'starts_at' => 'nullable|date',
                'expires_at' => 'nullable|date|after_or_equal:starts_at',
            ],
        };
    }

    public function messages(): array
    {
        return [
            'title.required' => 'عنوان تخفیف الزامی است.',
            'code.required' => 'کد تخفیف الزامی است.',
            'code.alpha_dash' => 'کد تخفیف فقط شامل حروف، اعداد، خط تیره و زیرخط باشد.',
            'code.unique' => 'این کد تخفیف قبلاً استفاده شده است.',
            'amount.required' => 'مبلغ تخفیف الزامی است.',
            'amount.numeric' => 'مبلغ تخفیف باید عددی باشد.',
            'amount.min' => 'مبلغ تخفیف نمی‌تواند منفی باشد.',
            'type.required' => 'نوع تخفیف الزامی است.',
            'type.in' => 'نوع تخفیف باید cash یا percent باشد.',
            'count.required' => 'تعداد دفعات قابل استفاده الزامی است.',
            'count.min' => 'تعداد دفعات باید حداقل ۱ باشد.',
            'max_usage_per_user.required' => 'حداکثر استفاده توسط هر کاربر الزامی است.',
            'expires_at.after_or_equal' => 'تاریخ انقضا باید بعد از تاریخ شروع باشد.',
            'is_active.required' => 'وضعیت فعال/غیرفعال الزامی است.',
        ];
    }
}
