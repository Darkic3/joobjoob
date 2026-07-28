<?php

namespace App\Trait;

use Illuminate\Support\Str;

trait HasSlug
{
    protected function prepareSlug($title, $url = null): string
    {
        return Str::slug($url ?: $title);
    }
}
