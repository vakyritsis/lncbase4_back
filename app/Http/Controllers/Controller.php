<?php

namespace App\Http\Controllers;

abstract class Controller
{
    //
    public function stringToList($input)
    {
        return empty($input) ? [] : explode(',', $input);
    }
}
