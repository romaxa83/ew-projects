<?php

namespace App\GraphQL\Types\Dictionaries;

use App\Models\Dictionaries\RegulationTranslate;

class RegulationTranslateType extends BaseDictionaryTranslateType
{
    public const NAME = 'RegulationTranslateType';
    public const MODEL = RegulationTranslate::class;
}
