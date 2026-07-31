<?php

namespace App\DTOs;

use App\Http\Requests\Admin\HeroSlideRequest;
use Illuminate\Http\UploadedFile;

readonly class HeroSlideDTO
{
    public function __construct(
        public array  $title,
        public array  $desc,
        public array  $badge,
        public int    $price,
        public string $currency,
        public ?string $linkable_type,
        public ?string $linkable_id,
        public ?UploadedFile $image,
        public bool   $is_active,
    ) {}

    public static function fromRequest(HeroSlideRequest $request): self
    {
        $linkType = $request->input('link_type');
        $linkValue = $request->input('link_value');

        $linkableType = match ($linkType) {
            'product' => \App\Models\Product::class,
            'category' => \App\Models\Category::class,
            'blog' => \App\Models\Blog::class,
            'custom' => 'url',
            default => null,
        };

        return new self(
            title: [
                'fa' => $request->input('title.fa', ''),
                'en' => $request->input('title.en', ''),
            ],
            desc: [
                'fa' => $request->input('desc.fa', ''),
                'en' => $request->input('desc.en', ''),
            ],
            badge: [
                'fa' => $request->input('badge.fa', ''),
                'en' => $request->input('badge.en', ''),
            ],
            price: (int) $request->input('price', 0),
            currency: $request->input('currency', 'IRT'),
            linkable_type: $linkableType,
            linkable_id: $linkValue,
            image: $request->file('image'),
            is_active: $request->boolean('is_active'),
        );
    }
}
