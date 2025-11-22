<?php

namespace App\GraphQL\InputTypes\Dictionaries;

class TireRelationshipTypeInputType extends BaseDictionaryInputType
{
    public const NAME = 'TireRelationshipTypeInputType';

    protected string $translateInputTypeClass = TireRelationshipTypeTranslateInputType::class;
}
