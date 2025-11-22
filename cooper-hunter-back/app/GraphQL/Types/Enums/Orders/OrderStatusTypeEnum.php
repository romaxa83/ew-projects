<?php

namespace App\GraphQL\Types\Enums\Orders;

use App\Enums\Orders\OrderStatusEnum;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class OrderStatusTypeEnum extends GenericBaseEnumType
{

    public const NAME = 'OrderStatusTypeEnum';
    public const DESCRIPTION = 'Available order statuses';
    public const ENUM_CLASS = OrderStatusEnum::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}
