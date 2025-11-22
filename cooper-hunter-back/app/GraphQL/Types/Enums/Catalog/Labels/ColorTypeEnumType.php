<?php

namespace App\GraphQL\Types\Enums\Catalog\Labels;

use App\Enums\Catalog\Labels\ColorType;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class ColorTypeEnumType extends GenericBaseEnumType
{
    public const NAME = 'ColorTypeEnumType';
    public const ENUM_CLASS = ColorType::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}
