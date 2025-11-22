<?php

namespace App\GraphQL\Types\Dictionaries;

use App\Models\Dictionaries\TireType;

class TireTypeType extends BaseDictionaryType
{
    public const NAME = 'TireTypeType';
    public const MODEL = TireType::class;

    protected string $translateTypeClass = TireTypeTranslateType::class;
}
