<?php

namespace App\GraphQL\Types\Dictionaries;

use App\Models\Dictionaries\VehicleClassTranslate;

class VehicleClassTranslateType extends BaseDictionaryTranslateType
{
    public const NAME = 'VehicleClassTranslateType';
    public const MODEL = VehicleClassTranslate::class;
}
