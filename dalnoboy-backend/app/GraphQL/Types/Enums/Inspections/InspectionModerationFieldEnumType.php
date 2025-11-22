<?php

namespace App\GraphQL\Types\Enums\Inspections;

use App\Enums\Inspections\InspectionModerationFieldEnum;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class InspectionModerationFieldEnumType extends GenericBaseEnumType
{
    public const NAME = 'InspectionModerationFieldEnumType';
    public const ENUM_CLASS = InspectionModerationFieldEnum::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}
