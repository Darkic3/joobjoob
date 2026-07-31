<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class VideoHandler
{
    public static function handle(
        string       $folderName,
        UploadedFile $file
    ): ?string
    {
        return VideoUploader::upload($folderName, $file);
    }

    public static function delete(string $folderName, mixed $videoName): bool
    {
        if (empty($videoName)) {
            return true;
        }

        $baseUpload = config('paths.upload', 'uploads');
        $filePath = public_path("{$baseUpload}/{$folderName}/{$videoName}");
//        $filePath = "/home/exirpd/public_html/{$baseUpload}/{$folderName}/{$videoName}";

        if (File::exists($filePath)) {
            return File::delete($filePath);
        }

        return true;
    }
}
