<?php

namespace App\GraphQL\Types\Enums\Dictionaries;

use App\Enums\Dictionaries\DictionaryEnum;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class DictionaryEnumType extends GenericBaseEnumType
{
    public const NAME = 'DictionaryEnumType';
    public const ENUM_CLASS = DictionaryEnum::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}
