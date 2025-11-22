<?php

namespace App\GraphQL\Types\Enums\Inspections;

use App\Enums\Inspections\InspectionModerationEntityEnum;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class InspectionModerationEntityEnumType extends GenericBaseEnumType
{
    public const NAME = 'InspectionModerationEntityEnumType';
    public const ENUM_CLASS = InspectionModerationEntityEnum::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}
