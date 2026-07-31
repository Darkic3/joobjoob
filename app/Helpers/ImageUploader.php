<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;

class ImageUploader
{
    public static function upload(string $folderName, UploadedFile $file): ?string
    {
        $baseUpload = config('paths.upload', 'uploads');
        $base_path = public_path("{$baseUpload}/{$folderName}/");
//        $base_path = "/home/exirpd/public_html/{$baseUpload}/{$folderName}/";
        if (!is_dir($base_path)) {
            mkdir($base_path, 0755, true); // 0755 check_later            //fix-later
        }

        $extension = $file->getClientOriginalExtension();
        $originalName = md5(microtime()) . '.' . $extension;
        $originalPath = $base_path . $originalName;

        if (!$file->move($base_path, $originalName)) {
            return null;
        }

        // if format is webp dont format it
        if ($extension === 'webp' || $extension === 'svg' || $extension === 'gif') {
            return $originalName;
        }
        // تبدیل به WebP
        if( $extension != 'svg' || $extension != 'gif'){
            $webpName = pathinfo($originalName, PATHINFO_FILENAME) . '.webp';
        }else{
            $webpName = $originalName;
        }

        $webpPath = $base_path . $webpName;
        try {
            self::convertToWebP($originalPath, $webpPath);
            unlink($originalPath); // فقط WebP
            return $webpName;
        } catch (\Exception $e) {
            unlink($originalPath);
            return null;
        }
    }

    /**
     * @throws \Exception
     */
    private static function convertToWebP(string $inputPath, string $outputPath): void
    {
        $info = getimagesize($inputPath);
        if (!$info) {
            throw new \Exception("فایل تصویری نامعتبر است.");
        }

        $image = match ($info[2]) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($inputPath),
            IMAGETYPE_PNG  => imagecreatefrompng($inputPath),
            default => throw new \Exception("فرمت پشتیبانی نمی‌شود.")
        };

        if (!$image) {
            throw new \Exception("امکان ایجاد شیء تصویر وجود ندارد.");
        }

        if (!imageistruecolor($image)) {
            imagepalettetotruecolor($image);
        }

        imagewebp($image, $outputPath, 90);
        imagedestroy($image);
    }
}
