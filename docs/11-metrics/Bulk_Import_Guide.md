# Bulk Product Import Guide

> **Command:** `php artisan products:import {file}`
> **Date:** 2026-09-08
> **Author:** Buffy (Module Code Inspector)

---

## Overview

Import products from a CSV file into the Joobjoob database. Supports:
- **Upsert** — if slug exists, product is updated; otherwise created
- **Image download** — automatic download and WebP conversion via ImageHandler
- **Chunk processing** — memory-efficient for large files
- **Dry-run mode** — validate without inserting
- **Error handling** — continues on row failure, prints summary

---

## CSV Format

### Required Columns

| Column | Type | Description | Example |
|--------|------|-------------|---------|
| `name` | string | Product name (Persian) | `گوشی سامسونگ Galaxy S24` |
| `slug` | string | URL slug (unique) | `samsung-galaxy-s24` |
| `category_id` | integer | Category ID (must exist) | `1` |
| `price` | integer | Price in Toman | `25000000` |
| `stock` | integer | Stock quantity | `50` |

### Optional Columns

| Column | Type | Default | Description |
|--------|------|---------|-------------|
| `brand_id` | integer | `null` | Brand ID (must exist) |
| `discount_price` | integer | `null` | Discounted price in Toman |
| `weight` | integer | `null` | Weight in grams |
| `sku` | string | `null` | Stock Keeping Unit code |
| `short_description` | string | `null` | Short description (max 500 chars) |
| `description` | string | `null` | Full description (max 5000 chars) |
| `is_active` | boolean | `1` | Is product active? |
| `is_featured` | boolean | `0` | Is product featured? |
| `image_url` | string | `null` | Image URL to download |

---

## Sample CSV

```csv
name,slug,category_id,brand_id,price,discount_price,stock,weight,sku,short_description,description,is_active,is_featured,image_url
گوشی سامسونگ Galaxy S24,samsung-galaxy-s24,1,1,25000000,22000000,50,200,SM-S921,گوشی پرچم سامسونگ,توضیحات کامل گوشی Samsung Galaxy S24,1,0,https://example.com/images/s24.jpg
لپ‌تاپ اپل MacBook Pro 14,apple-macbook-pro-14,2,2,85000000,,25,1800,MBP14-M3,لپ‌تاپ حرفه‌ای اپل,MacBook Pro 14 اینچ با تراشه M3,1,1,https://example.com/images/mbp14.jpg
هندزفری بی‌سیم سونی WF-1000XM5,sony-wf1000xm5,3,3,8500000,7500000,100,50,WF1000XM5,هندزفری نویزکنسلینگ سونی, Sony WF-1000XM5 با کیفیت صدای فوق‌العاده,1,0,https://example.com/images/sony-wf.jpg
```

---

## Usage

### Basic Import
```bash
php artisan products:import products.csv
```

### Dry Run (Validate Only)
```bash
php artisan products:import products.csv --dry-run
```

### Custom Chunk Size
```bash
php artisan products:import products.csv --chunk=50
```

### From Different Directory
```bash
# File in storage/app/
php artisan products:import imports/products.csv

# Absolute path
php artisan products:import /home/user/products.csv
```

---

## Image Handling

When `image_url` is provided:
1. Image is downloaded via HTTP (30s timeout)
2. Converted to WebP via `ImageHandler::handle('products', $file)`
3. Stored in `public/uploads/products/`
4. 3 resize variants created: `thumb/`, `medium/`, `large/`

If download fails:
- Product is still created/updated without image
- Warning is logged but process continues

---

## Error Handling

- **Row validation failure:** Row skipped, error logged, continues to next row
- **Database constraint violation:** Row skipped, error logged, continues
- **Image download failure:** Product created without image, warning logged
- **Memory management:** Rows processed in chunks (default 100)

### Sample Output
```
📂 File: /path/to/products.csv
📋 Columns: name, slug, category_id, price, stock, ...
⚙️  Mode: LIVE IMPORT

  ✓ Row 2: VALID — گوشی سامسونگ Galaxy S24
  ✓ Row 3: VALID — لپ‌تاپ اپل MacBook Pro 14
  ⏭ Row 4: SKIP — The category id field is required.
  ✓ Row 5: VALID — هندزفری بی‌سیم سونی WF-1000XM5

═══════════════════════════════════════
📊 Import Summary
═══════════════════════════════════════
✅ Success: 2
❌ Failed:  1

Errors:
  Row 4: The category id field is required.
```

---

## Prerequisites

1. **Categories must exist** — `category_id` references `product_categories.id`
2. **Brands must exist** — `brand_id` references `brands.id` (if provided)
3. **CSV encoding** — UTF-8 recommended for Persian text
4. **Image URLs** — must be publicly accessible HTTP/HTTPS URLs

---

## Performance Notes

- **Chunk processing** — rows processed in batches (default 100) for memory efficiency
- **Upsert** — `updateOrCreate` on slug prevents duplicates
- **Image download** — sequential (one at a time) to avoid rate limiting
- **DB transactions** — each chunk is wrapped in a transaction

### Recommended Chunk Sizes
| File Size | Rows | Recommended Chunk |
|-----------|------|-------------------|
| < 1MB | < 1000 | `--chunk=100` (default) |
| 1-10MB | 1000-10000 | `--chunk=200` |
| > 10MB | > 10000 | `--chunk=500` |

---

## Troubleshooting

### "Missing required columns"
Check CSV headers match exactly: `name, slug, category_id, price, stock`

### "File not found"
File path is resolved in order:
1. `storage/app/{file}`
2. `public/{file}`
3. Absolute path

### "Category/Brand does not exist"
Ensure referenced IDs exist in database before import.

### Memory limit exceeded
Reduce chunk size: `--chunk=50` or increase PHP memory limit.

---

## File Locations

| File | Location |
|------|----------|
| Command | `app/Console/Commands/ImportProductsCommand.php` |
| DTO | `app/DTOs/ProductImportDTO.php` |
| Image Handler | `app/Helpers/ImageHandler.php` |
| Products Model | `app/Models/Product.php` |
