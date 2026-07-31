<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;

class VideoUploader
{
    public static function upload(string $folderName, UploadedFile $file): ?string
    {
        $baseUpload = config('paths.upload', 'uploads');
        $base_path = public_path("{$baseUpload}/{$folderName}/");
//        $base_path = "/home/exirpd/public_html/{$baseUpload}/{$folderName}/";

        if (!is_dir($base_path)) {
            mkdir($base_path, 0755, true);
        }

        $extension = $file->getClientOriginalExtension();

        $allowedExtensions = ['mp4', 'mkv', 'avi', 'mov', 'webm'];
        if (!in_array(strtolower($extension), $allowedExtensions)) {
            return null;
        }

        $fileName = md5(microtime()) . '.' . $extension;

        if (!$file->move($base_path, $fileName)) {
            return null;
        }

        return $fileName;
    }
}
