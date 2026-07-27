<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CkeditorController
{

    public function upload(Request $request)
    {
        if ($request->hasFile('upload')) {
            $file = $request->file('upload');
            $fileName = time() . '_' . $file->getClientOriginalName();

            // ذخیره در پوشه public/uploads/ckeditor
            $path = $file->storeAs('uploads/ckeditor', $fileName, 'public');

            $url = Storage::url($path);

            // فرمت پاسخ مورد نیاز CKEditor
            return response()->json([
                'uploaded' => 1,
                'fileName' => $fileName,
                'url' => $url
            ]);
        }

        return response()->json(['uploaded' => 0, 'error' => ['message' => 'فایل نامعتبر است.']]);
    }
}
