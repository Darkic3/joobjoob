<?php

namespace App\Services;

use App\Models\State;

class StateService
{
    public static function findAll()
    {
        return State::orderBy('name', 'ASC')->get();
    }

    public static function findActive()
    {
        return State::active()->orderBy('name', 'ASC')->get();
    }

    public static function findById($id)
    {
        return State::find($id);
    }
}
