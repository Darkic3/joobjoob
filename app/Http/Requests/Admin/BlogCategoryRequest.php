<?php

namespace App\Http\Requests\Admin;

use App\Models\BlogCategory;
use App\Rules\UrlRule;
use App\Traits\HasSlug;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BlogCategoryRequest extends FormRequest
{
    use HasSlug;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'url' => $this->prepareSlug($this->title, $this->url),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $id = $this->route('blogCategory')?->id ?? $this->route('blog_category')?->id;

        return [
            'parent_id' => [
                'nullable',
                'exists:blog_categories,id',
                // جلوگیری از انتخاب خودش به عنوان والد
                function ($attribute, $value, $fail) use ($id) {
                    if ($id && $value == $id) {
                        $fail(__('admin.blog_categories.self_parent_error'));
                    }
                },
            ],
            'title' => 'required|string|max:255',
            'url' => ['required', 'string', 'max:255', new UrlRule(BlogCategory::class, $id, 'slug')],
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active' => 'boolean',
            'show_in_first_page' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => __('validation.requests.blog_category.title_required'),
            'title.string' => __('validation.requests.blog_category.title_string'),
            'title.max' => __('validation.requests.blog_category.title_max'),
            'url.required' => __('validation.requests.blog_category.url_required'),
            'url.max' => __('validation.requests.blog_category.url_max'),
            'description.string' => __('validation.requests.blog_category.description_string'),
            'parent_id.exists' => __('validation.requests.blog_category.parent_exists'),
            'is_active.boolean' => __('validation.requests.blog_category.is_active_boolean'),
            'show_in_first_page.boolean' => __('validation.requests.blog_category.show_first_boolean'),
            'sort_order.integer' => __('validation.requests.blog_category.sort_integer'),
            'sort_order.min' => __('validation.requests.blog_category.sort_min'),
            'image.max' => __('validation.requests.blog_category.image_max'),
            'image.mimes' => __('validation.requests.blog_category.image_mimes'),
        ];
    }
}
