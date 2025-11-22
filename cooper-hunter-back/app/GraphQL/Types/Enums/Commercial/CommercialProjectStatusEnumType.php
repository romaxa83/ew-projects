<?php

namespace App\GraphQL\Types\Enums\Commercial;

use App\Enums\Commercial\CommercialProjectStatusEnum;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class CommercialProjectStatusEnumType extends GenericBaseEnumType
{
    public const NAME = 'CommercialProjectStatusEnumType';
    public const ENUM_CLASS = CommercialProjectStatusEnum::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}