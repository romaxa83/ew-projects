<?php

namespace App\GraphQL\Types\Enums\Orders;

use App\Enums\Orders\OrderFilterTrkNumberExistsEnum;
use App\GraphQL\Types\GenericBaseEnumType;

class OrderFilterTrkNumberExistsTypeEnum extends GenericBaseEnumType
{

    public const NAME = 'OrderFilterTrkNumberExistsTypeEnum';
    public const DESCRIPTION = '';
    public const ENUM_CLASS = OrderFilterTrkNumberExistsEnum::class;

}
