<?php

namespace App\Trait;

use App\Models\MetaData;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasMetaData
{

    public function metaData(): MorphOne
    {
        return $this->morphOne(MetaData::class, 'meta_datable');
    }
    public function getSeoTitleAttribute(): ?string
    {
        return $this->metaData?->title;
    }
    public function getSeoH1Attribute(): ?string
    {
        return $this->metaData?->H1;
    }
    public function getSeoDescriptionAttribute(): ?string
    {
        return $this->metaData?->description;
    }
    public function getSeoIndexAttribute(): bool
    {
        return $this->metaData?->noIndex ?? false;
    }
}
