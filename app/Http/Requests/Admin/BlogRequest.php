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
            'title.required' => __('validation.requests.blog.title_required'),
            'title.max' => __('validation.requests.blog.title_max'),
            'slug.required' => __('validation.requests.blog.slug_required'),
            'slug.max' => __('validation.requests.blog.slug_max'),
            'excerpt.string' => __('validation.requests.blog.excerpt_string'),
            'content.required' => __('validation.requests.blog.content_required'),
            'blog_category_id.exists' => __('validation.requests.blog.category_exists'),
            'is_active.boolean' => __('validation.requests.blog.is_active_boolean'),
            'show_in_first_page.boolean' => __('validation.requests.blog.show_first_boolean'),
            'image.max' => __('validation.requests.blog.image_max'),
            'image.mimes' => __('validation.requests.blog.image_mimes'),
        ];
    }
}
