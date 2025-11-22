<?php

namespace App\GraphQL\Types\Enums\Warranties;

use App\Enums\Warranties\WarrantyType;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class WarrantyTypeEnumType extends GenericBaseEnumType
{
    public const NAME = 'WarrantyTypeEnumType';
    public const ENUM_CLASS = WarrantyType::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}

