<?php

namespace App\GraphQL\Types\Enums\Catalog\Solutions;

use App\Enums\Solutions\SolutionIndoorEnum;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class SolutionIndoorEnumType extends GenericBaseEnumType
{
    public const NAME = 'SolutionIndoorTypeEnumType';
    public const ENUM_CLASS = SolutionIndoorEnum::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}
