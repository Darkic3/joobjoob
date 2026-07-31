<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageHandler
{
    public static function handle(
        string       $folderName,
        UploadedFile $file,
        ?array       $sizes = null
    ): ?string
    {
        $fileName = ImageUploader::upload($folderName, $file);

        if (!$fileName || !$sizes) {
            return $fileName;
        }

        ImageResizer::resize($fileName, $folderName, $sizes);
        return $fileName;
    }

    public static function delete(string $folderName, mixed $image, ?array $sizes = null): ?bool
    {
        if (empty($image)) {
            return true;
        }

        Storage::delete("{$folderName}/{$image}");

        if (!empty($sizes)) {
            $filename  = pathinfo($image, PATHINFO_FILENAME);
            $extension = pathinfo($image, PATHINFO_EXTENSION);

            foreach ($sizes as $sizeKey => $value) {
                $resized = "{$filename}_{$sizeKey}.{$extension}";
                Storage::delete("{$folderName}/{$resized}");
            }
        }

        return true;
    }

}
