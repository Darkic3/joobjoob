<?php

namespace App\Http\Requests\Admin;

use App\Models\ProductCategory;
use App\Rules\UrlRule; // فرض بر وجود این Rule در پروژه شماست
use App\Traits\HasSlug; // فرض بر وجود این Trait در پروژه شماست
use Illuminate\Foundation\Http\FormRequest;

class ProductCategoryRequest extends FormRequest
{
    use HasSlug;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // تولید اسلاگ از روی نام فارسی اگر اسلاگ وارد نشده باشد
        if (empty($this->slug) && ! empty($this->name['fa'])) {
            $this->merge([
                'slug' => $this->prepareSlug($this->name['fa'], null),
            ]);
        }
    }

    public function rules(): array
    {
        $id = $this->route('product_category')?->id;

        return [
            'parent_id' => 'nullable|exists:product_categories,id',
            'name' => 'required|array',
            'name.fa' => 'required|string|max:100',
            'name.en' => 'required|string|max:100',
            'slug' => ['required', 'string', 'max:150', new UrlRule(ProductCategory::class, $id)],
            'icon' => 'nullable|string|max:50',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'description' => 'nullable|array',
            'description.fa' => 'nullable|string',
            'description.en' => 'nullable|string',
            'meta_title' => 'nullable|array',
            'meta_description' => 'nullable|array',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('validation.requests.category.name_required'),
            'name.fa.required' => __('validation.requests.category.name_fa_required'),
            'name.en.required' => __('validation.requests.category.name_en_required'),
            'slug.required' => __('validation.requests.category.slug_required'),
            'image.max' => __('validation.requests.category.image_max'),
            'sort_order.integer' => __('validation.requests.category.sort_integer'),
        ];
    }
}
