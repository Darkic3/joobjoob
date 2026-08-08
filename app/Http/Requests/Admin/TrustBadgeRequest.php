<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TrustBadgeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('trust_badge')?->id;

        return [
            'icon' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'icon.required' => 'فیلد آیکون (ایموجی یا کلاس) الزامی است.',
            'icon.max' => 'مقدار آیکون نباید بیشتر از 100 کاراکتر باشد.',
            'title.required' => 'فیلد عنوان الزامی است.',
            'title.max' => 'فیلد عنوان نباید بیشتر از 255 کاراکتر باشد.',
            'description.string' => 'فیلد توضیحات باید یک رشته متنی باشد.',
            'description.max' => 'توضیحات نباید بیشتر از 500 کاراکتر باشد.',
            'is_active.boolean' => 'مقدار وضعیت فعال/غیرفعال باید صحیح باشد.',
        ];
    }
}
