<?php

namespace App\Helpers;

class ImageResizer
{
    public static function resize(string $fileName, string $folderName, array $sizes): void
    {
        $baseUpload = config('paths.upload');
        $originalPath = public_path("{$baseUpload}/{$folderName}/{$fileName}");
//        $originalPath = "/home/exirpd/public_html/{$baseUpload}/{$folderName}/{$fileName}";

        if (!file_exists($originalPath)) {
            throw new \Exception("فایل یافت نشد.");
        }

        $src = imagecreatefromwebp($originalPath);
        [$width, $height] = getimagesize($originalPath);

        foreach ($sizes as $sizeFolder => $dimensions) {
            [$newWidth, $newHeight] = $dimensions;
            $sizePath = public_path("{$baseUpload}/{$folderName}/{$sizeFolder}/");
//            $sizePath = "/home/exirpd/public_html/{$baseUpload}/{$folderName}/{$fileName}/";

            if (!is_dir($sizePath)) {
                mkdir($sizePath, 0755, true);
                // fix-later
            }

            $resized = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($resized, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            $outputPath = $sizePath . $fileName;
            imagewebp($resized, $outputPath, 90);

            imagedestroy($resized);
        }

        imagedestroy($src);
    }
}
