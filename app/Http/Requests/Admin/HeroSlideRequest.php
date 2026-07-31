<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class HeroSlideRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title.fa' => 'required|string|max:255',
            'desc.fa' => 'nullable|string',
            'badge.fa' => 'nullable|string|max:100',

            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|in:IRT,USD,EUR',

            'link_type' => 'required|in:custom,product,category,blog',
            'link_value' => 'required',

            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'title.fa.required' => 'عنوان فارسی الزامی است.',
            'price.numeric' => 'قیمت باید یک عدد معتبر باشد.',
            'price.min' => 'قیمت نمی‌تواند منفی باشد.',
            'link_value.required' => 'مقدار لینک الزامی است.',
        ];
    }
}
