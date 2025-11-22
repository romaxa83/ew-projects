<?php

namespace App\GraphQL\Types\Enums\Orders\Dealer;

use App\Enums\Orders\Dealer\OrderStatus;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class OrderStatusTypeEnum extends GenericBaseEnumType
{
    public const NAME = 'DealerOrderTypeEnumType';
    public const ENUM_CLASS = OrderStatus::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}
