<?php

namespace App\View\Components\Admin;

use Illuminate\View\Component;
use Illuminate\View\View;

class ProductAssociationsPicker extends Component
{
    /** دادهٔ JSON-ready برای Alpine (انتخاب محصولات مرتبط/مکمل) */
    public mixed $payload;

    public function __construct(
        public mixed $products = null,
        public array $relatedIds = [],
        public array $complementIds = [],
        public mixed $currentProductId = null,
    ) {
        $productList = collect($products)
            ->filter(fn ($p) => (int) $p->id !== (int) $currentProductId)
            ->map(fn ($p) => [
                'id' => (int) $p->id,
                'label' => $p->trans('name').' (#'.$p->id.')',
            ])
            ->values()
            ->all();

        $this->payload = [
            'products' => $productList,
            'relatedIds' => array_map('intval', (array) $relatedIds),
            'complementIds' => array_map('intval', (array) $complementIds),
        ];
    }

    public function render(): View
    {
        return view('components.admin.product-associations-picker');
    }
}
