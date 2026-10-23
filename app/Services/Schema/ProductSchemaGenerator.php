<?php

namespace App\Services\Schema;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductSchemaGenerator
{
    protected Model $product;

    public function __construct(Model $product)
    {
        $this->product = $product;
    }

    public function generate(): array
    {
        $product = $this->product;
        // Ensure these properties exist on your Product model or adjust accordingly
        $name = $product->name ?? '';
        $description = $product->description ?? '';
        $image = $product->featured_image ?? $product->image ?? config('seo.default_image');
        $sku = $product->sku ?? $product->id;
        // Assuming SKU or ID
        $url = method_exists($product, 'getUrl') ? $product->getUrl() : url('/products/' . $product->slug);
        // Adjust product URL generation
//          // ItemAvailability logic
        $availability = 'https://schema.org/OutOfStock';
        if (isset($product->stock) && $product->stock > 0) {
            $availability = 'https://schema.org/InStock';
        }
        // You might have other conditions for availability like 'backorder', 'preorder' etc.
//          // Pricing logic (Discounted Price)
        $price = $product->price;
        // Default to regular price
        $priceValidUntil = null;
        if (isset($product->discount_price) && $product->discount_price > 0 && $product->discount_price < $product->price) {
            $price = $product->discount_price;
            // If you have discount validity, set priceValidUntil
            $priceValidUntil = $product->discount_ends_at ? $product->discount_ends_at->format('Y-m-d') : null;
        }
        $currency = config('shop.currency', 'IRR'); // Assuming currency is configured in config/shop.php
        // AggregateRating (if available)
        $aggregateRating = null;
        if (isset($product->average_rating) && isset($product->reviews_count) && $product->reviews_count > 0) {
            $aggregateRating = [
                '@type' => 'AggregateRating',
                'ratingValue' => number_format($product->average_rating, 1),
                'reviewCount' => $product->reviews_count,];
        }
        return [
            '@context' => 'https://schema.org/',
            '@type' => 'Product',
            'name' => $name,
            'image' => $image,
            'description' => Str::limit(strip_tags($description), 200),
// Clean HTML and limit
            'sku' => (string)$sku,
            'url' => $url,
            'offers' => [
                '@type' => 'Offer',
                'url' => $url,
                'priceCurrency' => $currency,
                'price' => number_format($price, 0, '.', ''),
// Ensure no thousands separator
                'itemCondition' => 'https://schema.org/NewCondition',
// Or UsedCondition, RefurbishedCondition
                'availability' => $availability,
                'priceValidUntil' => $priceValidUntil,
                'seller' => [
                    '@type' => 'Organization',
                    'name' => config('app.name', 'Joobjoob'),
                ],
            ],
            'aggregateRating' => $aggregateRating,
// Add brand, review, etc. as needed
        ];
    }
}
