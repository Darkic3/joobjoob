<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ImageHandler;
use Illuminate\Http\Request;

class CkeditorController
{
    public function upload(Request $request)
    {
        if ($request->hasFile('upload')) {
            $file = $request->file('upload');

            // آپلود با هلپر اختصاصی شما (ImageUploader → تبدیل WebP)
            $fileName = ImageHandler::handle('ckeditor', $file);

            if (! $fileName) {
                return response()->json(['uploaded' => 0, 'error' => ['message' => 'آپلود تصویر ناموفق بود.']]);
            }

            // فرمت پاسخ مورد نیاز CKEditor
            return response()->json([
                'uploaded' => 1,
                'fileName' => $fileName,
                'url' => asset(config('paths.upload', 'uploads').'/ckeditor/'.$fileName),
            ]);
        }

        return response()->json(['uploaded' => 0, 'error' => ['message' => 'فایل نامعتبر است.']]);
    }
}
