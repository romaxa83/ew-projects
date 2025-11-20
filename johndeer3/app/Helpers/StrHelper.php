<?php

namespace App\Helpers;

class StrHelper
{
    public static function clear(string $str, array $needle = []): string
    {
        foreach ($needle as $search => $replace){
            $str = str_replace($search, $replace, $str);
        }

        return $str;
    }
}
