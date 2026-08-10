<?php

namespace App\Http\Requests\Admin;

use App\Models\Blog;
use App\Rules\UrlRule;
use App\Traits\HasSlug;
use Illuminate\Foundation\Http\FormRequest;

class BlogRequest extends FormRequest
{
    use HasSlug;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (empty($this->slug)) {
            $this->merge([
                'slug' => $this->prepareSlug($this->title, null),
            ]);
        }
    }

    public function rules(): array
    {
        $id = $this->route('blog')?->id;

        return [
            'title' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:255', new UrlRule(Blog::class, $id)],
            'excerpt' => 'nullable|string',
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
            'title.max' => 'فیلد عنوان نباید بیشتر از 255 کاراکتر باشد.',
            'slug.required' => 'آدرس یکتا (URL) اجباری است.',
            'slug.max' => 'آدرس یکتا حداکثر می‌تواند 255 کاراکتر باشد.',
            'excerpt.string' => 'فیلد خلاصه باید یک رشته باشد.',
            'content.required' => 'فیلد محتوا الزامی است.',
            'blog_category_id.exists' => 'دسته‌بندی انتخاب‌شده معتبر نیست.',
            'is_active.boolean' => 'مقدار فعال باید صحیح باشد.',
            'show_in_first_page.boolean' => 'مقدار نمایش در صفحه اول باید صحیح باشد.',
            'image.max' => 'حجم تصویر نباید بیشتر از 2 مگابایت باشد.',
            'image.mimes' => 'فرمت تصویر مجاز نیست (فقط jpg, jpeg, png, webp).',
        ];
    }
}
