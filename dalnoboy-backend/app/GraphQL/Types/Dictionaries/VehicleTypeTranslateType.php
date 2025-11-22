<?php

namespace App\GraphQL\Types\Dictionaries;

use App\Models\Dictionaries\VehicleTypeTranslate;

class VehicleTypeTranslateType extends BaseDictionaryTranslateType
{
    public const NAME = 'VehicleTypeTranslateType';
    public const MODEL = VehicleTypeTranslate::class;
}
