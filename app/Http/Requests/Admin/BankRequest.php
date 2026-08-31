<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BankRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return match ($this->method()) {
            'PUT', 'PATCH' => [
                'title' => 'required|string|max:255',
                'icon' => 'nullable|image|mimes:jpg,jpeg,png,svg,webp|max:2048',
                'bank_type' => 'required|string|max:100',
                'status' => 'required|boolean',
                'admin_test' => 'nullable|boolean',
                'config' => 'nullable|string',
                'card_number' => 'nullable|string|max:30',
                'card_holder_name' => 'nullable|string|max:255',
                'card_description' => 'nullable|string|max:1000',
            ],
            default => [
                'title' => 'required|string|max:255',
                'icon' => 'nullable|image|mimes:jpg,jpeg,png,svg,webp|max:2048',
                'bank_type' => 'required|string|max:100',
                'status' => 'required|boolean',
                'admin_test' => 'nullable|boolean',
                'config' => 'nullable|string',
                'card_number' => 'nullable|string|max:30',
                'card_holder_name' => 'nullable|string|max:255',
                'card_description' => 'nullable|string|max:1000',
            ],
        };
    }

    public function messages(): array
    {
        return [
            'title.required' => 'عنوان الزامی است',
            'icon.image' => 'فایل باید تصویر باشد',
            'icon.mimes' => 'فرمت‌های مجاز: jpg, jpeg, png, svg, webp',
            'icon.max' => 'حداکثر حجم تصویر ۲ مگابایت است',
            'bank_type.required' => 'نوع درگاه الزامی است',
            'status.required' => 'وضعیت الزامی است',
        ];
    }
}
