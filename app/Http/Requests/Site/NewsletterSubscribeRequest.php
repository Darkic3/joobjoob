<?php

namespace App\Http\Requests\Site;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ولیدیشن فرم خبرنامه (فوتر).
 *
 * فیلدها:
 *   - email: الزامی، ایمیل معتبر
 */
class NewsletterSubscribeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:190'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('validation.requests.newsletter.email_required'),
            'email.email'    => __('validation.requests.newsletter.email_invalid'),
            'email.max'      => __('validation.requests.newsletter.email_max'),
        ];
    }
}
