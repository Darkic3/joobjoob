<?php

namespace App\Helpers;


class Helper
{
    public static function isMobile(): bool
    {
        $userAgent = $_SERVER["HTTP_USER_AGENT"] ?? '';
        $mobileKeywords = array(
            'android', 'iphone', 'ipad', 'blackberry', 'windows phone', 'kindle', 'symbian', 'webos',
            'opera mini', 'opera mobi', 'ucbrowser', 'iemobile', 'palm', 'maemo', 'meego', 'bada',
            'fennec', 'nokia', 'motorola', 'sonyericsson', 'lg', 'samsung', 'htc', 'zte', 'huawei'
        );

        foreach ($mobileKeywords as $keyword) {
            if (stripos($userAgent, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    public static function persian2LatinDigit($string): array|string
    {
        $persian_digits = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
        $arabic_digits = array('٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩');
        $english_digits = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');

        $string = str_replace($persian_digits, $english_digits, $string);
        return str_replace($arabic_digits, $english_digits, $string);
    }

    public static function uploadFile($file, $folder_name, $request): ?string
    {
        if ($request->hasFile($file)) {

            $baseUpload = config('paths.upload');
            $path = $baseUpload . "/" . $folder_name . "/";

            $extension = $request->file($file)->getClientOriginalExtension();
            $fileName = md5(microtime() . ".$extension.");
            $request->file($file)->move($path, $fileName.".".$extension);
            $input[$file] = $fileName;
            return $input[$file] . ".". $extension;

        }

        return null;
    }

    public static function uploadImage($file, $folder_name, $request): ?string
    {
        if ($request->hasFile($file)) {

            $baseUpload = config('paths.upload');

            $base_path = $baseUpload . "/" . $folder_name . '/';
            $path_medium = $base_path . 'medium/';
            $path_big = $base_path . 'big/';
            $path_original = $base_path;

            $realPath = $request->file($file)->getRealPath();

            $extension = $request->file($file)->getClientOriginalExtension();
            $fileName = md5(microtime() . ".$extension.");

            $image_size = getimagesize($request->file($file));
            list($width, $height) = getimagesize($request->file($file));


            $file_path = $path_original . $fileName;
            $request->file($file)->move($path_original, $fileName);

            if (!file_exists($file_path)) {
                dd($file_path, 'این مسیر وجود نداره دوست من ');
            }

            if (!file_exists($path_original)) {
                mkdir($path_original, 0777, true);
            }
            if (!file_exists($path_medium)) {
                mkdir($path_medium, 0777, true);
            }
            if (!file_exists($path_big)) {
                mkdir($path_big, 0777, true);
            }


            switch ($extension) {
                case 'jpg':
                case 'jpeg':
                    $src = imagecreatefromjpeg($file_path);
                    break;
                case 'png':
                    $src = imagecreatefrompng($file_path);
                    break;
                case 'webp':
                    $src = imagecreatefromwebp($file_path);
                    break;
                default:
                    //اینو باید توی ریکوئست هندل کرد! میدونم.
                    return redirect()->back()->with('error', 'این فرمت تصویری پشتیبانی نمی‌شود.');
            }

            $new_height1 = 1000;
            $new_width1 = 1000;
            //اینجا باید عرض عکس رو به نسب طول به عرض عکس اصلی بدست بیاریم که شکل عکس بهم نخوره !
            //$new_width1 = ($height/$width)*$new_height1;
            $tmp1 = imagecreatetruecolor($new_width1, $new_height1);

            $new_height2 = 500;
            $new_width2 = 500;
            //$new_width2 = ($height/$width)*$new_height2;
            $tmp2 = imagecreatetruecolor($new_width2, $new_height2);


            imagecopyresampled($tmp1, $src, 0, 0, 0, 0, $new_width1, $new_height1, $width, $height);
            imagecopyresampled($tmp2, $src, 0, 0, 0, 0, $new_width2, $new_height2, $width, $height);
            $file_name_1 = $path_big . $fileName;
            $file_name_2 = $path_medium . $fileName;

            imagewebp($tmp1, $file_name_1, 100);
            imagewebp($tmp2, $file_name_2, 100);

            imagedestroy($src);
            imagedestroy($tmp2);
            imagedestroy($tmp1);

            $input[$file] = $fileName;

            return $input[$file];
        }

        return null;
    }

}
