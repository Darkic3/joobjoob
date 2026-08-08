<?php

namespace App\Helpers;

class ImageResizer
{
    /**
     * ساخت نسخه‌های رسیز شده با حفظ نسبت ابعاد (cover-crop)
     *
     * @param string $fileName نام فایل اصلی (webp)
     * @param string $folderName پوشه مقصد، مثلا products
     * @param array  $sizes     [ 'thumb' => [300, 300], 'medium' => [800, 800] ]
     */
    public static function resize(string $fileName, string $folderName, array $sizes): void
    {
        $baseUpload = config('paths.upload', 'uploads');
        $originalPath = public_path("{$baseUpload}/{$folderName}/{$fileName}");

        if (!file_exists($originalPath)) {
            throw new \Exception("فایل یافت نشد.");
        }

        $src = @imagecreatefromwebp($originalPath);
        if (!$src) {
            // فرمت قابل خواندن نیست — رسیز را رد می‌کنیم تا کل آپلود از بین نرود
            return;
        }
        [$width, $height] = getimagesize($originalPath);

        foreach ($sizes as $sizeKey => $dimensions) {
            [$newWidth, $newHeight] = $dimensions;
            $sizePath = public_path("{$baseUpload}/{$folderName}/{$sizeKey}/");

            if (!is_dir($sizePath)) {
                mkdir($sizePath, 0755, true);
            }

            // محاسبه برش مرکزی (cover) برای حفظ نسبت ابعاد
            $srcRatio = $width / $height;
            $targetRatio = $newWidth / $newHeight;

            if ($srcRatio > $targetRatio) {
                // تصویر عریض‌تر از قاب هدف → برش از چپ و راست
                $cropWidth = (int) round($height * $targetRatio);
                $cropHeight = $height;
                $cropX = (int) round(($width - $cropWidth) / 2);
                $cropY = 0;
            } else {
                // تصویر بلندتر از قاب هدف → برش از بالا و پایین
                $cropWidth = $width;
                $cropHeight = (int) round($width / $targetRatio);
                $cropX = 0;
                $cropY = (int) round(($height - $cropHeight) / 2);
            }

            $resized = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled(
                $resized, $src,
                0, 0, $cropX, $cropY,
                $newWidth, $newHeight, $cropWidth, $cropHeight
            );

            imagewebp($resized, $sizePath . $fileName, 90);
            imagedestroy($resized);
        }

        imagedestroy($src);
    }
}
