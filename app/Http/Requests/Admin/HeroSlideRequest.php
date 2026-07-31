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
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');
        $imageRule = $isUpdate ? 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048' : 'required|image|mimes:jpg,jpeg,png,webp|max:2048';

        return [
            'title.fa' => 'required|string|max:255',
            'title.en' => 'nullable|string|max:255',

            'desc.fa' => 'required|string|max:500',
            'desc.en' => 'nullable|string|max:500',

            'badge.fa' => 'nullable|string|max:100',
            'badge.en' => 'nullable|string|max:100',

            'btn_text.fa' => 'nullable|string|max:100',
            'btn_text.en' => 'nullable|string|max:100',

            'price.fa' => 'nullable|string|max:50',
            'price.en' => 'nullable|string|max:50',

            'old_price.fa' => 'nullable|string|max:50',
            'old_price.en' => 'nullable|string|max:50',

            // فیلدهای ثابت
            'image' => $imageRule,
            'btn_link' => 'required|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'title.fa.required' => 'عنوان به زبان فارسی الزامی است.',
            'desc.fa.required' => 'توضیحات به زبان فارسی الزامی است.',
            'image.required' => 'تصویر بنر الزامی است.',
            'image.image' => 'فایل آپلود شده باید یک تصویر باشد.',
        ];
    }
}
