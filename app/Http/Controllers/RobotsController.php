<?php

namespace App\Http\Controllers;

use App\Services\Seo\RobotsBuilder;
use Illuminate\Http\Response;

/**
 * `/robots.txt` داینامیک.
 *
 * فایل استاتیک `public/robots.txt` حذف شد چون دامنهٔ هاردکد `example.com` را به
 * گوگل اعلام می‌کرد، مسیرهای ناموجود (`/profile`، `/register`، `/search`) را
 * می‌بست و از پنل ادمین قابل ویرایش نبود.
 */
class RobotsController extends Controller
{
    public function __construct(private readonly RobotsBuilder $builder) {}

    public function index(): Response
    {
        return response($this->builder->content(), 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
