<?php

namespace App\Services;

use App\Models\City;

class CityService
{
    public static function findByState($stateId)
    {
        return City::orderBy('name', 'ASC')
            ->where('state_id', $stateId)
            ->get();
    }

    public static function findActiveByState($stateId)
    {
        return City::active()
            ->orderBy('name', 'ASC')
            ->where('state_id', $stateId)
            ->get();
    }

    public static function findById($id)
    {
        return City::find($id);
    }
}
