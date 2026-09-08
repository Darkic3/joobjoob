<?php

namespace App\Console\Commands;

use App\DTOs\ProductImportDTO;
use App\Helpers\ImageHandler;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Brand;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use SplFileObject;

class ImportProductsCommand extends Command
{
    protected $signature = 'products:import
                            {file : Path to CSV file (relative to storage/app or absolute)}
                            {--dry-run : Validate only, do not insert into database}
                            {--chunk=100 : Number of rows to process in each DB chunk}';

    protected $description = 'Import products from a CSV file into the database';

    /** @var int */
    private int $successCount = 0;

    /** @var int */
    private int $failCount = 0;

    /** @var array */
    private array $errors = [];

    public function handle(): int
    {
        $filePath = $this->argument('file');
        $dryRun = $this->option('dry-run');
        $chunkSize = (int) $this->option('chunk');

        // Resolve file path
        $fullPath = $this->resolveFilePath($filePath);

        if (! file_exists($fullPath)) {
            $this->error("❌ File not found: {$fullPath}");

            return self::FAILURE;
        }

        // Verify CSV headers
        $headers = $this->readHeaders($fullPath);

        if (! $headers) {
            $this->error('❌ Could not read CSV headers or file is empty.');

            return self::FAILURE;
        }

        $required = ['name', 'slug', 'category_id', 'price', 'stock'];
        $missing = array_diff($required, $headers);

        if (! empty($missing)) {
            $this->error('❌ Missing required columns: '.implode(', ', $missing));
            $this->newLine();
            $this->info('Required columns: '.implode(', ', $required));
            $this->info('Found columns:    '.implode(', ', $headers));

            return self::FAILURE;
        }

        $this->info('📂 File: '.$fullPath);
        $this->info('📋 Columns: '.implode(', ', $headers));
        $this->info('⚙️  Mode: '.($dryRun ? 'DRY RUN (validation only)' : 'LIVE IMPORT'));
        $this->newLine();

        // Process rows
        $this->processRows($fullPath, $headers, $dryRun, $chunkSize);

        // Print summary
        $this->newLine();
        $this->info('═══════════════════════════════════════');
        $this->info('📊 Import Summary');
        $this->info('═══════════════════════════════════════');
        $this->info("✅ Success: {$this->successCount}");
        $this->info("❌ Failed:  {$this->failCount}");

        if (! empty($this->errors)) {
            $this->newLine();
            $this->warn('Errors:');
            foreach (array_slice($this->errors, 0, 20) as $error) {
                $this->line("  Row {$error['row']}: {$error['message']}");
            }
            if (count($this->errors) > 20) {
                $this->line('  ... and '.(count($this->errors) - 20).' more errors');
            }
        }

        $this->newLine();

        return $this->failCount > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Process CSV rows in chunks for memory efficiency.
     */
    private function processRows(string $filePath, array $headers, bool $dryRun, int $chunkSize): void
    {
        $file = new SplFileObject($filePath);
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::READ_AHEAD);

        // Skip header row
        $file->fgetcsv();

        $batch = [];
        $rowNumber = 1; // data rows start at 1

        while (! $file->eof()) {
            $row = $file->fgetcsv();

            if ($row === false || $row === null || ($row[0] ?? null) === null) {
                continue;
            }

            $rowNumber++;
            $rowData = array_combine($headers, array_pad($row, count($headers), ''));

            // Validate row
            $result = ProductImportDTO::fromRow($rowData, $rowNumber);

            if (! $result['success']) {
                $this->failCount++;
                $this->errors[] = $result['errors'];
                $this->line("  <comment>⏭ Row {$rowNumber}: SKIP — {$result['errors']['message']}</comment>");

                continue;
            }

            if ($dryRun) {
                $this->successCount++;
                $this->line("  <info>✓ Row {$rowNumber}: VALID — {$result['dto']->name}</info>");

                continue;
            }

            $batch[] = $result['dto'];

            // Process batch when chunk size reached
            if (count($batch) >= $chunkSize) {
                $this->processBatch($batch);
                $batch = [];
            }
        }

        // Process remaining rows
        if (! empty($batch)) {
            $this->processBatch($batch);
        }
    }

