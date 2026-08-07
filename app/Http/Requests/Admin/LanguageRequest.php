<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LanguageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // کد فقط حروف کوچک a-z (۲ تا ۸ کاراکتر).
            // در ویرایش فقط‌خواندنی است (input با disabled رندر می‌شود و سابمیت نمی‌شود)
            // پس فقط در ساخت (POST) الزامی است.
            'code' => [
                Rule::requiredIf($this->isMethod('post')),
                'string',
                'regex:/^[a-z]{2,8}$/',
                Rule::unique('languages', 'code')->ignore($this->route('language')),
            ],
            'name' => ['required', 'array'],
            'name.fa' => ['required', 'string', 'max:255'],
            'name.*' => ['nullable', 'string', 'max:255'],
            'native_name' => ['required', 'string', 'max:255'],
            'dir' => ['required', 'in:rtl,ltr'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.regex' => __('admin.languages.code_invalid'),
        ];
    }
}
