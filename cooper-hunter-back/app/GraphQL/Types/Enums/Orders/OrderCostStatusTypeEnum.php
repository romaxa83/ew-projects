<?php

namespace App\GraphQL\Types\Enums\Orders;

use App\Enums\Orders\OrderCostStatusEnum;
use App\GraphQL\Types\GenericBaseEnumType;

class OrderCostStatusTypeEnum extends GenericBaseEnumType
{

    public const NAME = 'OrderCostStatusTypeEnum';
    public const DESCRIPTION = 'Available order cost statuses';
    public const ENUM_CLASS = OrderCostStatusEnum::class;
}
