<?php

namespace App\View\Components;

use App\Services\SeoService;
use Illuminate\View\Component;
use Illuminate\Database\Eloquent\Model;

class SeoHead extends Component
{
    public SeoService $seoService;

    /**
     * Create a new component instance.
     *
     * @param Model|null $model
     * @param array $meta
     * @param array $schemas
     */
    public function __construct(Model $model = null, array $meta = [], array $schemas = [])
    {
        $this->seoService = new SeoService($model, $meta, $schemas);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $seo = $this->seoService->getSeoData();

        return view('components.seo-head', compact('seo'));
    }
}
