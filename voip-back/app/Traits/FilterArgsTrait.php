<?php

namespace App\Traits;

trait FilterArgsTrait
{
    public static function removeNullableArgs(array $args): array
    {
        foreach ($args as $key => $value){
            if(!$value){
                unset($args[$key]);
            }
        }

        return $args;
    }
}
