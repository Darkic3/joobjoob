<?php

namespace App\Http\Requests\Site;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ولیدیشن فرم تماس با ما.
 *
 * فیلدها:
 *   - name: الزامی، حداکثر ۱۲۰ کاراکتر
 *   - email: الزامی، ایمیل معتبر، حداکثر ۱۹۰ کاراکتر
 *   - phone: اختیاری، فقط ارقام، حداکثر ۱۵ رقم
 *   - department: اختیاری، یکی از مقادیر مجاز
 *   - message: الزامی، حداقل ۱۰ حداکثر ۲۰۰۰ کاراکتر
 */
class SendContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $validDepartments = array_keys(
            (array) trans('messages.pages.contact.departments')
        );

        return [
            'name'       => ['required', 'string', 'min:2', 'max:120'],
            'email'      => ['required', 'email', 'max:190'],
            'phone'      => ['nullable', 'string', 'digits_between:10,15', 'regex:/^0[0-9]{9,14}$/'],
            'department' => ['nullable', 'string', 'in:'.implode(',', $validDepartments)],
            'message'    => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'       => __('validation.requests.contact.name_required'),
            'name.min'            => __('validation.requests.contact.name_min'),
            'name.max'            => __('validation.requests.contact.name_max'),
            'email.required'      => __('validation.requests.contact.email_required'),
            'email.email'         => __('validation.requests.contact.email_invalid'),
            'email.max'           => __('validation.requests.contact.email_max'),
            'phone.digits_between' => __('validation.requests.contact.phone_invalid'),
            'phone.regex'         => __('validation.requests.contact.phone_invalid'),
            'department.in'       => __('validation.requests.contact.department_invalid'),
            'message.required'    => __('validation.requests.contact.message_required'),
            'message.min'         => __('validation.requests.contact.message_min'),
            'message.max'         => __('validation.requests.contact.message_max'),
        ];
    }
}
