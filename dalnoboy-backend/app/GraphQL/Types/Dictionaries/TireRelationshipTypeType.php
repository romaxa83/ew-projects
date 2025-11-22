<?php

namespace App\GraphQL\Types\Dictionaries;

use App\Models\Dictionaries\TireRelationshipType;

class TireRelationshipTypeType extends BaseDictionaryType
{
    public const NAME = 'TireRelationshipTypeType';
    public const MODEL = TireRelationshipType::class;

    protected string $translateTypeClass = TireRelationshipTypeTranslateType::class;
}
