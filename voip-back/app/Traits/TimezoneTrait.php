<?php

namespace App\Traits;

trait TimezoneTrait
{
    public static function getTimezone(): string
    {
        return request()->header('timezone') ?: config('app.timezone');
    }
}
