<?php

namespace App\Http\Requests\Admin;

use App\Models\Blog;
use App\Rules\UrlRule;
use App\Traits\HasSlug;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BlogRequest extends FormRequest
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
        $id = $this->route('blog')?->id;

        return [
            'title' => 'required|string|max:255',
            'url' => ['required', 'string', 'max:255', new UrlRule(Blog::class, $id)],
            'description' => 'nullable|string',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'blog_category_id' => 'nullable|exists:blog_categories,id',
            'is_active' => 'boolean',
            'show_in_first_page' => 'boolean',
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
            'content.required' => 'فیلد محتوا الزامی است.',
            'content.string' => 'فیلد محتوا باید یک رشته باشد.',
            'blog_category_id.exists' => 'دسته بندی مقاله باید وجود داشته باشد.',
            'is_active.boolean' => 'فیلد فعال باید یک مقدار صحیح باشد.',
            'show_in_first_page.boolean' => 'فیلد نمایش در صفحه ی اول باید یک مقدار صحیح باشد.',
            'image.max' => 'حجم تصویر نباید بیشتر از 2048 کیلوبایت (2 مگابایت) باشد.',
            'image.mimes' => 'فرمت تصویر مجاز نمی باشد.',
        ];
    }
}
