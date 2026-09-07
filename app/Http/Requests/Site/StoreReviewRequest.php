<?php

namespace App\Http\Requests\Site;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ولیدیشن ثبت نظر برای محصول.
 *
 * فیلدها:
 *   - rating: الزامی، عدد صحیح ۱ تا ۵
 *   - comment: الزامی، حداقل ۳ حداکثر ۱۰۰۰ کاراکتر
 *   - guest_name: الزامی فقط برای مهمان‌ها، حداقل ۲ حداکثر ۱۰۰ کاراکتر
 *   - guest_phone: الزامی فقط برای مهمان‌ها، شماره موبایل معتبر ایرانی
 */
class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'rating'  => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'min:3', 'max:1000'],
        ];

        if (! $this->user()) {
            $rules['guest_name']  = ['required', 'string', 'min:2', 'max:100'];
            $rules['guest_phone'] = ['required', 'string', 'regex:/^0[0-9]{9,}$/'];
        }

        return $rules;
    }

    public function messages(): array
    {
        $messages = [
            'rating.required'  => __('validation.requests.review.rating_required'),
            'rating.integer'   => __('validation.requests.review.rating_invalid'),
            'rating.min'       => __('validation.requests.review.rating_min'),
            'rating.max'       => __('validation.requests.review.rating_max'),
            'comment.required' => __('validation.requests.review.comment_required'),
            'comment.min'      => __('validation.requests.review.comment_min'),
            'comment.max'      => __('validation.requests.review.comment_max'),
        ];

        if (! $this->user()) {
            $messages['guest_name.required']  = __('validation.requests.review.guest_name_required');
            $messages['guest_name.min']       = __('validation.requests.review.guest_name_min');
            $messages['guest_name.max']       = __('validation.requests.review.guest_name_max');
            $messages['guest_phone.required'] = __('validation.requests.review.guest_phone_required');
            $messages['guest_phone.regex']    = __('validation.requests.review.guest_phone_invalid');
        }

        return $messages;
    }
}
