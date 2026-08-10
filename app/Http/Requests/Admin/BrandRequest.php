<?php

namespace App\Http\Requests\Admin;

use App\Models\Brand;
use App\Rules\UrlRule;
use App\Traits\HasSlug;
use Illuminate\Foundation\Http\FormRequest;

class BrandRequest extends FormRequest
{
    use HasSlug;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // تولید خودکار اسلاگ اگر خالی باشد
        if (empty($this->url) && ! empty($this->title['fa'])) {
            $this->merge([
                'url' => $this->prepareSlug($this->title['fa'], null),
            ]);
        }
    }

    public function rules(): array
    {
        $id = $this->route('brand')?->id;

        return [
            'title' => 'required|array',
            'title.fa' => 'required|string|max:255',
            'title.en' => 'nullable|string|max:255',
            'url' => ['required', 'string', 'max:255', new UrlRule(Brand::class, $id, 'url')],
            'description' => 'nullable|array',
            'description.fa' => 'nullable|string',
            'description.en' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'active' => 'boolean',
            'show_in_first_page' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'title.fa.required' => 'نام فارسی برند الزامی است.',
            'url.required' => 'آدرس (اسلاگ) برند الزامی است.',
            'image.max' => 'حجم تصویر برند نباید بیشتر از ۲ مگابایت باشد.',
        ];
    }
}
