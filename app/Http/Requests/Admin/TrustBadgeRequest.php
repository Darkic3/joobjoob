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
            'title' => 'required|array',
            'title.fa' => 'required|string|max:255',
            'title.en' => 'nullable|string|max:255',
            'description' => 'nullable|array',
            'description.fa' => 'nullable|string|max:500',
            'description.en' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'icon.required' => __('validation.requests.trust_badge.icon_required'),
            'icon.max' => __('validation.requests.trust_badge.icon_max'),
            'title.fa.required' => __('validation.requests.trust_badge.title_fa_required'),
            'title.fa.max' => __('validation.requests.trust_badge.title_fa_max'),
            'description.fa.max' => __('validation.requests.trust_badge.description_fa_max'),
            'is_active.boolean' => __('validation.requests.trust_badge.is_active_boolean'),
        ];
    }
}
