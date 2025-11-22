<?php

namespace App\GraphQL\Types\Enums\Orders;

use App\Enums\Orders\OrderSubscriptionActionEnum;
use App\GraphQL\Types\GenericBaseEnumType;

class OrderSubscriptionActionTypeEnum extends GenericBaseEnumType
{
    public const NAME = 'OrderSubscriptionActionTypeEnum';
    public const DESCRIPTION = '';
    public const ENUM_CLASS = OrderSubscriptionActionEnum::class;
}
