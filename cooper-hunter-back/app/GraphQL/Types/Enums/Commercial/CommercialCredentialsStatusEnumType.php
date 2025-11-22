<?php

namespace App\GraphQL\Types\Enums\Commercial;

use App\Enums\Commercial\CommercialCredentialsStatusEnum;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class CommercialCredentialsStatusEnumType extends GenericBaseEnumType
{
    public const NAME = 'CommercialCredentialsStatusEnumType';
    public const ENUM_CLASS = CommercialCredentialsStatusEnum::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}