<?php

namespace App\GraphQL\Types\Enums\Catalog\Products;

use App\Enums\Catalog\Products\ProductUnitType;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class ProductUnitTypeEnumType extends GenericBaseEnumType
{
    public const NAME = 'ProductUnitTypeTypeEnumType';
    public const ENUM_CLASS = ProductUnitType::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}
