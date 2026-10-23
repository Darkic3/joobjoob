<?php

namespace App\Services;

use App\Models\Meta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class MetaService
{
    protected array $meta = [];

    // Default meta tags (can be loaded from config or database settings)
    protected array $defaults = [
        'meta_title' => 'فروشگاه اینترنتی جوب‌جوب',
        'meta_description' => 'لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با استفاده از طراحان گرافیک است.',
        'meta_keywords' => 'فروشگاه, اینترنتی, خرید, آنلاین, جوب‌جوب',
        'og_title' => 'فروشگاه اینترنتی جوب‌جوب',
        'og_description' => 'لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با استفاده از طراحان گرافیک است.',
        'og_image' => '/images/logo-social.png',
        'twitter_title' => 'فروشگاه اینترنتی جوب‌جوب',
        'twitter_description' => 'لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با استفاده از طراحان گرافیک است.',
        'twitter_image' => '/images/logo-social.png',
    ];

    public function __construct()
    {
        // Load defaults from config if available, otherwise use hardcoded ones
        // For simplicity, using hardcoded for now
        // $this->defaults = config('seo.defaults', $this->defaults);
    }

    /**
     * Set a specific meta tag value.
     */
    public function set(string $key, ?string $value): self
    {
        $this->meta[$key] = $value;
        return $this;
    }

    /**
     * Set multiple meta tags from an array.
     */
    public function setMany(array $metaData): self
    {
        foreach ($metaData as $key => $value) {
            $this->set($key, $value);
        }
        return $this;
    }

    /**
     * Set meta tags from an Eloquent model.
     *
     * @param Model|null $model The model that has a 'meta' relationship.
     */
    public function fromModel(?Model $model): self
    {
        if ($model && method_exists($model, 'meta') && $model->meta)
        {
            foreach ($model->meta->getAttributes() as $key => $value) {
                if (str_starts_with($key, 'meta_') || str_starts_with($key, 'og_') || str_starts_with($key, 'twitter_')) {
                    $this->set($key, $value);
                }
            }
        }
        return $this;
    }

    /**
     * Get a specific meta tag value, with fallback to defaults.
     */
    public function get(string $key): ?string
    {
        return $this->meta[$key] ?? $this->defaults[$key] ?? null;
    }

    /**
     * Get all compiled meta tags.
     */
    public function getAll(): array
    {
        $compiled = [];
        foreach ($this->defaults as $key => $defaultValue) {
            $compiled[$key] = $this->get($key);
        }
        return $compiled;
    }

    /**
     * Render the meta tags as HTML strings.
     */
    public function render(): string
    {
        $html = '';
        $meta = $this->getAll();

        // Title
        $html .= '<title>' . ($meta['meta_title'] ?? $this->defaults['meta_title']) . '</title>\n';

        // Description
        if ($description = ($meta['meta_description'] ?? $this->defaults['meta_description'])) {
            $html .= '<meta name="description" content="' . e($description) . '">\n';
        }

        // Keywords
        if ($keywords = ($meta['meta_keywords'] ?? $this->defaults['meta_keywords'])) {
            $html .= '<meta name="keywords" content="' . e($keywords) . '">\n';
        }

        // Open Graph
        if ($ogTitle = ($meta['og_title'] ?? $this->defaults['og_title'])) {
            $html .= '<meta property="og:title" content="' . e($ogTitle) . '">\n';
        }
        if ($ogDescription = ($meta['og_description'] ?? $this->defaults['og_description'])) {
            $html .= '<meta property="og:description" content="' . e($ogDescription) . '">\n';
        }
        if ($ogImage = ($meta['og_image'] ?? $this->defaults['og_image'])) {
            $html .= '<meta property="og:image" content="' . e(url($ogImage)) . '">\n';
        }
        $html .= '<meta property="og:type" content="website">\n'; // Default to website type

        // Twitter Card
        if ($twitterTitle = ($meta['twitter_title'] ?? $this->defaults['twitter_title'])) {
            $html .= '<meta name="twitter:title" content="' . e($twitterTitle) . '">\n';
        }
        if ($twitterDescription = ($meta['twitter_description'] ?? $this->defaults['twitter_description'])) {
            $html .= '<meta name="twitter:description" content="' . e($twitterDescription) . '">\n';
        }
        if ($twitterImage = ($meta['twitter_image'] ?? $this->defaults['twitter_image'])) {
            $html .= '<meta name="twitter:image" content="' . e(url($twitterImage)) . '">\n';
        }
        $html .= '<meta name="twitter:card" content="summary_large_image">\n'; // Default to summary_large_image

        return $html;
    }
}
