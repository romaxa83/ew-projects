<?php

namespace App\GraphQL\Types\Enums\Catalog\Solutions;

use App\Enums\Solutions\SolutionTypeEnum;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class SolutionTypeEnumType extends GenericBaseEnumType
{
    public const NAME = 'SolutionTypeEnumType';
    public const ENUM_CLASS = SolutionTypeEnum::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}
