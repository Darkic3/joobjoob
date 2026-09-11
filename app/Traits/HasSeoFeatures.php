<?php

namespace App\Traits;

use App\Models\MetaTag;
use App\Services\SeoService; // Import SeoService
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Str;

trait HasSeoFeatures
{
    /**
     * Get the entity's meta tags relationship.
     */
    public function metaTag(): MorphOne
    {
        return $this->morphOne(MetaTag::class, 'meta_taggable');
    }

    /**
     * Sync (create or update) the meta tags for the entity.
     *
     * @param array $data
     * @return MetaTag
     */
    public function syncMetaTags(array $data): MetaTag
    {
        // Filter out keys that don't belong to the MetaTag model
        $fillableMetaKeys = (new MetaTag())->getFillable();
        $metaData = array_intersect_key($data, array_flip($fillableMetaKeys));

        return $this->metaTag()->updateOrCreate(
            [], // No unique identifier needed for updateOrCreate on a morphOne relationship
            $metaData
        );
    }

    /**
     * Get an instance of the SeoService pre-configured for this model.
     */
    public function seo(): SeoService
    {
        return new SeoService($this);
    }

    /**
     * Replace dynamic placeholders in a string.
     * This method can be extended to support more complex placeholders.
     */
    protected function replaceDynamicPlaceholders(string $value): string
    {
        // Example: Replace {name} with the entity's name
        $value = Str::replace('{name}', $this->name ?? '', $value);
        $value = Str::replace('{title}', $this->title ?? '', $value);
        $value = Str::replace('{description}', $this->description ?? '', $value);
        $value = Str::replace('{app_name}', config('app.name', 'Shopora'), $value);
        // Add more placeholders as needed

        return $value;
    }
}
