<?php

namespace App\Http\Requests\Admin;

use App\Models\PromoBanner;
use Illuminate\Foundation\Http\FormRequest;

class PromoBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'icon' => 'required|string|in:'.implode(',', PromoBanner::ICONS),
            'title' => 'required|array',
            'title.fa' => 'required|string|max:255',
            'title.en' => 'nullable|string|max:255',
            'subtitle' => 'nullable|array',
            'subtitle.fa' => 'nullable|string|max:500',
            'subtitle.en' => 'nullable|string|max:500',
            'url' => 'nullable|url|max:500',
            'style' => 'required|in:gradient,neumorphic,minimal,dark,coupon',
            'tone' => 'required|in:'.implode(',', PromoBanner::TONES),
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'icon.in' => __('validation.requests.promo_banner.icon_in'),
            'title.fa.required' => __('validation.requests.promo_banner.title_fa_required'),
            'title.fa.max' => __('validation.requests.promo_banner.title_fa_max'),
            'url.url' => __('validation.requests.promo_banner.url_url'),
            'style.in' => __('validation.requests.promo_banner.style_in'),
            'tone.in' => __('validation.requests.promo_banner.tone_in'),
            'is_active.boolean' => __('validation.requests.promo_banner.is_active_boolean'),
        ];
    }
}
