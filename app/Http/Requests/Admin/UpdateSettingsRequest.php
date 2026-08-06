<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true ;
    }

    public function rules(): array
    {
        $group = $this->route('group'); // مثلاً: general یا payment
        $fields = collect(config('settings.fields', []))
            ->filter(fn ($field) => ($field['tab'] ?? 'general') === $group)
            ->all();
        $rules = [];

        foreach ($fields as $key => $field) {
            $validation = $field['validation'] ?? 'nullable';
            $isFile = in_array($field['type'] ?? 'text', ['file', 'video'], true);

            // اگر فیلد از نوع فایل باشد و در ریکوئست فایل آمده باشد، ولیدیشن فایل اعمال می‌شود
            // در غیر این صورت ولیدیشن متنی
            if ($isFile && $this->hasFile("settings.{$key}")) {
                $rules["settings.{$key}"] = $validation;
            } else {
                // حذف قوانین فایل اگر فایلی آپلود نشده باشد
                $validationString = str_replace(['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'], '', $validation);
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
