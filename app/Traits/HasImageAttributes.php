<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasImageAttributes
{
    protected function makeImageAttribute(string $column, ?string $folder = null, string $size = 'original'): ?string
    {
        $baseUpload = config('paths.upload', 'assets/uploads');
        $value = $this->{$column};

        if (empty($value) || $value === ' ') {
            return null; // asset('assets/admin/images/noimage.png')
        }

        $uploadFolder = $folder ?? Str::kebab(Str::pluralStudly(class_basename($this)));
        if ($size === 'original') {
            $path = "{$baseUpload}/{$uploadFolder}/{$value}";
        } else {
            $path = "{$baseUpload}/{$uploadFolder}/{$size}/{$value}";
        }

        if (! app()->environment('local')) {
            $fullPath = base_path('public_html/'.$path);
        } else {
            $fullPath = public_path($path);
        }

        return file_exists($fullPath) ? asset($path) : null; // asset('assets/admin/images/noimage.png')
    }
}
