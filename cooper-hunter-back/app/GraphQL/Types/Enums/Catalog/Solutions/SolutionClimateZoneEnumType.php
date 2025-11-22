<?php

namespace App\GraphQL\Types\Enums\Catalog\Solutions;

use App\Enums\Solutions\SolutionClimateZoneEnum;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class SolutionClimateZoneEnumType extends GenericBaseEnumType
{
    public const NAME = 'SolutionClimateZoneEnumType';
    public const ENUM_CLASS = SolutionClimateZoneEnum::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}
