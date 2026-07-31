<?php

namespace App\Services;

use App\Models\HeroSlide;

class HeroSlideService
{

    public static function getSlides()
    {
        return HeroSlide::active()->get();
    }

    public function dashboardIndexContent()
    {
    }
}
