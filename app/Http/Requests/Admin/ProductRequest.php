<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use App\Rules\UrlRule;
use App\Traits\HasSlug;
use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    use HasSlug;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // تولید خودکار اسلاگ اگر خالی باشد
        if (empty($this->slug) && !empty($this->name['fa'])) {
            $this->merge([
                'slug' => $this->prepareSlug($this->name['fa'], null),
            ]);
        }

        // تبدیل رشته‌های جدا شده با کاما به آرایه برای colors و sizes
        if ($this->has('colors') && is_string($this->colors)) {
            $this->merge([
                'colors' => array_map('trim', explode(',', $this->colors)),
            ]);
        }

        if ($this->has('sizes') && is_string($this->sizes)) {
            $this->merge([
                'sizes' => array_map('trim', explode(',', $this->sizes)),
            ]);
        }
    }

    public function rules(): array
    {
        $id = $this->route('product')?->id;

        return [
            'product_category_id'   => 'required|exists:product_categories,id',
            'name'                  => 'required|array',
            'name.fa'               => 'required|string|max:255',
            'name.en'               => 'required|string|max:255',
            'slug'                  => ['required', 'string', 'max:255', new UrlRule(Product::class, $id)],

            'short_description'     => 'nullable|array',
            'description'           => 'nullable|array',

            'sku'                   => 'nullable|string|max:100|unique:products,sku,' . $id,
            'brand'                 => 'nullable|string|max:100',

            'price'                 => 'required|integer|min:0',
            'discount_price'        => 'nullable|integer|min:0|lt:price', // باید از قیمت اصلی کمتر باشد
            'stock'                 => 'required|integer|min:0',
            'weight'                => 'nullable|integer|min:0',

            'is_active'             => 'boolean',
            'is_featured'           => 'boolean',
            'is_best_seller'        => 'boolean',

            'colors'                => 'nullable|array',
            'sizes'                 => 'nullable|array',
            'deal_ends_at'          => 'nullable|date|after:now',

            // اعتبارسنجی آرایه‌ای از فایل‌های آپلودی
            'images'                => 'nullable|array',
            'images.*'              => 'image|mimes:jpg,jpeg,png,webp|max:3072', // حداکثر 3 مگابایت
        ];
    }

    public function messages(): array
    {
        return [
            'product_category_id.required' => 'انتخاب دسته‌بندی محصول الزامی است.',
            'name.fa.required' => 'نام فارسی محصول الزامی است.',
            'name.en.required' => 'نام انگلیسی محصول الزامی است.',
            'price.required' => 'قیمت محصول الزامی است.',
            'discount_price.lt' => 'قیمت با تخفیف باید کمتر از قیمت اصلی باشد.',
            'deal_ends_at.after' => 'تاریخ پایان پیشنهاد ویژه باید در آینده باشد.',
            'images.*.max' => 'حجم هر تصویر محصول نباید بیشتر از ۳ مگابایت باشد.',
        ];
    }
}
