<?php

namespace App\GraphQL\Types\Enums\Catalog\Products;

use App\Enums\Catalog\Products\ProductOwnerType;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class ProductOwnerTypeEnumType extends GenericBaseEnumType
{
    public const NAME = 'ProductOwnerTypeTypeEnumType';
    public const ENUM_CLASS = ProductOwnerType::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}
