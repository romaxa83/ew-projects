<?php

namespace App\GraphQL\Types\Enums\Commercial\Commissioning;

use App\Enums\Commercial\Commissioning\ProtocolStatus;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class ProtocolStatusEnumType extends GenericBaseEnumType
{
    public const NAME = 'CommissioningProtocolStatusEnumType';
    public const ENUM_CLASS = ProtocolStatus::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}
