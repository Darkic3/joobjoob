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
            'hero_type' => 'required|in:informational,external,internal',

            'title.fa' => 'required|string|max:255',
            'title.en' => 'nullable|string|max:255',
            'desc.fa' => 'nullable|string',
            'desc.en' => 'nullable|string',
            'badge.fa' => 'nullable|string|max:100',
            'badge.en' => 'nullable|string|max:100',
            'btn_text.fa' => 'nullable|string|max:100',
            'btn_text.en' => 'nullable|string|max:100',

            'price' => 'nullable|numeric|min:0',
            'old_price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|in:IRT,USD,EUR',
            'rating' => 'nullable|string|max:20',
            'viewers' => 'nullable|integer|min:0',

            'link_type' => 'nullable|in:custom,product,category,blog',
            'link_value' => 'nullable',

            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'hero_type.required' => 'نوع بنر الزامی است.',
            'hero_type.in' => 'نوع بنر باید یکی از مقادیر معتبر باشد.',
            'title.fa.required' => 'عنوان فارسی الزامی است.',
            'price.numeric' => 'قیمت باید یک عدد معتبر باشد.',
            'price.min' => 'قیمت نمی‌تواند منفی باشد.',
        ];
    }
}
