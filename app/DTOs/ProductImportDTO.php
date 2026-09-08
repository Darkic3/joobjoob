<?php

namespace App\DTOs;

use Illuminate\Support\Facades\Validator;

/**
 * DTO for validating a single CSV row during product import.
 * Ensures data integrity before database insertion.
 */
readonly class ProductImportDTO
{
    public function __construct(
        public string $name,
        public string $slug,
        public int $categoryId,
        public ?int $brandId,
        public int $price,
        public ?int $discountPrice,
        public int $stock,
        public ?int $weight,
        public ?string $sku,
        public ?string $shortDescription,
        public ?string $description,
        public bool $isActive,
        public bool $isFeatured,
        public ?string $imageUrl,
    ) {}

    /**
     * Create DTO from a CSV row array with validation.
     *
     * @return array{success: bool, dto?: self, errors?: array}
     */
    public static function fromRow(array $row, int $rowNumber): array
    {
        $data = [
            'name'              => trim($row['name'] ?? ''),
            'slug'              => trim($row['slug'] ?? ''),
            'category_id'       => $row['category_id'] ?? null,
            'brand_id'          => $row['brand_id'] ?? null,
            'price'             => $row['price'] ?? null,
            'discount_price'    => $row['discount_price'] ?? null,
            'stock'             => $row['stock'] ?? 0,
            'weight'            => $row['weight'] ?? null,
            'sku'               => $row['sku'] ?? null,
            'short_description' => $row['short_description'] ?? null,
            'description'       => $row['description'] ?? null,
            'is_active'         => $row['is_active'] ?? '1',
            'is_featured'       => $row['is_featured'] ?? '0',
            'image_url'         => $row['image_url'] ?? null,
        ];

        $validator = Validator::make($data, [
            'name'              => 'required|string|max:255',
            'slug'              => 'required|string|max:255',
            'category_id'       => 'required|integer|exists:product_categories,id',
            'brand_id'          => 'nullable|integer|exists:brands,id',
            'price'             => 'required|integer|min:0',
            'discount_price'    => 'nullable|integer|min:0',
            'stock'             => 'required|integer|min:0',
            'weight'            => 'nullable|integer|min:0',
            'sku'               => 'nullable|string|max:100',
            'short_description' => 'nullable|string|max:500',
            'description'       => 'nullable|string|max:5000',
            'is_active'         => 'boolean',
            'is_featured'       => 'boolean',
            'image_url'         => 'nullable|url|max:2048',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors'  => [
                    'row'     => $rowNumber,
                    'message' => $validator->errors()->first(),
                    'details' => $validator->errors()->all(),
                ],
            ];
        }

        $v = $validator->validated();

        return [
            'success' => true,
            'dto'     => new self(
                name: $v['name'],
                slug: $v['slug'],
                categoryId: (int) $v['category_id'],
                brandId: $v['brand_id'] ? (int) $v['brand_id'] : null,
                price: (int) $v['price'],
                discountPrice: $v['discount_price'] ? (int) $v['discount_price'] : null,
                stock: (int) $v['stock'],
                weight: $v['weight'] ? (int) $v['weight'] : null,
                sku: $v['sku'],
                shortDescription: $v['short_description'],
                description: $v['description'],
                isActive: (bool) $v['is_active'],
                isFeatured: (bool) $v['is_featured'],
                imageUrl: $v['image_url'],
            ),
        ];
    }
}
