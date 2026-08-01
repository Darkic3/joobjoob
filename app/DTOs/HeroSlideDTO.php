<?php

namespace App\DTOs;

use App\Http\Requests\Admin\HeroSlideRequest;
use Illuminate\Http\UploadedFile;

readonly class HeroSlideDTO
{
    public function __construct(
        public string $hero_type,
        public array  $title,
        public array  $desc,
        public array  $badge,
        public array  $btn_text,
        public ?int   $price,
        public ?int   $old_price,
        public string $currency,
        public ?string $rating,
        public ?int   $viewers,
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
            hero_type: $request->input('hero_type', 'internal'),
            title: ['fa' => $request->input('title.fa', ''), 'en' => $request->input('title.en', '')],
            desc: ['fa' => $request->input('desc.fa', ''), 'en' => $request->input('desc.en', '')],
            badge: ['fa' => $request->input('badge.fa', ''), 'en' => $request->input('badge.en', '')],
            btn_text: ['fa' => $request->input('btn_text.fa', ''), 'en' => $request->input('btn_text.en', '')],
            price: $request->integer('price', 0),
            old_price: $request->integer('old_price', 0),
            currency: $request->input('currency', 'IRT'),
            rating: $request->input('rating'),
            viewers: $request->integer('viewers', 0),
            linkable_type: $linkableType,
            linkable_id: $linkValue,
            image: $request->file('image'),
            is_active: $request->boolean('is_active'),
        );
    }
}
