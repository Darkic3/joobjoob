<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SpecificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|array',
            'title.fa' => 'required|string|max:255',
            'title.en' => 'nullable|string|max:255',
            'type' => 'required|in:text,select',
            'parent_id' => 'nullable|integer|exists:specifications,id',
            'active' => 'boolean',
            'is_filter' => 'boolean',
            'is_color' => 'boolean',
            'color_code' => 'nullable|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'title.fa.required' => 'نام فارسی مشخصه الزامی است.',
            'type.required' => 'نوع مشخصه (نوشتاری/انتخابی) الزامی است.',
        ];
    }
}
