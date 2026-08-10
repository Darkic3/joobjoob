<?php

namespace App\View\Components\Admin;

use Illuminate\View\Component;
use Illuminate\View\View;

class ProductSpecBuilder extends Component
{
    /** دادهٔ JSON-ready برای Alpine (سازندهٔ مشخصات/واریانت‌ها) */
    public mixed $payload;

    public function __construct(
        public mixed $specGroups = null,
        public mixed $textSpecifications = null,
        public array $selectedSpecs = [],
        public array $textSpecValues = [],
        public array $existingVariants = [],
        public array $existingProperties = [],
    ) {
        $groups = $specGroups ?? collect();
        $textSpecs = $textSpecifications ?? collect();

        $this->payload = [
            'groups' => $groups->map(fn ($g) => [
                'id' => $g->id,
                'title' => $g->trans('title'),
                'is_color' => (bool) $g->is_color,
                'children' => $g->children->map(fn ($c) => [
                    'id' => $c->id,
                    'title' => $c->trans('title'),
                    'color_code' => $c->color_code,
                ])->values(),
            ])->values(),
            'textSpecs' => $textSpecs->map(fn ($s) => [
                'id' => $s->id,
                'title' => $s->trans('title'),
            ])->values(),
            'selected' => array_map(fn ($ids) => array_map('intval', (array) $ids), (array) $selectedSpecs),
            'textValues' => (object) array_map('strval', (array) $textSpecValues),
            'variants' => $existingVariants,
            'properties' => $existingProperties,
        ];
    }

    public function render(): View
    {
        return view('components.admin.product-spec-builder');
    }
}
