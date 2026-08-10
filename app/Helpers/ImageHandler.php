<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;

class ImageHandler
{
    /**
     * آپلود و رسیز تصویر
     */
    public static function handle(
        string $folderName,
        UploadedFile $file,
        ?array $sizes = null
    ): ?string {
        $fileName = ImageUploader::upload($folderName, $file);

        if (! $fileName || ! $sizes) {
            return $fileName;
        }

        ImageResizer::resize($fileName, $folderName, $sizes);

        return $fileName;
    }

    /**
     * حذف فیزیکی تصویر اصلی و نسخه‌های رسیز شده
     */
    public static function delete(string $folderName, mixed $image, ?array $sizes = null): ?bool
    {
        if (empty($image)) {
            return true;
        }

        $baseUpload = config('paths.upload', 'uploads');
        $basePath = public_path("{$baseUpload}/{$folderName}");

        // حذف تصویر اصلی
        $mainFile = "{$basePath}/{$image}";
        if (is_file($mainFile)) {
            @unlink($mainFile);
        }

        // حذف نسخه‌های رسیز شده (هر کدام در زیرپوشه خودشان)
        if (! empty($sizes)) {
            foreach (array_keys($sizes) as $sizeKey) {
                $resizedFile = "{$basePath}/{$sizeKey}/{$image}";
                if (is_file($resizedFile)) {
                    @unlink($resizedFile);
                }
            }
        }

        return true;
    }
}
