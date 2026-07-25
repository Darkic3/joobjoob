<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // بعداً با Middleware ادمین محدود می‌شود
    }

    public function rules(): array
    {
        $group = $this->route('group'); // مثلاً: general یا payment
        $fields = config("settings.{$group}.fields", []);
        $rules = [];

        foreach ($fields as $key => $field) {
            // اگر فیلد از نوع فایل باشد و در ریکوئست فایل آمده باشد، ولیدیشن فایل اعمال می‌شود
            // در غیر این صورت ولیدیشن متنی
            if ($this->hasFile("settings.{$key}")) {
                $rules["settings.{$key}"] = $field['validation'];
            } else {
                // حذف قوانین فایل اگر فایلی آپلود نشده باشد
                $validationString = str_replace(['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'], '', $field['validation']);
                $rules["settings.{$key}"] = trim($validationString, '|') ?: 'nullable';
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'settings.site_name.required' => 'نام سایت الزامی است.',
            'settings.site_logo.image' => 'فایل لوگو باید یک تصویر معتبر باشد.',
        ];
    }
}
