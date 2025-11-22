<?php

namespace App\GraphQL\Types\Enums\Commercial\Commissioning;

use App\Enums\Commercial\Commissioning\ProtocolType;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class ProtocolTypeEnumType extends GenericBaseEnumType
{
    public const NAME = 'CommissioningProtocolTypeEnumType';
    public const ENUM_CLASS = ProtocolType::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}
