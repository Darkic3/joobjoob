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
            'title.en' => 'nullable|string|max:255',
            'desc.fa' => 'nullable|string',
            'desc.en' => 'nullable|string',
            'badge.fa' => 'nullable|string|max:100',
            'badge.en' => 'nullable|string|max:100',

            'button_active' => 'boolean',
            'btn_text.fa' => 'nullable|required_if:button_active,1|string|max:100',
            'btn_text.en' => 'nullable|string|max:100',
            'link_type' => 'nullable|required_if:button_active,1|in:custom,page,product,product_category,blog,blog_category',
            'link_value' => 'nullable|required_if:button_active,1|string',

            'price' => 'nullable|numeric|min:0',
            'old_price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|in:IRT,USD,EUR',

            'rating_active' => 'boolean',
            'rating' => 'nullable|required_if:rating_active,1|string|max:20',

            'viewers_active' => 'boolean',
            'viewers' => 'nullable|required_if:viewers_active,1|integer|min:0',

            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'title.fa.required' => 'عنوان فارسی الزامی است.',
            'btn_text.fa.required_if' => 'متن دکمه فارسی الزامی است.',
            'link_type.required_if' => 'نوع مقصد لینک الزامی است.',
            'link_value.required_if' => 'مقدار مقصد لینک الزامی است.',
            'rating.required_if' => 'مقدار امتیاز الزامی است.',
            'viewers.required_if' => 'تعداد بازدیدکنندگان الزامی است.',
            'price.numeric' => 'قیمت باید یک عدد معتبر باشد.',
            'price.min' => 'قیمت نمی‌تواند منفی باشد.',
        ];
    }
}