    /**
     * Process a batch of validated DTOs — insert or update in DB transaction.
     */
    private function processBatch(array $batch): void
    {
        DB::transaction(function () use ($batch) {
            foreach ($batch as $dto) {
                try {
                    $this->importSingle($dto);
                    $this->successCount++;
                } catch (\Throwable $e) {
                    $this->failCount++;
                    $this->errors[] = [
                        'row'     => $this->successCount + $this->failCount,
                        'message' => $e->getMessage(),
                    ];
                    $this->line("  <comment>⏭ Error: {$e->getMessage()}</comment>");
                }
            }
        });
    }

    /**
     * Import a single product from DTO.
     */
    private function importSingle(ProductImportDTO $dto): void
    {
        // Calculate final price
        $finalPrice = ($dto->discountPrice && $dto->discountPrice > 0)
            ? $dto->discountPrice
            : $dto->price;

        // Download and process image if URL provided
        $mainImage = null;
        if ($dto->imageUrl) {
            $mainImage = $this->downloadImage($dto->imageUrl, $dto->slug);
        }

        // Create or update product
        $product = Product::updateOrCreate(
            ['slug' => $dto->slug],
            [
                'product_category_id' => $dto->categoryId,
                'brand_id'            => $dto->brandId,
                'name'                => ['fa' => $dto->name, 'en' => $dto->name],
                'slug'                => $dto->slug,
                'short_description'   => $dto->shortDescription ? ['fa' => $dto->shortDescription, 'en' => $dto->shortDescription] : null,
                'description'         => $dto->description ? ['fa' => $dto->description, 'en' => $dto->description] : null,
                'sku'                 => $dto->sku,
                'price'               => $dto->price,
                'discount_price'      => $dto->discountPrice,
                'final_price'         => $finalPrice,
                'stock'               => $dto->stock,
                'weight'              => $dto->weight,
                'is_active'           => $dto->isActive,
                'is_featured'         => $dto->isFeatured,
                'main_image'          => $mainImage,
            ]
        );

        $this->line("  <info>✓ {$dto->name}</info> (ID: {$product->id})");
    }

    /**
     * Download image from URL and process with ImageHandler.
     */
    private function downloadImage(string $url, string $slug): ?string
    {
        try {
            $response = Http::timeout(30)->get($url);

            if (! $response->successful()) {
                $this->line("    <comment>⚠ Image download failed: HTTP {$response->status()}</comment>");

                return null;
            }

            $contentType = $response->header('Content-Type', '');
            $extension = match (true) {
                str_contains($contentType, 'jpeg') => 'jpg',
                str_contains($contentType, 'png')  => 'png',
                str_contains($contentType, 'webp') => 'webp',
                default                             => 'jpg',
            };

            $tempFile = tempnam(sys_get_temp_dir(), 'import_');
            file_put_contents($tempFile, $response->body());

            $uploadedFile = new \Illuminate\Http\UploadedFile(
                $tempFile,
                $slug.'.'.$extension,
                $contentType,
                null,
                true // test mode — skip file validation
            );

            $imagePath = ImageHandler::handle('products', $uploadedFile);

            // Clean up temp file
            @unlink($tempFile);

            return $imagePath;
        } catch (\Throwable $e) {
            $this->line("    <comment>⚠ Image error: {$e->getMessage()}</comment>");

            return null;
        }
    }

    /**
     * Read CSV headers from file.
     */
    private function readHeaders(string $filePath): ?array
    {
        $file = new SplFileObject($filePath);
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);

        $headers = $file->fgetcsv();

        if (! $headers || empty($headers[0])) {
            return null;
        }

        return array_map('strtolower', array_map('trim', $headers));
    }

    /**
     * Resolve file path — check storage/app first, then absolute path.
     */
    private function resolveFilePath(string $path): string
    {
        // Check storage/app first
        $storagePath = storage_path('app/'.$path);
        if (file_exists($storagePath)) {
            return $storagePath;
        }

        // Check public path
        $publicPath = public_path($path);
        if (file_exists($publicPath)) {
            return $publicPath;
        }

        // Return as-is (absolute path)
        return $path;
    }
}
