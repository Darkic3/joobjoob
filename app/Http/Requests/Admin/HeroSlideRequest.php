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

            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:20480',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'title.fa.required' => __('validation.requests.hero_slide.title_fa_required'),
            'btn_text.fa.required_if' => __('validation.requests.hero_slide.btn_text_required_if'),
            'link_type.required_if' => __('validation.requests.hero_slide.link_type_required_if'),
            'link_value.required_if' => __('validation.requests.hero_slide.link_value_required_if'),
            'image.image' => __('validation.requests.hero_slide.image_image'),
            'image.mimes' => __('validation.requests.hero_slide.image_mimes'),
            'image.max' => __('validation.requests.hero_slide.image_max'),
        ];
    }
}
