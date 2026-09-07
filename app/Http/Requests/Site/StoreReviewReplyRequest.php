<?php

namespace App\Http\Requests\Site;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ولیدیشن پاسخ به نظر محصول.
 *
 * فیلدها:
 *   - comment: الزامی، حداقل ۳ حداکثر ۵۰۰ کاراکتر
 *   - guest_name: الزامی فقط برای مهمان‌ها، حداقل ۲ حداکثر ۱۰۰ کاراکتر
 *   - guest_phone: الزامی فقط برای مهمان‌ها، شماره موبایل معتبر ایرانی
 */
class StoreReviewReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'comment' => ['required', 'string', 'min:3', 'max:500'],
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
            'comment.required' => __('validation.requests.review_reply.comment_required'),
            'comment.min'      => __('validation.requests.review_reply.comment_min'),
            'comment.max'      => __('validation.requests.review_reply.comment_max'),
        ];

        if (! $this->user()) {
            $messages['guest_name.required']  = __('validation.requests.review_reply.guest_name_required');
            $messages['guest_name.min']       = __('validation.requests.review_reply.guest_name_min');
            $messages['guest_name.max']       = __('validation.requests.review_reply.guest_name_max');
            $messages['guest_phone.required'] = __('validation.requests.review_reply.guest_phone_required');
            $messages['guest_phone.regex']    = __('validation.requests.review_reply.guest_phone_invalid');
        }

        return $messages;
    }
}
