<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MetaTagRequest extends FormRequest
{
    /**       * Determine if the user is authorized to make this request.       */
    public function authorize(): bool
    {          // Adjust authorization logic as needed, e.g., check user permissions
        return true;
    }


    /**
     * Get the validation rules that apply to the request.
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return ['meta_title' => ['nullable', 'string', 'max:255'],
            // Max 60-70 for Google display
            'meta_description' => ['nullable', 'string', 'max:500'],
            // Max 150-160 for Google display
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'meta_canonical_url' => ['nullable', 'url', 'max:500'],
            'meta_robots' => ['nullable', 'string', 'max:255'],
// Open Graph
            'meta_og_title' => ['nullable', 'string', 'max:255'],
            'meta_og_description' => ['nullable', 'string', 'max:500'],
            'meta_og_image' => ['nullable', 'url', 'max:500'],
            'meta_og_type' => ['nullable', 'string', 'max:255'],
            'meta_og_url' => ['nullable', 'url', 'max:500'],
// Twitter Cards
            'meta_twitter_card' => ['nullable', 'string', 'max:255'],
            'meta_twitter_site' => ['nullable', 'string', 'max:255'],
            'meta_twitter_creator' => ['nullable', 'string', 'max:255'],
            'meta_twitter_title' => ['nullable', 'string', 'max:255'],
            'meta_twitter_description' => ['nullable', 'string', 'max:500'],
            'meta_twitter_image' => ['nullable', 'url', 'max:500'],];
    }

    /**       * Get the error messages for the defined validation rules.       *       * @return array<string, string> */
    public function messages(): array
    {
        return ['meta_title.max' => 'عنوان متا نباید بیشتر از 255 کاراکتر باشد.',
// Consider 60-70 for better SEO
            'meta_description.max' => 'توضیحات متا نباید بیشتر از 500 کاراکتر باشد.',
// Consider 150-160 for better SEO
            'meta_canonical_url.url' => 'آدرس Canonical باید یک URL معتبر باشد.',
// Add more custom messages as needed
        ];
    }

    /**       * Prepare the data for validation.       *       * @return void */
    protected function prepareForValidation()
    {
        // Prefix all meta tag fields to avoid collision with main model fields
        $this->merge([
            'meta_title' => $this->title,
// Assuming input name is 'title'
            'meta_description' => $this->description,
// ... merge other meta fields
        ]);
    }
}
