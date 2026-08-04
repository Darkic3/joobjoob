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

        // اگر محصول واریانت دارد، قیمت/موجودی از واریانت‌ها تجمیع می‌شود
        // پس فیلدهای اصلی می‌توانند اختیاری باشند
    }

    public function rules(): array
    {
        $id = $this->route('product')?->id;

        return [
            'product_category_id'   => 'required|exists:product_categories,id',
            'brand_id'              => 'nullable|exists:brands,id',
            'name'                  => 'required|array',
            'name.fa'               => 'required|string|max:255',
            'name.en'               => 'required|string|max:255',
            'slug'                  => ['required', 'string', 'max:255', new UrlRule(Product::class, $id)],

            'short_description'     => 'nullable|array',
            'description'           => 'nullable|array',

            'sku'                   => 'nullable|string|max:100|unique:products,sku,' . $id,

            'price'                 => 'nullable|integer|min:0',
            'discount_price'        => 'nullable|integer|min:0',
            'stock'                 => 'nullable|integer|min:0',
            'weight'                => 'nullable|integer|min:0',

            'is_active'             => 'boolean',
            'is_featured'           => 'boolean',
            'is_best_seller'        => 'boolean',

            'deal_ends_at'          => 'nullable|date|after:now',
            'timer_active'          => 'boolean',
            'start_timer'           => 'nullable|date',
            'end_timer'             => 'nullable|date|after:start_timer',

            // مشخصه‌های انتخابی: [parent_id => [child_id, ...]]
            'specs'                 => 'nullable|array',
            'specs.*'               => 'array',
            'specs.*.*'             => 'integer|exists:specifications,id',

            // مقادیر نوشتاری: [specification_id => value]
            'text_specs'            => 'nullable|array',
            'text_specs.*'          => 'nullable|string|max:500',

            // واریانت‌ها
            'variants'              => 'nullable|array',
            'variants.*.id'         => 'nullable|integer|exists:product_variants,id',
            'variants.*.spec_ids'   => 'nullable|array',
            'variants.*.spec_ids.*' => 'integer|exists:specifications,id',
            'variants.*.price'      => 'nullable|integer|min:0',
            'variants.*.discount_price' => 'nullable|integer|min:0',
            'variants.*.stock'      => 'nullable|integer|min:0',
            'variants.*.weight'     => 'nullable|integer|min:0',

            // ویژگی‌های آزاد (properties)
            'properties'            => 'nullable|array',
            'properties.*.title'    => 'nullable|string|max:255',
            'properties.*.value'    => 'nullable|string|max:500',

            // محصولات مرتبط و مکمل
            'related_ids'           => 'nullable|array',
            'related_ids.*'         => 'integer|exists:products,id',
            'complement_ids'        => 'nullable|array',
            'complement_ids.*'      => 'integer|exists:products,id',

            // تصاویر
            'images'                => 'nullable|array',
            'images.*'              => 'image|mimes:jpg,jpeg,png,webp|max:3072',

            'delete_images'         => 'nullable|array',
            'delete_images.*'       => 'integer',

            'main_image_id'         => 'nullable|integer',
            'main_new_index'        => 'nullable|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'product_category_id.required' => 'انتخاب دسته‌بندی محصول الزامی است.',
            'name.fa.required' => 'نام فارسی محصول الزامی است.',
            'name.en.required' => 'نام انگلیسی محصول الزامی است.',
            'end_timer.after' => 'تاریخ پایان تایمر باید بعد از تاریخ شروع باشد.',
            'deal_ends_at.after' => 'تاریخ پایان پیشنهاد ویژه باید در آینده باشد.',
            'images.*.max' => 'حجم هر تصویر محصول نباید بیشتر از ۳ مگابایت باشد.',
        ];
    }
}
