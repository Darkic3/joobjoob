<?php

namespace App\Services;

use App\Models\MetaTag;
use App\Services\Schema\BreadcrumbSchemaGenerator;
use App\Services\Schema\ProductSchemaGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SeoService
{
    protected ?Model $model = null;
    protected array $metaData = [];

    public function __construct(?Model $model = null, array $metaData = [])
    {
        $this->model = $model;
        $this->metaData = $metaData;
    }

    /**
     * Get the SEO title with fallback logic.
     */
    public function getTitle(): string
    {
        // 1. From passed metaData (highest priority)
        if (!empty($this->metaData['title'])) {
            return $this->replaceDynamicPlaceholders($this->metaData['title']);
        }

        // 2. From model's stored MetaTag
        if ($this->model && $this->model->relationLoaded('metaTag') && $this->model->metaTag && !empty($this->model->metaTag->title)) {
            return $this->replaceDynamicPlaceholders($this->model->metaTag->title);
        }

        // 3. From model's properties (e.g., product->name, category->title)
        if ($this->model && property_exists($this->model, 'name') && !empty($this->model->name)) {
            return $this->replaceDynamicPlaceholders($this->model->name);
        }

        if ($this->model && property_exists($this->model, 'title') && !empty($this->model->title)) {
            return $this->replaceDynamicPlaceholders($this->model->title);
        }

        // 4. From config default
        return config('seo.default_title', config('app.name', 'Shopora'));
    }

    /**
     * Get the SEO description with fallback logic.
     */
    public function getDescription(): string
    {
        // 1. From passed metaData
        if (!empty($this->metaData['description'])) {
            return $this->replaceDynamicPlaceholders($this->metaData['description']);
        }

        // 2. From model's stored MetaTag
        if ($this->model && $this->model->relationLoaded('metaTag') && $this->model->metaTag && !empty($this->model->metaTag->description)) {
            return $this->replaceDynamicPlaceholders($this->model->metaTag->description);
        }

        // 3. From model's properties
        if ($this->model && property_exists($this->model, 'description') && !empty($this->model->description)) {
            return $this->replaceDynamicPlaceholders($this->model->description);
        }

        // 4. From config default
        return config('seo.default_description', '');
    }

    /**
     * Get the SEO keywords with fallback logic.
     */
    public function getKeywords(): string
    {
        // 1. From passed metaData
        if (!empty($this->metaData['keywords'])) {
            return $this->replaceDynamicPlaceholders($this->metaData['keywords']);
        }

        // 2. From model's stored MetaTag
        if ($this->model && $this->model->relationLoaded('metaTag') && $this->model->metaTag && !empty($this->model->metaTag->keywords)) {
            return $this->replaceDynamicPlaceholders($this->model->metaTag->keywords);
        }

        // 3. From config default
        return config('seo.default_keywords', '');
    }

    /**
     * Get the canonical URL with fallback logic.
     */
    public function getCanonicalUrl(): string
    {
        // 1. From passed metaData
        if (!empty($this->metaData['canonical_url'])) {
            return url($this->metaData['canonical_url']);
        }

        // 2. From model's stored MetaTag
        if ($this->model && $this->model->relationLoaded('metaTag') && $this->model->metaTag && !empty($this->model->metaTag->canonical_url)) {
            return url($this->model->metaTag->canonical_url);
        }

        // 3. From model's getUrl() method or current URL
        if ($this->model && method_exists($this->model, 'getUrl')) {
            return url($this->model->getUrl());
        }

        // Fallback to current URL
        return url()->current();
    }

    /**
     * Get the robots meta tag content with fallback logic.
     */
    public function getRobots(): string
    {
        // 1. From passed metaData
        if (!empty($this->metaData['robots'])) {
            return $this->metaData['robots'];
        }

        // 2. From model's stored MetaTag
        if ($this->model && $this->model->relationLoaded('metaTag') && $this->model->metaTag && !empty($this->model->metaTag->robots)) {
            return $this->model->metaTag->robots;
        }

        // 3. From config default
        return config('seo.default_robots', 'index, follow');
    }

    /**
     * Get the Open Graph data as an associative array.
     */
    public function getOgData(): array
    {
        $og = [];

        // Title & Description
        $og['og:title'] = $this->getTitle();
        $og['og:description'] = $this->getDescription();

        // Image
        $image = Arr::get($this->metaData, 'og_image')
            ?? Arr::get($this->model->metaTag ?? [], 'og_image')
            ?? ($this->model && method_exists($this->model, 'getFeaturedImageUrl') ? $this->model->getFeaturedImageUrl() : null)
            ?? ($this->model && property_exists($this->model, 'featured_image') ? asset('storage/' . $this->model->featured_image) : null)
            ?? config('seo.default_og_image');

        if ($image) {
            $og['og:image'] = url($image);
        }

        // Type
        $og['og:type'] = Arr::get($this->metaData, 'og_type')
            ?? Arr::get($this->model->metaTag ?? [], 'og_type')
            ?? (Str::contains(request()->url(), 'products') ? 'product' : 'website');

        // URL & Site Name
        $og['og:url'] = $this->getCanonicalUrl();
        $og['og:site_name'] = config('app.name');

        return array_filter($og);
    }

    /**
     * Get the Twitter Card data as an associative array.
     */
    public function getTwitterData(): array
    {
        $twitter = [];

        // Card Type
        $twitter['twitter:card'] = Arr::get($this->metaData, 'twitter_card')
            ?? Arr::get($this->model->metaTag ?? [], 'twitter_card')
            ?? config('seo.default_twitter_card', 'summary_large_image');

        // Title & Description
        $twitter['twitter:title'] = $this->getTitle();
        $twitter['twitter:description'] = $this->getDescription();

        // Image (reusing OG image logic for simplicity)
        $image = Arr::get($this->metaData, 'twitter_image')
            ?? Arr::get($this->model->metaTag ?? [], 'twitter_image')
            ?? ($this->model && method_exists($this->model, 'getFeaturedImageUrl') ? $this->model->getFeaturedImageUrl() : null)
            ?? ($this->model && property_exists($this->model, 'featured_image') ? asset('storage/' . $this->model->featured_image) : null)
            ?? config('seo.default_twitter_image');

        if ($image) {
            $twitter['twitter:image'] = url($image);
        }

        // Site & Creator (Twitter handles)
        if (config('seo.twitter_site')) {
            $twitter['twitter:site'] = config('seo.twitter_site');
        }
        if (config('seo.twitter_creator')) {
            $twitter['twitter:creator'] = config('seo.twitter_creator');
        }

        return array_filter($twitter);
    }

    /**
     * Get all structured data (JSON-LD) for the current context.
     */
    public function getSchemas(): array
    {
        $schemas = [];

        if ($this->model instanceof \App\Models\Product) {
            $schemas[] = (new ProductSchemaGenerator($this->model))->generate();
        }

        // Example: If you have breadcrumbs logic on the model
        if ($this->model && method_exists($this->model, 'getBreadcrumbs')) {
            $schemas[] = (new BreadcrumbSchemaGenerator($this->model->getBreadcrumbs()))->generate();
        }

        // Clean up null/empty values from schemas recursively
        return array_map([$this, 'arrayFilterRecursive'], $schemas);
    }

    /**
     * Replace dynamic placeholders in a string (e.g., {product_name}, {site_name}).
     */
    protected function replaceDynamicPlaceholders(string $value): string
    {
        $value = str_replace('{site_name}', config('app.name', 'Shopora'), $value);

        if ($this->model) {
            if (property_exists($this->model, 'name')) {
                $value = str_replace(
                    ['{name}', '{product_name}', '{category_name}', '{page_name}'],
                    $this->model->name,
                    $value
                );
            }
            if (property_exists($this->model, 'title')) {
                $value = str_replace(
                    ['{title}', '{product_title}', '{category_title}', '{page_title}'],
                    $this->model->title,
                    $value
                );
            }
            if (property_exists($this->model, 'description')) {
                $value = str_replace(
                    ['{description}', '{product_description}', '{category_description}', '{page_description}'],
                    Str::limit($this->model->description, 150),
                    $value
                );
            }
            // Add more dynamic placeholders as needed
        }

        return $value;
    }

    /**
     * Recursively filters out null and empty array values.
     */
    protected function arrayFilterRecursive(array $array): array
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->arrayFilterRecursive($value);
            }
        }

        return array_filter($array, fn ($value) => !is_null($value) && $value !== '' && $value !== []);
    }
}
