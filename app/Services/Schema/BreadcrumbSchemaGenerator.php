<?php

namespace App\Services\Schema;

use Illuminate\Support\Collection;

class BreadcrumbSchemaGenerator
{
    protected Collection
        $breadcrumbs;

    // A collection of ['name' => '...', 'url' => '...']
    public function __construct(Collection $breadcrumbs)
    {
        $this->breadcrumbs = $breadcrumbs;
    }

    public function generate(): array
    {
        $itemListElement = [];
        $position = 1;
        foreach ($this->breadcrumbs as $breadcrumb) {
            $itemListElement[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $breadcrumb['name'],
                'item' => $breadcrumb['url'],];
        }
        return ['@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $itemListElement,];
    }
}
