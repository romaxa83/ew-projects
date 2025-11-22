<?php

namespace App\GraphQL\Types\Enums\Payments;

use App\Enums\Payments\PaymentReturnPlatformEnum;
use App\GraphQL\Types\GenericBaseEnumType;

class PaymentReturnPlatformTypeEnum extends GenericBaseEnumType
{

    public const NAME = 'PaymentReturnPlatformTypeEnum';
    public const DESCRIPTION = 'The platform with which the user pays.';
    public const ENUM_CLASS = PaymentReturnPlatformEnum::class;

}
