<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;

class Uploader
{
    public static function upload(UploadedFile $file, string $path): string
    {
        $fileName = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path($path), $fileName);

        return $fileName;
    }
}
