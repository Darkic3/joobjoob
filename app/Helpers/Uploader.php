<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;

class Uploader
{
    public static function upload(UploadedFile $file, string $path): string
    {
        // نام فایل امن‌سازی می‌شود تا کلاینت نتواند با نامی مثل ../ مسیر خروجی را عوض کند
        $originalName = basename(str_replace('\\', '/', $file->getClientOriginalName()));
        $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName) ?: 'file';
        $fileName = time().'_'.$safeName;
        $file->move(public_path($path), $fileName);

        return $fileName;
    }
}
