<?php

namespace App\GraphQL\Types\Enums\Inspections;

use App\Enums\Inspections\TirePhotoType;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class TirePhotoTypeEnumType extends GenericBaseEnumType
{
    public const NAME = 'TirePhotoTypeEnumType';
    public const ENUM_CLASS = TirePhotoType::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}
