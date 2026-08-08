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
                        $fail('نمی‌توانید یک دسته‌بندی را والد خودش قرار دهید.');
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
            'title.required' => 'فیلد عنوان الزامی است.',
            'title.string' => 'فیلد عنوان باید یک رشته باشد.',
            'title.max' => 'فیلد عنوان نباید بیشتر از 255 کاراکتر باشد.',
            'url.required' => 'URL اجباری است.',
            'url.max' => 'URL حداکثر می‌تواند 255 کاراکتر باشد.',
            'description.string' => 'فیلد توضیحات باید یک رشته باشد.',
            'parent_id.exists' => 'دسته‌بندی والد انتخابی وجود ندارد.',
            'is_active.boolean' => 'فیلد فعال باید یک مقدار صحیح باشد.',
            'show_in_first_page.boolean' => 'فیلد نمایش در صفحه ی اول باید یک مقدار صحیح باشد.',
            'sort_order.integer' => 'ترتیب نمایش باید یک عدد باشد.',
            'sort_order.min' => 'ترتیب نمایش نمی‌تواند منفی باشد.',
            'image.max' => 'حجم تصویر نباید بیشتر از 2048 کیلوبایت (2 مگابایت) باشد.',
            'image.mimes' => 'فرمت تصویر مجاز نمی باشد.',
        ];
    }
}
