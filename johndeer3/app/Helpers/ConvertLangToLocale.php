<?php

namespace App\Helpers;

class ConvertLangToLocale
{
    public static function convert($lang): string
    {
        $lang = mb_strtolower($lang);

        $local = [
            "ua" => "ua",
            "cz" => "cs",
            "da" => "dk",
            "et" => "ee",
            "el" => "gr",
            "nn" => "no",
            "sv" => "se"
        ];

        $flip = array_flip($local);

        if(array_key_exists($lang, $flip)){
            return $flip[$lang];
        }


        return $lang;
    }
}
