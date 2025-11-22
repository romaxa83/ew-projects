<?php

namespace App\GraphQL\Types\Enums\Orders\Dealer;

use App\Enums\Orders\Dealer\OrderType;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class OrderTypeTypeEnum extends GenericBaseEnumType
{
    public const NAME = 'DealerOrderEnumType';
    public const ENUM_CLASS = OrderType::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}

