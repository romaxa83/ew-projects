<?php

namespace App\Helpers;

class ParseQueryParams
{
    public static function name(string $name): array
    {
        if(strpos($name, '_')){
            return explode('_', $name);
        }

        return [$name];
    }
}
