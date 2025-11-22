<?php

namespace App\GraphQL\Types\Enums\Projects\Systems;

use App\Enums\Projects\Systems\WarrantyStatus;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class WarrantyStatusEnumType extends GenericBaseEnumType
{
    public const NAME = 'WarrantyStatusEnumType';
    public const ENUM_CLASS = WarrantyStatus::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}
