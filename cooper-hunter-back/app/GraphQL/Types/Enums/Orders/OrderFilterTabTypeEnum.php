<?php

namespace App\GraphQL\Types\Enums\Orders;

use App\Enums\Orders\OrderFilterTabEnum;
use App\GraphQL\Types\GenericBaseEnumType;

class OrderFilterTabTypeEnum extends GenericBaseEnumType
{

    public const NAME = 'OrderFilterTabTypeEnum';
    public const DESCRIPTION = 'Available order tabs in filter';
    public const ENUM_CLASS = OrderFilterTabEnum::class;

}
