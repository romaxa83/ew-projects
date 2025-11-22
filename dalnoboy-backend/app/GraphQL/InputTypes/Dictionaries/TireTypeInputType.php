<?php

namespace App\GraphQL\InputTypes\Dictionaries;

class TireTypeInputType extends BaseDictionaryInputType
{
    public const NAME = 'TireTypeInputType';

    protected string $translateInputTypeClass = TireTypeTranslateInputType::class;
}
