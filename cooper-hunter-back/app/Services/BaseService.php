<?php

namespace App\Services;

use App\Models\BaseModel;

abstract class BaseService
{
    public function __construct()
    {}

    public function createSimpleTranslation(array $translations, $modelTranslation)
    {
        foreach ($translations as $translation){
            dd($translations);
        }
    }
}

