<?php

namespace App\GraphQL\Types\Enums\Catalog\Products;

use App\Enums\Catalog\Products\ProductUnitSubType;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class ProductUnitSubTypeEnumType extends GenericBaseEnumType
{
    public const NAME = 'ProductUnitSubTypeTypeEnumType';
    public const ENUM_CLASS = ProductUnitSubType::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}

